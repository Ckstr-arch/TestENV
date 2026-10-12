<?php
declare(strict_types=1);
require_once __DIR__.'/../app/bootstrap.php';
$tests=0;
function check(bool $value,string $label): void { global $tests; if (!$value) throw new RuntimeException($label); $tests++; }
$secret=base32_encode('12345678901234567890');
foreach ([59=>'94287082',1111111109=>'07081804',1111111111=>'14050471',1234567890=>'89005924',2000000000=>'69279037',20000000000=>'65353130'] as $time=>$code) check(totp($secret,intdiv($time,30),8)===$code,'RFC 6238 SHA-1 vector');
$key=base32_encode(random_bytes(20)); check(strlen($key)===32,'160-bit authenticator key');
$now=intdiv(time(),30); $code=totp($key,$now);
check(totp_counter($key,$code)===$now,'Current TOTP accepted');
check(totp_counter($key,$code,$now)===null,'Replay rejected');
check(totp_counter($key,'abc123')===null,'Malformed code rejected');
check(totp_counter($key,totp($key,$now-1))===$now-1,'One-step drift accepted');
check(totp_counter($key,totp($key,$now-3))===null,'Excess drift rejected');
$encrypted=encrypt_secret($key); check(decrypt_secret($encrypted)===$key,'Secret encryption roundtrip');
check(!str_contains($encrypted,$key),'Ciphertext hides secret');
$data=base64_decode($encrypted); $data[25]=chr(ord($data[25])^1);
try { decrypt_secret(base64_encode($data)); check(false,'Tampering rejected'); } catch (RuntimeException) { check(true,'Tampering rejected'); }
$hash=password_hash(str_repeat('a',72).'first',PASSWORD_ARGON2ID);
check(!password_verify(str_repeat('a',72).'second',$hash),'Long passwords not truncated');
check(e('<script>')==='&lt;script&gt;','HTML output escaping');
echo "$tests security checks passed.\n";
