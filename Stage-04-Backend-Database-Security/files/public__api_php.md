# JSON entrypoint: stable errors without internals

[Stage dashboard](../README.md) · [Real source](../../public/api.php) · [Source inventory](../02-source-inventory.md)

## 1. File card

| Item | Value |
|---|---|
| Canonical path | [public/api.php](../../public/api.php) |
| Primary owner | Stage 04; Member D **TBD**, no authorship assigned |
| Secondary reviewers | Stage 02 for student effects; Stage 03 for admin rules; Stage 01 for transport/UI effects |
| Language / size | PHP; 23 physical lines (many contain several statements) |
| Role | Establish request identity, load services and convert exceptions into safe JSON responses. |
| Inspected baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |

## 2. Why it exists

Expected business errors and database outages need different HTTP statuses. A caller must never receive raw SQL or an HTML stack trace from the JSON endpoint.

## 3. Prerequisites

Read [PHP and HTTP foundations](../php-http-and-security.md), then the [request sequence](../01-architecture.md). Know variables, arrays, if/else, functions and exceptions before following a whole request. SQL prerequisites are in [SQL and transactions](../sql-and-transactions.md).

## 4. Dependencies

app/api.php loaded first to make json_error available; bootstrap initializes helpers; actions loads domain/community; PDOException codes; private server logs.

The dependency is canonical source, not a PHP copy in these learning folders. See [the full map](../../Assemble/01-repository-assembly-map.md).

## 5. Source-order coverage tour

The index below accounts for every meaningful function/branch group in this file. Related operations share a row where their implementation is inseparable; read the rows in order. No intentional runtime region is omitted.

- [Request context/constants](#tour-1)
- [Headers and display_errors](#tour-2)
- [Include ordering](#tour-3)
- [RequestError / DomainException](#tour-4)
- [PDOException](#tour-5)
- [Throwable](#tour-6)

<a id="tour-1"></a>

### Request context/constants

REST_REQUEST changes authentication transport; REST_REQUEST_ID correlates one request without identifying the user. It is not an auth token.

<a id="tour-2"></a>

### Headers and display_errors

JSON content type is explicit and PHP exception text must not leak into output. Production logs still need access control and retention.

<a id="tour-3"></a>

### Include ordering

api.php declares functions before bootstrap may fail; this supports safe error envelopes for configuration failures. actions loads models/community; dispatch runs only after includes succeed.

<a id="tour-4"></a>

### RequestError / DomainException

RequestError uses its explicit status/message. Other domain failures422 reflect invalid fields or business state; do not collapse every exception into200.

<a id="tour-5"></a>

### PDOException

Log request-correlated failure, attempt rollback without masking original failure, map integrity23000 to409 and other DB failures503 with generic messages.

<a id="tour-6"></a>

### Throwable

Handle the remaining unexpected failure class with rollback and500. Client uses request ID to report it; developer consults protected logs without asking for credentials.

## 6. Actual annotated excerpts

These excerpts are copied verbatim from the inspected source; their line numbers refer to the baseline, not a promise that future edits retain the same positions.

### Source lines 3–12

```php
define('REST_REQUEST',true);
define('REST_REQUEST_ID',bin2hex(random_bytes(12)));
header('X-Request-ID: '.REST_REQUEST_ID);
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors','0');
require __DIR__.'/../app/api.php';
try {
 require __DIR__.'/../app/bootstrap.php';
 require __DIR__.'/../app/actions.php';
 api_dispatch();
```

Define REST_REQUEST before bootstrap so require_user uses401 JSON behavior. Generate a 12-byte random opaque ID and attach X-Request-ID. Disable display_errors. Load response functions, then bootstrap/actions inside try and dispatch.

### Source lines 15–23

```php
catch (PDOException $ex) {
 error_log('REST '.REST_REQUEST_ID.': '.$ex->getMessage());
 if (function_exists('db')) { try { if (db()->inTransaction()) db()->rollBack(); } catch (Throwable) {} }
 json_error($ex->getCode()==='23000'?409:503,$ex->getCode()==='23000'?'This change conflicts with an existing record.':'Database service is temporarily unavailable.');
} catch (Throwable $ex) {
 error_log('REST '.REST_REQUEST_ID.': '.$ex->getMessage());
 if (function_exists('db')) { try { if (db()->inTransaction()) db()->rollBack(); } catch (Throwable) {} }
 json_error(500,'The request could not be completed. Contact your administrator with the request ID.');
}
```

PDO integrity code23000 becomes409; other SQL errors503. Unexpected exceptions become500. Before answering, try rollback if a connection exists and has a transaction. Detailed errors go only to protected server logs associated with the request ID.

## 7. Input → transformation → output

**Input:** REST HTTP request and exceptions from bootstrap/services.

**Transformation:** Follow the coverage tour; validate at the boundary, enforce role/state rules, then write or project data.

**Output:** JSON response plus X-Request-ID, rolled-back failed transaction where possible.

## 8. Connections and call chain

Native: [public/index.php](public__index_php.md) → [actions](app__actions_php.md) → [models](app__models_php.md) / [community](../../Stage-02-Student-Experience/files/app__community_php.md) → [bootstrap PDO](app__bootstrap_php.md). REST: [public/api.php](public__api_php.md) → [REST dispatcher](app__api_php.md) → those same services. [Security](app__security_php.md) participates in authentication; [schema](database__schema_sql.md) supplies persistent constraints. [Verification suites](../verification-suites.md) connect these paths to actual assertions.

## 9. Why this implementation

Explicit catches make transport behavior visible. Generic fallbacks prevent leakage but require reliable private logs for diagnosis.

## 10. Security, correctness and accessibility

The server is authoritative even when controls are hidden in HTML. Keep prepared values, trusted identifier allowlists, CSRF, role/ownership checks and transaction boundaries. Escape dynamic text at HTML output, not by damaging data before storage. Transport errors must remain understandable to the interface; Stage 01 reviews focus/announcements when error presentation changes. A diagram or explanation here is not a security certification. See [known product limits](../../docs/security.md).

## 11. Common failure diagnosis

- Returning exception message to the client can reveal SQL/paths/private data.
- Loading bootstrap outside try can break consistent JSON failures.
- Removing rollback leaves locks pending until connection close and obscures intent.
- A generic500 should not be retried blindly for a mutation that might already have committed.

## 12. Run and verify safely

From repository root:

```bash
php -l public/api.php
```

This checks PHP syntax only; it does not prove HTTP, SQL, authorization or concurrency behavior. Follow [testing](../06-testing-and-debugging.md) for the appropriate functional suite. Do not execute the file as a standalone web application or point reset-capable tests at real data.

## 13. Debugging lesson

Set a breakpoint at the first relevant tour function, then step into its callee. Observe method, route, integer record ID, expected branch and transaction state. Do not inspect/share screenshots of password fields, session IDs, CSRF values, factor secrets, recovery codes or full account rows. If logging is needed, use a safe event label and request ID in protected logs; remove temporary diagnostics after the exercise.

## 14. Guided experiment

Compare a known404 and a malformed JSON400 response on local dev; confirm both contain request_id, and neither contains a stack trace. Do not induce a real DB outage.

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

Exception boundary: location converting errors to responses; integrity violation: conflict with database constraints; opaque ID: random correlation value.

## 20. One-minute explanation to personalize

Explain the file’s role, show one actual input check, follow one call to its next file, and demonstrate one failure case. Say “this existing baseline does…” before describing any real change you personally made. Add your own evidence and assistance disclosure; do not read this guide as a claim that you authored the prototype.
