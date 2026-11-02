<?php
declare(strict_types=1);

// Optional, local-only provisioning. Never expose this script over HTTP.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once __DIR__ . '/../app/bootstrap.php';
if (config('APP_ENV') !== 'development' || !in_array('--confirm-demo', $argv, true)) {
    fwrite(STDERR, "Local demo accounts require APP_ENV=development and --confirm-demo. No changes made.\n");
    exit(1);
}

$privateDirectory = ROOT . '/var';
$credentialPath = $privateDirectory . '/demo-credentials.json';
if (is_link($privateDirectory)) {
    fwrite(STDERR, "Private demo storage must be a normal directory. No changes made.\n");
    exit(1);
}
if (!is_dir($privateDirectory) && !mkdir($privateDirectory, 0700, true) && !is_dir($privateDirectory)) {
    fwrite(STDERR, "Cannot prepare private demo storage. No changes made.\n");
    exit(1);
}
if (file_exists($credentialPath) || is_link($credentialPath)) {
    // Deliberately preserve previously generated passwords, 2FA and identities.
    echo "Existing private demo record preserved; provisioning skipped: var/demo-credentials.json\n";
    exit(0);
}

// Validate encryption before creating accounts or reserving the private file.
$athleteSecret = base32_encode(random_bytes(20));
$encryptedAthleteSecret = encrypt_secret($athleteSecret);
$oldMask = umask(0077);
$record = fopen($credentialPath, 'x');
umask($oldMask);
if ($record === false) {
    fwrite(STDERR, "Cannot exclusively create the private demo record. No changes made.\n");
    exit(1);
}
// The creation mask protects new files; chmod additionally verifies POSIX hosts.
if (PHP_OS_FAMILY !== 'Windows' && !chmod($credentialPath, 0600)) {
    fclose($record);
    unlink($credentialPath); // This run created the file; no existing file is removed.
    fwrite(STDERR, "Cannot restrict private demo record permissions. No changes made.\n");
    exit(1);
}

$accounts = [
    ['Local Admin', 'admin@backup.sportshub.test', null, 'Sports office', 'admin', false],
    ['Local Student', 'student@backup.sportshub.test', 'BACKUP-DEMO-001', 'Computing', 'student', false],
    ['Local Athlete (2FA)', 'athlete@backup.sportshub.test', 'BACKUP-DEMO-002', 'Business', 'student', true],
];
$credentials = [];
$transactionStarted = false;
try {
    db()->beginTransaction();
    $transactionStarted = true;
    $club = one('SELECT id FROM clubs ORDER BY id LIMIT 1');
    foreach ($accounts as [$name, $email, $studentId, $faculty, $role, $twoFactor]) {
        // Existing accounts are never reset, overwritten, or enrolled silently.
        if (one('SELECT id FROM users WHERE email=? OR student_id=?', [$email, $studentId])) continue;
        $password = bin2hex(random_bytes(18));
        query(
            'INSERT INTO users(name,email,student_id,faculty,password_hash,role,totp_secret) VALUES(?,?,?,?,?,?,?)',
            [$name, $email, $studentId, $faculty, password_hash($password, PASSWORD_ARGON2ID), $role, $twoFactor ? $encryptedAthleteSecret : null]
        );
        $userId = (int) db()->lastInsertId();
        $membershipStatus = $twoFactor ? 'approved' : 'pending';
        if ($role === 'student' && $club !== null) {
            query('INSERT INTO memberships(user_id,club_id,status) VALUES(?,?,?)', [$userId, $club['id'], $membershipStatus]);
        }
        $credentials[] = [
            'role' => $role,
            'email' => $email,
            'password' => $password,
            'authenticator_secret' => $twoFactor ? $athleteSecret : null,
            'authenticator_settings' => $twoFactor ? ['algorithm' => 'SHA1', 'digits' => 6, 'period' => 30] : null,
            'membership_club_id' => $role === 'student' && $club !== null ? (int) $club['id'] : null,
            'membership_status' => $role === 'student' && $club !== null ? $membershipStatus : null,
        ];
    }
    $json = json_encode([
        'notice' => 'PRIVATE DEVELOPMENT-ONLY CREDENTIALS. Never publish, commit, screenshot, or deploy this file. Existing identities were preserved.',
        'accounts' => $credentials,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
    $written = fwrite($record, $json);
    if ($written !== strlen($json) || !fflush($record)) throw new RuntimeException('Private demo record could not be saved completely.');
    db()->commit();
    $transactionStarted = false;
    fclose($record);
    echo "Local-only demo credentials saved privately: var/demo-credentials.json\n";
} catch (Throwable $exception) {
    if ($transactionStarted && db()->inTransaction()) db()->rollBack();
    if (is_resource($record)) fclose($record);
    unlink($credentialPath); // Remove only the new, incomplete record created above.
    fwrite(STDERR, "Demo provisioning failed; new account changes were rolled back. Check local configuration privately.\n");
    exit(1);
}
