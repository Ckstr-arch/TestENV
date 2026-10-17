"""Optional Playwright UI checks. Resets ONLY sportshub_test; requires playwright + Chromium."""
import base64, hashlib, hmac, json, os, secrets, socket, struct, subprocess, time
from pathlib import Path
from playwright.sync_api import sync_playwright
ROOT=Path(__file__).resolve().parents[1]
PHP=os.environ.get('PHP_BIN','php')
env=dict(os.environ,DB_NAME='sportshub_test',APP_ENV='development',APP_URL='http://localhost:8083',TEST_PASSWORD=secrets.token_urlsafe(24))
subprocess.check_output([PHP,str(ROOT/'tests/fixtures.php')],env=env,text=True)
server=subprocess.Popen([PHP,'-S','127.0.0.1:8083','-t',str(ROOT/'public'),str(ROOT/'public/router.php')],env=env,stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL)
def totp(secret):
 digest=hmac.new(base64.b32decode(secret),struct.pack('>Q',int(time.time())//30),hashlib.sha1).digest();offset=digest[-1]&15
 return str((struct.unpack('>I',digest[offset:offset+4])[0]&0x7fffffff)%1000000).zfill(6)
try:
 for _ in range(50):
  try:socket.create_connection(('127.0.0.1',8083),.1).close();break
  except OSError:time.sleep(.1)
 with sync_playwright() as p:
  options={'headless':True}
  if os.environ.get('CHROMIUM_PATH'):options['executable_path']=os.environ['CHROMIUM_PATH']
  browser=p.chromium.launch(**options)
  context=browser.new_context(viewport={'width':1440,'height':1000})
  page=context.new_page();errors=[];requests=[]
  page.on('pageerror',lambda e:errors.append(str(e)))
  page.on('request',lambda req:requests.append((req.method,req.url)) if '/api/v1/' in req.url else None)
  page.goto('http://localhost:8083/?page=register')
  for label,value in [('Full name','Browser Athlete'),('Student ID','BROWSER001'),('Email address','browser@example.invalid'),('Password',env['TEST_PASSWORD'])]:page.get_by_label(label,exact=True).fill(value)
  page.get_by_label('Faculty').select_option('Computing');page.get_by_role('button',name='Create account').click();page.wait_for_url('**/?page=login')
  def login(tab,email,password):
   tab.goto('http://localhost:8083/?page=login');tab.get_by_label('Email address').fill(email);tab.get_by_label('Password',exact=True).fill(password);tab.get_by_role('button',name='Sign in').click()
  login(page,'browser@example.invalid','incorrectpassword')
  page.locator('.api-alert').wait_for(state='visible');assert 'incorrect' in page.locator('.api-alert').inner_text()
  page.get_by_label('Password',exact=True).fill(env['TEST_PASSWORD']);page.get_by_role('button',name='Sign in').click();page.wait_for_url('**/?page=dashboard')
  page.goto('http://localhost:8083/?page=clubs');page.locator('article.card').filter(has=page.get_by_role('heading',name='Green Warriors Cricket',exact=True)).get_by_role('button',name='Apply to join').click();page.wait_for_url('**/?page=dashboard')
  assert 'Pending' in page.locator('main').inner_text()
  admincontext=browser.new_context();admin=admincontext.new_page()
  login(admin,'admin@example.invalid',env['TEST_PASSWORD']);admin.wait_for_url('**/?page=admin')
  admin.goto('http://localhost:8083/?page=approvals');admin.get_by_role('button',name='Approve',exact=True).first.click();admin.wait_for_url('**/?page=approvals')
  page.reload();assert 'Approved' in page.locator('main').inner_text()
  page.goto('http://localhost:8083/?page=schedule');page.get_by_role('button',name='Register',exact=False).first.click();page.wait_for_url('**/?page=dashboard')
  page.goto('http://localhost:8083/?page=tournaments');page.get_by_role('button',name='Register for tournament').first.click();page.wait_for_url('**/?page=dashboard')
  page.goto('http://localhost:8083/?page=facilities');form=page.locator('form').filter(has=page.locator('input[name="action"][value="book"]')).first
  start=time.strftime('%Y-%m-%dT10:00',time.localtime(time.time()+20*86400));end=start.replace('T10:00','T12:00')
  form.get_by_label('Start time').fill(start);form.get_by_label('End time').fill(end);form.get_by_label('Booking purpose').fill('Browser training');form.get_by_role('button',name='Request booking').click();page.wait_for_url('**/?page=dashboard')
  admin.goto('http://localhost:8083/?page=manage_clubs')
  for label,value in [('Name','Browser Test Club'),('Sport','Tennis'),('Description','Browser exercise'),('Coach','Coach'),('Location','Court'),('Capacity','12')]:admin.get_by_label(label,exact=True).fill(value)
  admin.get_by_role('button',name='Save record').click();admin.wait_for_url('**/?page=manage_clubs');admin.get_by_role('heading',name='Browser Test Club',exact=True).wait_for()
  admin.locator('article.card').filter(has=admin.get_by_role('heading',name='Browser Test Club',exact=True)).get_by_role('link',name='Edit record').click()
  admin.get_by_label('Name',exact=True).fill('Browser Updated Club');admin.get_by_role('button',name='Save record').click();admin.wait_for_url('**/?page=manage_clubs');admin.get_by_role('heading',name='Browser Updated Club',exact=True).wait_for()
  admin.on('dialog',lambda dialog:dialog.accept())
  with admin.expect_navigation(wait_until='load'):
   admin.locator('article.card').filter(has=admin.get_by_role('heading',name='Browser Updated Club',exact=True)).get_by_role('button',name='Delete').click()
  assert admin.get_by_role('heading',name='Browser Updated Club',exact=True).count()==0
  page.goto('http://localhost:8083/?page=security');page.get_by_role('button',name='Set up authenticator').click();page.wait_for_url('**/?page=security');page.locator('.setup-key code').wait_for()
  secret=page.locator('.setup-key code').inner_text();page.get_by_label('Current password').fill(env['TEST_PASSWORD']);page.get_by_label('Authenticator code',exact=True).fill(totp(secret));page.get_by_role('button',name='Confirm & enable 2FA').click()
  page.locator('.recovery-dialog').wait_for();codes=page.locator('.recovery-dialog code').all_inner_texts();assert len(codes)==8
  page.get_by_role('button',name='I have saved my codes').click();page.wait_for_url('**/?page=security');assert page.locator('.recovery-dialog').count()==0
  page.locator('.profile-menu summary').click();page.get_by_role('button',name='Sign out').click();page.wait_for_url('**/?page=home')
  login(page,'browser@example.invalid',env['TEST_PASSWORD']);page.wait_for_url('**/?page=verify')
  page.get_by_label('Authenticator / recovery code').fill(codes[0]);page.get_by_role('button',name='Verify & sign in').click();page.wait_for_url('**/?page=dashboard')
  page.goto('http://localhost:8083/?page=security');page.get_by_text('Disable two-factor authentication',exact=True).click();page.get_by_label('Current password').fill(env['TEST_PASSWORD']);page.get_by_label('Authenticator or recovery code').fill(codes[1]);page.get_by_role('button',name='Disable 2FA',exact=True).click();page.wait_for_url('**/?page=security')
  page.get_by_role('button',name='Set up authenticator').wait_for(state='visible')
  # Club discussions exercise the actual fetch handlers and server-side membership boundary.
  page.goto('http://localhost:8083/?page=club&club_id=1&tab=community')
  page.get_by_label('Share with your club',exact=True).fill('Browser community training update')
  with page.expect_navigation(wait_until='load'):page.locator('.composer').get_by_role('button',name='Post',exact=False).click()
  post=page.locator('.post-card').filter(has_text='Browser community training update');post.wait_for()
  with page.expect_navigation(wait_until='load'):post.get_by_role('button',name='React to post',exact=True).click()
  post.get_by_role('button',name='Remove reaction',exact=True).wait_for()
  post.get_by_text('Comments (0)',exact=True).click();post.get_by_label('Write a comment').fill('Ready for training!')
  with page.expect_navigation(wait_until='load'):post.get_by_role('button',name='Add comment',exact=True).click()
  post.get_by_text('Comments (1)',exact=True).click();assert post.get_by_text('Ready for training!',exact=True).is_visible()
  post.get_by_text('Post options',exact=True).click();post.get_by_label('Edit post',exact=True).fill('Browser community updated post')
  with page.expect_navigation(wait_until='load'):post.get_by_role('button',name='Save edit',exact=True).click()
  post=page.locator('.post-card').filter(has_text='Browser community updated post');post.wait_for()
  post.get_by_text('Post options',exact=True).click();post.get_by_label('Report reason').fill('Review example for browser verification')
  with page.expect_navigation(wait_until='load'):post.get_by_role('button',name='Report privately').click()
  admin.goto('http://localhost:8083/?page=moderation');admin.get_by_label('Moderation reason').fill('Hidden for browser verification')
  with admin.expect_navigation(wait_until='load'):admin.get_by_role('button',name='Save decision').click()
  page.reload();assert page.locator('.post-card').filter(has_text='Browser community updated post').count()==0
  admin.goto('http://localhost:8083/?page=moderation&status=hidden');admin.get_by_label('Decision').select_option('visible');admin.get_by_label('Moderation reason').fill('Reviewed and restored')
  with admin.expect_navigation(wait_until='load'):admin.get_by_role('button',name='Save decision').click()
  page.reload();page.locator('.post-card').filter(has_text='Browser community updated post').wait_for()
  # Theme preference is browser-local, persists across navigation, and precedes profile in the DOM.
  assert page.locator('html').get_attribute('data-theme')=='dark'
  page.get_by_role('button',name='Switch to light mode').click();page.reload();assert page.locator('html').get_attribute('data-theme')=='light'
  assert page.locator('.theme-toggle + .profile-menu').count()==1
  assert page.locator('.sidebar').get_by_role('link',name='Community moderation',exact=True).count()==0
  page.set_viewport_size({'width':390,'height':900});page.locator('.menu-toggle').click();page.locator('.sidebar').wait_for(state='visible')
  page.keyboard.press('Escape');assert page.locator('.menu-toggle').get_attribute('aria-expanded')=='false'
  page.locator('.sidebar').wait_for(state='hidden')
  for width in [1440,390]:
   page.set_viewport_size({'width':width,'height':900});admin.set_viewport_size({'width':width,'height':900})
   for theme in ['dark','light']:
    for tab in ['community','members','overview','schedule']:
     page.goto('http://localhost:8083/?page=club&club_id=1&tab='+tab);page.evaluate('(t)=>{document.documentElement.dataset.theme=t;localStorage.setItem("sportshub-theme",t)}',theme)
     assert page.locator('body').evaluate('e=>e.scrollWidth<=window.innerWidth'),(width,theme,tab)
    for name in ['admin','approvals','moderation','reports','audit','manage_clubs','manage_events','manage_tournaments','manage_resources']:
     response=admin.goto('http://localhost:8083/?page='+name);admin.evaluate('(t)=>{document.documentElement.dataset.theme=t;localStorage.setItem("sportshub-theme",t)}',theme)
     assert response.status==200,(name,response.status)
     assert admin.locator('body').evaluate('e=>e.scrollWidth<=window.innerWidth'),(width,theme,name)
  page.set_viewport_size({'width':1440,'height':1000});page.goto('http://localhost:8083/?page=dashboard')
  assert any(method=='POST' and '/auth/login' in url for method,url in requests)
  assert any(method=='DELETE' and '/me/2fa' in url for method,url in requests)
  page.locator('.profile-menu summary').click();page.get_by_role('button',name='Sign out').click();page.wait_for_url('**/?page=home')
  for width in [1440,390]:
   page.set_viewport_size({'width':width,'height':900})
   for name in ['home','clubs','schedule','tournaments','results','facilities','login','register']:
    response=page.goto('http://localhost:8083/?page='+name);assert response.status==200,(width,name,response.status)
    assert page.locator('body').evaluate('e=>e.scrollWidth<=window.innerWidth'),(width,name)
  page.goto('http://localhost:8083/');page.locator('.menu-toggle').click();assert page.locator('#navigation').is_visible()
  assert not errors,errors
  browser.close()
 print('Chromium REST-backed registration/login, club approval, session/tournament registration, booking, admin CRUD, 2FA, club discussions/comments/reactions/moderation, theme persistence, role navigation and desktop/mobile checks passed.')
finally:
 server.terminate();server.wait(timeout=5)
