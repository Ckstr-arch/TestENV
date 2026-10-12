<?php
declare(strict_types=1);
require_once __DIR__.'/../app/bootstrap.php';
if (PHP_SAPI!=='cli') exit;
foreach (explode(';',file_get_contents(ROOT.'/database/schema.sql')) as $statement) if (trim($statement)!=='') db()->exec($statement);
echo "Schema created (existing tables preserved).\n";
