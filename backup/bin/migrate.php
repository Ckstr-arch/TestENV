<?php
declare(strict_types=1);
require_once __DIR__.'/../app/bootstrap.php';
if (PHP_SAPI!=='cli') exit;
$lock='sportshub:'.substr(hash('sha256',config('DB_NAME','sportshub')),0,48);
if ((int)query('SELECT GET_LOCK(?,10)',[$lock])->fetchColumn()!==1) throw new RuntimeException('Another schema migration is running.');
try {
 foreach (explode(';',file_get_contents(ROOT.'/database/schema.sql')) as $sql) if (trim($sql)!=='') db()->exec($sql);
 db()->exec('CREATE TABLE IF NOT EXISTS schema_migrations (version VARCHAR(190) PRIMARY KEY, applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB');
 $files=glob(ROOT.'/database/migrations/*.sql'); sort($files);
 foreach ($files as $file) {
  $version=basename($file);
  if (one('SELECT version FROM schema_migrations WHERE version=?',[$version])) continue;
  // DDL may auto-commit. Each statement must be safe to retry after partial failure.
  foreach (explode(';',file_get_contents($file)) as $sql) if (trim($sql)!=='') db()->exec($sql);
  query('INSERT INTO schema_migrations(version) VALUES(?)',[$version]);
  echo "Applied $version\n";
 }
 echo "Schema ready (existing data preserved).\n";
} finally { query('SELECT RELEASE_LOCK(?)',[$lock]); }
