<?php
declare(strict_types=1);
/** Destructive ONLY to the explicitly named disposable test schema. Never a public endpoint. */
function initialize_test_database(string $password): array {
    global $argv;
    if (PHP_SAPI !== 'cli' || !in_array('--confirm-test-reset', $argv ?? [], true)) {
        throw new RuntimeException('Test fixtures require CLI and --confirm-test-reset.');
    }
    if (config('DB_NAME') !== 'sportshub_backup_test' || config('APP_ENV') !== 'development') {
        throw new RuntimeException('Fixtures require DB_NAME=sportshub_backup_test and APP_ENV=development.');
    }
    if ((string)query('SELECT DATABASE()')->fetchColumn() !== 'sportshub_backup_test') {
        throw new RuntimeException('Connected database does not match the disposable test schema.');
    }
    $version = (string)query('SELECT VERSION()')->fetchColumn();
    if (stripos($version, 'mariadb') !== false || version_compare($version, '8.0', '<')) {
        throw new RuntimeException('Verification requires actual MySQL 8+, not MariaDB.');
    }
    // The exact schema, mode, actual connection and explicit CLI flag have all been verified.
    db()->exec('SET FOREIGN_KEY_CHECKS=0');
    try {
        foreach (rows('SHOW TABLES') as $record) {
            $table = (string)array_values($record)[0];
            if (!preg_match('/^[A-Za-z0-9_]+$/D', $table)) throw new RuntimeException('Unexpected test table identifier.');
            db()->exec('DROP TABLE `'.$table.'`');
        }
    } finally { db()->exec('SET FOREIGN_KEY_CHECKS=1'); }
    require __DIR__.'/../bin/migrate.php';
    query('INSERT INTO users(name,email,password_hash,role) VALUES(?,?,?,"admin")',
        ['Verification Administrator','admin@example.invalid',password_hash($password,PASSWORD_ARGON2ID)]);
    $admin = (int)db()->lastInsertId();
    query('INSERT INTO users(name,email,student_id,faculty,password_hash) VALUES(?,?,?,?,?)',
        ['Other Synthetic Student','other@example.invalid','VERIFY-OTHER','Computing',password_hash($password,PASSWORD_ARGON2ID)]);
    $other = (int)db()->lastInsertId();
    query('INSERT INTO clubs(name,sport,description,coach,location,capacity) VALUES(?,?,?,?,?,?)',
        ['Verification Tennis','Tennis','Synthetic verification club','Verification Coach','Practice Court',20]);
    $club = (int)db()->lastInsertId();
    query('INSERT INTO tournaments(name,sport,description,location,starts_at,ends_at,registration_deadline,capacity) VALUES(?,?,?,?,?,?,?,?)',
        ['Verification Cup','Tennis','Synthetic verification cup','Practice Court',date('Y-m-d H:i:s',time()+20*86400),date('Y-m-d H:i:s',time()+21*86400),date('Y-m-d H:i:s',time()+15*86400),10]);
    $tournament = (int)db()->lastInsertId();
    query('INSERT INTO events(club_id,title,kind,location,starts_at,ends_at) VALUES(?,?,"training",?,?,?)',
        [$club,'Future verification training','Practice Court',date('Y-m-d H:i:s',time()+3*86400),date('Y-m-d H:i:s',time()+3*86400+3600)]);
    $future = (int)db()->lastInsertId();
    query('INSERT INTO events(club_id,title,kind,location,starts_at,ends_at) VALUES(?,?,"training",?,?,?)',
        [$club,'Completed verification training','Practice Court',date('Y-m-d H:i:s',time()-3*86400),date('Y-m-d H:i:s',time()-3*86400+3600)]);
    $past = (int)db()->lastInsertId();
    query('INSERT INTO resources(name,kind,description,location) VALUES(?,"facility",?,?)',
        ['Verification Court','Synthetic reservable unit','Practice Zone']);
    return ['admin'=>$admin,'other'=>$other,'club'=>$club,'tournament'=>$tournament,'future_event'=>$future,'past_event'=>$past,'resource'=>(int)db()->lastInsertId()];
}
if (realpath((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__) {
    fwrite(STDERR,"Use php tests/integration.php --confirm-test-reset after verifying the disposable test database. Fixtures are not a standalone account provisioning tool.\n");
    exit(1);
}
