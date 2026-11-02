<?php
declare(strict_types=1);
/** Development server only. Serve the website, the REST entrypoint, or known public assets. */
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
if (is_string($path) && str_starts_with($path,'/api/')) {
 require __DIR__.'/api.php';
 return true;
}
if ($path==='/' || $path==='/index.php') {
 require __DIR__.'/index.php';
 return true;
}
// Decode before checking traversal/hidden segments; never delegate arbitrary paths to PHP -S.
$decoded=is_string($path)?rawurldecode($path):'';
$assetRoot=realpath(__DIR__.'/assets');
$asset=(!str_contains($decoded,"\0") && !str_contains($decoded,'\\'))?realpath(__DIR__.$decoded):false;
$segments=explode('/',$decoded);
$hidden=false;
foreach ($segments as $segment) if ($segment!=='' && $segment[0]==='.') $hidden=true;
if (str_starts_with($decoded,'/assets/') && !$hidden && !str_contains($decoded,'\\')
 && !str_contains($decoded,"\0") && $assetRoot!==false && $asset!==false
 && str_starts_with($asset,$assetRoot.DIRECTORY_SEPARATOR) && is_file($asset)
 && in_array(strtolower(pathinfo($asset,PATHINFO_EXTENSION)),['css','js','png','jpg','jpeg','gif','webp','svg','ico','json','woff','woff2'],true)) {
 return false;
}
http_response_code(404);
header('Content-Type: text/plain; charset=utf-8');
header('X-Content-Type-Options: nosniff');
echo 'Not found.';
return true;
