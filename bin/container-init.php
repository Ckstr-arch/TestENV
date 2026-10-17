<?php
declare(strict_types=1);
require_once __DIR__.'/../app/bootstrap.php';
if (PHP_SAPI!=='cli') exit;
if (config('APP_KEY')==='') {
 if (config('APP_ENV')!=='development') throw new RuntimeException('Production requires an explicit private APP_KEY.');
 $file=ROOT.'/var/app.key';
 if (!is_file($file)) { file_put_contents($file,base64_encode(random_bytes(32)),LOCK_EX); chmod($file,0600); }
}
app_key();
if (config('APP_ENV')!=='development' && config('DEMO_MODE')==='1') throw new RuntimeException('Demo mode is not permitted in production.');
require __DIR__.'/migrate.php';
if (config('DEMO_MODE')==='1') {
 require __DIR__.'/seed.php';
 $argv=['demo-users.php','--confirm-demo']; require __DIR__.'/demo-users.php';
 require __DIR__.'/demo-community.php';
}
echo "Container application initialization completed.\n";
