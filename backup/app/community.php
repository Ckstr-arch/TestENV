<?php
declare(strict_types=1);
function club_access(int $clubId, ?array $u=null): array {
 $u ??= require_user();
 $club=one('SELECT * FROM clubs WHERE id=?',[$clubId]);
 if (!$club) throw new RequestError(404,'Club not found.');
 if ($u['role']!=='admin' && !one('SELECT id FROM memberships WHERE club_id=? AND user_id=? AND status="approved"',[$clubId,$u['id']])) throw new RequestError(403,'An approved club membership is required for this community.');
 return $club;
}
function community_post(int $clubId,int $postId,bool $allowHidden=false,bool $lock=false): array {
 $post=one('SELECT * FROM club_posts WHERE id=? AND club_id=?'.($lock?' FOR UPDATE':''),[$postId,$clubId]);
 if (!$post || $post['status']==='deleted' || (!$allowHidden && $post['status']!=='visible')) throw new RequestError(404,'Post not found.');
 if (!$lock) $post+=one('SELECT name author,faculty FROM users WHERE id=?',[$post['user_id']])??[];
 return $post;
}
function community_body(string $field='body',int $max=5000): string { return text($field,$max); }
function community_summary(int $clubId): array {
 return one('SELECT (SELECT COUNT(*) FROM memberships WHERE club_id=? AND status="approved") members,(SELECT COUNT(*) FROM club_posts WHERE club_id=? AND status="visible") posts,(SELECT COUNT(*) FROM events WHERE club_id=? AND starts_at>NOW()) upcoming',[$clubId,$clubId,$clubId]);
}
function community_posts(int $clubId,int $uid,int $limit=20,int $offset=0): array {
 return rows('SELECT p.id,p.club_id,p.user_id,p.body,p.created_at,p.updated_at,u.name author,u.faculty,(SELECT COUNT(*) FROM club_reactions r WHERE r.post_id=p.id) reactions,(SELECT COUNT(*) FROM club_comments c WHERE c.post_id=p.id) comments,EXISTS(SELECT 1 FROM club_reactions r WHERE r.post_id=p.id AND r.user_id=?) reacted FROM club_posts p JOIN users u ON u.id=p.user_id WHERE p.club_id=? AND p.status="visible" ORDER BY p.id DESC LIMIT ? OFFSET ?',[$uid,$clubId,$limit,$offset]);
}
function community_action(string $action): array {
 // Visibility checks must see the latest committed moderation/deletion after waiting for a parent lock.
 db()->exec('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
 db()->beginTransaction();
 try {
  $result=perform_community_action($action); db()->commit(); return $result;
 } catch (Throwable $ex) { if (db()->inTransaction()) db()->rollBack(); throw $ex; }
}
function perform_community_action(string $action): array {
 $u=require_user(); $clubId=positive('club_id'); $club=club_access($clubId,$u);
 $redirect='club&club_id='.$clubId.'&tab=community';
 $id=$action==='community_post'?0:positive('post_id');
 if ($action==='community_post') {
  $body=community_body(); throttle('community_post',(string)$u['id']);
  query('INSERT INTO club_posts(club_id,user_id,body) VALUES(?,?,?)',[$clubId,$u['id'],$body]);
  $created=(int)db()->lastInsertId(); audit('community_post','Club '.$clubId.', post '.$created);
  return action_result($redirect,'Post shared with your club.',['id'=>$created]);
 }
 $post=community_post($clubId,$id,$action==='community_delete' || $action==='community_edit',true);
 if (in_array($action,['community_edit','community_delete'],true)) {
  if ((int)$post['user_id']!==(int)$u['id'] && !($action==='community_delete' && $u['role']==='admin')) throw new RequestError(403,'You can only edit your own posts.');
  if ($action==='community_edit') {
   $body=community_body(); throttle('community_edit',(string)$u['id']);
   query('UPDATE club_posts SET body=? WHERE id=?',[$body,$id]);
  } else {
   query('UPDATE club_posts SET body="",status="deleted",moderation_reason="" WHERE id=?',[$id]);
    query('DELETE FROM club_comments WHERE post_id=?',[$id]); query('DELETE FROM club_reactions WHERE post_id=?',[$id]); query('DELETE FROM club_reports WHERE post_id=?',[$id]);
  }
  audit($action,'Post '.$id);
  return action_result($redirect,'Post updated.',['id'=>$id]);
 }
 if ($action==='community_comment') {
  $body=community_body('body',2000); throttle('community_comment',(string)$u['id']);
  query('INSERT INTO club_comments(post_id,user_id,body) VALUES(?,?,?)',[$id,$u['id'],$body]); $created=(int)db()->lastInsertId();
  return action_result($redirect,'Comment added.',['id'=>$created]);
 }
 if ($action==='community_comment_delete') {
  $commentId=positive('comment_id'); $comment=one('SELECT * FROM club_comments WHERE id=? AND post_id=?',[$commentId,$id]);
  if (!$comment) throw new RequestError(404,'Comment not found.');
  if ((int)$comment['user_id']!==(int)$u['id'] && $u['role']!=='admin') throw new RequestError(403,'You can only remove your own comments.');
  query('DELETE FROM club_comments WHERE id=?',[$commentId]); audit('community_comment_delete','Comment '.$commentId);
  return action_result($redirect,'Comment removed.');
 }
 if ($action==='community_react' || $action==='community_unreact') {
  if ($action==='community_react') query('INSERT IGNORE INTO club_reactions(post_id,user_id) VALUES(?,?)',[$id,$u['id']]);
  else query('DELETE FROM club_reactions WHERE post_id=? AND user_id=?',[$id,$u['id']]);
  return action_result($redirect,'Reaction updated.',['reacted'=>$action==='community_react','reactions'=>(int)query('SELECT COUNT(*) FROM club_reactions WHERE post_id=?',[$id])->fetchColumn()]);
 }
 if ($action==='community_report') {
  $reason=community_body('reason',500); throttle('community_report',(string)$u['id']);
  if (one('SELECT id FROM club_reports WHERE post_id=? AND user_id=?',[$id,$u['id']])) throw new RequestError(409,'You have already reported this post.');
  query('INSERT INTO club_reports(post_id,user_id,reason) VALUES(?,?,?)',[$id,$u['id'],$reason]);
  return action_result($redirect,'Report sent privately to the sports office.');
 }
 throw new RequestError(422,'Invalid community action.');
}
function moderate_post(): array {
 require_admin(); $id=positive('post_id'); $status=choice('status',['visible','hidden']); $reason=text('reason',500);
 db()->beginTransaction();
 try {
  $post=one('SELECT * FROM club_posts WHERE id=? AND status<>"deleted" FOR UPDATE',[$id]);
  if (!$post) throw new RequestError(404,'Post not found.');
  query('UPDATE club_posts SET status=?,moderation_reason=? WHERE id=?',[$status,$reason,$id]);
  query('UPDATE club_reports SET status="resolved" WHERE post_id=?',[$id]);
  audit('community_moderation','Post '.$id.': '.$status); db()->commit();
 } catch (Throwable $ex) { if (db()->inTransaction()) db()->rollBack(); throw $ex; }
 return action_result('moderation','Moderation decision saved.',['id'=>$id,'status'=>$status]);
}
function community_api(string $route,string $method,array $payload): bool {
 if (preg_match('~^clubs/([1-9][0-9]*)/(community|members|posts)(?:/([1-9][0-9]*)(?:/(comments|reaction|reports)(?:/([1-9][0-9]*))?)?)?$~',$route,$m)) {
  $clubId=(int)$m[1]; $section=$m[2]; $postId=(int)($m[3]??0); $sub=$m[4]??''; $commentId=(int)($m[5]??0);
  $u=require_user(); $club=club_access($clubId,$u);
  if ($method==='GET') {
   if ($section==='community') json_response(['club'=>$club,'summary'=>community_summary($clubId)]);
   if ($section==='members') paginated('u.id,u.name,u.faculty,m.created_at joined_at','memberships m JOIN users u ON u.id=m.user_id','m.club_id=? AND m.status="approved"',[$clubId],'u.name,u.id');
   if ($postId && $sub==='') {
    $post=community_post($clubId,$postId);
    json_response(array_intersect_key($post,array_flip(['id','club_id','user_id','body','created_at','updated_at','author','faculty'])));
   }
   if ($postId) { community_post($clubId,$postId); paginated('c.id,c.post_id,c.user_id,c.body,c.created_at,u.name author','club_comments c JOIN users u ON u.id=c.user_id','c.post_id=?',[$postId],'c.id'); }
   [$page,$limit,$offset]=api_pagination(); $total=(int)query('SELECT COUNT(*) FROM club_posts WHERE club_id=? AND status="visible"',[$clubId])->fetchColumn();
   json_response(community_posts($clubId,(int)$u['id'],$limit,$offset),200,['pagination'=>['page'=>$page,'per_page'=>$limit,'total'=>$total,'pages'=>(int)ceil($total/$limit)]]);
  }
  $action=match(true) {
   $sub==='comments' && $method==='DELETE'=>'community_comment_delete',
   $sub==='comments'=>'community_comment',
   $sub==='reaction' && $method==='DELETE'=>'community_unreact',
   $sub==='reaction'=>'community_react',
   $sub==='reports'=>'community_report',
   $postId>0 && $method==='DELETE'=>'community_delete',
   $postId>0=>'community_edit',
   default=>'community_post'
  };
  $allowed=in_array($action,['community_post','community_edit','community_comment'],true)?['body']:($action==='community_report'?['reason']:[]);
  api_fields($payload,$allowed); $_POST=array_merge($payload,['club_id'=>$clubId,'post_id'=>$postId,'comment_id'=>$commentId]);
  $result=community_action($action); $status=in_array($action,['community_post','community_comment','community_report'],true)?201:200;
  if ($action==='community_post') header('Location: /api/v1/clubs/'.$clubId.'/posts/'.$result['data']['id']);
  json_response($result['data']+['message'=>$result['message']],$status,['redirect'=>'/?page=club&club_id='.$clubId.'&tab=community']);
 }
 if (preg_match('~^admin/community/posts/([1-9][0-9]*)/reports$~',$route,$m)) {
  require_admin();
  if (!one('SELECT id FROM club_posts WHERE id=? AND status<>"deleted"',[$m[1]])) throw new RequestError(404,'Post not found.');
  paginated('r.id,r.post_id,r.reason,r.status,r.created_at,u.name reporter','club_reports r JOIN users u ON u.id=r.user_id','r.post_id=?',[$m[1]],'r.id DESC');
 }
 if ($route==='admin/community') {
  require_admin(); $status=api_filter('status');
  if (!in_array($status,['','visible','hidden','reported'],true)) throw new RequestError(422,'Invalid moderation filter.');
  $where='p.status<>"deleted"'; $args=[];
  if ($status==='reported') $where.=' AND EXISTS(SELECT 1 FROM club_reports r WHERE r.post_id=p.id AND r.status="pending")';
  elseif ($status!=='') { $where.=' AND p.status=?'; $args[]=$status; }
  paginated('p.*,u.name author,c.name club,(SELECT COUNT(*) FROM club_reports r WHERE r.post_id=p.id AND r.status="pending") reports','club_posts p JOIN users u ON u.id=p.user_id JOIN clubs c ON c.id=p.club_id',$where,$args,'p.id DESC');
 }
 if (preg_match('~^admin/community/posts/([1-9][0-9]*)$~',$route,$m)) {
  api_fields($payload,['status','reason']); $_POST=array_merge($payload,['post_id'=>$m[1]]); $result=moderate_post();
  json_response($result['data'],200,['redirect'=>'/?page=moderation']);
 }
 return false;
}
