<?php
declare(strict_types=1);
/** Real HTTP + actual MySQL verification using PHP core only. No external test framework. */
if (PHP_SAPI !== 'cli' || !in_array('--confirm-test-reset', $argv, true)) {
    fwrite(STDERR,"Usage: php tests/integration.php --confirm-test-reset\nThis rebuilds ONLY the deliberately disposable sportshub_backup_test schema. Verify that it contains no wanted data first.\n");
    exit(1);
}
// Never derive the destructive target from the working application's default database.
putenv('DB_NAME=sportshub_backup_test');
putenv('APP_ENV=development');
putenv('APP_URL=http://localhost:8092');
if (getenv('APP_KEY') === false || getenv('APP_KEY') === '') putenv('APP_KEY='.base64_encode(random_bytes(32)));
require __DIR__.'/../app/bootstrap.php';
require __DIR__.'/fixtures.php';

final class VerificationClient {
    private array $cookies = [];
    public ?string $csrf = null;
    public function request(string $path, string $method='GET', ?string $body=null, array $headers=[]): array {
        $lines = [];
        if ($this->cookies) $lines[] = 'Cookie: '.implode('; ',array_map(fn($k)=>$k.'='.$this->cookies[$k],array_keys($this->cookies)));
        foreach ($headers as $key=>$value) $lines[] = $key.': '.$value;
        $context = stream_context_create(['http'=>['method'=>$method,'header'=>implode("\r\n",$lines),'content'=>$body ?? '',
            'ignore_errors'=>true,'follow_location'=>0,'timeout'=>10]]);
        $result = @file_get_contents('http://localhost:8092'.$path,false,$context);
        if ($result === false) throw new RuntimeException('Local verification HTTP request failed. Check the private server log.');
        $responseHeaders = $http_response_header ?? [];
        preg_match('/\s(\d{3})\s/', $responseHeaders[0] ?? '', $match);
        $status = (int)($match[1] ?? 0); $map=[];
        foreach (array_slice($responseHeaders,1) as $header) {
            if (!str_contains($header,':')) continue;
            [$key,$value] = explode(':',$header,2); $key=strtolower(trim($key)); $value=trim($value);
            $map[$key]=$value;
            if ($key==='set-cookie') {
                [$pair] = explode(';',$value,2); [$name,$cookie]=explode('=',$pair,2); $this->cookies[$name]=$cookie;
            }
        }
        return [$status,$result,$map];
    }
    public function api(string $path,string $method='GET',array $data=[],bool $token=true,array $extra=[]): array {
        if ($method!=='GET' && $this->csrf===null) $this->api('/auth/session');
        $headers=['Accept'=>'application/json'];
        if ($method!=='GET') {
            $headers['Content-Type']='application/json';
            if ($token) $headers['X-CSRF-Token']=$this->csrf ?? '';
        }
        [$status,$text,$responseHeaders]=$this->request('/api/v1'.$path,$method,$method==='GET'?null:json_encode((object)$data,JSON_THROW_ON_ERROR),[...$headers,...$extra]);
        $response=json_decode($text,true,512,JSON_THROW_ON_ERROR);
        $this->csrf=$response['meta']['csrf_token'] ?? $this->csrf;
        return [$status,$response,$responseHeaders];
    }
    public function login(string $email,string $password): array { return $this->api('/auth/login','POST',['email'=>$email,'password'=>$password]); }
    public function form(string $page,string $action,array $fields=[]): array {
        [, $html]=$this->request('/?page='.$page);
        if (!preg_match('/name="csrf" value="([a-f0-9]+)"/',$html,$match)) throw new RuntimeException('Expected CSRF field is absent.');
        return $this->request('/?page='.$page,'POST',http_build_query(['csrf'=>$match[1],'action'=>$action,...$fields]),['Content-Type'=>'application/x-www-form-urlencoded']);
    }
}
$checks=0;
function verify(bool $condition,string $label): void {
    global $checks;
    if (!$condition) throw new RuntimeException('FAILED: '.$label);
    ++$checks; echo 'PASS '.$label.PHP_EOL;
}
function scalar(string $sql,array $args=[]): mixed { return query($sql,$args)->fetchColumn(); }

$root=realpath(__DIR__.'/..'); $php=getenv('PHP_BIN') ?: PHP_BINARY;
$existing=@stream_socket_client('tcp://127.0.0.1:8092',$errno,$error,0.2);
if (is_resource($existing)) { fclose($existing); throw new RuntimeException('Port8092 is already occupied; refusing to touch another service.'); }
$password=bin2hex(random_bytes(18));
$ids=initialize_test_database($password);
$log=tempnam(sys_get_temp_dir(),'sportshub-backup-test-');
if ($log===false) throw new RuntimeException('Cannot create private verification log.');
chmod($log,0600);
$server=proc_open([$php,'-S','127.0.0.1:8092','-t',$root.'/public',$root.'/public/router.php'],[0=>['pipe','r'],1=>['file',$log,'a'],2=>['file',$log,'a']],$pipes,$root,getenv());
if (!is_resource($server)) throw new RuntimeException('Cannot start local verification PHP server.');
fclose($pipes[0]);
$succeeded=false;
try {
    $ready=false;
    for ($i=0;$i<50;$i++) {
        $socket=@stream_socket_client('tcp://127.0.0.1:8092',$errno,$error,0.1);
        if (is_resource($socket)) { fclose($socket);$ready=true;break; }
        usleep(100000);
    }
    verify($ready,'Temporary PHP server started on its own port');
    $guest=new VerificationClient();$admin=new VerificationClient();$student=new VerificationClient();$other=new VerificationClient();
    foreach (['home','clubs','schedule','tournaments','results','facilities','login','register'] as $page) {
        [$status,$html,$headers]=$guest->request('/?page='.$page);
        verify($status===200 && str_contains($html,'SportsHub'),'Public rendered page '.$page);
    }
    verify(str_contains($headers['content-security-policy'] ?? '',"frame-ancestors 'none'"),'CSP forbids framing');
    verify(str_contains($headers['set-cookie'] ?? '','HttpOnly') || str_contains((new VerificationClient())->request('/?page=login')[2]['set-cookie'] ?? '','HttpOnly'),'Session cookie is HttpOnly');
    foreach (['/.env','/app/bootstrap.php','/database/schema.sql','/bin/create-admin.php','/tests/fixtures.php','/var/sessions/'] as $private) {
        [$status,$html]=$guest->request($private);
        verify($status===404 && !str_contains($html,'<?php'),'Private path is inaccessible: '.$private);
    }
    verify($guest->api('/health')[1]['data']['status']==='ok','Database-backed REST health');
    $session=$guest->api('/auth/session');
    verify($session[0]===200 && $session[1]['data']['user']===null && strlen($guest->csrf ?? '')===64,'Anonymous session and CSRF bootstrap');
    verify(($session[2]['x-request-id'] ?? '')===$session[1]['meta']['request_id'],'REST request ID is traceable');
    verify($guest->api('/me')[0]===401,'Anonymous protected API denied');
    verify($guest->api('/auth/login','POST',[],false)[0]===403,'REST missing CSRF rejected');
    verify($guest->api('/auth/login','POST',[],true,['Origin'=>'https://foreign.example.invalid'])[0]===403,'Foreign Origin rejected');
    verify($guest->request('/?page=login','POST','action=login',['Content-Type'=>'application/x-www-form-urlencoded'])[0]===419,'Native missing CSRF rejected');
    verify($guest->api('/auth/register','POST',['name'=>'Escalate','email'=>'escalate@example.invalid','student_id'=>'VERIFY-ESC','faculty'=>'Computing','password'=>$password,'role'=>'admin'])[0]===422,'Registration cannot mass-assign administrator role');
    $registration=$student->api('/auth/register','POST',['name'=>'<script>Verification Learner</script>','email'=>'learner@example.invalid','student_id'=>'VERIFY-LEARN','faculty'=>'Computing','password'=>$password]);
    verify($registration[0]===201,'Student registration persists'); $uid=(int)$registration[1]['data']['id'];
    verify($student->login('learner@example.invalid','incorrect-password-value')[0]===401,'Incorrect login rejected');
    verify($student->login('learner@example.invalid',$password)[0]===200,'Student login succeeds');
    verify($student->api('/me')[1]['data']['role']==='student','Registration role remains student');
    $profile=$student->api('/me')[1]['data'];
    verify(!array_intersect(['password_hash','totp_secret','totp_last_counter'],array_keys($profile)),'Profile excludes private authentication fields');
    verify(str_contains($student->request('/?page=dashboard')[1],'&lt;script&gt;Verification Learner&lt;/script&gt;'),'Stored user text escaped in HTML');
    verify($student->request('/?page=admin')[0]===403,'Student cannot open admin HTML');
    verify($student->api('/admin/reports')[0]===403,'Student cannot read admin API');
    verify($student->api('/admin/clubs','POST',[])[0]===403,'Student cannot forge admin mutation');
    verify($admin->form('login','login',['email'=>'admin@example.invalid','password'=>$password])[0]===303,'Native administrator login preserves redirect fallback');
    verify($admin->request('/?page=admin')[0]===200,'Native login grants real administrator interface');
    verify($other->login('other@example.invalid',$password)[0]===200,'Independent second student authenticated');
    $club=$ids['club'];
    verify($student->api('/clubs?q=Tennis')[1]['meta']['pagination']['total']===1,'Club search reads database');
    verify($student->api('/clubs?sport=Unlisted')[1]['data']===[],'Sport filter handles empty result');
    verify($student->api('/clubs?q=%27%20OR%201%3D1%20--')[1]['data']===[],'SQL metacharacters remain search data');
    verify($student->api('/clubs/'.$club.'/posts')[0]===403,'Nonmember community denied');
    $member=$student->api('/clubs/'.$club.'/memberships','POST');$membership=(int)$member[1]['data']['id'];
    verify($member[0]===201 && $member[1]['data']['status']==='pending','Membership application starts pending');
    verify($student->api('/clubs/'.$club.'/posts')[0]===403,'Pending member cannot read private community');
    verify($student->api('/events/'.$ids['future_event'].'/attendance','POST')[0]===422,'Unapproved session registration rejected');
    verify($student->api('/clubs/'.$club.'/memberships','POST')[0]===422,'Duplicate membership prevented');
    verify($admin->api('/admin/memberships/'.$membership,'PATCH',['status'=>'approved'])[0]===200,'Administrator approves membership');
    verify($student->api('/clubs/'.$club.'/community')[0]===200,'Approved member enters community');
    $roster=$student->api('/clubs/'.$club.'/members')[1]['data'];
    verify(count($roster)===1 && !isset($roster[0]['email']) && !isset($roster[0]['student_id']),'Community roster excludes contact identifiers');
    $postResult=$student->api('/clubs/'.$club.'/posts','POST',['body'=>'<img src=x onerror=alert(1)> Synthetic practice update']);$post=(int)$postResult[1]['data']['id'];$postPath='/clubs/'.$club.'/posts/'.$post;
    verify($postResult[0]===201,'Approved member creates club post');
    verify(str_contains($student->request('/?page=club&club_id='.$club.'&tab=community')[1],'&lt;img src=x onerror=alert(1)&gt;'),'Community plain text escaped in HTML');
    verify($other->api($postPath,'PATCH',['body'=>'Forged edit'])[0]===403,'Outsider cannot edit club content');
    verify($student->api($postPath,'PATCH',['body'=>'Updated synthetic practice plan'])[0]===200,'Author edits own post');
    $comment=$student->api($postPath.'/comments','POST',['body'=>'Synthetic reply']);$commentId=(int)$comment[1]['data']['id'];
    verify($comment[0]===201,'Member adds comment');
    verify($student->api($postPath.'/reaction','PUT')[1]['data']['reactions']===1,'Member reaction persists');
    verify($student->api($postPath.'/reaction','PUT')[1]['data']['reactions']===1,'Repeated reaction is idempotent');
    verify($student->api($postPath.'/reports','POST',['reason'=>'Synthetic safety review'])[0]===201,'Private moderation report accepted');
    verify($student->api('/admin/community')[0]===403,'Student cannot read moderation queue');
    verify($admin->api('/admin/community/posts/'.$post,'PATCH',['status'=>'hidden','reason'=>'Synthetic review'])[0]===200,'Admin hides post with reason');
    verify($student->api($postPath)[0]===404,'Hidden post unavailable to members');
    verify($student->api($postPath,'PATCH',['body'=>'Corrected synthetic practice plan'])[0]===200,'Hidden author may correct own text');
    verify($student->api($postPath)[0]===404,'Author edit cannot unhide moderation');
    verify($admin->api('/admin/community/posts/'.$post,'PATCH',['status'=>'visible','reason'=>'Synthetic correction reviewed'])[0]===200,'Admin restores reviewed post');
    verify($student->api($postPath.'/comments/'.$commentId,'DELETE')[0]===200,'Comment author deletes own comment');
    verify($student->api('/events/'.$ids['future_event'].'/attendance','POST')[0]===201,'Approved member registers for future training');
    $attendance=(int)scalar('SELECT id FROM attendance WHERE user_id=? AND event_id=?',[$uid,$ids['future_event']]);
    verify($admin->api('/admin/attendance/'.$attendance,'PATCH',['status'=>'attended'])[0]===422,'Future attendance cannot be fabricated');
    $enrollment=$student->api('/tournaments/'.$ids['tournament'].'/registrations','POST');$enrollmentId=(int)$enrollment[1]['data']['id'];
    verify($enrollment[0]===201,'Individual tournament registration');
    verify($student->api('/tournaments/'.$ids['tournament'].'/registrations','POST')[0]===422,'Duplicate tournament registration rejected');
    foreach (['memberships','attendance','registrations','bookings'] as $entity) verify($student->api('/me/'.$entity)[0]===200,'Own participation history '.$entity);
    $start=date('Y-m-d\T10:00',time()+30*86400);$end=date('Y-m-d\T12:00',time()+30*86400);
    $booking=$student->api('/bookings','POST',['resource_id'=>$ids['resource'],'starts_at'=>$start,'ends_at'=>$end,'purpose'=>'Synthetic team practice']);$bookingId=(int)$booking[1]['data']['id'];
    verify($booking[0]===201,'Future facility booking starts pending');
    verify($student->api('/bookings','POST',['resource_id'=>$ids['resource'],'starts_at'=>$start,'ends_at'=>$end,'purpose'=>'Synthetic overlap'])[0]===422,'Pending booking prevents interval collision');
    verify($admin->api('/admin/bookings/'.$bookingId,'PATCH',['status'=>'approved'])[0]===200,'Administrator approves booking');
    verify($other->api('/me/bookings/'.$bookingId,'DELETE')[0]===404,'Booking cancellation enforces ownership');
    verify($student->api('/me/bookings/'.$bookingId,'DELETE')[0]===200,'Owner cancels eligible booking');
    verify(scalar('SELECT status FROM bookings WHERE id=?',[$bookingId])==='cancelled','Cancelled booking remains in history');
    verify($student->api('/me/bookings/'.$bookingId,'DELETE')[0]===409,'Repeated cancellation is a conflict');
    verify($student->api('/bookings','POST',['resource_id'=>$ids['resource'],'starts_at'=>$end,'ends_at'=>date('Y-m-d\T13:00',time()+30*86400),'purpose'=>'Synthetic adjacent interval'])[0]===201,'Adjacent interval is allowed');
    verify($student->api('/bookings','POST',['resource_id'=>$ids['resource'],'starts_at'=>$end,'ends_at'=>date('Y-m-d\T18:00',time()+30*86400),'purpose'=>'Too long'])[0]===422,'Booking duration limit enforced');
    $records=[
        'clubs'=>['name'=>'Disposable Verification Club','sport'=>'Tennis','description'=>'Synthetic CRUD club','coach'=>'Synthetic Coach','location'=>'Practice Zone','capacity'=>8],
        'tournaments'=>['name'=>'Disposable Verification Cup','sport'=>'Tennis','description'=>'Synthetic CRUD cup','location'=>'Practice Zone','starts_at'=>$start,'ends_at'=>$end,'registration_deadline'=>date('Y-m-d\T09:00',time()+30*86400),'capacity'=>2,'status'=>'open'],
        'events'=>['title'=>'Disposable Verification Match','club_id'=>$club,'tournament_id'=>$ids['tournament'],'kind'=>'match','location'=>'Practice Zone','starts_at'=>$start,'ends_at'=>$end,'opponent'=>'Synthetic Visitors','home_score'=>2,'away_score'=>1],
        'resources'=>['name'=>'Disposable Verification Kit','kind'=>'equipment','description'=>'Synthetic CRUD kit','location'=>'Practice Zone'],
    ];
    foreach ($records as $entity=>$payload) {
        $created=$admin->api('/admin/'.$entity,'POST',$payload);$record=(int)($created[1]['data']['id'] ?? 0);
        verify($created[0]===201 && $record>0,'Administrator creates '.$entity);
        verify(str_ends_with($created[2]['location'] ?? '', '/'.$entity.'/'.$record),'Creation Location points to '.$entity);
        verify($admin->api('/admin/'.$entity.'/'.$record)[0]===200,'Administrator reads '.$entity);
        verify($guest->api('/'.$entity.'/'.$record)[0]===200,'Public detail reads '.$entity);
        $payload[$entity==='events'?'title':'name']='Updated Synthetic Record';
        if ($entity==='events') $payload['home_score']=3;
        verify($admin->api('/admin/'.$entity.'/'.$record,'PUT',$payload)[0]===200,'Administrator updates '.$entity);
        verify($admin->api('/admin/'.$entity.'/'.$record,'PUT',['unexpected'=>'value'])[0]===422,'Unknown replacement fields rejected '.$entity);
        if ($entity==='events') {
            verify(str_contains($guest->request('/?page=results')[1],'3 : 1'),'Updated match scores visible in rendered results');
            $standings=$guest->api('/standings')[1]['data'];
            verify(count($standings)>0 && (int)$standings[0]['points']===3,'Scored win yields three standing points');
        }
        verify($admin->api('/admin/'.$entity.'/'.$record,'DELETE')[0]===200,'Administrator deletes '.$entity);
        verify($guest->api('/'.$entity.'/'.$record)[0]===404,'Deleted '.$entity.' no longer visible');
    }
    verify($admin->api('/admin/events','POST',[...$records['events'],'away_score'=>null])[0]===422,'Unpaired match scores rejected');
    verify($admin->api('/admin/clubs','POST',[...$records['clubs'],'sport'=>str_repeat('x',61)])[0]===422,'Sport validation agrees with MySQL column length');
    $nativeFields=$records['resources'];$nativeFields['name']='Native Synthetic Resource';
    verify($admin->form('manage_resources','save',['entity'=>'resources','id'=>0,...$nativeFields])[0]===303,'Native administrator CRUD uses shared service and redirect');
    $nativeId=(int)scalar('SELECT id FROM resources WHERE name=?',['Native Synthetic Resource']);
    verify($nativeId>0,'Native CRUD write persisted to MySQL');
    verify($admin->form('manage_resources','delete',['entity'=>'resources','id'=>$nativeId])[0]===303,'Native administrator delete works');
    query('INSERT INTO attendance(user_id,event_id) VALUES(?,?)',[$uid,$ids['past_event']]);$pastAttendance=(int)db()->lastInsertId();
    verify($admin->api('/admin/attendance/'.$pastAttendance,'PATCH',['status'=>'attended'])[0]===200,'Admin records completed-session attendance');
    verify(scalar('SELECT status FROM attendance WHERE id=?',[$pastAttendance])==='attended','Recorded attendance persists');
    verify(str_contains($student->request('/?page=activity')[1],'Attended'),'Student sees participation history');
    foreach (['admin','approvals','reports','audit','moderation','manage_clubs','manage_events','manage_tournaments','manage_resources'] as $page) verify($admin->request('/?page='.$page)[0]===200,'Administrator rendered interface '.$page);
    verify($admin->api('/admin/reports')[0]===200,'Participation/performance report API works');
    verify(count($admin->api('/admin/audit-logs')[1]['data'])>0,'Administrative operations recorded in audit history');
    verify($other->api('/me/memberships/'.$membership,'DELETE')[0]===404,'Membership removal enforces ownership');
    verify($student->api('/me/registrations/'.$enrollmentId,'DELETE')[0]===200,'Owner cancels future tournament enrollment');
    verify($student->api('/me/attendance/'.$attendance,'DELETE')[0]===200,'Owner cancels future session registration');
    $setup=$student->api('/me/2fa/setup','POST');$secret=(string)($setup[1]['data']['secret'] ?? '');
    verify($setup[0]===200 && strlen($secret)===32,'Unique authenticator enrollment starts');
    $enrollmentCode=totp($secret,intdiv(time(),30));
    $enrolled=$student->api('/me/2fa','POST',['password'=>$password,'code'=>$enrollmentCode]);$recovery=$enrolled[1]['data']['recovery_codes'] ?? [];
    verify($enrolled[0]===200 && count($recovery)===8,'Authenticator enrollment returns eight one-time recovery codes');
    $encrypted=(string)scalar('SELECT totp_secret FROM users WHERE id=?',[$uid]);
    verify($encrypted!==$secret && decrypt_secret($encrypted)===$secret,'Authenticator secret encrypted at rest with private application key');
    $security=$student->api('/me/security')[1]['data'];
    verify($security['two_factor_enabled']===true && !array_key_exists('recovery_codes',$security),'Security read does not redisplay recovery codes');
    verify($student->api('/auth/logout','POST')[0]===200,'Authenticated logout works');
    $challenge=$student->login('learner@example.invalid',$password);
    verify($challenge[0]===200 && $challenge[1]['data']['requires_2fa']===true && $challenge[1]['data']['user']===null,'Password alone leaves second-factor challenge unauthenticated');
    verify($student->api('/me')[0]===401,'Pending second factor cannot access protected API');
    verify($student->request('/?page=dashboard')[0]===303,'Pending second factor cannot enter rendered dashboard');
    verify($student->api('/auth/2fa','POST',['code'=>$enrollmentCode])[0]===401,'Enrollment code replay rejected');
    verify($student->api('/auth/2fa','POST',['code'=>$recovery[0]])[0]===200,'Unused recovery code completes login');
    verify((int)scalar('SELECT COUNT(*) FROM recovery_codes WHERE user_id=?',[$uid])===7,'Recovery code consumed atomically');
    $student->api('/auth/logout','POST');$student->login('learner@example.invalid',$password);
    verify($student->api('/auth/2fa','POST',['code'=>$recovery[0]])[0]===401,'Consumed recovery code cannot be reused');
    verify($student->api('/auth/2fa','POST',['code'=>$recovery[1]])[0]===200,'Another unused recovery code remains usable');
    verify($student->api('/me/2fa','DELETE',['password'=>'incorrect-password-value','code'=>$recovery[2]])[0]===422,'Disabling2FA requires current password');
    verify($student->api('/me/2fa','DELETE',['password'=>$password,'code'=>$recovery[2]])[0]===200,'Disabling2FA requires password and second factor');
    verify($student->api('/me/security')[1]['data']['two_factor_enabled']===false,'Disabled2FA state persisted');
    verify((int)scalar('SELECT COUNT(*) FROM recovery_codes WHERE user_id=?',[$uid])===0,'Disabling2FA removes old recovery hashes');
    verify($student->api('/me/memberships/'.$membership,'DELETE')[0]===200,'Student leaves own club');
    verify($student->api('/clubs/'.$club.'/posts')[0]===403,'Leaving immediately revokes club feed access');
    $before=(int)scalar('SELECT COUNT(*) FROM users');
    require __DIR__.'/../bin/migrate.php';require __DIR__.'/../bin/migrate.php';
    verify((int)scalar('SELECT COUNT(*) FROM users')===$before,'Repeated migrations preserve existing users');
    verify((int)scalar('SELECT COUNT(*) FROM schema_migrations WHERE version=?',['20261017_club_communities.sql'])===1,'Additive migration version recorded once');
    $locked=new VerificationClient();
    for ($i=0;$i<8;$i++) $locked->login('limited@example.invalid','incorrect-password-value');
    verify($locked->login('limited@example.invalid','incorrect-password-value')[0]===429,'Repeated failed login is rate limited');
    $succeeded=true;
    echo $checks.' PHP HTTP/MySQL checks passed.'.PHP_EOL;
    echo "Only sportshub_backup_test was rebuilt. Synthetic test rows remain there for inspection; no production deployment was performed.\n";
} finally {
    proc_terminate($server);
    proc_close($server);
    // Delete only our successful run's temporary log; preserve a failing run privately for diagnosis.
    if ($succeeded && is_file($log)) unlink($log);
    elseif (!$succeeded) fwrite(STDERR,'Private verification server log retained at '.$log.'. Inspect locally; do not publish secret-bearing errors.'.PHP_EOL);
}
