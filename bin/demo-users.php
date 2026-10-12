<?php
declare(strict_types=1);
require_once __DIR__.'/../app/bootstrap.php';
if (PHP_SAPI!=='cli') exit;
if (config('APP_ENV')!=='development' || !in_array('--confirm-demo',$argv,true)) {
 fwrite(STDERR,"Demo accounts require APP_ENV=development and --confirm-demo. Never use demo credentials on a public deployment.\n"); exit(1);
}
$accounts=[
 ['Demo Admin','admin@sportshub.test',null,'Sports office','AdminSport!2026','admin',false],
 ['Demo Student','student@sportshub.test','DEMO-STU-001','Computing','StudentSport!2026','student',false],
 ['Demo Athlete (2FA)','athlete@sportshub.test','DEMO-STU-002','Business','AthleteSport!2026','student',true],
];
$secret='JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP';
// Validate encryption configuration before any account is inserted.
$encrypted=encrypt_secret($secret);
db()->beginTransaction();
try {
 foreach ($accounts as [$name,$email,$studentId,$faculty,$password,$role,$twoFactor]) {
  if (one('SELECT id FROM users WHERE email=? OR student_id=?',[$email,$studentId])) { echo "Existing demo identity skipped: $email (password and 2FA preserved).\n"; continue; }
  query('INSERT INTO users(name,email,student_id,faculty,password_hash,role,totp_secret) VALUES(?,?,?,?,?,?,?)',[$name,$email,$studentId,$faculty,password_hash($password,PASSWORD_ARGON2ID),$role,$twoFactor?$encrypted:null]);
  $uid=(int)db()->lastInsertId();
  if ($role==='student') {
   $club=one('SELECT id FROM clubs ORDER BY id LIMIT 1');
   if ($club) query('INSERT INTO memberships(user_id,club_id,status) VALUES(?,?,?)',[$uid,$club['id'],$twoFactor?'approved':'pending']);
  }
  echo "Created disposable demo account: $email\n";
 }
 db()->commit();
} catch (Throwable $ex) { if (db()->inTransaction()) db()->rollBack(); throw $ex; }
echo "Credentials and the manual authenticator setup key are documented in docs/local-setup.md. These known demo credentials are for local testing only.\n";
