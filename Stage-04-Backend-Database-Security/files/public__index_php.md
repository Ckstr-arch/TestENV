# HTML entrypoint: controller before template

[Stage dashboard](../README.md) · [Real source](../../public/index.php) · [Source inventory](../02-source-inventory.md)

## 1. File card

| Item | Value |
|---|---|
| Canonical path | [public/index.php](../../public/index.php) |
| Primary owner | Stage 04; Member D **TBD**, no authorship assigned |
| Secondary reviewers | Stage 02 for student effects; Stage 03 for admin rules; Stage 01 for transport/UI effects |
| Language / size | PHP; 26 physical lines (many contain several statements) |
| Role | Handle the native form path and select authenticated/role-appropriate server-rendered pages. |
| Inspected baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |

## 2. Why it exists

This is the canonical website controller. The homepage remains PHP-rendered through views; no stage folder or exercise replaces this webroot.

## 3. Prerequisites

Read [PHP and HTTP foundations](../php-http-and-security.md), then the [request sequence](../01-architecture.md). Know variables, arrays, if/else, functions and exceptions before following a whole request. SQL prerequisites are in [SQL and transactions](../sql-and-transactions.md).

## 4. Dependencies

app/bootstrap.php,actions.php,layout.php; layout includes views and portal views; users/clubs; approved membership checked deeper for club community.

The dependency is canonical source, not a PHP copy in these learning folders. See [the full map](../../Assemble/01-repository-assembly-map.md).

## 5. Source-order coverage tour

The index below accounts for every meaningful function/branch group in this file. Related operations share a row where their implementation is inseparable; read the rows in order. No intentional runtime region is omitted.

- [Strict bootstrap/imports](#tour-1)
- [Page selection](#tour-2)
- [Native mutation error boundary](#tour-3)
- [Auth gates and 2FA](#tour-4)
- [Club prefetch](#tour-5)
- [Notice/layout/render failure](#tour-6)

<a id="tour-1"></a>

### Strict bootstrap/imports

strict_types applies to this calling file. require_once bootstrap avoids duplicate helper declarations; actions requires its dependent services.

<a id="tour-2"></a>

### Page selection

Default home, explicit list including student and admin paths; unknown becomes notfound404 rather than a dynamic include path.

<a id="tour-3"></a>

### Native mutation error boundary

Only POST invokes handle_action; expected errors become readable page errors; generic Throwable attempts rollback and logs server-side. Logging is not permission to print secrets or request payloads.

<a id="tour-4"></a>

### Auth gates and 2FA

Reload user, gate personal/admin destinations, reject expired pending verify. A pending_user is not an authenticated account.

<a id="tour-5"></a>

### Club prefetch

Fetch club by integer club_id, convert missing record to notfound and avoid constructing a fake club space.

<a id="tour-6"></a>

### Notice/layout/render failure

Consume flash once and include layout. Rendering failure logs and returns503 plain fallback. No database details are disclosed to browser.

## 6. Actual annotated excerpts

These excerpts are copied verbatim from the inspected source; their line numbers refer to the baseline, not a promise that future edits retain the same positions.

### Source lines 5–13

```php
$page=(string)($_GET['page']??'home');
$pages=['activity','club','my_clubs','moderation','audit','home','clubs','schedule','tournaments','results','facilities','login','register','verify','dashboard','security','admin','approvals','reports','manage_clubs','manage_tournaments','manage_events','manage_resources'];
if (!in_array($page,$pages,true)) { http_response_code(404); $page='notfound'; }
$error=null;
try {
 if ($_SERVER['REQUEST_METHOD']==='POST') handle_action();
} catch (RequestError $ex) { $error=$ex->getMessage(); http_response_code($ex->status); }
catch (DomainException $ex) { $error=$ex->getMessage(); http_response_code(422); }
catch (Throwable $ex) { if (db()->inTransaction()) db()->rollBack(); error_log((string)$ex); $error='Something went wrong. Please try again or contact your administrator.'; http_response_code(500); }
```

A page allowlist makes unknown names404. Native POST calls handle_action inside an exception boundary. Expected RequestError preserves its status; business validation becomes422; unexpected failures log privately and render a generic message. A native successful action exits through go before layout rendering.

### Source lines 15–25

```php
 $current=user();
 if (in_array($page,['dashboard','activity','security','my_clubs','club'],true)) $current=require_user();
 if (in_array($page,['admin','approvals','reports','moderation','audit'],true) || str_starts_with($page,'manage_')) $current=require_admin();
 if ($page==='verify' && (empty($_SESSION['pending_user']) || ($_SESSION['pending_until']??0)<time())) go('login');
 $clubView=null;
 if ($page==='club') {
  $clubView=one('SELECT * FROM clubs WHERE id=?',[(int)($_GET['club_id']??0)]);
  if (!$clubView) { http_response_code(404); $page='notfound'; }
 }
 $notice=$_SESSION['flash']??null; unset($_SESSION['flash']);
 require __DIR__.'/../app/layout.php';
```

Authenticated page names call require_user; admin pages and manage_ prefixes call require_admin. A stale second-factor challenge redirects to login. Club ID is looked up before rendering; missing club becomes404. The one-use flash is consumed, then layout is required.

## 7. Input → transformation → output

**Input:** GET page/club_id, POST native form data, session state.

**Transformation:** Follow the coverage tour; validate at the boundary, enforce role/state rules, then write or project data.

**Output:** HTML page, error status, 303 redirect, or generic service failure.

## 8. Connections and call chain

Native: [public/index.php](public__index_php.md) → [actions](app__actions_php.md) → [models](app__models_php.md) / [community](../../Stage-02-Student-Experience/files/app__community_php.md) → [bootstrap PDO](app__bootstrap_php.md). REST: [public/api.php](public__api_php.md) → [REST dispatcher](app__api_php.md) → those same services. [Security](app__security_php.md) participates in authentication; [schema](database__schema_sql.md) supplies persistent constraints. [Verification suites](../verification-suites.md) connect these paths to actual assertions.

## 9. Why this implementation

Separating controller and PHP templates keeps native HTML simple. Many pages share view files; ownership must therefore be by branch and peer review.

## 10. Security, correctness and accessibility

The server is authoritative even when controls are hidden in HTML. Keep prepared values, trusted identifier allowlists, CSRF, role/ownership checks and transaction boundaries. Escape dynamic text at HTML output, not by damaging data before storage. Transport errors must remain understandable to the interface; Stage 01 reviews focus/announcements when error presentation changes. A diagram or explanation here is not a security certification. See [known product limits](../../docs/security.md).

## 11. Common failure diagnosis

- Removing manage_ guard leaks admin views even if actions still block writes.
- Using page as a filename allows path traversal; keep the explicit page dispatch.
- Debug echo before redirect breaks headers.
- Assuming a dashboard redirect proves authentication ignores server gates.

## 12. Run and verify safely

From repository root:

```bash
php -l public/index.php
```

This checks PHP syntax only; it does not prove HTTP, SQL, authorization or concurrency behavior. Follow [testing](../06-testing-and-debugging.md) for the appropriate functional suite. Do not execute the file as a standalone web application or point reset-capable tests at real data.

## 13. Debugging lesson

Set a breakpoint at the first relevant tour function, then step into its callee. Observe method, route, integer record ID, expected branch and transaction state. Do not inspect/share screenshots of password fields, session IDs, CSRF values, factor secrets, recovery codes or full account rows. If logging is needed, use a safe event label and request ID in protected logs; remove temporary diagnostics after the exercise.

## 14. Guided experiment

Request /?page=does-not-exist and a private page while signed out; predict404 versus login303. Use GET only and record final/status chain.

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

Controller: request coordinator; template: HTML-producing source; allowlist: explicit accepted values; flash: one-use notice.

## 20. One-minute explanation to personalize

Explain the file’s role, show one actual input check, follow one call to its next file, and demonstrate one failure case. Say “this existing baseline does…” before describing any real change you personally made. Add your own evidence and assistance disclosure; do not read this guide as a claim that you authored the prototype.
