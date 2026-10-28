# Bootstrap: the common trust boundary

[Stage dashboard](../README.md) · [Real source](../../app/bootstrap.php) · [Source inventory](../02-source-inventory.md)

## 1. File card

| Item | Value |
|---|---|
| Canonical path | [app/bootstrap.php](../../app/bootstrap.php) |
| Primary owner | Stage 04; Member D **TBD**, no authorship assigned |
| Secondary reviewers | Stage 02 for student effects; Stage 03 for admin rules; Stage 01 for transport/UI effects |
| Language / size | PHP; 58 physical lines (many contain several statements) |
| Role | Initialize configuration, SQL access and browser sessions before either transport performs work. |
| Inspected baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |

## 2. Why it exists

Both entrypoints need exactly the same timezone, role rules and database helpers. Without a common boot, the API and native forms can disagree about who may perform a mutation.

## 3. Prerequisites

Read [PHP and HTTP foundations](../php-http-and-security.md), then the [request sequence](../01-architecture.md). Know variables, arrays, if/else, functions and exceptions before following a whole request. SQL prerequisites are in [SQL and transactions](../sql-and-transactions.md).

## 4. Dependencies

APP_ENV/APP_URL; DB_HOST/DB_PORT/DB_NAME/DB_USER/DB_PASSWORD; ROOT/var/sessions; app/security.php; users and audit_logs.

The dependency is canonical source, not a PHP copy in these learning folders. See [the full map](../../Assemble/01-repository-assembly-map.md).

## 5. Source-order coverage tour

The index below accounts for every meaningful function/branch group in this file. Related operations share a row where their implementation is inseparable; read the rows in order. No intentional runtime region is omitted.

- [RequestError and ROOT](#tour-1)
- [Environment loading](#tour-2)
- [config](#tour-3)
- [db](#tour-4)
- [query / rows / one](#tour-5)
- [e](#tour-6)
- [go / flash](#tour-7)
- [user / require_user / require_admin](#tour-8)
- [csrf / csrf_field / check_csrf](#tour-9)
- [audit](#tour-10)
- [text](#tour-11)
- [date_input](#tour-12)
- [positive / choice / when](#tour-13)
- [HTTP-only session initialization](#tour-14)
- [Headers and idle timeout](#tour-15)

<a id="tour-1"></a>

### RequestError and ROOT

The final class extends DomainException while adding a readonly HTTP status. ROOT resolves relative to this source file, not the shell working directory; private configuration stays above public/.

<a id="tour-2"></a>

### Environment loading

Skip comment/blank/non-assignment lines, split only the first =, trim names and values, then putenv only if the variable is absent. Real environment bindings override .env. This parser does not remove quotes or inline comments, so use literal values.

<a id="tour-3"></a>

### config

Return the process environment value or an explicit default. An empty present value is different from an absent variable; do not add debug output of values.

<a id="tour-4"></a>

### db

static $pdo caches one connection per request. PDO::ATTR_EMULATE_PREPARES=false delegates binding to MySQL. The fixed +05:30 SQL session offset matches Sri Lanka date comparisons.

<a id="tour-5"></a>

### query / rows / one

query returns PDOStatement after execution; rows consumes all rows; one returns the first row or null. Callers must distinguish null from an empty field. Placeholder binding protects values, not arbitrary table names.

<a id="tour-6"></a>

### e

htmlspecialchars uses both quote escaping and invalid UTF-8 substitution. It protects HTML text/quoted attributes when called at output. It is not SQL escaping and is not a general JavaScript encoder.

<a id="tour-7"></a>

### go / flash

go URL-encodes the internal page value, restores internal & and = separators and sends 303 then exits. flash writes one notice into the session for the next render. Only trusted internal redirect destinations should reach go.

<a id="tour-8"></a>

### user / require_user / require_admin

Fetch the account from user_id on each request, then enforce signed-in and administrator rules. REST_REQUEST changes error transport. A pending_user used in the 2FA challenge is deliberately not user_id.

<a id="tour-9"></a>

### csrf / csrf_field / check_csrf

Generate a random 32-byte value, hex-encode and retain it per session. Forms render it as hidden csrf. A native mismatch is 419; API checks its header separately and uses 403.

<a id="tour-10"></a>

### audit

Insert action/details and a caller-supplied or session user ID. Some operations call audit inside their transaction; simple CRUD writes are not automatically transactional merely because audit follows them.

<a id="tour-11"></a>

### text

Trim a field, require nonempty text and limit Unicode character count with mb_strlen. The password action uses byte strlen separately; do not assume the two limits are interchangeable.

<a id="tour-12"></a>

### date_input

Parse exactly Y-m-d\TH:i using ! to initialize omitted fields, then round-trip the formatted value to reject normalized invalid dates. Return SQL datetime Y-m-d H:i:s.

<a id="tour-13"></a>

### positive / choice / when

positive validates integer input and bounds; choice checks a strict string allowlist; when formats already-stored datetimes for presentation. None of these makes an arbitrary SQL identifier safe.

<a id="tour-14"></a>

### HTTP-only session initialization

PHP_SAPI !== cli includes cli-server, so development HTTP still uses sessions. Production requires HTTPS APP_URL. Strict sessions, HttpOnly/Secure/SameSite=Lax, private 0700 storage and session name are configured before session_start.

<a id="tour-15"></a>

### Headers and idle timeout

CSP limits resources/forms to same origin; nosniff, referrer policy, no-store and production HSTS reduce exposure. More than 1800 seconds of authenticated inactivity clears state and rotates the session ID. security.php is required once at the end.

## 6. Actual annotated excerpts

These excerpts are copied verbatim from the inspected source; their line numbers refer to the baseline, not a promise that future edits retain the same positions.

### Source lines 16–23

```php
function db(): PDO {
    static $pdo;
    if ($pdo) return $pdo;
    $pdo = new PDO('mysql:host='.config('DB_HOST','127.0.0.1').';port='.config('DB_PORT','3306').';dbname='.config('DB_NAME','sportshub').';charset=utf8mb4', config('DB_USER','sportshub'), config('DB_PASSWORD'), [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES=>false]);
    $pdo->exec("SET time_zone = '+05:30'");
    return $pdo;
}
function query(string $sql, array $args = []): PDOStatement { $s = db()->prepare($sql); $s->execute($args); return $s; }
```

A cached PDO is shared inside this PHP request. The connection string selects MySQL protocol, a database and utf8mb4. The options turn SQL failures into exceptions, return named columns and use real server-side prepared statements. SET time_zone aligns SQL NOW() with PHP Asia/Colombo. query() prepares SQL text first, then sends values through execute: a quote inside a value remains data.

### Source lines 29–34

```php
function user(): ?array { return isset($_SESSION['user_id']) ? one('SELECT * FROM users WHERE id=?', [$_SESSION['user_id']]) : null; }
function require_user(): array { $u=user(); if (!$u) { if (defined('REST_REQUEST')) throw new RequestError(401,'Sign in to continue.'); flash('Please sign in to continue.'); go('login'); } return $u; }
function require_admin(): array { $u=require_user(); if ($u['role']!=='admin') { if (defined('REST_REQUEST')) throw new RequestError(403,'Administrator access is required.'); http_response_code(403); exit('Access denied'); } return $u; }
function csrf(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function csrf_field(): string { return '<input type="hidden" name="csrf" value="'.e(csrf()).'">'; }
function check_csrf(): void { if (!hash_equals(csrf(), (string)($_POST['csrf']??''))) { http_response_code(419); exit('Your form expired. Reload the page and try again.'); } }
```

user() trusts the session identifier, then reloads the current account. require_user() adapts failure to REST 401 or a native redirect. require_admin() performs a second, different check: having an account does not imply being an administrator. csrf() stores a random token in that session; csrf_field() escapes it into a hidden input; check_csrf() compares the posted token using hash_equals and terminates on failure.

## 7. Input → transformation → output

**Input:** Environment bindings and private .env lines, session cookie, form fields, SQL arguments.

**Transformation:** Follow the coverage tour; validate at the boundary, enforce role/state rules, then write or project data.

**Output:** Configured PDO connection, typed helper results, protected session, HTTP headers and escaped HTML.

## 8. Connections and call chain

Native: [public/index.php](public__index_php.md) → [actions](app__actions_php.md) → [models](app__models_php.md) / [community](../../Stage-02-Student-Experience/files/app__community_php.md) → [bootstrap PDO](app__bootstrap_php.md). REST: [public/api.php](public__api_php.md) → [REST dispatcher](app__api_php.md) → those same services. [Security](app__security_php.md) participates in authentication; [schema](database__schema_sql.md) supplies persistent constraints. [Verification suites](../verification-suites.md) connect these paths to actual assertions.

## 9. Why this implementation

Small named functions keep a framework-free assignment approachable. Global process environment and superglobals couple callers to the transport; explicit request objects would be a future refactor, not this baseline.

## 10. Security, correctness and accessibility

The server is authoritative even when controls are hidden in HTML. Keep prepared values, trusted identifier allowlists, CSRF, role/ownership checks and transaction boundaries. Escape dynamic text at HTML output, not by damaging data before storage. Transport errors must remain understandable to the interface; Stage 01 reviews focus/announcements when error presentation changes. A diagram or explanation here is not a security certification. See [known product limits](../../docs/security.md).

## 11. Common failure diagnosis

- Quoted .env password is treated literally: inspect parser rules without printing the password.
- Wrong PHP extension produces could not find driver: compare php --ini and php -m for the running SAPI.
- Wrong APP_URL hostname fails Origin checks even if DNS resolves to the same server.
- Output before header/session calls breaks cookies and redirects: inspect whitespace/BOM in edited PHP files.
- Replacing e() with raw echo opens stored XSS on names/community content.

## 12. Run and verify safely

From repository root:

```bash
php -l app/bootstrap.php
```

This checks PHP syntax only; it does not prove HTTP, SQL, authorization or concurrency behavior. Follow [testing](../06-testing-and-debugging.md) for the appropriate functional suite. Do not execute the file as a standalone web application or point reset-capable tests at real data.

## 13. Debugging lesson

Set a breakpoint at the first relevant tour function, then step into its callee. Observe method, route, integer record ID, expected branch and transaction state. Do not inspect/share screenshots of password fields, session IDs, CSRF values, factor secrets, recovery codes or full account rows. If logging is needed, use a safe event label and request ID in protected logs; remove temporary diagnostics after the exercise.

## 14. Guided experiment

In DevTools on a disposable account, observe the cookie flags and compare a native form 303 with an API JSON success. Record flag names and status only, never cookie values.

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

PDO: PHP database interface; SAPI: how PHP runs; CSRF: forged cross-site action; CSP: browser resource policy; UTF-8: Unicode encoding.

## 20. One-minute explanation to personalize

Explain the file’s role, show one actual input check, follow one call to its next file, and demonstrate one failure case. Say “this existing baseline does…” before describing any real change you personally made. Add your own evidence and assistance disclosure; do not read this guide as a claim that you authored the prototype.
