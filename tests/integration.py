"""Real HTTP + MySQL checks. Uses ONLY the separate sportshub_test database."""
import base64, hashlib, hmac, http.cookiejar, json, os, re, secrets, socket, struct, subprocess, time, urllib.error, urllib.parse, urllib.request
from pathlib import Path
ROOT=Path(__file__).resolve().parents[1]
PHP=os.environ.get('PHP_BIN','php')
env=dict(os.environ, DB_NAME='sportshub_test', APP_ENV='development', APP_URL='http://localhost:8081', TEST_PASSWORD=secrets.token_urlsafe(24))
fixture=subprocess.check_output([PHP,str(ROOT/'tests/fixtures.php')],env=env,text=True)
ids=json.loads(fixture[fixture.index('{'):])
server=subprocess.Popen([PHP,'-S','127.0.0.1:8081','-t',str(ROOT/'public'),str(ROOT/'public/router.php')],env=env,stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL)
checks=0
class Browser:
    def __init__(self): self.opener=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
    def request(self,page='home',data=None):
        request=urllib.request.Request('http://127.0.0.1:8081/?page='+page, data=None if data is None else urllib.parse.urlencode(data).encode())
        try: response=self.opener.open(request)
        except urllib.error.HTTPError as ex: response=ex
        return response.status,response.read().decode(),response.headers
    def post(self,page,action,**fields):
        status,html,_=self.request(page)
        csrf=re.search(r'name="csrf" value="([a-f0-9]+)"',html)
        if not csrf: raise AssertionError('No CSRF token on '+page)
        return self.request(page,dict(action=action,csrf=csrf.group(1),**fields))
def check(value,label):
    global checks
    if not value: raise AssertionError(label)
    checks+=1
    print('PASS',label)
def dbvalue(sql):
    code='require '+json.dumps(str(ROOT/'app/bootstrap.php'))+'; echo json_encode(query('+json.dumps(sql)+')->fetchColumn());'
    return json.loads(subprocess.check_output([PHP,'-r',code],env=env,text=True))
def totp(secret):
    counter=int(time.time())//30
    digest=hmac.new(base64.b32decode(secret),struct.pack('>Q',counter),hashlib.sha1).digest()
    offset=digest[-1]&15
    return str((struct.unpack('>I',digest[offset:offset+4])[0]&0x7fffffff)%1000000).zfill(6)
try:
    for _ in range(50):
        try:
            with socket.create_connection(('127.0.0.1',8081),timeout=.1): break
        except OSError: time.sleep(.1)
    student,admin,guest=Browser(),Browser(),Browser()
    for page in ['home','clubs','tournaments','schedule','results','facilities','login','register']:
        status,html,headers=guest.request(page)
        check(status==200 and 'SportsHub' in html,'Public page '+page)
    _,_,headers=guest.request()
    check("frame-ancestors 'none'" in headers['Content-Security-Policy'],'CSP and clickjacking protection')
    check('HttpOnly' in Browser().request('login')[2].get('Set-Cookie',''),'HttpOnly session cookie')
    check(guest.request('login',dict(action='login',email='x',password='x'))[0]==419,'Missing CSRF rejected')
    check(guest.request('admin')[1].find('Welcome back.')>=0,'Unauthenticated admin redirects to login')
    result=student.post('register','register',name='<script>Athlete</script>',email='student@example.invalid',student_id='STU001',faculty='Computing',password=env['TEST_PASSWORD'])
    check(result[0]==200 and 'Account created' in result[1],'Student registration')
    check(student.post('login','login',email='student@example.invalid',password='wrongpasswordlong')[0]==422,'Wrong password rejected')
    check(student.post('login','login',email='student@example.invalid',password=env['TEST_PASSWORD'])[0]==200,'Student login')
    check('&lt;script&gt;Athlete&lt;/script&gt;' in student.request('dashboard')[1],'Stored user text escaped')
    check(student.request('admin')[0]==403,'Student cannot access admin')
    check(student.post('dashboard','save',entity='clubs',id=0)[0]==403,'Student cannot submit admin mutation')
    check(student.post('clubs','join',id=ids['club'])[0]==200,'Membership application')
    check(student.post('clubs','join',id=ids['club'])[0]==422,'Duplicate membership prevented')
    check(student.post('schedule','attend',id=ids['future_event'])[0]==422,'Unapproved club session registration rejected')
    check(admin.post('login','login',email='admin@example.invalid',password=env['TEST_PASSWORD'])[0]==200,'Admin login')
    membership=dbvalue('SELECT id FROM memberships WHERE user_id=(SELECT id FROM users WHERE email="student@example.invalid")')
    check(admin.post('approvals','review',entity='memberships',id=membership,status='approved')[0]==200,'Admin approves membership')
    check(student.post('schedule','attend',id=ids['future_event'])[0]==200,'Approved member registers for training')
    attendance=dbvalue('SELECT id FROM attendance LIMIT 1')
    check(admin.post('reports','attendance',id=attendance)[0]==422,'Future attendance cannot be fabricated')
    check(student.post('tournaments','register_tournament',id=ids['tournament'])[0]==200,'Tournament registration')
    check(student.post('tournaments','register_tournament',id=ids['tournament'])[0]==422,'Duplicate tournament registration rejected')
    start=time.strftime('%Y-%m-%dT10:00',time.localtime(time.time()+30*86400)); end=start.replace('T10:00','T12:00')
    check(student.post('facilities','book',resource_id=1,starts_at=start,ends_at=end,purpose='Team training')[0]==200,'Facility booking')
    check(student.post('facilities','book',resource_id=1,starts_at=start,ends_at=end,purpose='Overlap')[0]==422,'Overlapping booking rejected')
    booking=dbvalue('SELECT id FROM bookings LIMIT 1')
    check(admin.post('approvals','review',entity='bookings',id=booking,status='approved')[0]==200,'Booking approval')
    fields=dict(name='Test Club',sport='Tennis',description='A test club',coach='Coach',location='Court',capacity=10)
    check(admin.post('manage_clubs','save',entity='clubs',id=0,**fields)[0]==200,'Admin creates club')
    cid=dbvalue('SELECT id FROM clubs WHERE name="Test Club"'); fields['name']='Updated Club'
    check(admin.post('manage_clubs','save',entity='clubs',id=cid,**fields)[0]==200,'Admin updates club')
    check(dbvalue('SELECT name FROM clubs WHERE id='+str(cid))=='Updated Club','Update persisted to MySQL')
    check(admin.post('manage_clubs','delete',entity='clubs',id=cid)[0]==200,'Admin deletes club')
    check(not dbvalue('SELECT COUNT(*) FROM clubs WHERE id='+str(cid)),'Deletion persisted')
    for page in ['admin','approvals','reports','manage_clubs','manage_events','manage_tournaments','manage_resources']:
        check(admin.request(page)[0]==200,'Admin page '+page)
    tournament_fields=dict(name='Test tournament',sport='Football',description='Test event',location='Ground',starts_at=start,ends_at=end,registration_deadline=start.replace('T10:00','T09:00'),capacity=2,status='open')
    check(admin.post('manage_tournaments','save',entity='tournaments',id=0,**tournament_fields)[0]==200,'Admin creates tournament')
    tid=dbvalue('SELECT id FROM tournaments WHERE name="Test tournament"')
    tournament_fields['name']='Updated tournament'
    check(admin.post('manage_tournaments','save',entity='tournaments',id=tid,**tournament_fields)[0]==200,'Admin updates tournament')
    check(admin.post('manage_tournaments','delete',entity='tournaments',id=tid)[0]==200,'Admin deletes tournament')
    resource_fields=dict(name='Test resource',kind='equipment',description='Training gear',location='Office')
    check(admin.post('manage_resources','save',entity='resources',id=0,**resource_fields)[0]==200,'Admin creates equipment')
    rid=dbvalue('SELECT id FROM resources WHERE name="Test resource"');resource_fields['name']='Updated equipment'
    check(admin.post('manage_resources','save',entity='resources',id=rid,**resource_fields)[0]==200,'Admin updates equipment')
    check(admin.post('manage_resources','delete',entity='resources',id=rid)[0]==200,'Admin deletes equipment')
    event_fields=dict(title='Test match',club_id=1,tournament_id='',kind='match',location='Ground',starts_at=start,ends_at=end,opponent='Visitors',home_score=2,away_score=1)
    check(admin.post('manage_events','save',entity='events',id=0,**event_fields)[0]==200,'Admin creates scored fixture')
    eid=dbvalue('SELECT id FROM events WHERE title="Test match"');event_fields['home_score']=3
    check(admin.post('manage_events','save',entity='events',id=eid,**event_fields)[0]==200,'Admin updates scores')
    check('3 : 1' in guest.request('results')[1],'Recorded scores visible on results')
    check(admin.post('manage_events','delete',entity='events',id=eid)[0]==200,'Admin deletes fixture')
    code='require '+json.dumps(str(ROOT/'app/bootstrap.php'))+'; query("INSERT INTO attendance(user_id,event_id) VALUES((SELECT id FROM users WHERE email=?),?)", ["student@example.invalid",'+str(ids['past_event'])+']);'
    subprocess.check_call([PHP,'-r',code],env=env)
    past_attendance=dbvalue('SELECT id FROM attendance WHERE event_id='+str(ids['past_event']))
    check(admin.post('reports','attendance',id=past_attendance)[0]==200,'Completed session attendance recorded')
    check('Attended' in student.request('dashboard')[1],'Student participation history updated')
    check(student.post('security','setup_2fa')[0]==200,'2FA setup initiated')
    secret=re.search(r'<code>([A-Z2-7]{32})</code>',student.request('security')[1]).group(1)
    result=student.post('security','enable_2fa',password=env['TEST_PASSWORD'],code=totp(secret))
    check(result[0]==200 and '2FA enabled' in result[1],'Authenticator enrolled')
    recovery=re.findall(r'<code>([a-f0-9]{16})</code>',result[1])
    check(len(recovery)==8,'Eight recovery codes displayed once')
    check(not any(code in student.request('security')[1] for code in recovery),'Recovery codes not redisplayed')
    stored=dbvalue('SELECT totp_secret FROM users WHERE email="student@example.invalid"')
    check(stored!=secret and len(stored)>32,'TOTP secret encrypted at rest')
    student.post('dashboard','logout')
    result=student.post('login','login',email='student@example.invalid',password=env['TEST_PASSWORD'])
    check('One more step.' in result[1],'Password alone does not complete 2FA login')
    check(student.request('dashboard')[1].find('Welcome back.')>=0,'Pending 2FA session has no student access')
    # Dashboard redirect does not erase the pending challenge.
    check(student.post('verify','verify',code=totp(secret))[0]==422,'Enrollment code replay rejected')
    check(student.post('verify','verify',code=recovery[0])[0]==200,'Recovery code completes login')
    check(dbvalue('SELECT COUNT(*) FROM recovery_codes')==7,'Recovery code consumed atomically')
    student.post('dashboard','logout'); student.post('login','login',email='student@example.invalid',password=env['TEST_PASSWORD'])
    check(student.post('verify','verify',code=recovery[0])[0]==422,'Used recovery code rejected')
    # Advance past the enrollment time step without weakening replay checks.
    remaining=30-int(time.time())%30
    time.sleep(remaining+1)
    check(student.post('verify','verify',code=totp(secret))[0]==200,'Real authenticator code completes login')
    check(student.post('dashboard','cancel',entity='bookings',id=booking)[0]==200,'Student cancels own booking')
    check(dbvalue('SELECT status FROM bookings WHERE id='+str(booking))=='cancelled','Cancellation persisted')
    # An owned-record condition prevents one student cancelling another student’s participation.
    other=Browser(); other.post('login','login',email='other@example.invalid',password=env['TEST_PASSWORD'])
    other.post('dashboard','cancel',entity='memberships',id=membership)
    check(dbvalue('SELECT COUNT(*) FROM memberships WHERE id='+str(membership))==1,'Cross-user deletion prevented')
    check(student.request('clubs&q=%27+OR+1%3D1+--')[0]==200,'Search SQL metacharacters handled safely')
    locked=Browser()
    for _ in range(8): locked.post('login','login',email='rate@example.invalid',password='incorrectpassword')
    check('Too many attempts' in locked.post('login','login',email='rate@example.invalid',password='incorrectpassword')[1],'Repeated login attempts rate limited')
    print(f'{checks} HTTP/database checks passed.')
finally:
    server.terminate(); server.wait(timeout=5)
