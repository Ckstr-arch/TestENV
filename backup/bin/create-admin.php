<?php
declare(strict_types=1);
require_once __DIR__.'/../app/bootstrap.php';
if (PHP_SAPI!=='cli') exit;
$email=strtolower(config('ADMIN_EMAIL')); $password=config('ADMIN_PASSWORD'); $name=config('ADMIN_NAME','Sports Administrator');
if (!filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($password)<12 || strlen($password)>128) { fwrite(STDERR,"Set ADMIN_EMAIL and ADMIN_PASSWORD (12–128 characters) in the process environment.\n"); exit(1); }
if (one('SELECT id FROM users WHERE email=?',[$email])) { fwrite(STDERR,"Account already exists; no changes made.\n"); exit(1); }
query('INSERT INTO users(name,email,password_hash,role) VALUES(?,?,?,"admin")',[$name,$email,password_hash($password,PASSWORD_ARGON2ID)]);
echo "Admin created. Sign in and enroll 2FA in Account security.\n";
