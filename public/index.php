<?php
declare(strict_types=1);
require_once __DIR__.'/../app/bootstrap.php';
require __DIR__.'/../app/actions.php';
$page=(string)($_GET['page']??'home');
$pages=['activity','club','my_clubs','moderation','audit','home','clubs','schedule','tournaments','results','facilities','login','register','verify','dashboard','security','admin','approvals','reports','manage_clubs','manage_tournaments','manage_events','manage_resources'];
if (!in_array($page,$pages,true)) { http_response_code(404); $page='notfound'; }
$error=null;
try {
 if ($_SERVER['REQUEST_METHOD']==='POST') handle_action();
} catch (RequestError $ex) { $error=$ex->getMessage(); http_response_code($ex->status); }
catch (DomainException $ex) { $error=$ex->getMessage(); http_response_code(422); }
catch (Throwable $ex) { if (db()->inTransaction()) db()->rollBack(); error_log((string)$ex); $error='Something went wrong. Please try again or contact your administrator.'; http_response_code(500); }
try {
 $current=user();
 if (in_array($page,['dashboard','activity','security','my_clubs','club'],true)) $current=require_user();
 if (in_array($page,['admin','approvals','reports','moderation','audit'],true) || str_starts_with($page,'manage_')) $current=require_admin();
 if ($page==='verify' && (empty($_SESSION['pending_user']) || ($_SESSION['pending_until']??0)<time())) go('login');
 $clubView=null;
 if ($page==='club') {
  $clubView=one('SELECT * FROM clubs WHERE id=?',[(int)($_GET['club_id']??0)]);
  if (!$clubView) { http_response_code(404); $page='notfound'; }
 }
 $notice=$_SESSION['flash']??null; unset($_SESSION['flash']);
 require __DIR__.'/../app/layout.php';
} catch (Throwable $ex) { error_log((string)$ex); http_response_code(503); echo 'SportsHub is temporarily unavailable. Please check the database configuration.'; }
