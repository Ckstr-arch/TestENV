#!/usr/bin/env bash
set -euo pipefail
cd /workspace/TestENV
export PATH="/workspace/toolchain/bin:$PATH"
export LD_LIBRARY_PATH="/workspace/toolchain/root/usr/lib/x86_64-linux-gnu${LD_LIBRARY_PATH:+:$LD_LIBRARY_PATH}"
mkdir -p var/log
if ! python -c 'import socket; socket.create_connection(("127.0.0.1",3307),1).close()' 2>/dev/null; then
  nohup /workspace/toolchain/root/usr/sbin/mariadbd --no-defaults \
    --basedir=/workspace/toolchain/root/usr --datadir=/workspace/toolchain/data \
    --socket=/workspace/toolchain/run/mysql.sock --pid-file=/workspace/toolchain/run/mysql.pid \
    --bind-address=127.0.0.1 --port=3307 --log-error=/workspace/toolchain/run/mysql.log \
    >var/log/database-launch.log 2>&1 &
fi
python - <<'PY'
import socket,time
for attempt in range(100):
 try:
  socket.create_connection(('127.0.0.1',3307),.2).close();break
 except OSError: time.sleep(.1)
else: raise SystemExit('Database did not start; inspect /workspace/toolchain/run/mysql.log')
PY
php -r 'require "app/bootstrap.php"; if ((int)query("SELECT 1")->fetchColumn() !== 1) exit(1);'
if ! python -c 'import socket; socket.create_connection(("127.0.0.1",8080),1).close()' 2>/dev/null; then
  nohup php -S 0.0.0.0:8080 -t public public/router.php >var/log/web.log 2>&1 &
fi
python - <<'PY'
import urllib.request,time
for attempt in range(50):
 try:
  with urllib.request.urlopen('http://127.0.0.1:8080/?page=clubs',timeout=2) as r:
   text=r.read().decode()
   assert r.status==200 and 'One campus. Every sport.' in text and 'Warning' not in text
   assert "frame-ancestors 'none'" in r.headers.get('Content-Security-Policy','')
  with urllib.request.urlopen('http://127.0.0.1:8080/api/v1/health',timeout=2) as r:
   import json
   assert r.status==200 and json.load(r)['data']['status']=='ok'
  break
 except Exception:
  if attempt==49: raise
  time.sleep(.1)
print('SportsHub database and HTTP readiness checks passed.')
PY
