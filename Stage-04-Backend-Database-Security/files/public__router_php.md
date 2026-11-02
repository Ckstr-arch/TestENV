# Development router: route API requests before static files

[Stage dashboard](../README.md) · [Real source](../../public/router.php) · [Source inventory](../02-source-inventory.md)

## 1. File card

| Item | Value |
|---|---|
| Canonical path | [public/router.php](../../public/router.php) |
| Primary owner | Stage 04; Member D **TBD**, no authorship assigned |
| Secondary reviewers | Stage 02 for student effects; Stage 03 for admin rules; Stage 01 for transport/UI effects |
| Language / size | PHP; 6 physical lines (many contain several statements) |
| Role | Bridge PHP’s built-in development server to the same page/API entrypoints Apache serves. |
| Inspected baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |

## 2. Why it exists

Starting php -S with only -t public does not make /api/v1/... reach api.php. This short router gives friendly REST paths their real PHP handler while keeping assets static.

## 3. Prerequisites

Read [PHP and HTTP foundations](../php-http-and-security.md), then the [request sequence](../01-architecture.md). Know variables, arrays, if/else, functions and exceptions before following a whole request. SQL prerequisites are in [SQL and transactions](../sql-and-transactions.md).

## 4. Dependencies

public/api.php,index.php; PHP built-in server; Apache uses public/.htaccess instead.

The dependency is canonical source, not a PHP copy in these learning folders. See [the full map](../../Assemble/01-repository-assembly-map.md).

## 5. Source-order coverage tour

The index below accounts for every meaningful function/branch group in this file. Related operations share a row where their implementation is inseparable; read the rows in order. No intentional runtime region is omitted.

- [Path extraction](#tour-1)
- [API prefix](#tour-2)
- [Website routes](#tour-3)
- [Static fallback](#tour-4)

<a id="tour-1"></a>

### Path extraction

parse_url(...,PHP_URL_PATH) reads only path, so ?page=clubs does not interfere with matching /.

<a id="tour-2"></a>

### API prefix

Only a string beginning /api/ includes api.php; that entrypoint later rejects unsupported versions/routes.

<a id="tour-3"></a>

### Website routes

/ and /index.php include index.php and report handled=true. Page selection remains in index.php GET page.

<a id="tour-4"></a>

### Static fallback

return false delegates assets to PHP development server under -t public. It does not expose app/ if public is correctly configured as document root.

## 6. Actual annotated excerpts

These excerpts are copied verbatim from the inspected source; their line numbers refer to the baseline, not a promise that future edits retain the same positions.

### Source lines 1–6

```php
<?php
/** Router for PHP's local development server. Apache uses .htaccess. */
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
if (is_string($path) && str_starts_with($path,'/api/')) { require __DIR__.'/api.php'; return true; }
if ($path==='/' || $path==='/index.php') { require __DIR__.'/index.php'; return true; }
return false;
```

parse_url strips query parameters before matching. /api/ prefixes go to JSON entrypoint and return true; / and /index.php go to HTML. Everything else returns false, letting the built-in server serve actual public files or404.

## 7. Input → transformation → output

**Input:** Request URI path for the development server.

**Transformation:** Follow the coverage tour; validate at the boundary, enforce role/state rules, then write or project data.

**Output:** Included API/page controller, or false so the built-in server handles static files.

## 8. Connections and call chain

Native: [public/index.php](public__index_php.md) → [actions](app__actions_php.md) → [models](app__models_php.md) / [community](../../Stage-02-Student-Experience/files/app__community_php.md) → [bootstrap PDO](app__bootstrap_php.md). REST: [public/api.php](public__api_php.md) → [REST dispatcher](app__api_php.md) → those same services. [Security](app__security_php.md) participates in authentication; [schema](database__schema_sql.md) supplies persistent constraints. [Verification suites](../verification-suites.md) connect these paths to actual assertions.

## 9. Why this implementation

This file is deliberately development-only; production uses Apache rewrite or an equivalent reviewed Nginx/PHP-FPM route.

## 10. Security, correctness and accessibility

The server is authoritative even when controls are hidden in HTML. Keep prepared values, trusted identifier allowlists, CSRF, role/ownership checks and transaction boundaries. Escape dynamic text at HTML output, not by damaging data before storage. Transport errors must remain understandable to the interface; Stage 01 reviews focus/announcements when error presentation changes. A diagram or explanation here is not a security certification. See [known product limits](../../docs/security.md).

## 11. Common failure diagnosis

- Omitting public/router.php in php -S makes API404.
- Running -t . exposes private project files: use -t public.
- Copying this development routing expectation to Nginx without a matching server rule breaks API paths.

## 12. Run and verify safely

From repository root:

```bash
php -l public/router.php
```

This checks PHP syntax only; it does not prove HTTP, SQL, authorization or concurrency behavior. Follow [testing](../06-testing-and-debugging.md) for the appropriate functional suite. Do not execute the file as a standalone web application or point reset-capable tests at real data.

## 13. Debugging lesson

Set a breakpoint at the first relevant tour function, then step into its callee. Observe method, route, integer record ID, expected branch and transaction state. Do not inspect/share screenshots of password fields, session IDs, CSRF values, factor secrets, recovery codes or full account rows. If logging is needed, use a safe event label and request ID in protected logs; remove temporary diagnostics after the exercise.

## 14. Guided experiment

Start only a new isolated dev server on a free port with matching development APP_URL, request a CSS asset and /api/v1/health, then stop only that foreground process with Ctrl+C. Do not stop teammates’ services.

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

Document root: HTTP-visible directory; static file: bytes served without PHP execution; URI path: URL portion before query.

## 20. One-minute explanation to personalize

Explain the file’s role, show one actual input check, follow one call to its next file, and demonstrate one failure case. Say “this existing baseline does…” before describing any real change you personally made. Add your own evidence and assistance disclosure; do not read this guide as a claim that you authored the prototype.
