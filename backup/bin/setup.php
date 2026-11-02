<?php
declare(strict_types=1);
/** Cross-platform, noninteractive local configuration creation. Never overwrites .env. */
if (PHP_SAPI!=='cli') exit;
$root=dirname(__DIR__); $file=$root.'/.env';
if (is_file($file)) { echo ".env already exists; preserved unchanged.\n"; exit; }
$key=base64_encode(random_bytes(32));
$data=file_get_contents($root.'/.env.example');
$data=preg_replace('/^APP_KEY=.*$/m','APP_KEY='.$key,$data);
if (file_put_contents($file,$data,LOCK_EX)===false) throw new RuntimeException('Could not create .env');
chmod($file,0600);
echo "Created private .env with a random APP_KEY. Set your own DB_PASSWORD and database settings before migrating.\n";
