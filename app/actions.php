<?php
require_once __DIR__.'/models.php';
require_once __DIR__.'/community.php';
function action_result(string $redirect, string $message='', array $data=[]): array {
 return ['redirect'=>$redirect,'message'=>$message,'data'=>$data];
}
function handle_action(): never {
 check_csrf();
 $result=perform_action((string)($_POST['action']??''));
 if ($result['message']!=='') flash($result['message']);
 go($result['redirect']);
}
function perform_action(string $action): array {
 if (str_starts_with($action,'community_')) return community_action($action);
 if ($action==='moderate_post') return moderate_post();
 if ($action==='register') {
  $name=text('name',100); $email=strtolower(text('email',190)); $sid=text('student_id',30); $faculty=text('faculty',80); $password=(string)($_POST['password']??'');
  if (!filter_var($email,FILTER_VALIDATE_EMAIL) || !preg_match('/^[A-Za-z0-9-]{3,30}$/D',$sid)) throw new DomainException('Enter a valid email and student ID.');
  if (strlen($password)<12 || strlen($password)>128) throw new DomainException('Use a password between 12 and 128 characters.');
  throttle('register',$email);
  if (one('SELECT id FROM users WHERE email=? OR student_id=?',[$email,$sid])) throw new DomainException('Email or student ID is already registered.');
  query('INSERT INTO users(name,email,student_id,faculty,password_hash) VALUES(?,?,?,?,?)',[$name,$email,$sid,$faculty,password_hash($password,PASSWORD_ARGON2ID)]);
  $createdId=(int)db()->lastInsertId();
  return action_result('login','Account created. Sign in to get started.',['id'=>$createdId]);
 }
 if ($action==='login') {
  $email=strtolower(text('email',190)); $bucket=throttle('login',$email); $u=one('SELECT * FROM users WHERE email=?',[$email]);
  $dummy='$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi';
  if (!password_verify((string)($_POST['password']??''),$u['password_hash']??$dummy) || !$u) throw new RequestError(defined('REST_REQUEST')?401:422,'Email or password is incorrect.');
  query('DELETE FROM login_attempts WHERE bucket=?',[$bucket]);
  if (password_needs_rehash($u['password_hash'],PASSWORD_ARGON2ID)) query('UPDATE users SET password_hash=? WHERE id=?',[password_hash((string)$_POST['password'],PASSWORD_ARGON2ID),$u['id']]);
  if ($u['totp_secret']) { session_regenerate_id(true); $_SESSION=['pending_user'=>(int)$u['id'],'pending_until'=>time()+300]; return action_result('verify','', ['requires_2fa'=>true]); }
  return finish_login($u);
 }
 if ($action==='verify') {
  if (empty($_SESSION['pending_user']) || ($_SESSION['pending_until']??0)<time()) { unset($_SESSION['pending_user']); if (defined('REST_REQUEST')) throw new RequestError(401,'The sign-in challenge expired. Sign in again.'); return action_result('login','Sign in again to continue.'); }
  $u=one('SELECT * FROM users WHERE id=?',[$_SESSION['pending_user']]); throttle('2fa',(string)$_SESSION['pending_user']);
  if (!$u || !verify_second_factor($u,trim((string)($_POST['code']??'')))) throw new RequestError(defined('REST_REQUEST')?401:422,'Invalid or already used code.'); return finish_login($u);
 }
 $u=require_user();
 if ($action==='logout') { audit('logout','Signed out'); $_SESSION=[]; session_regenerate_id(true); return action_result('home','Signed out.'); }
 if ($action==='setup_2fa') { if ($u['totp_secret']) throw new DomainException('2FA is already enabled.'); $_SESSION['setup_secret']=base32_encode(random_bytes(20)); $_SESSION['setup_until']=time()+600; return action_result('security'); }
 if ($action==='enable_2fa') {
  throttle('2fa_setup',(string)$u['id']);
  if (!password_verify((string)($_POST['password']??''),$u['password_hash'])) throw new DomainException('Password is incorrect.');
  $secret=$_SESSION['setup_secret']??'';
  $counter=$secret && ($_SESSION['setup_until']??0)>=time()?totp_counter($secret,trim((string)($_POST['code']??''))):null;
  if ($counter===null || $u['totp_secret']) throw new DomainException('Setup expired or authenticator code is incorrect.');
  db()->beginTransaction();
  try {
   query('UPDATE users SET totp_secret=?,totp_last_counter=? WHERE id=?',[encrypt_secret($secret),$counter,$u['id']]);
   query('DELETE FROM recovery_codes WHERE user_id=?',[$u['id']]); $codes=[];
   for ($i=0;$i<8;$i++) { $code=bin2hex(random_bytes(8)); $codes[]=$code; query('INSERT INTO recovery_codes(user_id,code_hash) VALUES(?,?)',[$u['id'],password_hash($code,PASSWORD_ARGON2ID)]); }
   audit('2fa_enabled','Authenticator enrolled'); db()->commit();
  } catch (Throwable $e) { if (db()->inTransaction()) db()->rollBack(); throw $e; }
  $_SESSION['recovery_codes']=$codes; unset($_SESSION['setup_secret'],$_SESSION['setup_until']); return action_result('security','2FA enabled. Save your recovery codes now.',['recovery_codes'=>$codes]);
 }
 if ($action==='disable_2fa') {
  throttle('2fa_disable',(string)$u['id']);
  if (!password_verify((string)($_POST['password']??''),$u['password_hash']) || !verify_second_factor($u,trim((string)($_POST['code']??'')))) throw new DomainException('Password or second factor is incorrect.');
  query('UPDATE users SET totp_secret=NULL,totp_last_counter=NULL WHERE id=?',[$u['id']]); query('DELETE FROM recovery_codes WHERE user_id=?',[$u['id']]); audit('2fa_disabled','Authenticator removed'); return action_result('security','2FA disabled.');
 }
 if (in_array($action,['join','register_tournament','attend'],true)) { reserve(['join'=>'memberships','register_tournament'=>'registrations','attend'=>'attendance'][$action],positive('id'),(int)$u['id']); return action_result('dashboard','Your request has been saved.'); }
 if ($action==='book') {
  $resource=positive('resource_id'); $start=date_input('starts_at'); $end=date_input('ends_at'); $purpose=text('purpose',255);
  if (strtotime($start)<=time() || $end<=$start || strtotime($end)-strtotime($start)>4*3600) throw new DomainException('Choose a future booking of up to four hours.');
  db()->beginTransaction();
  try {
   if (!one('SELECT id FROM resources WHERE id=? FOR UPDATE',[$resource])) throw new DomainException('Resource not found.');
   if (one("SELECT id FROM bookings WHERE resource_id=? AND status IN ('pending','approved') AND starts_at<? AND ends_at>?",[$resource,$end,$start])) throw new DomainException('This time slot is unavailable.');
   query('INSERT INTO bookings(user_id,resource_id,starts_at,ends_at,purpose) VALUES(?,?,?,?,?)',[$u['id'],$resource,$start,$end,$purpose]); audit('booking_created','Resource '.$resource); db()->commit();
  } catch (Throwable $e) { if (db()->inTransaction()) db()->rollBack(); throw $e; }
  return action_result('dashboard','Booking submitted for approval.');
 }
 if ($action==='cancel') {
  $entity=choice('entity',['memberships','registrations','attendance','bookings']); $id=positive('id');
  $record=one("SELECT * FROM $entity WHERE id=? AND user_id=?",[$id,$u['id']]);
  if (!$record) throw new RequestError(404,'Participation record not found.');
  if ($entity==='bookings') {
   if (!in_array($record['status'],['pending','approved'],true) || strtotime($record['starts_at'])<=time()) throw new RequestError(409,'This booking cannot be cancelled.');
   query("UPDATE bookings SET status='cancelled' WHERE id=? AND user_id=?",[$id,$u['id']]);
  } else {
   if ($entity==='attendance') {
    $event=one('SELECT starts_at FROM events WHERE id=?',[$record['event_id']]);
    if ($record['status']==='attended' || !$event || strtotime($event['starts_at'])<=time()) throw new RequestError(409,'Completed participation cannot be cancelled.');
   }
   if ($entity==='registrations') {
    $tournament=one('SELECT starts_at FROM tournaments WHERE id=?',[$record['tournament_id']]);
    if (!$tournament || strtotime($tournament['starts_at'])<=time()) throw new RequestError(409,'A started tournament registration cannot be cancelled.');
   }
   query("DELETE FROM $entity WHERE id=? AND user_id=?",[$id,$u['id']]);
  }
  audit('cancel_'.$entity,'Record '.$id); return action_result('dashboard','Your participation has been updated.');
 }
 require_admin();
 if ($action==='save') { $entity=choice('entity',array_keys(entities())); $savedId=save_entity($entity,(int)($_POST['id']??0)); return action_result('manage_'.$entity,'Record saved.',['id'=>$savedId]); }
 if ($action==='delete') { $entity=choice('entity',array_keys(entities())); $id=positive('id'); query("DELETE FROM $entity WHERE id=?",[$id]); audit('delete_'.$entity,'Record '.$id); return action_result('manage_'.$entity,'Record deleted, including linked records where applicable.'); }
 if ($action==='review') { review_request(choice('entity',['memberships','bookings']),positive('id'),choice('status',['approved','rejected'])); return action_result('approvals','Request reviewed.'); }
 if ($action==='attendance') {
  $record=one('SELECT a.id,e.starts_at FROM attendance a JOIN events e ON e.id=a.event_id WHERE a.id=?',[positive('id')]);
  if (!$record || strtotime($record['starts_at'])>time()) throw new DomainException('Attendance can only be recorded after a session starts.');
  query("UPDATE attendance SET status='attended' WHERE id=?",[positive('id')]); audit('attendance_recorded','Record '.$_POST['id']); return action_result('reports','Attendance recorded.'); }
 throw new DomainException('Unknown action.');
}
