"""REST integration checks against a disposable, real MySQL database."""
import base64, hashlib, hmac, http.cookiejar, json, os, secrets, socket, struct, subprocess, time, urllib.error, urllib.parse, urllib.request
from pathlib import Path
ROOT=Path(__file__).resolve().parents[1]
PHP=os.environ.get('PHP_BIN','php')
env=dict(os.environ,DB_NAME='sportshub_test',APP_ENV='development',APP_URL='http://localhost:8082',TEST_PASSWORD=secrets.token_urlsafe(24))
fixture=subprocess.check_output([PHP,str(ROOT/'tests/fixtures.php')],env=env,text=True)
ids=json.loads(fixture[fixture.index('{'):])
subprocess.check_output([PHP,str(ROOT/'bin/demo-users.php'),'--confirm-demo'],env=env,text=True)
server=subprocess.Popen([PHP,'-S','127.0.0.1:8082','-t',str(ROOT/'public'),str(ROOT/'public/router.php')],env=env,stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL)
checks=0
class Client:
 def __init__(self):
  self.opener=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar())); self.csrf=None
 def call(self,path,method='GET',data=None,token=True,content_type='application/json',origin=None,raw=None):
  if method!='GET' and self.csrf is None: self.call('/auth/session')
  headers={'Accept':'application/json'}
  if method!='GET':
   headers['Content-Type']=content_type
   if token: headers['X-CSRF-Token']=self.csrf
  if origin: headers['Origin']=origin
  body=raw if raw is not None else (None if method=='GET' else json.dumps(data or {}).encode())
  request=urllib.request.Request('http://127.0.0.1:8082/api/v1'+path,method=method,headers=headers,data=body)
  try: response=self.opener.open(request)
  except urllib.error.HTTPError as ex: response=ex
  result=json.loads(response.read()); self.csrf=result.get('meta',{}).get('csrf_token',self.csrf)
  return response.status,result,response.headers
 def login(self,email,password):return self.call('/auth/login','POST',dict(email=email,password=password))
def check(value,label):
 global checks
 if not value:raise AssertionError(label)
 checks+=1;print('PASS',label)
def dbvalue(sql):
 code='require '+json.dumps(str(ROOT/'app/bootstrap.php'))+'; echo json_encode(query('+json.dumps(sql)+')->fetchColumn());'
 return json.loads(subprocess.check_output([PHP,'-r',code],env=env,text=True))
def totp(secret):
 digest=hmac.new(base64.b32decode(secret),struct.pack('>Q',int(time.time())//30),hashlib.sha1).digest();offset=digest[-1]&15
 return str((struct.unpack('>I',digest[offset:offset+4])[0]&0x7fffffff)%1000000).zfill(6)
try:
 for _ in range(50):
  try:
   socket.create_connection(('127.0.0.1',8082),.1).close();break
  except OSError:time.sleep(.1)
 guest,admin,student=Client(),Client(),Client()
 check(guest.call('/health')[1]['data']['status']=='ok','JSON health/database readiness')
 status,result,headers=guest.call('/auth/session')
 check(status==200 and result['data']['user'] is None and len(guest.csrf)==64,'Anonymous session and CSRF bootstrap')
 check(headers['Content-Type'].startswith('application/json') and headers['Cache-Control']=='no-store','JSON content type and no-cache headers')
 check(headers['X-Request-ID']==result['meta']['request_id'],'Traceable request IDs')
 check(guest.call('/me')[0]==401,'Protected resource returns JSON 401')
 check(guest.call('/admin/clubs')[0]==401,'Anonymous admin denied')
 check(guest.call('/auth/login','POST',{},token=False)[0]==403,'Missing CSRF header rejected')
 check(guest.call('/auth/login','POST',{},origin='https://evil.example')[0]==403,'Foreign origin rejected')
 check(guest.call('/auth/login','POST',{},content_type='text/plain')[0]==415,'Non-JSON content rejected')
 check(guest.call('/auth/login','POST',raw=b'{bad')[0]==400,'Malformed JSON rejected')
 check(guest.call('/auth/login','POST',raw=b'[]')[0]==400,'JSON arrays rejected as request object')
 check(guest.call('/auth/login','POST',dict(email=['nested'],password='test'))[0]==422,'Nested payload rejected')
 check(guest.call('/auth/login','POST',raw=b'{"x":"'+b'a'*65536+b'"}')[0]==413,'Oversized JSON rejected')
 check(guest.call('/unknown')[0]==404,'Unknown route JSON 404')
 check(guest.call('/clubs','PATCH')[0]==405,'Unsupported verb 405')
 check(guest.call('/clubs','PATCH')[2]['Allow']=='GET','Allow header on unsupported verb')
 for path in ['/clubs','/tournaments','/events','/resources','/results','/standings']:
  status,result,_=guest.call(path);check(status==200 and isinstance(result['data'],list),'Public GET '+path)
 for entity in ['clubs','tournaments','events','resources']:
  check(guest.call('/'+entity+'/1')[0]==200,'Public detail '+entity)
  check(guest.call('/'+entity+'/999999')[0]==404,'Missing detail '+entity)
 result=guest.call('/clubs?per_page=2&page=2')[1]
 check(len(result['data'])==2 and result['meta']['pagination']['total']==4,'Pagination count and second page')
 check(guest.call('/clubs?per_page=101')[0]==422,'Pagination bounds validated')
 check(guest.call('/clubs?q=Cricket')[1]['meta']['pagination']['total']==1,'Search filter')
 check(guest.call('/clubs?sport=Football')[1]['data'][0]['sport']=='Football','Sport filter')
 check(guest.call('/clubs?q=%27+OR+1%3D1+--')[1]['data']==[],'SQL input safely parameterized')
 check(guest.call('/clubs?q[]=x')[0]==422,'Array filter rejected')
 check(guest.call('/auth/register','POST',dict(name='API Student',email='api@example.invalid',student_id='API001',faculty='Computing',password=env['TEST_PASSWORD'],role='admin'))[0]==422,'Role mass assignment rejected')
 result=student.call('/auth/register','POST',dict(name='API Student',email='api@example.invalid',student_id='API001',faculty='Computing',password=env['TEST_PASSWORD']))
 check(result[0]==201 and result[1]['data']['id']>0,'REST account creation 201')
 check(student.login('api@example.invalid','incorrectpassword')[0]==401,'Invalid credentials JSON 401')
 check(student.login('api@example.invalid',env['TEST_PASSWORD'])[0]==200,'REST student login')
 result=student.call('/me')[1]['data']
 check(result['role']=='student' and not any(key in result for key in ['password_hash','totp_secret','totp_last_counter']),'User response excludes sensitive fields')
 check(student.call('/admin/reports')[0]==403,'Student admin read denied')
 check(student.call('/admin/clubs','POST',{})[0]==403,'Student admin write denied')
 check(admin.login('admin@example.invalid',env['TEST_PASSWORD'])[0]==200,'REST admin login')
 result=student.call('/clubs/1/memberships','POST');membership=result[1]['data']['id']
 check(result[0]==201 and result[1]['data']['status']=='pending','REST membership application')
 check(student.call('/clubs/1/memberships','POST')[0]==422,'Duplicate membership rejected')
 check(student.call('/events/1/attendance','POST')[0]==422,'Unapproved attendance rejected')
 check(admin.call('/admin/memberships/'+str(membership),'PATCH',dict(status='approved'))[0]==200,'REST membership approval')
 result=student.call('/events/1/attendance','POST');attendance=result[1]['data']['id']
 check(result[0]==201,'REST event registration')
 check(admin.call('/admin/attendance/'+str(attendance),'PATCH',dict(status='attended'))[0]==422,'Future attendance marking rejected')
 result=student.call('/tournaments/1/registrations','POST');registration=result[1]['data']['id']
 check(result[0]==201,'REST tournament registration')
 check(student.call('/tournaments/1/registrations','POST')[0]==422,'Duplicate tournament registration rejected')
 start=time.strftime('%Y-%m-%dT10:00',time.localtime(time.time()+30*86400));end=start.replace('T10:00','T12:00')
 result=student.call('/bookings','POST',dict(resource_id=1,starts_at=start,ends_at=end,purpose='API training'));booking=result[1]['data']['id']
 check(result[0]==201,'REST facility booking')
 check(student.call('/bookings','POST',dict(resource_id=1,starts_at=start,ends_at=end,purpose='Collision'))[0]==422,'REST overlapping booking rejected')
 check(admin.call('/admin/bookings/'+str(booking),'PATCH',dict(status='approved'))[0]==200,'REST booking approval')
 for path in ['/me/memberships','/me/registrations','/me/attendance','/me/bookings','/me/security']:
  check(student.call(path)[0]==200,'Personal GET '+path)
 other=Client();other.login('other@example.invalid',env['TEST_PASSWORD'])
 check(other.call('/me/memberships/'+str(membership),'DELETE')[0]==404,'Ownership enforced on REST cancellation')
 check(student.call('/me/bookings/'+str(booking),'DELETE')[0]==200,'REST booking cancellation')
 check(dbvalue('SELECT status FROM bookings WHERE id='+str(booking))=='cancelled','REST booking cancellation retained in database')
 check(student.call('/me/bookings/'+str(booking),'DELETE')[0]==409,'Repeated cancellation returns conflict')
 check(student.call('/me/registrations/'+str(registration),'DELETE')[0]==200,'REST tournament cancellation')
 check(student.call('/me/attendance/'+str(attendance),'DELETE')[0]==200,'REST session cancellation')
 check(student.call('/me/memberships/'+str(membership),'DELETE')[0]==200,'REST membership cancellation')
 records={
  'clubs':dict(name='API tennis',sport='Tennis',description='API club',coach='Coach',location='Court',capacity=10),
  'tournaments':dict(name='API cup',sport='Tennis',description='API tournament',location='Court',capacity=2,status='open',starts_at=start,ends_at=end,registration_deadline=start.replace('T10:00','T09:00')),
  'events':dict(title='API match',club_id=1,tournament_id=None,kind='match',location='Court',starts_at=start,ends_at=end,opponent='Visitors',home_score=2,away_score=1),
  'resources':dict(name='API kit',kind='equipment',description='API kit',location='Office')
 }
 for entity,payload in records.items():
  status,result,headers=admin.call('/admin/'+entity,'POST',payload);record_id=result['data']['id']
  check(status==201,'REST creates '+entity)
  check(headers['Location'].endswith('/'+entity+'/'+str(record_id)),'Created resource Location '+entity)
  check(admin.call('/admin/'+entity+'/'+str(record_id))[0]==200,'Admin detail '+entity)
  payload['title' if entity=='events' else 'name']='Updated API record'
  check(admin.call('/admin/'+entity+'/'+str(record_id),'PUT',payload)[0]==200,'REST replaces '+entity)
  check(admin.call('/admin/'+entity+'/'+str(record_id),'PUT',{'role':'admin'})[0]==422,'Unknown update fields rejected '+entity)
  check(admin.call('/admin/'+entity+'/'+str(record_id),'DELETE')[0]==200,'REST deletes '+entity)
  check(guest.call('/'+entity+'/'+str(record_id))[0]==404,'Deleted resource absent '+entity)
 check(admin.call('/admin/clubs/999999','DELETE')[0]==404,'Delete missing admin resource returns 404')
 for path in ['/admin/clubs','/admin/tournaments','/admin/events','/admin/resources','/admin/memberships','/admin/bookings','/admin/attendance','/admin/reports','/admin/audit-logs']:
  check(admin.call(path)[0]==200,'Admin GET '+path)
 check(guest.call('/results?tournament_id=999999')[1]['data']==[],'Tournament result filtering')
 check(guest.call('/standings?tournament_id=999999')[1]['data']==[],'Tournament standings filtering')
 check(student.call('/me/2fa/setup','POST')[0]==200,'REST 2FA setup')
 # Fetching setup again generates a new pending secret; only the most recent is valid.
 secret=student.call('/me/2fa/setup','POST')[1]['data']['secret']
 result=student.call('/me/2fa','POST',dict(password=env['TEST_PASSWORD'],code=totp(secret)))
 check(result[0]==200 and len(result[1]['data']['recovery_codes'])==8,'REST enrollment and one-time recovery code response')
 codes=result[1]['data']['recovery_codes']
 check(student.call('/me/security')[1]['data']['two_factor_enabled'],'REST security status updated')
 check('recovery_codes' not in student.call('/me/security')[1]['data'],'Security endpoint never reveals recovery codes')
 check(student.call('/auth/logout','POST')[0]==200,'REST logout')
 check(student.call('/me')[0]==401,'Session access revoked after logout')
 result=student.login('api@example.invalid',env['TEST_PASSWORD'])
 check(result[1]['data']['requires_2fa'] and result[1]['data']['user'] is None,'Password leaves 2FA session unauthenticated')
 check(student.call('/me')[0]==401,'Pending 2FA denies protected resources')
 check(student.call('/auth/2fa','POST',dict(code=totp(secret)))[0]==401,'Enrollment step replay rejected via API')
 check(student.call('/auth/2fa','POST',dict(code=codes[0]))[0]==200,'REST recovery-code login')
 check(student.call('/me/security')[1]['data']['recovery_codes_remaining']==7,'Recovery code consumed')
 student.call('/auth/logout','POST');student.login('api@example.invalid',env['TEST_PASSWORD'])
 check(student.call('/auth/2fa','POST',dict(code=codes[0]))[0]==401,'REST used recovery code rejected')
 check(student.call('/auth/2fa','POST',dict(code=codes[1]))[0]==200,'Another unused recovery code accepted')
 check(student.call('/me/2fa','DELETE',dict(password='bad',code=codes[2]))[0]==422,'2FA disable requires valid password')
 check(student.call('/me/2fa','DELETE',dict(password=env['TEST_PASSWORD'],code=codes[2]))[0]==200,'REST 2FA disable requires both factors')
 check(not student.call('/me/security')[1]['data']['two_factor_enabled'],'REST 2FA disabled state')
 demo=Client()
 check(demo.login('student@sportshub.test','StudentSport!2026')[1]['data']['user']['role']=='student','Disposable student demo login')
 demo.call('/auth/logout','POST')
 check(demo.login('admin@sportshub.test','AdminSport!2026')[1]['data']['user']['role']=='admin','Disposable admin demo login')
 demo.call('/auth/logout','POST')
 result=demo.login('athlete@sportshub.test','AthleteSport!2026')
 check(result[1]['data']['requires_2fa'],'Demo athlete requires 2FA')
 check(demo.call('/auth/2fa','POST',dict(code=totp('JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP')))[0]==200,'Demo authenticator key works')
 before=dbvalue('SELECT COUNT(*) FROM users')
 subprocess.check_output([PHP,str(ROOT/'bin/demo-users.php'),'--confirm-demo'],env=env,text=True)
 check(dbvalue('SELECT COUNT(*) FROM users')==before,'Demo seeder repeatable without overwriting identities')
 refused=subprocess.run([PHP,str(ROOT/'bin/demo-users.php'),'--confirm-demo'],env=dict(env,APP_ENV='production'),stdout=subprocess.PIPE,stderr=subprocess.PIPE)
 check(refused.returncode!=0 and dbvalue('SELECT COUNT(*) FROM users')==before,'Production demo seeding refused')
 locked=Client()
 for _ in range(8):locked.login('locked@example.invalid','invalidpassword')
 status,result,headers=locked.login('locked@example.invalid','invalidpassword')
 check(status==429 and headers['Retry-After']=='900','REST rate limit status and Retry-After')
 print(f'{checks} REST API checks passed.')
finally:
 server.terminate();server.wait(timeout=5)
