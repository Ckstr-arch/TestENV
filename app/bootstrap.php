<?php
declare(strict_types=1);
const ROOT = __DIR__ . '/..';
final class RequestError extends DomainException {
 public function __construct(public readonly int $status, string $message) { parent::__construct($message); }
}
if (is_file(ROOT . '/.env')) {
    foreach (file(ROOT . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$name, $value] = explode('=', $line, 2);
        if (getenv(trim($name)) === false) putenv(trim($name) . '=' . trim($value));
    }
}
date_default_timezone_set('Asia/Colombo');
function config(string $key, string $default = ''): string { return getenv($key) === false ? $default : (string)getenv($key); }
function db(): PDO {
    static $pdo;
    if ($pdo) return $pdo;
    $pdo = new PDO('mysql:host='.config('DB_HOST','127.0.0.1').';port='.config('DB_PORT','3306').';dbname='.config('DB_NAME','sportshub').';charset=utf8mb4', config('DB_USER','sportshub'), config('DB_PASSWORD'), [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES=>false]);
    $pdo->exec("SET time_zone = '+05:30'");
    return $pdo;
}
function query(string $sql, array $args = []): PDOStatement { $s = db()->prepare($sql); $s->execute($args); return $s; }
function rows(string $sql, array $args = []): array { return query($sql,$args)->fetchAll(); }
function one(string $sql, array $args = []): ?array { return query($sql,$args)->fetch() ?: null; }
function e(mixed $value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function go(string $page): never { header('Location: /?page='.rawurlencode($page), true, 303); exit; }
function flash(string $message): void { $_SESSION['flash'] = $message; }
function user(): ?array { return isset($_SESSION['user_id']) ? one('SELECT * FROM users WHERE id=?', [$_SESSION['user_id']]) : null; }
function require_user(): array { $u=user(); if (!$u) { if (defined('REST_REQUEST')) throw new RequestError(401,'Sign in to continue.'); flash('Please sign in to continue.'); go('login'); } return $u; }
function require_admin(): array { $u=require_user(); if ($u['role']!=='admin') { if (defined('REST_REQUEST')) throw new RequestError(403,'Administrator access is required.'); http_response_code(403); exit('Access denied'); } return $u; }
function csrf(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function csrf_field(): string { return '<input type="hidden" name="csrf" value="'.e(csrf()).'">'; }
function check_csrf(): void { if (!hash_equals(csrf(), (string)($_POST['csrf']??''))) { http_response_code(419); exit('Your form expired. Reload the page and try again.'); } }
function audit(string $action, string $details, ?int $uid = null): void { query('INSERT INTO audit_logs(user_id,action,details) VALUES(?,?,?)', [$uid ?? ($_SESSION['user_id']??null),$action,$details]); }
function text(string $key, int $max=120): string { $v=trim((string)($_POST[$key]??'')); if ($v==='' || mb_strlen($v)>$max) throw new DomainException('Please enter a valid '.str_replace('_',' ',$key).'.'); return $v; }
function date_input(string $key): string { $v=text($key,30); $d=DateTimeImmutable::createFromFormat('!Y-m-d\TH:i',$v); if (!$d || $d->format('Y-m-d\TH:i')!==$v) throw new DomainException('Please enter a valid date and time.'); return $d->format('Y-m-d H:i:s'); }
function positive(string $key, int $max=PHP_INT_MAX): int { $v=filter_var($_POST[$key]??null,FILTER_VALIDATE_INT); if ($v===false || $v<1 || $v>$max) throw new DomainException('Please enter a valid '.str_replace('_',' ',$key).'.'); return $v; }
function choice(string $key, array $allowed): string { $v=text($key); if (!in_array($v,$allowed,true)) throw new DomainException('Invalid selection.'); return $v; }
function when(string $value): string { return date('M j, Y · g:i a',strtotime($value)); }
if (PHP_SAPI !== 'cli') {
    $secure=config('APP_ENV')==='production';
    if ($secure && !str_starts_with(config('APP_URL'),'https://')) throw new RuntimeException('Production APP_URL must use HTTPS.');
    ini_set('session.use_strict_mode','1');
    session_set_cookie_params(['httponly'=>true,'secure'=>$secure,'samesite'=>'Lax','path'=>'/']);
    $sessionPath=ROOT.'/var/sessions';
    if (!is_dir($sessionPath) && !mkdir($sessionPath,0700,true) && !is_dir($sessionPath)) throw new RuntimeException('Cannot create session storage.');
    session_save_path($sessionPath);
    session_name('sportshub_session');
    if (!session_start()) throw new RuntimeException('Cannot start secure session.');
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self'; script-src 'self'; base-uri 'none'; frame-ancestors 'none'; form-action 'self'");
    header('X-Content-Type-Options: nosniff'); header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Cache-Control: no-store');
    if ($secure) header('Strict-Transport-Security: max-age=31536000');
    if (isset($_SESSION['user_id']) && time()-($_SESSION['last_active']??time())>1800) { $_SESSION=[]; session_regenerate_id(true); }
    $_SESSION['last_active']=time();
}
require_once __DIR__.'/security.php';
