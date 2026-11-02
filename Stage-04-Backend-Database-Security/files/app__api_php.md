# REST transport: route, validate, authorize, serialize

[Stage dashboard](../README.md) · [Real source](../../app/api.php) · [Source inventory](../02-source-inventory.md)

## 1. File card

| Item | Value |
|---|---|
| Canonical path | [app/api.php](../../app/api.php) |
| Primary owner | Stage 04; Member D **TBD**, no authorship assigned |
| Secondary reviewers | Stage 02 for student effects; Stage 03 for admin rules; Stage 01 for transport/UI effects |
| Language / size | PHP; 193 physical lines (many contain several statements) |
| Role | Enforce the versioned HTTP contract and adapt requests to shared services or read queries. |
| Inspected baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |

## 2. Why it exists

REST is a transport contract over the same PHP/MySQL application, not a second authorization model. A central parser and route allowlist keep malformed or unexpected requests away from business mutations.

## 3. Prerequisites

Read [PHP and HTTP foundations](../php-http-and-security.md), then the [request sequence](../01-architecture.md). Know variables, arrays, if/else, functions and exceptions before following a whole request. SQL prerequisites are in [SQL and transactions](../sql-and-transactions.md).

## 4. Dependencies

public/api.php constants REST_REQUEST_ID/REST_REQUEST; bootstrap,actions,models,community helpers; public/assets/app.js; generated OpenAPI and tests/api.py.

The dependency is canonical source, not a PHP copy in these learning folders. See [the full map](../../Assemble/01-repository-assembly-map.md).

## 5. Source-order coverage tour

The index below accounts for every meaningful function/branch group in this file. Related operations share a row where their implementation is inseparable; read the rows in order. No intentional runtime region is omitted.

- [public_user](#tour-1)
- [json_response / json_error](#tour-2)
- [api_payload / api_fields](#tour-3)
- [api_pagination / paginated](#tour-4)
- [api_filter / api_record](#tour-5)
- [api_mutation](#tour-6)
- [api_dispatch route recognition](#tour-7)
- [api_dispatch middleware](#tour-8)
- [health / auth session / account security](#tour-9)
- [Authentication and 2FA mutations](#tour-10)
- [Booking and reservation mutations](#tour-11)
- [Personal histories and cancellation](#tour-12)
- [Public collection and detail](#tour-13)
- [Results and standings](#tour-14)
- [Admin four-entity CRUD](#tour-15)
- [Admin request review](#tour-16)
- [Attendance / audit / reports](#tour-17)
- [Terminal404](#tour-18)

<a id="tour-1"></a>

### public_user

Return only id,name,email,student_id,faculty,role,two_factor_enabled from a full account. It is for the current user; club roster has a different reduced projection. Password hash and encrypted TOTP never enter this projection.

<a id="tour-2"></a>

### json_response / json_error

Set status/content type and encode JSON using exceptions, include opaque request_id and current csrf token. Success contains data; failure contains error.status/message. 429 adds Retry-After900. These functions exit: no later route branch executes.

<a id="tour-3"></a>

### api_payload / api_fields

Require application/json, <=65536 bytes, valid object, depth<=32, string keys/scalar-or-null values. api_fields rejects any key not in the per-route allowlist; a client cannot set role or overwrite fixed ownership/route ID values.

<a id="tour-4"></a>

### api_pagination / paginated

Validate page1..100000 and per_page1..100, calculate offset, run count and sorted LIMIT/OFFSET query, return array plus page/per_page/total/pages. SQL identifier fragments are internal strings; numeric bounds ensure interpolated limit/offset are validated.

<a id="tour-5"></a>

### api_filter / api_record

Only string filter values <=190 Unicode characters; trim. api_record reads one server-chosen table ID or throws404. Avoid exposing this helper with a client-supplied table name.

<a id="tour-6"></a>

### api_mutation

Allowlist fields, combine payload with fixed server-owned IDs/entity, set $_POST and call perform_action. Optionally reread saved record and emit Location for creates. Setup2FA returns key/otpauth only to its authenticated caller; enabling removes session recovery copy. Login/verify add a sanitized current user.

<a id="tour-7"></a>

### api_dispatch route recognition

Extract URL path, require /api/v1/, remove prefix/trailing slash, match anchored regex patterns with positive integer IDs. Unknown pattern404; recognized URL with unsupported method405 and Allow. Explicit dispatch is readable but requires matching updates to JS, OpenAPI and tests.

<a id="tour-8"></a>

### api_dispatch middleware

Require admin for admin/*, signed-in for me*,bookings,logout and reservation suffixes. Non-GET verifies CSRF header and exact supplied Origin, parses JSON. community_api runs before core branches and enforces club-specific access itself.

<a id="tour-9"></a>

### health / auth session / account security

health executes SELECT1 before returning ok; auth/session returns sanitized current user and valid pending2FA flag. me requires user. me/security exposes only enrollment boolean and remaining recovery-code count.

<a id="tour-10"></a>

### Authentication and 2FA mutations

auth/register→register201; login→login; auth/2fa→verify; logout→logout. me/2fa/setup→setup_2fa; POST me/2fa→enable_2fa; DELETE→disable_2fa. Valid field sets are explicit per call.

<a id="tour-11"></a>

### Booking and reservation mutations

bookings body resource_id/starts_at/ends_at/purpose maps to book. It rereads the current user latest booking; the app’s file-session locking serializes requests on that session, but a future session-store refactor should return inserted ID explicitly. Clubs/tournaments/events ID paths map to join/register_tournament/attend with empty JSON body; record IDs come from path.

<a id="tour-12"></a>

### Personal histories and cancellation

me/memberships,registrations,attendance,bookings GET use user_id filtering and joins for labels. DELETE id passes fixed entity/id to cancel, which checks ownership and eligibility before writing.

<a id="tour-13"></a>

### Public collection and detail

clubs/tournaments/events/resources GET optionally read id; list q maps name LIKE except events title. Only metadata-existing sport/status/kind/club_id/tournament_id filters are accepted into SQL. Events sort starts_at DESC, others by id.

<a id="tour-14"></a>

### Results and standings

Only match events with nonnull home score, optional tournament filter. Results joins club/tournament labels. Standings aggregate per club played/wins/draws/losses/3-1-0 points; these are sample club points, not sport-specific bracket rankings.

<a id="tour-15"></a>

### Admin four-entity CRUD

Admin GET list/detail, POST create, PUT update, DELETE remove use trusted regex entities and metadata fields. Existing IDs are checked before writes. save/delete actions repeat require_admin, so native and REST both enforce role.

<a id="tour-16"></a>

### Admin request review

GET membership/booking lists joins user and parent labels and optional status; PATCH requires status, fixes entity/id and calls review. Related records may appear in administrator output because they are authorized operational views.

<a id="tour-17"></a>

### Attendance / audit / reports

GET attendance lists users/events; PATCH id accepts only status=attended and maps to attendance action. Audit projects actor via LEFT JOIN and paginates descending. Reports aggregate club memberships/registrations/attended, tournament registrations and booking statuses. No report download service is invented.

<a id="tour-18"></a>

### Terminal404

After all recognized branches, throw404 instead of emitting partial HTML or a success-shaped empty object. A missing handler for a declared route must be caught by tests.

## 6. Actual annotated excerpts

These excerpts are copied verbatim from the inspected source; their line numbers refer to the baseline, not a promise that future edits retain the same positions.

### Source lines 19–27

```php
}
function api_payload(): array {
 $contentType=strtolower(trim(explode(';',$_SERVER['CONTENT_TYPE']??'')[0]));
 if ($contentType!=='application/json') throw new RequestError(415,'Use Content-Type: application/json.');
 $raw=file_get_contents('php://input',false,null,0,65537);
 if (strlen($raw)>65536) throw new RequestError(413,'Request body exceeds 64 KiB.');
 try { $body=json_decode($raw,true,32,JSON_THROW_ON_ERROR); } catch (JsonException) { throw new RequestError(400,'Malformed JSON request body.'); }
 if (!is_array($body) || !str_starts_with(ltrim($raw),'{')) throw new RequestError(400,'The JSON body must be an object.');
 foreach ($body as $key=>$value) if (!is_string($key) || (!is_scalar($value) && $value!==null)) throw new RequestError(422,'Fields must contain scalar values.');
```

Normalize the media type, then read one byte beyond the 64KiB limit to distinguish exact-limit from too large. json_decode uses an explicit depth and exceptions. The raw opening brace distinguishes object {} from array [] even though both decode to PHP arrays. Every field must be scalar or null; nested arrays/objects are rejected.

### Source lines 106–116

```php
 if (str_starts_with($route,'admin/')) require_admin();
 elseif (str_starts_with($route,'me') || $route==='bookings' || $route==='auth/logout' || preg_match('~/(memberships|registrations|attendance)$~',$route)) require_user();
 $payload=[];
 if ($method!=='GET') {
  if (!hash_equals(csrf(),(string)($_SERVER['HTTP_X_CSRF_TOKEN']??''))) throw new RequestError(403,'Invalid CSRF token. Fetch /api/v1/auth/session and retry.');
  if (isset($_SERVER['HTTP_ORIGIN']) && rtrim($_SERVER['HTTP_ORIGIN'],'/')!==rtrim(config('APP_URL'),'/')) throw new RequestError(403,'Cross-origin requests are not allowed.');
  $payload=api_payload();
 }
 community_api($route,$method,$payload);
 if ($route==='health') {
  query('SELECT 1'); json_response(['status'=>'ok','service'=>'NSBM SportsHub','version'=>'1']);
```

Admin URLs require administrator role before dispatch; personal/mutation routes require a user. Every non-GET requires a session-matching header CSRF token and, if Origin is present, exact APP_URL origin equality after trimming /. Payload parsing follows those gates. Community routing then applies additional club membership/ownership rules.

## 7. Input → transformation → output

**Input:** HTTP path/method/Origin/content type, cookie session, X-CSRF-Token, query filters and scalar JSON object.

**Transformation:** Follow the coverage tour; validate at the boundary, enforce role/state rules, then write or project data.

**Output:** JSON data/error envelopes, request/CSRF metadata, status/Allow/Location headers and optional UI redirect metadata.

## 8. Connections and call chain

Native: [public/index.php](public__index_php.md) → [actions](app__actions_php.md) → [models](app__models_php.md) / [community](../../Stage-02-Student-Experience/files/app__community_php.md) → [bootstrap PDO](app__bootstrap_php.md). REST: [public/api.php](public__api_php.md) → [REST dispatcher](app__api_php.md) → those same services. [Security](app__security_php.md) participates in authentication; [schema](database__schema_sql.md) supplies persistent constraints. [Verification suites](../verification-suites.md) connect these paths to actual assertions.

## 9. Why this implementation

A compact explicit router avoids framework dependencies and keeps the assignment stack clear. It is not OpenAPI-driven: the generator must be maintained beside route changes.

## 10. Security, correctness and accessibility

The server is authoritative even when controls are hidden in HTML. Keep prepared values, trusted identifier allowlists, CSRF, role/ownership checks and transaction boundaries. Escape dynamic text at HTML output, not by damaging data before storage. Transport errors must remain understandable to the interface; Stage 01 reviews focus/announcements when error presentation changes. A diagram or explanation here is not a security certification. See [known product limits](../../docs/security.md).

## 11. Common failure diagnosis

- Sending multipart FormData directly to REST yields415: enhancement converts it to scalar JSON.
- Copying an old CSRF value after login session rotation yields403: refresh from response metadata.
- Allowing wildcard CORS would not solve a mismatched APP_URL safely: keep same-origin cookie model.
- Unexpected {role:admin} must produce422 unknown field, never silently become privileged.
- Automatically retrying timed-out POST may repeat a committed write: read current state first.
- Treating fetch promise resolution as success misses HTTP4xx: check response.ok in JS.

## 12. Run and verify safely

From repository root:

```bash
php -l app/api.php
```

This checks PHP syntax only; it does not prove HTTP, SQL, authorization or concurrency behavior. Follow [testing](../06-testing-and-debugging.md) for the appropriate functional suite. Do not execute the file as a standalone web application or point reset-capable tests at real data.

## 13. Debugging lesson

Set a breakpoint at the first relevant tour function, then step into its callee. Observe method, route, integer record ID, expected branch and transaction state. Do not inspect/share screenshots of password fields, session IDs, CSRF values, factor secrets, recovery codes or full account rows. If logging is needed, use a safe event label and request ID in protected logs; remove temporary diagnostics after the exercise.

## 14. Guided experiment

Use DevTools only on a disposable local session: GET /api/v1/auth/session, then compare an unsupported GET-only endpoint method with a malformed write. Record just status, route and safe error message; do not export headers/cookies.

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

REST: resource-oriented HTTP interface; JSON: structured text data; Origin: scheme+host+port; 405: wrong method; pagination: bounded list pages.

## 20. One-minute explanation to personalize

Explain the file’s role, show one actual input check, follow one call to its next file, and demonstrate one failure case. Say “this existing baseline does…” before describing any real change you personally made. Add your own evidence and assistance disclosure; do not read this guide as a claim that you authored the prototype.
