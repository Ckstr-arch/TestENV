<?php
declare(strict_types=1);
/** Two-connection regressions for stale approval snapshots; PHP core + actual MySQL only. */
if (PHP_SAPI!=='cli' || !in_array('--confirm-test-reset',$argv,true)) {
    fwrite(STDERR,"Usage: php tests/concurrency.php --confirm-test-reset\nRebuilds ONLY the confirmed disposable sportshub_backup_test schema; run sequentially with integration.php.\n");
    exit(1);
}
putenv('DB_NAME=sportshub_backup_test');putenv('APP_ENV=development');
if (getenv('APP_KEY')===false || getenv('APP_KEY')==='') putenv('APP_KEY='.base64_encode(random_bytes(32)));
require __DIR__.'/../app/bootstrap.php';
require __DIR__.'/../app/models.php';
require __DIR__.'/fixtures.php';

function require_test_target(): void {
    if (config('DB_NAME')!=='sportshub_backup_test' || config('APP_ENV')!=='development'
        || (string)query('SELECT DATABASE()')->fetchColumn()!=='sportshub_backup_test') {
        throw new RuntimeException('Concurrency verification requires the exact disposable test schema.');
    }
    if (!mysql_version_supported((string)query('SELECT VERSION()')->fetchColumn())) {
        throw new RuntimeException('Concurrency verification requires actual MySQL8+.');
    }
}
require_test_target();
if (($argv[1] ?? '')==='--worker') {
    $entity=$argv[2] ?? ''; $id=filter_var($argv[3] ?? null,FILTER_VALIDATE_INT);
    if (!in_array($entity,['memberships','bookings'],true) || !$id || $id<1) exit(2);
    // Make the engine's old/default snapshot behavior explicit. review_request must request
    // READ COMMITTED for its own transaction, regardless of this session default.
    db()->exec('SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ');
    echo 'READY '.(int)query('SELECT CONNECTION_ID()')->fetchColumn().PHP_EOL;flush();
    try { review_request($entity,$id,'approved');echo "APPROVED\n"; }
    catch (DomainException) { echo "REJECTED\n"; }
    catch (Throwable $error) { fwrite(STDERR,'Unexpected worker exception: '.get_class($error).PHP_EOL);exit(2); }
    exit(0);
}
$checks=0;
function concurrent_check(bool $ok,string $label): void {
    global $checks;
    if (!$ok) throw new RuntimeException('FAILED: '.$label);
    ++$checks;echo 'PASS '.$label.PHP_EOL;
}
/** Hold parent, prove worker reaches its blocked locking read, commit competitor, inspect outcome. */
function approval_race(string $entity,string $parentTable,int $parentId,int $waitingId,int $competingId): void {
    global $argv;
    if (!in_array($entity,['memberships','bookings'],true) || !in_array($parentTable,['clubs','resources'],true)) throw new RuntimeException('Invalid race fixture.');
    $php=getenv('PHP_BIN') ?: PHP_BINARY;
    $child=null;$pipes=[];$log=tempnam(sys_get_temp_dir(),'sportshub-lock-test-');$success=false;
    if ($log===false) throw new RuntimeException('Cannot create private worker log.');
    chmod($log,0600);
    db()->beginTransaction();
    try {
        one("SELECT id FROM $parentTable WHERE id=? FOR UPDATE",[$parentId]);
        $child=proc_open([$php,__FILE__,'--worker',$entity,(string)$waitingId,'--confirm-test-reset'],
            [0=>['pipe','r'],1=>['pipe','w'],2=>['file',$log,'a']],$pipes,dirname(__DIR__),getenv());
        if (!is_resource($child)) throw new RuntimeException('Could not start concurrent PHP worker.');
        fclose($pipes[0]);stream_set_timeout($pipes[1],10);
        $ready=trim((string)fgets($pipes[1]));
        if (!preg_match('/^READY ([0-9]+)$/D',$ready,$match)) throw new RuntimeException('Concurrent worker did not announce a database connection.');
        $connectionId=(int)$match[1];$blocked=false;$deadline=microtime(true)+10;
        while (microtime(true)<$deadline) {
            // An unprivileged MySQL user may inspect its own threads. Do not dump process lists:
            // inspect only the synthetic worker's query and return a boolean observation.
            $state=one('SELECT INFO FROM information_schema.PROCESSLIST WHERE ID=?',[$connectionId]);
            $info=(string)($state['INFO'] ?? '');
            if (str_contains(strtolower($info),'from '.$parentTable) && str_contains(strtoupper($info),'FOR UPDATE')) {
                $blocked=true;break;
            }
            if (!proc_get_status($child)['running']) break;
            usleep(20000);
        }
        concurrent_check($blocked,$entity.': competing reviewer demonstrably waits at the held parent lock');
        // Deliberately change a different synthetic pending request while this transaction owns
        // the shared parent. This simulates the first administrator committing the last slot.
        query("UPDATE $entity SET status='approved' WHERE id=? AND status='pending'",[$competingId]);
        db()->commit();
        $outcome=trim((string)fgets($pipes[1]));
        concurrent_check($outcome==='REJECTED',$entity.': waiting reviewer rejects conflict committed while it waited');
        concurrent_check((string)query("SELECT status FROM $entity WHERE id=?",[$waitingId])->fetchColumn()==='pending',
            $entity.': rejected approval leaves the waiting request pending');
        concurrent_check((string)query("SELECT status FROM $entity WHERE id=?",[$competingId])->fetchColumn()==='approved',
            $entity.': competing committed approval remains approved');
        $success=true;
    } finally {
        if (db()->inTransaction()) db()->rollBack();
        if (isset($pipes[1]) && is_resource($pipes[1])) fclose($pipes[1]);
        if (is_resource($child)) {
            $state=proc_get_status($child);
            if ($state['running']) proc_terminate($child);
            proc_close($child);
        }
        if ($success && is_file($log)) unlink($log);
        elseif (!$success) fwrite(STDERR,'Private worker log retained at '.$log.'. Do not publish private errors.'.PHP_EOL);
    }
}

$ids=initialize_test_database(bin2hex(random_bytes(18)));
query('INSERT INTO users(name,email,student_id,faculty,password_hash) VALUES(?,?,?,?,?)',
    ['Concurrency Synthetic Student','race@example.invalid','VERIFY-RACE','Computing',password_hash(bin2hex(random_bytes(18)),PASSWORD_ARGON2ID)]);
$second=(int)db()->lastInsertId();
query('UPDATE clubs SET capacity=1 WHERE id=?',[$ids['club']]);
query('INSERT INTO memberships(user_id,club_id) VALUES(?,?)',[$ids['other'],$ids['club']]);$waiting=(int)db()->lastInsertId();
query('INSERT INTO memberships(user_id,club_id) VALUES(?,?)',[$second,$ids['club']]);$competing=(int)db()->lastInsertId();
approval_race('memberships','clubs',$ids['club'],$waiting,$competing);
concurrent_check((int)query("SELECT COUNT(*) FROM memberships WHERE club_id=? AND status='approved'",[$ids['club']])->fetchColumn()===1,
    'Membership capacity remains one approved member after simultaneous last-slot reviews');
$start=date('Y-m-d H:i:s',time()+30*86400);$end=date('Y-m-d H:i:s',time()+30*86400+3600);
// Ordinary booking creation prevents overlap early. These conflicting synthetic pending rows
// deliberately exercise approval-time protection against legacy/imported or concurrently changed data.
query('INSERT INTO bookings(user_id,resource_id,starts_at,ends_at,purpose) VALUES(?,?,?,?,?)',[$ids['other'],$ids['resource'],$start,$end,'Synthetic pending overlap A']);$waiting=(int)db()->lastInsertId();
query('INSERT INTO bookings(user_id,resource_id,starts_at,ends_at,purpose) VALUES(?,?,?,?,?)',[$second,$ids['resource'],$start,$end,'Synthetic pending overlap B']);$competing=(int)db()->lastInsertId();
approval_race('bookings','resources',$ids['resource'],$waiting,$competing);
concurrent_check((int)query("SELECT COUNT(*) FROM bookings WHERE resource_id=? AND status='approved'",[$ids['resource']])->fetchColumn()===1,
    'Overlapping booking requests result in one approved reservation');
echo $checks." concurrent MySQL approval checks passed using independent PHP/database connections.\n";
echo "Only sportshub_backup_test was rebuilt. No original prototype database or service was touched.\n";
