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
 report(mysql_version_supported((string)query('SELECT VERSION()')->fetchColumn()),'MySQL 8+ engine (MariaDB is not supported)');
 report((string)query('SELECT @@session.time_zone')->fetchColumn()==='+05:30','Database Sri Lanka timezone');
 foreach (['users','recovery_codes','login_attempts','clubs','memberships','tournaments','registrations','events','attendance','resources','bookings','audit_logs','club_posts','club_comments','club_reactions','club_reports','schema_migrations'] as $table) { query("SELECT COUNT(*) FROM $table"); report(true,'Table '.$table); }
} catch (Throwable) { report(false,'MySQL 8+ connection/schema unavailable; confirm the engine and private .env, then run bin/migrate.php'); }
report(is_writable(ROOT.'/var') || is_writable(ROOT),'Private session directory can be written');
exit($failed?1:0);
