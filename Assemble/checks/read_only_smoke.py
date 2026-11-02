import urllib.request,urllib.error,json
base='http://127.0.0.1:8080'
checks=0
for path in ['/?page=home','/?page=clubs','/?page=schedule','/?page=tournaments','/?page=results','/?page=facilities','/?page=login','/?page=register']:
 with urllib.request.urlopen(base+path,timeout=5) as r:
  body=r.read().decode();assert r.status==200 and 'SportsHub' in body and 'Warning:' not in body
  assert "frame-ancestors 'none'" in r.headers.get('Content-Security-Policy','')
 checks+=1
with urllib.request.urlopen(base+'/api/v1/health',timeout=5) as r:
 assert json.load(r)['data']['status']=='ok';checks+=1
for path in ['/api/v1/admin/reports','/api/v1/me/memberships']:
 try:urllib.request.urlopen(base+path,timeout=5);raise AssertionError('Private API exposed')
 except urllib.error.HTTPError as ex:assert ex.code==401;checks+=1
for path in ['/.env','/app/bootstrap.php','/database/schema.sql','/bin/setup.php','/tests/fixtures.php','/var/app.key']:
 try:
  with urllib.request.urlopen(base+path,timeout=5) as response:
   body=response.read().decode()
   assert '<!doctype html>' in body.lower() and 'SportsHub' in body and 'hero-copy' in body, 'Unexpected private path response'
 except urllib.error.HTTPError as ex:assert ex.code in (403,404)
 checks+=1
print(f'{checks} read-only HTTP smoke checks passed (8 public pages, health, 2 auth gates, 6 private paths).')
