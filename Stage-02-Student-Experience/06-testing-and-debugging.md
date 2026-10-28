# 06 · Verify behavior, not a green-looking page

## Isolation gate before database suites

The app's ordinary `sportshub` data must survive. The Python integration, REST, community and browser runners reset `sportshub_test`. Read each runner and `tests/fixtures.php` before running it. Confirm the override is literally the disposable test name, the test database exists, credentials grant only the intended databases, and no valued data lives there. Do not print passwords or full environment dictionaries. If any separation is unclear, SKIP and record the uncertainty.

```bash
# From repository root: read-only source checks and PHP syntax
rg -n 'sportshub_test|DB_NAME|DROP|fixtures.php|PHP_BIN' tests
php -l app/views.php
php -l app/portal-views.php
php -l app/community.php
php tests/security.php
```

After the gate, these are **manual optional future commands**, sequentially, not evidence that they ran here:

```bash
python3 tests/integration.py
python3 tests/api.py
python3 tests/community.py
python3 tests/browser.py
```

Use `PHP_BIN=/path/to/compatible/php` when the runners would select a different PHP. Browser tests require Python Playwright and Chromium; they run a temporary server and reset the same test schema. Do not run the suites concurrently. Current execution status belongs in [Assemble evidence](../Assemble/16-evidence-and-artifact-register.md); old counts in legacy validation are historical, not current-run results.

## Coverage to inspect

| Concern | Existing evidence in source | What it cannot alone prove |
|---|---|---|
| Native transport | integration.py POST signup/login/join/book/admin flows | Live host rewrites/HTTPS |
| API boundaries | api.py role mass assignment, enrollment, deadlines/capacity, own cancellation | Touch/keyboard usability |
| Private community | community.py guest/nonmember/pending/revoked and author/admin cases | Every moderation policy is appropriate |
| XSS | community.py synthetic markup escaped in HTML | Independent comprehensive security review |
| UI | browser.py actual forms/fetch/theme/mobile tabs | Real macOS/Windows execution or screen-reader quality |
| Auth primitives | security.php TOTP/encryption/password/escaping checks | Operational backup/key retention |

## Debug a single failing request

Start with the *first* failed operation: route, method, status and visible message. Use Network to distinguish no request, wrong URL, server refusal and redirect. Never export a HAR containing login, cookies, setup or recovery values.

| Symptom | Likely boundary | Safe diagnosis |
|---|---|---|
| Apply button asks to sign in | Session absent | Check exact APP_URL/browser context; sign in normally |
| Pending badge but no private feed | Membership authorization | Inspect own status; request approval, never change UI `$allowed` to bypass |
| Another club refuses feed | Club-scoped access | Membership in club A does not authorize club B |
| API 403 on writes | CSRF/Origin/role | Reload/bootstrap token, preserve cookie, match scheme/host/port |
| Native419 | Form token expired | Reload and resubmit once after verifying state |
| API 404 for a visible post | Hidden/deleted or wrong club/post pair | Read visibility and ownership rules; do not expose hidden body |
| Validation422 on booking | Interval/overlap/purpose rules | Compare parsed times in Asia/Colombo and status of synthetic bookings |
|403 editing someone else's post | Ownership | Expected refusal; do not copy another user's identity |
| Code rejected | Time/replay/expired challenge | Verify clock, correct account and fresh step; no secret logging |
| Fetch network error | Outcome unknown | Read current state before retry; the write may already have committed |

## Breakpoints without sensitive logs

For native membership, stop at `perform_action('join')`, then `reserve()`; inspect action name, integer target and branch choice, not entire `$_POST`/`$_SESSION`. For a community comment, stop at `club_access`, parent lock and insert; record only status and synthetic row ID. For output escaping, inspect the rendered DOM text and compare it with `e()`; do not log private report text.

Manual student smoke: register/sign in; pending→approval→private feed; post/comment/react/report; future event and tournament entry; own history; nonoverlap/overlap booking; native fallback; light/dark;390 px navigation and focus. Save exact observations, mark FAIL or SKIPPED when warranted, and leave production checks unverified until a real host is tested.
