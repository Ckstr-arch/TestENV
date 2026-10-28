# Security primitives: prove identity without storing plaintext

[Stage dashboard](../README.md) · [Real source](../../app/security.php) · [Source inventory](../02-source-inventory.md)

## 1. File card

| Item | Value |
|---|---|
| Canonical path | [app/security.php](../../app/security.php) |
| Primary owner | Stage 04; Member D **TBD**, no authorship assigned |
| Secondary reviewers | Stage 02 for student effects; Stage 03 for admin rules; Stage 01 for transport/UI effects |
| Language / size | PHP; 48 physical lines (many contain several statements) |
| Role | Encode authenticator keys, calculate and verify TOTP, encrypt secrets, throttle attempts and finish authenticated sessions. |
| Inspected baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |

## 2. Why it exists

Authentication must be shared by native forms and REST. The server must recompute TOTP, so the authenticator secret needs authenticated encryption; passwords instead need one-way hashing.

## 3. Prerequisites

Read [PHP and HTTP foundations](../php-http-and-security.md), then the [request sequence](../01-architecture.md). Know variables, arrays, if/else, functions and exceptions before following a whole request. SQL prerequisites are in [SQL and transactions](../sql-and-transactions.md).

## 4. Dependencies

bootstrap query/config/session helpers; sodium extension; APP_KEY or development var/app.key; users, recovery_codes, login_attempts and audit_logs; actions action_result.

The dependency is canonical source, not a PHP copy in these learning folders. See [the full map](../../Assemble/01-repository-assembly-map.md).

## 5. Source-order coverage tour

The index below accounts for every meaningful function/branch group in this file. Related operations share a row where their implementation is inseparable; read the rows in order. No intentional runtime region is omitted.

- [base32_encode](#tour-1)
- [base32_decode](#tour-2)
- [totp](#tour-3)
- [totp_counter](#tour-4)
- [app_key](#tour-5)
- [encrypt_secret / decrypt_secret](#tour-6)
- [throttle](#tour-7)
- [finish_login](#tour-8)
- [verify_second_factor](#tour-9)

<a id="tour-1"></a>

### base32_encode

Convert each random byte to eight binary characters, concatenate, split into five-bit groups and map through A–Z and 2–7. This is encoding, not encryption; anybody holding the encoded secret can generate codes.

<a id="tour-2"></a>

### base32_decode

Normalize uppercase, reject characters outside that alphabet, reconstruct five-bit chunks and consume complete bytes. Its validation is needed before HMAC receives the key.

<a id="tour-3"></a>

### totp

HMAC SHA-1 of the binary counter followed by RFC-style dynamic truncation. Default six digits keeps leading zeros by str_pad. It does not itself read time or prevent reuse.

<a id="tour-4"></a>

### totp_counter

Require /^\d{6}$/D, derive intdiv(time(),30), check drift -1,0,+1 and require counter > saved last. Return the matched integer or null, not a boolean; callers must use !==null because zero can be valid.

<a id="tour-5"></a>

### app_key

Read APP_KEY, falling back to private var/app.key only when APP_ENV is development. Strict base64-decode and require exactly 32 bytes. Changing this key does not change user secrets safely: existing ciphertext becomes unreadable.

<a id="tour-6"></a>

### encrypt_secret / decrypt_secret

Generate a fresh 24-byte secretbox nonce per encryption, concatenate nonce+ciphertext and base64 it for storage. secretbox_open authenticates before returning plaintext. Nonce is not the secret key; sharing the APP_KEY would compromise every enrolled secret.

<a id="tour-7"></a>

### throttle

Hash scope, lowercased identity and REMOTE_ADDR into a bucket. Count records newer than 15 minutes and reject at eight; otherwise insert an attempt. Count+insert is not one atomic distributed rate limiter. Successful password authentication clears its password-login bucket in actions.php.

<a id="tour-8"></a>

### finish_login

Rotate session identifier and delete old session storage, replace session state with user_id/last_active/new CSRF, audit and return the shared action_result. The admin/student redirect follows the database role.

<a id="tour-9"></a>

### verify_second_factor

Begin transaction, lock user by ID, reject absent/unenrolled user, decrypt and test fresh counter, or iterate and consume one recovery hash. Commit acceptance; rollback rejection or exception. Locking serializes simultaneous recovery/TOTP requests.

## 6. Actual annotated excerpts

These excerpts are copied verbatim from the inspected source; their line numbers refer to the baseline, not a promise that future edits retain the same positions.

### Source lines 15–24

```php
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
```

hash_hmac computes a keyed SHA-1 digest over an eight-byte moving counter. pack N2 uses two unsigned network-order words (high word zero in this implementation). Dynamic truncation uses the digest offset, strips the sign bit, takes the requested decimal modulus and pads leading zeros. The verifier admits only six decimal digits and checks previous/current/next 30-second counters strictly newer than last.

### Source lines 36–47

```php
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
```

The users row is locked before checking either TOTP or recovery codes. A matching TOTP stores its counter, so the same interval cannot authenticate again. Otherwise password_verify checks each stored recovery hash; the successful row is deleted before commit. Failure rolls back. Exceptions also roll back, avoiding partially consumed factors.

## 7. Input → transformation → output

**Input:** Random bytes, a base32 authenticator secret, time counter, user record and submitted second factor.

**Transformation:** Follow the coverage tour; validate at the boundary, enforce role/state rules, then write or project data.

**Output:** Codes, encrypted ciphertext, accepted/rejected verification and a rotated authenticated session.

## 8. Connections and call chain

Native: [public/index.php](public__index_php.md) → [actions](app__actions_php.md) → [models](app__models_php.md) / [community](../../Stage-02-Student-Experience/files/app__community_php.md) → [bootstrap PDO](app__bootstrap_php.md). REST: [public/api.php](public__api_php.md) → [REST dispatcher](app__api_php.md) → those same services. [Security](app__security_php.md) participates in authentication; [schema](database__schema_sql.md) supplies persistent constraints. [Verification suites](../verification-suites.md) connect these paths to actual assertions.

## 9. Why this implementation

Manual authenticator enrollment avoids an external QR endpoint receiving a secret. File sessions suit one instance; multi-node hosts need supported shared sessions and operational rate limiting.

## 10. Security, correctness and accessibility

The server is authoritative even when controls are hidden in HTML. Keep prepared values, trusted identifier allowlists, CSRF, role/ownership checks and transaction boundaries. Escape dynamic text at HTML output, not by damaging data before storage. Transport errors must remain understandable to the interface; Stage 01 reviews focus/announcements when error presentation changes. A diagram or explanation here is not a security certification. See [known product limits](../../docs/security.md).

## 11. Common failure diagnosis

- A copied current TOTP fails on second use: wait for the next interval, do not remove last-counter checks.
- A new random APP_KEY after container restart makes enrolled secrets unreadable: restore the matching private key backup.
- Device clock drift beyond one step rejects good-looking codes: synchronize clocks first.
- Logging setup_secret or an otpauth URI leaks the second factor: log scope/result/request ID only.
- A missing sodium module prevents secretbox calls: confirm the real web PHP modules.

## 12. Run and verify safely

From repository root:

```bash
php -l app/security.php
```

This checks PHP syntax only; it does not prove HTTP, SQL, authorization or concurrency behavior. Follow [testing](../06-testing-and-debugging.md) for the appropriate functional suite. Do not execute the file as a standalone web application or point reset-capable tests at real data.

## 13. Debugging lesson

Set a breakpoint at the first relevant tour function, then step into its callee. Observe method, route, integer record ID, expected branch and transaction state. Do not inspect/share screenshots of password fields, session IDs, CSRF values, factor secrets, recovery codes or full account rows. If logging is needed, use a safe event label and request ID in protected logs; remove temporary diagnostics after the exercise.

## 14. Guided experiment

Run php tests/security.php after checking its source; it exercises public RFC vectors and synthetic encryption, not personal keys. Predict why a leading-zero TOTP must remain a string.

Save a prediction and observed status/state, then explain any mismatch. Use no personal records. If you edit a study file, keep a before-copy outside public/, review only your diff and manually undo only your added lines. Never reset somebody else’s changes.

## 15. Challenge ladder

1. **Understand:** point to the first trust boundary in this file and predict one rejected input. Expected evidence: named branch and status/result.
2. **Modify:** propose one clearer validation error or regression assertion for this specific boundary. First write expected before/after behavior; implement manually only after a scoped review. Expected evidence: a focused diff and actual test output.
3. **Engineer:** analyze two concurrent requests, an expired session or an unavailable dependency at this boundary. Identify the preserved invariant and a remaining limit; propose a test without production data. Hint: distinguish transport checks from service/database rules.

## 16. Knowledge check

1. Which caller reaches this file for native forms and which for REST?
2. Why can a valid-looking browser form still fail on the server?
3. If an exception occurs after a SQL write, can you assume the write was rolled back?

<details><summary>Answer key — read after predicting</summary>

1. index.php→handle_action and public/api.php→api_dispatch are the entry chains; the coverage tour identifies the applicable branch.
2. The server rechecks session, role, ownership, fields, timing and persistent state. A UI cannot authorize an operation.
3. Only a write inside a live transaction that is rolled back is undone. Some simple writes in this baseline are autocommitted; inspect the exact transaction scope rather than claiming universal atomicity.

</details>

## 17. Contribution boundary

Member D is a suggested reviewer/learning track, not a retroactive author. Name the exact function/branch in a human-owned feature PR. Stage 02/03 review changed behavior; all stages review schema contracts; Stage 01 reviews form/API mapping effects. Run the focused tests, record actual results and disclose help according to course policy. Do not change authentication, encryption, field allowlists or destructive semantics merely to simplify a lesson. [Shared contract](../07-contribution-contract.md).

## 18. Status and limits

**VERIFIED BY INSPECTION:** source excerpts and coverage are based on the recorded commit. **Execution:** consult the current [evidence register](../../Assemble/16-evidence-and-artifact-register.md); historic results in legacy docs are not this run’s results. **PLANNED:** learner improvements and exercises have not been credited as completed human work. MySQL 8 deployment must be verified separately from the existing MariaDB local setup.

## 19. Glossary

TOTP: time-based one-time password; HMAC: keyed message authentication; nonce: unique public encryption input; ciphertext: encrypted data; Argon2id: slow password hash.

## 20. One-minute explanation to personalize

Explain the file’s role, show one actual input check, follow one call to its next file, and demonstrate one failure case. Say “this existing baseline does…” before describing any real change you personally made. Add your own evidence and assistance disclosure; do not read this guide as a claim that you authored the prototype.
