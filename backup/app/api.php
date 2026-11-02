<?php
declare(strict_types=1);
/** Versioned REST transport. Business mutations are shared with the HTML fallback. */
function public_user(?array $u): ?array {
 if (!$u) return null;
 return ['id'=>(int)$u['id'],'name'=>$u['name'],'email'=>$u['email'],'student_id'=>$u['student_id'],'faculty'=>$u['faculty'],'role'=>$u['role'],'two_factor_enabled'=>$u['totp_secret']!==null];
}
function json_response(mixed $data, int $status=200, array $meta=[]): never {
 http_response_code($status);
 header('Content-Type: application/json; charset=utf-8');
 echo json_encode(['data'=>$data,'meta'=>array_merge(['request_id'=>REST_REQUEST_ID,'csrf_token'=>csrf()],$meta)],JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
 exit;
}
function json_error(int $status, string $message): never {
 http_response_code($status); header('Content-Type: application/json; charset=utf-8');
 if ($status===429) header('Retry-After: 900');
 echo json_encode(['error'=>['status'=>$status,'message'=>$message],'meta'=>['request_id'=>REST_REQUEST_ID,'csrf_token'=>csrf()]],JSON_THROW_ON_ERROR);
 exit;
}
function api_payload(): array {
 $contentType=strtolower(trim(explode(';',$_SERVER['CONTENT_TYPE']??'')[0]));
 if ($contentType!=='application/json') throw new RequestError(415,'Use Content-Type: application/json.');
 $raw=file_get_contents('php://input',false,null,0,65537);
 if (strlen($raw)>65536) throw new RequestError(413,'Request body exceeds 64 KiB.');
 try { $body=json_decode($raw,true,32,JSON_THROW_ON_ERROR); } catch (JsonException) { throw new RequestError(400,'Malformed JSON request body.'); }
 if (!is_array($body) || !str_starts_with(ltrim($raw),'{')) throw new RequestError(400,'The JSON body must be an object.');
 foreach ($body as $key=>$value) if (!is_string($key) || (!is_scalar($value) && $value!==null)) throw new RequestError(422,'Fields must contain scalar values.');
 return $body;
}
function api_fields(array $payload, array $allowed): void {
 $unknown=array_diff(array_keys($payload),$allowed);
 if ($unknown) throw new RequestError(422,'Unknown field: '.implode(', ',$unknown));
}
function api_pagination(): array {
 $page=filter_var($_GET['page']??1,FILTER_VALIDATE_INT);
 $perPage=filter_var($_GET['per_page']??20,FILTER_VALIDATE_INT);
 if ($page===false || $page<1 || $page>100000 || $perPage===false || $perPage<1 || $perPage>100) throw new RequestError(422,'Use page >= 1 and per_page between 1 and 100.');
 return [$page,$perPage,($page-1)*$perPage];
}
function paginated(string $select, string $from, string $where='1=1', array $args=[], string $order='id'): never {
 [$page,$limit,$offset]=api_pagination();
 $total=(int)query("SELECT COUNT(*) FROM $from WHERE $where",$args)->fetchColumn();
 $list=rows("SELECT $select FROM $from WHERE $where ORDER BY $order LIMIT $limit OFFSET $offset",$args);
 json_response($list,200,['pagination'=>['page'=>$page,'per_page'=>$limit,'total'=>$total,'pages'=>(int)ceil($total/$limit)]]);
}
function api_filter(string $name): string {
 $v=$_GET[$name]??'';
 if (!is_string($v) || mb_strlen($v)>190) throw new RequestError(422,'Invalid filter: '.$name);
 return trim($v);
}
function api_record(string $table, int $id): array {
 $r=one("SELECT * FROM $table WHERE id=?",[$id]);
 if (!$r) throw new RequestError(404,'Record not found.');
 return $r;
}
function api_mutation(string $action,array $payload,array $allowed,array $fixed=[],int $status=200,?string $resource=null): never {
 api_fields($payload,$allowed);
 $_POST=array_merge($payload,$fixed);
 $result=perform_action($action);
 $data=$result['data'];
 if ($resource && isset($data['id'])) {
  $data=api_record($resource,(int)$data['id']);
  if ($status===201) header('Location: /api/v1/'.$resource.'/'.$data['id']);
 }
 if ($action==='setup_2fa') {
  $secret=$_SESSION['setup_secret']; $u=require_user();
  $data=['secret'=>$secret,'otpauth_uri'=>'otpauth://totp/'.rawurlencode('NSBM SportsHub:'.$u['email']).'?secret='.$secret.'&issuer=NSBM%20SportsHub&algorithm=SHA1&digits=6&period=30','expires_in'=>600];
 }
 if ($action==='enable_2fa') unset($_SESSION['recovery_codes']); // JSON response is the only recovery-code display.
 if (in_array($action,['login','verify'],true)) $data['user']=public_user(user());
 if ($result['message']!=='') $data['message']=$result['message'];
 json_response($data,$status,['redirect'=>'/?page='.rawurlencode($result['redirect'])]);
}
function api_dispatch(): never {
 $path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
 $method=$_SERVER['REQUEST_METHOD'];
 if (!is_string($path) || !str_starts_with($path,'/api/v1/')) throw new RequestError(404,'API route not found.');
 $route=rtrim(substr($path,8),'/');
 $routes=[
  'clubs/[1-9][0-9]*/(community|members)'=>['GET'],
  'clubs/[1-9][0-9]*/posts'=>['GET','POST'],
  'clubs/[1-9][0-9]*/posts/[1-9][0-9]*'=>['GET','PATCH','DELETE'],
  'clubs/[1-9][0-9]*/posts/[1-9][0-9]*/comments'=>['GET','POST'],
  'clubs/[1-9][0-9]*/posts/[1-9][0-9]*/comments/[1-9][0-9]*'=>['DELETE'],
  'clubs/[1-9][0-9]*/posts/[1-9][0-9]*/reaction'=>['PUT','DELETE'],
  'clubs/[1-9][0-9]*/posts/[1-9][0-9]*/reports'=>['POST'],
  'admin/community/posts/[1-9][0-9]*/reports'=>['GET'],
  'admin/community'=>['GET'],'admin/community/posts/[1-9][0-9]*'=>['PATCH'],
  'health'=>['GET'],'auth/session'=>['GET'],'auth/register'=>['POST'],'auth/login'=>['POST'],'auth/2fa'=>['POST'],'auth/logout'=>['POST'],
  'me'=>['GET'],'me/security'=>['GET'],'me/2fa/setup'=>['POST'],'me/2fa'=>['POST','DELETE'],
  'me/memberships'=>['GET'],'me/registrations'=>['GET'],'me/attendance'=>['GET'],'me/bookings'=>['GET'],
  'clubs'=>['GET'],'tournaments'=>['GET'],'events'=>['GET'],'resources'=>['GET'],'results'=>['GET'],'standings'=>['GET'],
  'bookings'=>['POST'],
  'clubs/[1-9][0-9]*'=>['GET'],'tournaments/[1-9][0-9]*'=>['GET'],'events/[1-9][0-9]*'=>['GET'],'resources/[1-9][0-9]*'=>['GET'],
  'clubs/[1-9][0-9]*/memberships'=>['POST'],'tournaments/[1-9][0-9]*/registrations'=>['POST'],'events/[1-9][0-9]*/attendance'=>['POST'],
  'me/(memberships|registrations|attendance|bookings)/[1-9][0-9]*'=>['DELETE'],
  'admin/(clubs|tournaments|events|resources)'=>['GET','POST'],
  'admin/(clubs|tournaments|events|resources)/[1-9][0-9]*'=>['GET','PUT','DELETE'],
  'admin/(memberships|bookings)'=>['GET'],'admin/(memberships|bookings)/[1-9][0-9]*'=>['PATCH'],
  'admin/attendance'=>['GET'],'admin/attendance/[1-9][0-9]*'=>['PATCH'],'admin/reports'=>['GET'],'admin/audit-logs'=>['GET'],
 ];
 $methods=null;
 foreach ($routes as $pattern=>$allowed) if (preg_match('~^'.$pattern.'$~D',$route)) { $methods=$allowed; break; }
 if (!$methods) throw new RequestError(404,'API route not found.');
 if (!in_array($method,$methods,true)) { header('Allow: '.implode(', ',$methods)); throw new RequestError(405,'Method not allowed.'); }
 if (str_starts_with($route,'admin/')) require_admin();
 elseif (str_starts_with($route,'me') || $route==='bookings' || $route==='auth/logout' || preg_match('~/(memberships|registrations|attendance)$~',$route)) require_user();
 $payload=[];
 if ($method!=='GET') {
  if (!hash_equals(csrf(),(string)($_SERVER['HTTP_X_CSRF_TOKEN']??''))) throw new RequestError(403,'Invalid CSRF token. Fetch /api/v1/auth/session and retry.');
  if (isset($_SERVER['HTTP_ORIGIN']) && rtrim($_SERVER['HTTP_ORIGIN'],'/')!==rtrim(config('APP_URL'),'/')) throw new RequestError(403,'Cross-origin requests are not allowed.');
  $payload=api_payload();
 }
 community_api($route,$method,$payload);
 if ($route==='health') {
  query('SELECT 1'); json_response(['status'=>'ok','service'=>'NSBM SportsHub','version'=>'1']);
 }
 if ($route==='auth/session') json_response(['user'=>public_user(user()),'requires_2fa'=>isset($_SESSION['pending_user']) && ($_SESSION['pending_until']??0)>=time()]);
 if ($route==='me') json_response(public_user(require_user()));
 if ($route==='me/security') { $u=require_user(); json_response(['two_factor_enabled'=>$u['totp_secret']!==null,'recovery_codes_remaining'=>(int)query('SELECT COUNT(*) FROM recovery_codes WHERE user_id=?',[$u['id']])->fetchColumn()]); }
 if ($route==='auth/register') api_mutation('register',$payload,['name','email','student_id','faculty','password'],[],201);
 if ($route==='auth/login') api_mutation('login',$payload,['email','password']);
 if ($route==='auth/2fa') api_mutation('verify',$payload,['code']);
 if ($route==='auth/logout') api_mutation('logout',$payload,[]);
 if ($route==='me/2fa/setup') api_mutation('setup_2fa',$payload,[]);
 if ($route==='me/2fa') api_mutation($method==='POST'?'enable_2fa':'disable_2fa',$payload,['password','code']);
 if ($route==='bookings') {
  api_fields($payload,['resource_id','starts_at','ends_at','purpose']); $_POST=$payload;
  $result=perform_action('book');
  $record=one('SELECT * FROM bookings WHERE user_id=? ORDER BY id DESC LIMIT 1',[require_user()['id']]);
  header('Location: /api/v1/me/bookings'); json_response($record,201,['redirect'=>'/?page=dashboard','message'=>$result['message']]);
 }
 if (preg_match('~^(clubs|tournaments|events)/([1-9][0-9]*)/(memberships|registrations|attendance)$~',$route,$m)) {
  $actions=['clubs'=>'join','tournaments'=>'register_tournament','events'=>'attend'];
  api_fields($payload,[]); $_POST=['id'=>$m[2]]; $result=perform_action($actions[$m[1]]);
  $column=['clubs'=>'club_id','tournaments'=>'tournament_id','events'=>'event_id'][$m[1]];
  $record=one("SELECT * FROM {$m[3]} WHERE user_id=? AND $column=?",[require_user()['id'],$m[2]]);
  header('Location: /api/v1/me/'.$m[3]); json_response($record,201,['redirect'=>'/?page=dashboard','message'=>$result['message']]);
 }
 if (preg_match('~^me/(memberships|registrations|attendance|bookings)(?:/([1-9][0-9]*))?$~',$route,$m)) {
  $entity=$m[1]; $uid=require_user()['id'];
  if ($method==='DELETE') api_mutation('cancel',$payload,[],['entity'=>$entity,'id'=>$m[2]]);
  if ($entity==='memberships') paginated('m.*,c.name club','memberships m JOIN clubs c ON c.id=m.club_id','m.user_id=?',[$uid],'m.id DESC');
  if ($entity==='registrations') paginated('r.*,t.name tournament,t.starts_at','registrations r JOIN tournaments t ON t.id=r.tournament_id','r.user_id=?',[$uid],'r.id DESC');
  if ($entity==='attendance') paginated('a.*,e.title,e.starts_at','attendance a JOIN events e ON e.id=a.event_id','a.user_id=?',[$uid],'e.starts_at DESC');
  paginated('b.*,r.name resource','bookings b JOIN resources r ON r.id=b.resource_id','b.user_id=?',[$uid],'b.id DESC');
 }
 if (preg_match('~^(clubs|tournaments|events|resources)(?:/([1-9][0-9]*))?$~',$route,$m)) {
  $entity=$m[1];
  if (isset($m[2])) json_response(api_record($entity,(int)$m[2]));
  $search=api_filter('q'); $where=$entity==='events'?'title LIKE ?':'name LIKE ?'; $args=['%'.$search.'%'];
  foreach (['sport','status','kind','club_id','tournament_id'] as $filter) {
   if (array_key_exists($filter,entities()[$entity]['fields']) && ($v=api_filter($filter))!=='') { $where.=" AND $filter=?"; $args[]=$v; }
  }
  paginated('*',$entity,$where,$args,$entity==='events'?'starts_at DESC':'id');
 }
 if ($route==='results') {
  $where='e.kind="match" AND e.home_score IS NOT NULL'; $args=[];
  if (($tid=api_filter('tournament_id'))!=='') { $where.=' AND e.tournament_id=?'; $args[]=$tid; }
  paginated('e.*,c.name club,t.name tournament','events e JOIN clubs c ON c.id=e.club_id LEFT JOIN tournaments t ON t.id=e.tournament_id',$where,$args,'e.starts_at DESC');
 }
 if ($route==='standings') {
  $where='e.kind="match" AND e.home_score IS NOT NULL'; $args=[];
  if (($tid=api_filter('tournament_id'))!=='') { $where.=' AND e.tournament_id=?'; $args[]=$tid; }
  json_response(rows('SELECT c.id club_id,c.name,COUNT(e.id) played,SUM(e.home_score>e.away_score) wins,SUM(e.home_score=e.away_score) draws,SUM(e.home_score<e.away_score) losses,SUM(CASE WHEN e.home_score>e.away_score THEN 3 WHEN e.home_score=e.away_score THEN 1 ELSE 0 END) points FROM clubs c JOIN events e ON e.club_id=c.id WHERE '.$where.' GROUP BY c.id,c.name ORDER BY points DESC,wins DESC,c.name',$args));
 }
 if (preg_match('~^admin/(clubs|tournaments|events|resources)(?:/([1-9][0-9]*))?$~',$route,$m)) {
  $entity=$m[1]; $id=isset($m[2])?(int)$m[2]:0;
  if ($method==='GET') { if ($id) json_response(api_record($entity,$id)); paginated('*',$entity); }
  if ($id) api_record($entity,$id);
  if ($method==='DELETE') api_mutation('delete',$payload,[],['entity'=>$entity,'id'=>$id]);
  api_mutation('save',$payload,array_keys(entities()[$entity]['fields']),['entity'=>$entity,'id'=>$id],$method==='POST'?201:200,$entity);
 }
 if (preg_match('~^admin/(memberships|bookings)(?:/([1-9][0-9]*))?$~',$route,$m)) {
  $entity=$m[1];
  if ($method==='PATCH') { api_record($entity,(int)$m[2]); api_mutation('review',$payload,['status'],['entity'=>$entity,'id'=>$m[2]]); }
  $status=api_filter('status');
  if ($entity==='memberships') paginated('m.*,u.name student,c.name club','memberships m JOIN users u ON u.id=m.user_id JOIN clubs c ON c.id=m.club_id','(?="" OR m.status=?)',[$status,$status],'m.id DESC');
  paginated('b.*,u.name student,r.name resource','bookings b JOIN users u ON u.id=b.user_id JOIN resources r ON r.id=b.resource_id','(?="" OR b.status=?)',[$status,$status],'b.id DESC');
 }
 if ($route==='admin/attendance') paginated('a.*,u.name student,e.title,e.starts_at','attendance a JOIN users u ON u.id=a.user_id JOIN events e ON e.id=a.event_id','1=1',[],'e.starts_at DESC');
 if (preg_match('~^admin/attendance/([1-9][0-9]*)$~',$route,$m)) {
  api_fields($payload,['status']); if (($payload['status']??'')!=='attended') throw new RequestError(422,'Status must be attended.');
  api_record('attendance',(int)$m[1]); api_mutation('attendance',[],[],['id'=>$m[1]]);
 }
 if ($route==='admin/audit-logs') paginated('a.id,a.action,a.details,a.created_at,u.name actor','audit_logs a LEFT JOIN users u ON u.id=a.user_id','1=1',[],'a.id DESC');
 if ($route==='admin/reports') json_response([
  'clubs'=>rows('SELECT c.id,c.name,(SELECT COUNT(*) FROM memberships m WHERE m.club_id=c.id AND m.status="approved") members,(SELECT COUNT(*) FROM attendance a JOIN events e ON e.id=a.event_id WHERE e.club_id=c.id) registrations,(SELECT COUNT(*) FROM attendance a JOIN events e ON e.id=a.event_id WHERE e.club_id=c.id AND a.status="attended") attended FROM clubs c ORDER BY c.name'),
  'tournaments'=>rows('SELECT t.id,t.name,t.capacity,t.status,COUNT(r.id) registrations FROM tournaments t LEFT JOIN registrations r ON r.tournament_id=t.id GROUP BY t.id,t.name,t.capacity,t.status ORDER BY t.id'),
  'bookings'=>rows('SELECT status,COUNT(*) count FROM bookings GROUP BY status')
 ]);
 throw new RequestError(404,'API route not found.');
}
