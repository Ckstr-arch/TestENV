<?php
function entities(): array {
 return [
 'clubs'=>['label'=>'Clubs','fields'=>['name'=>'text','sport'=>'text','description'=>'textarea','coach'=>'text','location'=>'text','capacity'=>'number']],
 'tournaments'=>['label'=>'Tournaments','fields'=>['name'=>'text','sport'=>'text','description'=>'textarea','location'=>'text','starts_at'=>'datetime-local','ends_at'=>'datetime-local','registration_deadline'=>'datetime-local','capacity'=>'number','status'=>['open','ongoing','completed']]],
 'events'=>['label'=>'Training & fixtures','fields'=>['title'=>'text','club_id'=>'clubs','tournament_id'=>'tournaments','kind'=>['training','match'],'location'=>'text','starts_at'=>'datetime-local','ends_at'=>'datetime-local','opponent'=>'optional','home_score'=>'score','away_score'=>'score']],
 'resources'=>['label'=>'Facilities & equipment','fields'=>['name'=>'text','kind'=>['facility','equipment'],'description'=>'textarea','location'=>'text']]
 ];
}
function save_entity(string $entity, int $id): int {
 $spec=entities()[$entity]??null; if (!$spec) throw new DomainException('Invalid resource.');
 $data=[];
 foreach ($spec['fields'] as $field=>$type) {
  if (is_array($type)) $data[$field]=choice($field,$type);
  elseif ($type==='datetime-local') $data[$field]=date_input($field);
  elseif ($type==='number' || $type==='clubs') $data[$field]=positive($field,$type==='number'?10000:PHP_INT_MAX);
  elseif ($type==='tournaments') $data[$field]=empty($_POST[$field])?null:positive($field);
  elseif ($type==='score') { $v=$_POST[$field]??''; $n=filter_var($v,FILTER_VALIDATE_INT); if ($v!=='' && ($n===false || $n<0 || $n>100000)) throw new DomainException('Invalid score.'); $data[$field]=$v===''?null:$n; }
  elseif ($type==='optional') { $data[$field]=trim((string)($_POST[$field]??'')); if (mb_strlen($data[$field])>120) throw new DomainException('Opponent name is too long.'); }
  else $data[$field]=text($field,$type==='textarea'?5000:($field==='name'?120:100));
 }
 if (isset($data['starts_at']) && $data['ends_at']<=$data['starts_at']) throw new DomainException('End time must be after start time.');
 if (isset($data['registration_deadline']) && $data['registration_deadline']>$data['starts_at']) throw new DomainException('Registration must close before the tournament starts.');
 if ($entity==='events') {
  if (!one('SELECT id FROM clubs WHERE id=?',[$data['club_id']])) throw new DomainException('Club does not exist.');
  if ($data['tournament_id'] && !one('SELECT id FROM tournaments WHERE id=?',[$data['tournament_id']])) throw new DomainException('Tournament does not exist.');
  if (($data['home_score']===null)!==($data['away_score']===null)) throw new DomainException('Enter both scores.');
  if ($data['home_score']!==null && ($data['kind']!=='match' || $data['opponent']==='')) throw new DomainException('Scores require a match and an opponent.');
 }
 if ($id) {
  if (!one("SELECT id FROM $entity WHERE id=?",[$id])) throw new DomainException('Record not found.');
  query("UPDATE $entity SET ".implode(',',array_map(fn($k)=>"$k=?",array_keys($data))).' WHERE id=?',[...array_values($data),$id]);
 } else query("INSERT INTO $entity (".implode(',',array_keys($data)).') VALUES('.implode(',',array_fill(0,count($data),'?')).')',array_values($data));
 $savedId=$id ?: (int)db()->lastInsertId();
 audit('save_'.$entity,'Record '.$savedId);
 return $savedId;
}
function reserve(string $table, int $id, int $uid): void {
 db()->beginTransaction();
 try {
  if ($table==='memberships') {
   $c=one('SELECT * FROM clubs WHERE id=? FOR UPDATE',[$id]); if (!$c) throw new DomainException('Club not found.');
   $existing=one('SELECT * FROM memberships WHERE club_id=? AND user_id=?',[$id,$uid]);
   if ($existing && $existing['status']!=='rejected') throw new DomainException('You already have a membership request.');
   if ((int)query("SELECT COUNT(*) FROM memberships WHERE club_id=? AND status='approved'",[$id])->fetchColumn()>=$c['capacity']) throw new DomainException('This club is full.');
   query("INSERT INTO memberships(user_id,club_id) VALUES(?,?) ON DUPLICATE KEY UPDATE status='pending'",[$uid,$id]);
  } elseif ($table==='registrations') {
   $t=one('SELECT * FROM tournaments WHERE id=? FOR UPDATE',[$id]);
   if (!$t || $t['status']!=='open' || strtotime($t['registration_deadline'])<time()) throw new DomainException('Registration is closed.');
   if (one('SELECT id FROM registrations WHERE user_id=? AND tournament_id=?',[$uid,$id])) throw new DomainException('You are already registered.');
   if ((int)query('SELECT COUNT(*) FROM registrations WHERE tournament_id=?',[$id])->fetchColumn()>=$t['capacity']) throw new DomainException('Tournament is full.');
   query('INSERT INTO registrations(user_id,tournament_id) VALUES(?,?)',[$uid,$id]);
  } else {
   $ev=one('SELECT * FROM events WHERE id=? FOR UPDATE',[$id]);
   if (!$ev || strtotime($ev['starts_at'])<time()) throw new DomainException('This session has already started.');
   if (!one("SELECT id FROM memberships WHERE user_id=? AND club_id=? AND status='approved'",[$uid,$ev['club_id']])) throw new DomainException('An approved club membership is required.');
   if (one('SELECT id FROM attendance WHERE user_id=? AND event_id=?',[$uid,$id])) throw new DomainException('You are already registered.');
   query('INSERT INTO attendance(user_id,event_id) VALUES(?,?)',[$uid,$id]);
  }
  audit('reserve_'.$table,'Record '.$id); db()->commit();
 } catch (Throwable $e) { if (db()->inTransaction()) db()->rollBack(); throw $e; }
}
function review_request(string $entity, int $id, string $status): void {
 if (!in_array($entity,['memberships','bookings'],true) || !in_array($status,['approved','rejected'],true)) throw new DomainException('Invalid review action.');
 db()->beginTransaction();
 try {
  $r=one("SELECT * FROM $entity WHERE id=?",[$id]); if (!$r || $r['status']!=='pending') throw new DomainException('Request is no longer pending.');
  if ($entity==='memberships') {
   $c=one('SELECT * FROM clubs WHERE id=? FOR UPDATE',[$r['club_id']]);
   if ($status==='approved' && (int)query("SELECT COUNT(*) FROM memberships WHERE club_id=? AND status='approved'",[$r['club_id']])->fetchColumn()>=$c['capacity']) throw new DomainException('Club is full.');
  } else {
   one('SELECT id FROM resources WHERE id=? FOR UPDATE',[$r['resource_id']]);
   if ($status==='approved' && one("SELECT id FROM bookings WHERE resource_id=? AND status='approved' AND starts_at<? AND ends_at>? AND id<>?",[$r['resource_id'],$r['ends_at'],$r['starts_at'],$id])) throw new DomainException('This booking overlaps an approved reservation.');
  }
  $r=one("SELECT * FROM $entity WHERE id=? FOR UPDATE",[$id]); if ($r['status']!=='pending') throw new DomainException('Request is no longer pending.');
  query("UPDATE $entity SET status=? WHERE id=?",[$status,$id]); audit('review_'.$entity,$id.': '.$status); db()->commit();
 } catch (Throwable $e) { if (db()->inTransaction()) db()->rollBack(); throw $e; }
}
