<?php
declare(strict_types=1);
function base32_encode(string $bytes): string {
    $alphabet='ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; $bits=''; $result='';
    foreach (str_split($bytes) as $byte) $bits.=str_pad(decbin(ord($byte)),8,'0',STR_PAD_LEFT);
    foreach (str_split($bits,5) as $chunk) $result.=$alphabet[bindec(str_pad($chunk,5,'0'))];
    return $result;
}
function base32_decode(string $encoded): string {
    $alphabet='ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; $bits=''; $result='';
    foreach (str_split(strtoupper($encoded)) as $char) { $index=strpos($alphabet,$char); if ($index===false) throw new DomainException('Invalid authenticator key.'); $bits.=str_pad(decbin($index),5,'0',STR_PAD_LEFT); }
    foreach (str_split($bits,8) as $chunk) if (strlen($chunk)===8) $result.=chr(bindec($chunk));
    return $result;
}
function totp(string $secret, int $counter, int $digits=6): string {
    $hash=hash_hmac('sha1',pack('N2',0,$counter),base32_decode($secret),true);
    $offset=ord($hash[19])&15; $number=unpack('N',substr($hash,$offset,4))[1]&0x7fffffff;
    return str_pad((string)($number % (10**$digits)),$digits,'0',STR_PAD_LEFT);
}
function totp_counter(string $secret, string $code, ?int $last=null): ?int {
    if (!preg_match('/^\d{6}$/D',$code)) return null;
    $now=intdiv(time(),30);
    for ($i=-1;$i<=1;$i++) { $counter=$now+$i; if (($last===null || $counter>$last) && hash_equals(totp($secret,$counter),$code)) return $counter; }
    return null;
}
function app_key(): string {
    $encoded=config('APP_KEY');
    if ($encoded==='' && config('APP_ENV')==='development' && is_file(ROOT.'/var/app.key')) $encoded=trim(file_get_contents(ROOT.'/var/app.key'));
    $key=base64_decode($encoded,true); if ($key===false || strlen($key)!==32) throw new RuntimeException('Configure APP_KEY with a base64 encoded 32-byte key.'); return $key; }
function encrypt_secret(string $value): string { $nonce=random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES); return base64_encode($nonce.sodium_crypto_secretbox($value,$nonce,app_key())); }
function decrypt_secret(string $value): string { $data=base64_decode($value,true); if (!$data) throw new RuntimeException('Invalid encrypted secret'); $plain=sodium_crypto_secretbox_open(substr($data,24),substr($data,0,24),app_key()); if ($plain===false) throw new RuntimeException('Could not decrypt authenticator key'); return $plain; }
function throttle(string $scope, string $identity): string {
    $bucket=hash('sha256',$scope.'|'.strtolower($identity).'|'.($_SERVER['REMOTE_ADDR']??'cli'));
    if ((int)query('SELECT COUNT(*) FROM login_attempts WHERE bucket=? AND created_at>DATE_SUB(NOW(),INTERVAL 15 MINUTE)',[$bucket])->fetchColumn()>=8) throw new RequestError(429,'Too many attempts. Please try again in 15 minutes.');
    query('INSERT INTO login_attempts(bucket) VALUES(?)',[$bucket]); return $bucket;
}
function finish_login(array $u): array { session_regenerate_id(true); $_SESSION=['user_id'=>(int)$u['id'],'last_active'=>time(),'csrf'=>bin2hex(random_bytes(32))]; audit('login','Successful sign in',(int)$u['id']); return action_result($u['role']==='admin'?'admin':'dashboard','Signed in successfully.',['requires_2fa'=>false]); }
function verify_second_factor(array $u, string $code): bool {
    db()->beginTransaction();
    try {
        $locked=one('SELECT * FROM users WHERE id=? FOR UPDATE',[$u['id']]);
        if (!$locked || !$locked['totp_secret']) { db()->rollBack(); return false; }
        $counter=totp_counter(decrypt_secret($locked['totp_secret']),$code,$locked['totp_last_counter']===null?null:(int)$locked['totp_last_counter']);
        if ($counter!==null) { query('UPDATE users SET totp_last_counter=? WHERE id=?',[$counter,$u['id']]); db()->commit(); return true; }
        foreach (rows('SELECT * FROM recovery_codes WHERE user_id=?',[$u['id']]) as $r) if (password_verify($code,$r['code_hash'])) { query('DELETE FROM recovery_codes WHERE id=?',[$r['id']]); db()->commit(); return true; }
        db()->rollBack(); return false;
    } catch (Throwable $e) { if (db()->inTransaction()) db()->rollBack(); throw $e; }
}
