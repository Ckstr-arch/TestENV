<?php
declare(strict_types=1);
require_once __DIR__.'/../app/bootstrap.php';
if (config('DB_NAME')!=='sportshub_test' || config('APP_ENV')==='production') throw new RuntimeException('Fixtures require DB_NAME=sportshub_test in development.');
db()->exec('SET FOREIGN_KEY_CHECKS=0');
foreach (rows('SHOW TABLES') as $r) db()->exec('DROP TABLE `'.array_values($r)[0].'`');
db()->exec('SET FOREIGN_KEY_CHECKS=1');
require __DIR__.'/../bin/migrate.php';
require __DIR__.'/../bin/seed.php';
query('INSERT INTO users(name,email,password_hash,role) VALUES(?,?,?,"admin")',['Test Admin','admin@example.invalid',password_hash(config('TEST_PASSWORD'),PASSWORD_ARGON2ID)]);
query('INSERT INTO users(name,email,student_id,faculty,password_hash) VALUES(?,?,?,?,?)',['Other Student','other@example.invalid','OTHER001','Computing',password_hash(config('TEST_PASSWORD'),PASSWORD_ARGON2ID)]);
query('INSERT INTO events(club_id,title,kind,location,starts_at,ends_at) VALUES(1,"Completed training","training","Track",DATE_SUB(NOW(),INTERVAL 2 DAY),DATE_SUB(NOW(),INTERVAL 1 DAY))');
echo json_encode(['club'=>1,'future_event'=>1,'past_event'=>(int)db()->lastInsertId(),'tournament'=>1,'resource'=>1]);
