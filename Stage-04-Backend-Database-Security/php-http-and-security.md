# 🧠 PHP, HTTP and security — from syntax to trust boundaries

[Stage dashboard](README.md) · [Actual bootstrap](files/app__bootstrap_php.md) · [Actions](files/app__actions_php.md) · [REST](files/app__api_php.md) · [Security primitives](files/app__security_php.md)

Read this beside the actual source. A word such as “session” is useful only when you can point to the code creating, checking and expiring one. The examples below are labeled **actual source** or **illustrative exercise**. Proposed changes are not implemented by this chapter.

## 1. PHP is the program producing the page

A browser does not execute `app/views.php`. PHP on the server evaluates its branches, SQL reads and loops, then sends HTML bytes. The browser builds a document object model (DOM) from those bytes and applies CSS/JavaScript. “View source” displays that emitted HTML, not the original PHP. The homepage lives in the `home` branch of [the real views](../app/views.php), while [layout](../app/layout.php) supplies `<html>`, `<head>` and shared navigation. There is no production `home.html`.

`<?php` begins PHP mode; a PHP template may close it to write literal HTML and reopen it for a dynamic value. An expression `<?= e($name) ?>` is a short echo using the HTML-escaping helper. Conditions decide which markup exists; CSS decides how existing markup looks. Hiding a button with CSS does not remove the server endpoint or authorize a request.

### Variables, arrays and function calls

Actual source from bootstrap:

```php
function query(string $sql, array $args = []): PDOStatement { $s = db()->prepare($sql); $s->execute($args); return $s; }
function rows(string $sql, array $args = []): array { return query($sql,$args)->fetchAll(); }
function one(string $sql, array $args = []): ?array { return query($sql,$args)->fetch() ?: null; }
```

- `$sql` and `$args` are local variables received as parameters. `$s` exists inside query, not in every caller.
- `string` and `array` describe accepted parameter types; `=[]` is the default empty positional argument list.
- `PDOStatement` after `:` describes the returned object. `?array` allows either an array or null when no row exists.
- `db()->prepare(...)` calls a method on the PDO object returned by db. The arrow accesses an object’s methods/properties; array fields instead use brackets such as `$u['role']`.
- A semicolon ends a statement. Several statements share one physical source line; count the operations, not just lines.
- `return` passes a value back and ends the function. It does not automatically emit HTML or JSON.
- `?: null` uses null for a falsey fetch result. A whole result row is distinct from one column whose valid value happens to be zero.

PHP arrays serve as ordered lists and maps. In entities(), `'clubs'=>[...]` maps a key to metadata; `foreach ($spec['fields'] as $field=>$type)` visits column names and validation/UI types. `array_keys` extracts allowed names; `array_values` produces values in the matching order. `[...array_values($data),$id]` expands values then appends an ID for an UPDATE WHERE placeholder. Do not reorder fields and values independently.

An arrow function such as `fn($k)=>"$k=?"` returns its expression. It is not JavaScript, even though both languages have arrow syntax. Here it builds SQL assignment fragments only from server-owned field metadata.

### Types, includes and process scope

Some entrypoints/helpers begin `declare(strict_types=1);`. It improves scalar type checking for calls made from that file; it does not convert arbitrary HTTP strings into verified domain objects. Other files in the baseline omit that declaration. The actual boundary still calls positive/text/choice/date_input. Do not claim strict_types replaces validation or automatically applies uniformly to every file.

`require` loads and executes another PHP file and fails if it cannot. `require_once` avoids a duplicate include in the same request, protecting function declarations from redeclaration. `__DIR__` resolves the directory of the source file, so deployment includes do not depend on whichever directory a shell started in. The private application can sit above `public/` because entrypoints explicitly include its files.

A function’s local variables are not the page template scope. PHP includes, however, execute in the scope of the including location; the website controller prepares `$page`, `$current`, `$error`, `$notice` and `$clubView` for the layout/views. Replacing includes with unrelated isolated files without preserving that contract can produce undefined variables or missing gates.

`$_GET`, `$_POST`, `$_SERVER` and `$_SESSION` are superglobal arrays. GET/POST/headers are untrusted request data. SESSION is server-managed state identified by a cookie, but still needs expiry and authorization checks; stale account roles/data must not be assumed forever. SportsHub `user()` reloads the account by session ID on each request.

## 2. HTTP: a request is a message, not a button

A request contains a method, target URL, headers and optional body. A response contains a status, headers and optional HTML/JSON body. A browser click is only one way to send one; anyone can construct a request manually. Every meaningful mutation therefore needs server validation and access control.

| Method / status | Meaning here | Actual example |
|---|---|---|
| GET | Read representation; safe navigation | `/api/v1/clubs`, `/?page=clubs` |
| POST | Create or perform an action | Club application, booking, login |
| PUT | Replace editable record fields / set one reaction | Admin entity update; community reaction |
| PATCH | Apply a smaller state change | Approval, moderation, attendance |
| DELETE | Cancel/remove under server rules | Own future participation, admin entity |
| 200 / 201 | Success / created resource | Read; new membership/record |
| 303 | Native write completed: fetch another page with GET | `go()` redirect |
| 400 | Malformed JSON/object shape | `[]` is not the required JSON object |
| 401 | Need authentication / invalid auth challenge | Signed-out personal endpoint |
| 403 | Role/CSRF/Origin restriction | Student on admin API; bad header token |
| 404 / 405 | No route/record / route exists, wrong method | Unknown ID; wrong method with Allow |
| 409 / 422 | Conflict / field or business validation | Ineligible cancel; invalid date/capacity |
| 413 / 415 | Body too large / unsupported media type | >64KiB; multipart sent to JSON API |
| 419 | Native form CSRF failure in this application | Expired hidden form token |
| 429 | Attempt throttle | Scope/identity/IP bucket limit |
| 500 / 503 | Unexpected failure / unavailable DB/service | Safe generic error + private diagnostics |

Status419 is an application convention for native CSRF errors, not a universal HTTP standard. REST uses403 for the analogous check. A validation duplicate sometimes yields422 through an explicit rule and409 through an SQL integrity exception. Read the exact branch rather than expecting one status for every “duplicate” situation.

### Redirect versus JSON

Actual source:

```php
function go(string $page): never { header('Location: /?page='.str_replace(['%26','%3D'],['&','='],rawurlencode($page)), true, 303); exit; }
```

`header` must run before response bytes. The third argument sets303, so the next navigation uses GET; `exit` prevents continuing into page rendering. `never` documents that no caller resumes normally. The encoding here supports internally constructed page+query destinations; it is not permission to accept arbitrary redirect destinations from clients.

The REST path returns a JSON object with data/meta or error/meta. `meta.redirect` is a UI hint, not HTTP authorization and not an instruction a nonbrowser client must follow. The browser adapter chooses to navigate. Fetch resolving does not guarantee HTTP success; app.js checks response.ok and parses its error envelope.

## 3. The native form and enhanced form share a mutation

Native `<form method="post">` sends form-encoded values and hidden action/csrf fields to the PHP page controller. The enhanced app reads FormData, selects an explicit mapping, removes hidden routing fields and sends scalar JSON to the correct REST endpoint. It does not move business authority into JavaScript.

For joining a club, the authoritative ID is the positive route/form ID and the authoritative user is the session account. Native: index→handle_action→perform_action→reserve. REST: public/api→api_dispatch→perform_action→reserve. Their outputs differ; their reservation rules converge. See [the full sequence](04-guided-walkthroughs.md).

**Illustrative read-only DevTools exercise, not production code added to the app:**

```javascript
const response = await fetch('/api/v1/health', {
  credentials: 'same-origin',
  headers: { Accept: 'application/json' }
});
const body = await response.json();
console.log(response.status, body.data?.status);
```

This logs safe health status only. Do not substitute auth/session then log its whole body: it includes a live CSRF token and account data. For a mutation, use the implemented app’s form or a reviewed test client preserving session/correct token; do not paste secret-bearing commands into shell history.

## 4. Prepared SQL: values and identifiers are different problems

Actual action source:

```php
query('INSERT INTO users(name,email,student_id,faculty,password_hash) VALUES(?,?,?,?,?)',[$name,$email,$sid,$faculty,password_hash($password,PASSWORD_ARGON2ID)]);
```

The SQL text declares structure; five placeholders receive five separate values. A value such as `O'Connor` should be stored as data rather than ending a quoted SQL string. You do not concatenate a password/email into SQL and then try to clean quotes. Native PDO prepares make the separation explicit.

You cannot bind a table name with `SELECT * FROM ?`: a placeholder is a value position, not syntax. Dynamic entity tables/columns are selected through server-owned `entities()` metadata or fixed regex/choice allowlists. Both protections matter. A numeric cast alone cannot make a client-controlled table name safe.

A PHP duplicate/capacity check can be invalidated by another request between read and write. SQL unique constraints and transactions provide another layer. [SQL and transactions](sql-and-transactions.md) teaches joins, keys, normalization and interleavings against the actual schema.

## 5. Sessions: identify the browser without exposing credentials to JS

A cookie carries a random session identifier. The PHP session store contains server-side state such as user_id and CSRF. This app uses private `var/sessions`, not a bearer JWT in localStorage. HttpOnly prevents JavaScript reading the cookie through document.cookie; it does **not** prevent malicious same-origin script sending requests. SameSite=Lax reduces cross-site cookie exposure but is not the sole CSRF control.

Bootstrap enables strict sessions before starting, names the cookie `sportshub_session`, sets its path `/` and production Secure. After successful authentication `finish_login` regenerates the ID with deletion of old storage and sets a new CSRF token. Rotation makes an attacker’s preselected session less useful (session fixation mitigation). Thirty minutes of authenticated inactivity clears the session and rotates it again. Logout clears state and rotates as well.

Production is selected by APP_ENV=production and requires an HTTPS APP_URL; the host must actually terminate HTTPS correctly. HSTS and Secure cookie settings cannot magically provision a certificate. File sessions need private durable writable storage, correct web-user permissions and cleanup policy. Multiple nodes need a supported shared session plan; do not assume the current local directory coordinates all hosts.

## 6. CSRF, XSS, SQL injection and authorization are different

| Threat | Attacker’s goal | Baseline boundary | Still required |
|---|---|---|---|
| CSRF | Make a browser send an unwanted write using its cookie | Session-random token; form check or X-CSRF-Token; API supplied-Origin equality | Same-origin deployment and no token leakage |
| XSS | Run script as the app’s origin through injected content | `e()` HTML escaping, text rendering, CSP | Correct output context and future UI review |
| SQL injection | Change SQL syntax using data input | Prepared values, identifier allowlists | Continue binding every new value |
| Authorization bypass | Act on another user’s/admin data | require_user/admin, ownership WHERE clauses, per-club approval | Check each new endpoint/service, not just UI |

CSRF token is an unpredictable value retained in the session and copied into legitimate forms/headers. A cross-site attacker who cannot read the page lacks it. Login itself needs CSRF to avoid login CSRF. REST additionally compares a supplied Origin with APP_URL (scheme/hostname/port); localhost and127.0.0.1 are different origins even on the same machine. Requests without Origin still need CSRF. No wildcard CORS is enabled or needed for this same-origin interface.

XSS can defeat CSRF by executing inside the trusted origin and reading legitimate form tokens. That is why escaping and CSP remain essential. `e()` protects HTML text/quoted attributes; it is not a JavaScript string encoder or SQL escaping routine. Keep community content as plain text; do not allow raw HTML or use innerHTML on untrusted messages.

Authentication answers “who is signed in?” Authorization answers “may this identity do this operation on this record now?” An approved membership in Club A is not approval for Club B. A post author is not the author of every post in that club. Services enforce these distinctions per request; a missing button is never proof of protection.

## 7. Password hashing versus TOTP encryption

Passwords use `password_hash(..., PASSWORD_ARGON2ID)` and `password_verify`. A salted, deliberately expensive one-way hash lets the server compare a submitted password without storing its original text. It should not be decrypted, emailed back or logged. Public registration accepts12–128 **bytes** in actual strlen checks; required text fields use mb_strlen character limits. Registration never accepts role, so a public caller cannot ask to become administrator.

Login normalizes email, throttles attempts, fetches an account and uses a dummy hash for missing users. Do not claim perfectly identical login timing: the dummy hash is bcrypt while real accounts use Argon2id, and other request work differs. The important lesson is to avoid exposing detailed account-existence errors and to retain defensive throttling. Successful passwords can trigger rehash when algorithm/cost requirements change.

TOTP requires the server to recompute a code using a shared secret. It therefore cannot be stored only as a one-way password hash. This app generates20 random bytes, encodes them base32 for an authenticator and encrypts the secret with libsodium secretbox plus a private32-byte APP_KEY. A fresh24-byte nonce precedes ciphertext; the nonce can be public but the key cannot. Secretbox authenticates ciphertext so tampering fails instead of silently producing bad plaintext.

APP_KEY is a base64 representation of32 random bytes, not a website password or the user’s authenticator setup key. Development Docker may persist its generated key in var/app.key. Existing encrypted enrollment data requires the matching stable key after updates/restores. Do not “refresh” it casually; lose it and existing secrets cannot be decrypted. Securely back up DB and matching key privately, controlling access separately. Fresh production should use a fresh private production key and fresh DB, without development identities.

## 8. TOTP and the login challenge, step by step

TOTP is a keyed code for a time interval. The counter is floor(current Unix seconds/30). `totp` computes HMAC-SHA1 over the packed counter, selects a digest segment, removes the sign bit, takes modulo10^6 and pads leading zeros. Base32 is only a human-friendly encoding of the secret, not encryption.

`totp_counter` verifies six decimal digits against previous/current/next intervals and requires a matched counter newer than stored totp_last_counter. This accepts limited drift but rejects replay, including a second successful use in the same interval. A user may need to wait for the next30-second code; removing replay checks is not a fix. Server/device clock synchronization is operationally important.

An enrolled account’s correct password rotates into pending_user/pending_until for five minutes. It has no user_id, so portal/admin gates continue to fail. After a valid factor, `finish_login` rotates into authenticated state with fresh CSRF. An expired challenge requires password login again. Setup enrollment is different: setup_secret/setup_until expires after ten minutes and only activates after the current password plus a valid TOTP.

`verify_second_factor` begins a transaction and locks the user before testing a fresh counter or a recovery code. On TOTP success it stores the accepted counter. On recovery success it deletes that matching hash. Acceptance commits; rejection/exception rolls back. This serializes concurrent attempts that might otherwise reuse the same factor.

Recovery codes are eight random values returned only on successful enrollment and stored as Argon2id hashes. API removes the session display copy because JSON is the only recovery-code display in that path. Never save codes in screenshots, issue threads or these learning folders. A recovery code is a password-equivalent factor; consuming one grants access and cannot be repeated. There is no security-question/email bypass in this baseline.

## 9. Rate limits, logs and limits of the prototype

`throttle` hashes scope+normalized identity+remote address into a bucket and allows eight records in15 minutes. It records attempts before the protected action; some successful login flows clear their bucket. This is a baseline DB-backed control. Count+insert is not an atomic distributed limiter, proxy REMOTE_ADDR behavior requires review and retention/cleanup needs an operational plan. A real institutional deployment should add appropriate edge monitoring/rate controls.

API unexpected errors include an opaque request ID and safe message, not SQL, stack traces or stored secrets. Detailed server logs need private access and retention; logging is not permission to include full bodies or authentication data. HTML error fallback also avoids raw exception output. Keep display_errors off on production; never remove authorization/CSRF to diagnose a failure.

**Existing limits:** 2FA is opt-in, no email ownership verification/password-reset delivery, no organization SSO or cross-device session revocation, no independent security certification. Tournaments register individuals; generic club standings are3/1/0, not sport-specific brackets. Resource rows are single units. Deleting parent records can cascade participation; production retention/archive policy remains a decision. [The real security document](../docs/security.md) records these gaps.

## 10. Five-minute prediction check

1. A password is correct but challenge expired: should dashboard access work? No: pending state never became authenticated user_id.
2. A booking is valid HTML but overlaps a pending request: should it save? No: server lock+overlap query rejects it regardless of UI.
3. A POST times out: can the client safely retry blindly? No: commit may have happened before response loss; read state first.
4. APP_KEY changed but user clock is right: will waiting a new TOTP interval fix decryption? No: restore matching key/data or use a reviewed recovery policy.
5. PHP lint passes: does that prove roles, transactions and live HTTPS? No: run meaningful functional checks in isolated data and real-host checks only after an authorized deployment.

Use [labs](05-labs-and-exercises.md), [the full knowledge check](08-knowledge-check.md) and a real personal evidence record to demonstrate understanding. Explain baseline behavior honestly before discussing what you personally contributed.
