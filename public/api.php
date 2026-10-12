<?php
declare(strict_types=1);
define('REST_REQUEST',true);
define('REST_REQUEST_ID',bin2hex(random_bytes(12)));
header('X-Request-ID: '.REST_REQUEST_ID);
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors','0');
require __DIR__.'/../app/api.php';
try {
 require __DIR__.'/../app/bootstrap.php';
 require __DIR__.'/../app/actions.php';
 api_dispatch();
} catch (RequestError $ex) { json_error($ex->status,$ex->getMessage()); }
catch (DomainException $ex) { json_error(422,$ex->getMessage()); }
catch (PDOException $ex) {
 error_log('REST '.REST_REQUEST_ID.': '.$ex->getMessage());
 if (function_exists('db')) { try { if (db()->inTransaction()) db()->rollBack(); } catch (Throwable) {} }
 json_error($ex->getCode()==='23000'?409:503,$ex->getCode()==='23000'?'This change conflicts with an existing record.':'Database service is temporarily unavailable.');
} catch (Throwable $ex) {
 error_log('REST '.REST_REQUEST_ID.': '.$ex->getMessage());
 if (function_exists('db')) { try { if (db()->inTransaction()) db()->rollBack(); } catch (Throwable) {} }
 json_error(500,'The request could not be completed. Contact your administrator with the request ID.');
}
