"""Community authorization, persistence, moderation and migration checks on sportshub_test."""
import concurrent.futures,http.cookiejar,json,os,secrets,socket,subprocess,tempfile,time,urllib.error,urllib.request
from pathlib import Path
ROOT=Path(__file__).resolve().parents[1];PHP=os.environ.get('PHP_BIN','php')
env=dict(os.environ,DB_NAME='sportshub_test',APP_ENV='development',APP_URL='http://localhost:8085',TEST_PASSWORD=secrets.token_urlsafe(24))
subprocess.check_output([PHP,str(ROOT/'tests/fixtures.php')],env=env,text=True)
serverlog=tempfile.TemporaryFile(mode='w+')
server=subprocess.Popen([PHP,'-S','127.0.0.1:8085','-t',str(ROOT/'public'),str(ROOT/'public/router.php')],env=env,stdout=subprocess.DEVNULL,stderr=serverlog)
checks=0
class Client:
 def __init__(self):self.opener=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()));self.csrf=None
 def call(self,path,method='GET',data=None,token=True):
  if method!='GET' and self.csrf is None:self.call('/auth/session')
  headers={'Accept':'application/json'}
  if method!='GET':
   headers['Content-Type']='application/json'
   if token:headers['X-CSRF-Token']=self.csrf
  request=urllib.request.Request('http://localhost:8085/api/v1'+path,method=method,headers=headers,data=None if method=='GET' else json.dumps(data or {}).encode())
  try:r=self.opener.open(request)
  except urllib.error.HTTPError as ex:r=ex
  result=json.load(r);self.last_headers=r.headers;self.csrf=result.get('meta',{}).get('csrf_token',self.csrf);return r.status,result
 def login(self,email):return self.call('/auth/login','POST',dict(email=email,password=env['TEST_PASSWORD']))
 def html(self,path):
  try:r=self.opener.open('http://localhost:8085/'+path)
  except urllib.error.HTTPError as ex:r=ex
  return r.status,r.read().decode()
def check(value,label):
 global checks
 if not value:raise AssertionError(label)
 checks+=1;print('PASS',label)
def dbvalue(sql):
 code='require '+json.dumps(str(ROOT/'app/bootstrap.php'))+';echo json_encode(query('+json.dumps(sql)+')->fetchColumn());'
 return json.loads(subprocess.check_output([PHP,'-r',code],env=env,text=True))
try:
 for _ in range(50):
  try:socket.create_connection(('127.0.0.1',8085),.1).close();break
  except OSError:time.sleep(.1)
 guest,admin,author,mate,outsider=Client(),Client(),Client(),Client(),Client()
 check(guest.call('/clubs/1/community')[0]==401,'Anonymous community access denied')
 check(guest.call('/clubs/1/posts')[0]==401,'Anonymous feed denied')
 check(guest.call('/clubs/1/members')[0]==401,'Anonymous roster denied')
 check(admin.login('admin@example.invalid')[0]==200,'Moderator authenticated')
 for client,email,sid in [(author,'author@example.invalid','AUTH001'),(mate,'mate@example.invalid','MATE001')]:
  check(client.call('/auth/register','POST',dict(name=sid,email=email,student_id=sid,faculty='Computing',password=env['TEST_PASSWORD']))[0]==201,'Community student created')
  client.login(email)
 outsider.login('other@example.invalid')
 check(author.call('/clubs/1/community')[0]==403,'Nonmember community denied')
 membership=author.call('/clubs/1/memberships','POST')[1]['data']['id']
 check(author.call('/clubs/1/posts')[0]==403,'Pending membership cannot read feed')
 check(author.call('/clubs/1/posts','POST',dict(body='Blocked'))[0]==403,'Pending membership cannot write feed')
 admin.call('/admin/memberships/'+str(membership),'PATCH',dict(status='approved'))
 mid=mate.call('/clubs/1/memberships','POST')[1]['data']['id'];admin.call('/admin/memberships/'+str(mid),'PATCH',dict(status='approved'))
 check(author.call('/clubs/1/community')[0]==200,'Approved member community access')
 check(admin.call('/clubs/1/community')[0]==200,'Administrator access without a membership')
 check(author.call('/clubs/999999/community')[0]==404,'Missing club 404')
 roster=author.call('/clubs/1/members')[1]['data']
 check(len(roster)==2 and all('email' not in r and 'student_id' not in r and 'password_hash' not in r for r in roster),'Roster contains approved members with private fields excluded')
 check(author.call('/clubs/2/community')[0]==403,'Membership is scoped to one club')
 check(author.call('/clubs/2/posts','POST',dict(body='Wrong club'))[0]==403,'Cross-club post blocked')
 check(author.call('/clubs/1/posts','POST',dict(body='Body'),token=False)[0]==403,'Community write requires CSRF')
 check(author.call('/clubs/1/posts','POST',dict(body=' ',role='admin'))[0]==422,'Unknown post fields rejected')
 check(author.call('/clubs/1/posts','POST',dict(body=''))[0]==422,'Empty post rejected')
 check(author.call('/clubs/1/posts','POST',dict(body='a'*5001))[0]==422,'Post length bounded')
 body='<img src=x onerror=alert(1)> Club update'
 status,result=author.call('/clubs/1/posts','POST',dict(body=body));pid=result['data']['id'];post='/clubs/1/posts/'+str(pid)
 check(status==201,'Community post persisted')
 check(author.last_headers['Location']=='/api/v1'+post,'Created post has a readable resource Location')
 detail=author.call(post)[1]['data']
 check(detail['body']==body and 'moderation_reason' not in detail,'Individual post read excludes internal moderation fields')
 check(author.call('/clubs/1/posts')[1]['data'][0]['body']==body,'Feed returns submitted plain text')
 status,html=author.html('?page=club&club_id=1&tab=community')
 check(status==200 and '&lt;img src=x onerror=alert(1)&gt;' in html and '<img src=x' not in html,'Rendered post escaped against stored XSS')
 check(author.call('/clubs/1/posts?per_page=1')[1]['meta']['pagination']['total']==1,'Community pagination metadata')
 check(author.call('/clubs/1/posts?per_page=101')[0]==422,'Feed pagination bounded')
 check(mate.call(post,'PATCH',dict(body='Steal'))[0]==403,'Teammate cannot edit another author')
 check(author.call(post,'PATCH',dict(body='Updated training plan'))[0]==200,'Author can edit own post')
 check(mate.call(post+'/reaction','PUT')[1]['data']['reactions']==1,'Member can react')
 check(mate.call(post+'/reaction','PUT')[1]['data']['reactions']==1,'Reaction is idempotent')
 check(outsider.call(post+'/reaction','PUT')[0]==403,'Outsider reaction denied')
 check(mate.call(post+'/reaction','DELETE')[1]['data']['reactions']==0,'Reaction removed')
 check(mate.call(post+'/reaction','DELETE')[1]['data']['reactions']==0,'Repeated reaction removal idempotent')
 status,result=mate.call(post+'/comments','POST',dict(body='I will be there!'));cid=result['data']['id']
 check(status==201,'Member comment created')
 check(len(author.call(post+'/comments')[1]['data'])==1,'Comments readable by teammates')
 check(author.call(post+'/comments/'+str(cid),'DELETE')[0]==403,'Another member cannot delete a comment')
 check(mate.call(post+'/comments/'+str(cid),'DELETE')[0]==200,'Comment author can remove it')
 cid=mate.call(post+'/comments','POST',dict(body='Moderator test'))[1]['data']['id']
 check(admin.call(post+'/comments/'+str(cid),'DELETE')[0]==200,'Moderator can remove a comment')
 check(mate.call(post+'/reports','POST',dict(reason='Please review this training advice'))[0]==201,'Private report submitted')
 check(mate.call(post+'/reports','POST',dict(reason='Duplicate'))[0]==409,'Duplicate report prevented')
 feed=author.call('/clubs/1/posts')[1]['data'][0]
 check('reason' not in feed and 'reports' not in feed,'Reports absent from member feed')
 check(author.call('/admin/community')[0]==403,'Member cannot read moderation queue')
 check(author.call('/admin/community/posts/'+str(pid),'PATCH',dict(status='hidden',reason='Forged'))[0]==403,'Member cannot moderate')
 check(admin.call('/admin/community?status=reported')[1]['data'][0]['reports']==1,'Moderator queue shows pending report')
 check(admin.call('/admin/community/posts/'+str(pid)+'/reports')[1]['data'][0]['reason']=='Please review this training advice','Moderator can read report details')
 check(author.call('/admin/community/posts/'+str(pid)+'/reports')[0]==403,'Report details remain restricted to administrators')
 check(admin.call('/admin/community/posts/'+str(pid),'PATCH',dict(status='hidden',reason='Needs a safety clarification'))[0]==200,'Moderator hides post')
 check(author.call('/clubs/1/posts')[1]['data']==[],'Hidden post absent from member feed')
 check(author.call(post+'/comments')[0]==404,'Hidden post comments inaccessible')
 check(author.call(post)[0]==404,'Hidden post detail inaccessible')
 check(author.call(post+'/reaction','PUT')[0]==404,'Hidden post reactions blocked')
 check(admin.call('/admin/community?status=reported')[1]['data']==[],'Decision resolves pending reports')
 check(admin.call('/admin/community?status=hidden')[1]['data'][0]['id']==pid,'Hidden post retained for review')
 check(author.call(post,'PATCH',dict(body='Author edits after moderation'))[0]==200,'Author can correct own hidden post')
 check(author.call('/clubs/1/posts')[1]['data']==[],'Author edit cannot unhide moderated content')
 check(admin.call('/admin/community/posts/'+str(pid),'PATCH',dict(status='visible',reason='Clarification reviewed'))[0]==200,'Moderator restores corrected post')
 check(len(author.call('/clubs/1/posts')[1]['data'])==1,'Restored post visible again')
 check(mate.call(post,'DELETE')[0]==403,'Teammate cannot delete another author post')
 check(author.call('/clubs/2/posts/'+str(pid),'DELETE')[0]==403,'Cross-club post ID cannot bypass access')
 check(admin.call('/admin/community?status=invalid')[0]==422,'Moderation status filter validated')
 status,html=outsider.html('?page=club&club_id=1&tab=community')
 check(status==200 and 'Your team is one step away.' in html and 'Author edits after moderation' not in html,'Nonmember UI shows membership gate without content leak')
 check(author.call('/me/memberships/'+str(membership),'DELETE')[0]==200,'Member can leave club')
 check(author.call('/clubs/1/posts')[0]==403,'Leaving club immediately revokes feed access')
 membership=author.call('/clubs/1/memberships','POST')[1]['data']['id'];admin.call('/admin/memberships/'+str(membership),'PATCH',dict(status='approved'))
 check(author.call(post,'DELETE')[0]==200,'Author deletes own post')
 check(dbvalue('SELECT body FROM club_posts WHERE id='+str(pid))=='','Deleted post content erased')
 check(admin.call('/admin/community/posts/'+str(pid),'PATCH',dict(status='visible',reason='Attempt restore'))[0]==404,'Deleted posts cannot be restored by moderation')
 check(author.call('/clubs/1/posts')[1]['data']==[],'Deleted post absent from feed')
 check(author.call(post)[0]==404,'Deleted post detail inaccessible')
 post2=author.call('/clubs/1/posts','POST',dict(body='Migration preservation check'))[1]['data']['id']
 before=dbvalue('SELECT COUNT(*) FROM users')
 subprocess.check_output([PHP,str(ROOT/'bin/migrate.php')],env=env,text=True);subprocess.check_output([PHP,str(ROOT/'bin/migrate.php')],env=env,text=True)
 check(dbvalue('SELECT COUNT(*) FROM users')==before and dbvalue('SELECT body FROM club_posts WHERE id='+str(post2))=='Migration preservation check','Repeat migration preserves users and community data')
 check(dbvalue('SELECT COUNT(*) FROM schema_migrations WHERE version="20261017_club_communities.sql"')==1,'Migration version recorded once')
 check(dbvalue('SELECT COUNT(*) FROM audit_logs WHERE action="community_moderation"')==2,'Moderation decisions auditable')
 # A deletion that commits while a comment waits for the parent lock must win.
 lockcode=f'require {json.dumps(str(ROOT/"app/bootstrap.php"))}; db()->beginTransaction(); query("SELECT id FROM club_posts WHERE id=? FOR UPDATE",[{post2}]); echo "ready",PHP_EOL; flush(); fgets(STDIN); query("UPDATE club_posts SET status=?,body=? WHERE id=?",["deleted","",{post2}]); db()->commit();'
 locked=subprocess.Popen([PHP,'-r',lockcode],env=env,stdin=subprocess.PIPE,stdout=subprocess.PIPE,stderr=subprocess.PIPE,text=True)
 if locked.stdout.readline().strip()!='ready':raise AssertionError('Race fixture could not lock parent')
 with concurrent.futures.ThreadPoolExecutor(max_workers=1) as pool:
  waiting=pool.submit(author.call,'/clubs/1/posts/'+str(post2)+'/comments','POST',{'body':'Racing comment'})
  try:waiting.result(timeout=.2)
  except concurrent.futures.TimeoutError:pass
  finally:locked.stdin.write('release\n');locked.stdin.flush()
  locked.wait(timeout=5)
  race_status,race_result=waiting.result(timeout=10)
  if race_status!=404:print('Race response:',race_status,race_result.get('error'))
  check(race_status==404,'Concurrent deletion blocks a stale comment write')
 check(dbvalue('SELECT COUNT(*) FROM club_comments WHERE post_id='+str(post2))==0,'Deleted parent retains no racing comment')
 print(f'{checks} community checks passed.')
except Exception:
 serverlog.seek(0);print(serverlog.read()[-2500:]);raise
finally:server.terminate();server.wait(timeout=5);serverlog.close()
