<?php
declare(strict_types=1);
require_once __DIR__.'/../app/bootstrap.php';
if (PHP_SAPI!=='cli') exit;
$failed=false;
function report(bool $ok,string $name): void { global $failed; echo ($ok?'PASS':'FAIL')." $name\n"; if (!$ok) $failed=true; }
report(version_compare(PHP_VERSION,'8.2','>='),'PHP 8.2+');
foreach (['pdo_mysql','mbstring','sodium'] as $ext) report(extension_loaded($ext),'Extension '.$ext);
report(in_array('argon2id',password_algos(),true),'Argon2id support');
try { app_key(); report(true,'APP_KEY has the required length'); } catch (Throwable) { report(false,'APP_KEY missing or invalid'); }
try {
 report((int)query('SELECT 1')->fetchColumn()===1,'Database connection');
 report((string)query('SELECT @@session.time_zone')->fetchColumn()==='+05:30','Database Sri Lanka timezone');
 foreach (['users','clubs','events','tournaments','resources','bookings'] as $table) { query("SELECT COUNT(*) FROM $table"); report(true,'Table '.$table); }
} catch (Throwable) { report(false,'Database connection/schema unavailable; check .env and run bin/migrate.php'); }
report(is_writable(ROOT.'/var') || is_writable(ROOT),'Private session directory can be written');
exit($failed?1:0);
