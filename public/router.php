<?php
/** Router for PHP's local development server. Apache uses .htaccess. */
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
if (is_string($path) && str_starts_with($path,'/api/')) { require __DIR__.'/api.php'; return true; }
if ($path==='/' || $path==='/index.php') { require __DIR__.'/index.php'; return true; }
return false;
