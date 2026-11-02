# ✅ Verification suites: evidence without damaging data

[Stage dashboard](README.md) · [Current execution register](../Assemble/16-evidence-and-artifact-register.md) · [Assemble quality gates](../Assemble/10-ci-tests-and-quality-gates.md)

A test is useful when a realistic defect would make it fail. “PHP syntax is valid,” “a port is open,” “the JSON parses,” and “a student completed the flow” prove different things. Keep these claims separate. The table below classifies **actual checked-in tests**, not proposed replacements.

## Suite matrix

| File / primary guide | Kind and actual coverage | Dependencies / port | Data effects | Evidence and limits |
|---|---|---|---|---|
| [security.php](files/tests__security_php.md) | Published RFC6238 SHA1 vectors; key size; replay/drift/malformed codes; authenticated encryption/tampering; long Argon2id passwords; HTML escaping | Compatible PHP  + sodium/Argon2id; valid private APP_KEY; no HTTP server | No fixture reset or SQL writes; bootstrap reads local config | Fresh execution17 checks reported in current task's central register; does not exercise cookies/SQL/authorization |
| [fixtures.php](files/tests__fixtures_php.md) | Guarded disposable starting state, schema migration, sports content, synthetic admin/other student and completed training | PHP/PDO, disposable `sportshub_test` with schema privileges | **Drops all tables in selected test schema**, then rebuilds; not a test by itself | Read guard before any runner. No current reset execution claimed |
| [integration.py](files/tests__integration_py.md) | Native form POST, CSRF419, cookies/CSP, registration/login/roles, memberships, events/tournaments, booking, four admin CRUD entities, scores, attendance, 2FA/recovery/replay, cancellation/ownership, SQL metacharacters, throttling | Python3 stdlib; PHP_BIN; real SQL;8081 | Invokes destructive test fixtures; temporary server stopped in finally | Historic71 checks in legacy validation; **NOT RERUN for this documentation-only task** |
| [api.py](files/tests__api_py.md) | JSON methods/statuses/envelopes/headers, body limits and field allowlists, Origin/CSRF, safe profile, public collections/detail/filter/pagination, role guards, full four-entity CRUD, participation/reviews/reports, 2FA and demo refusal/repeatability, rate limiting | Python3 stdlib; PHP_BIN; real SQL;8082 | Fixture reset; explicit development demo seed; state mutations; own server cleanup | Historic130 checks; **NOT RERUN** now. Does not render CSS or prove concurrent booking safety |
| [community.py](files/tests__community_py.md) | Club membership gating/privacy, ownership, XSS output escaping, feed/comments/reactions/reports, admin hide/restore, leave revocation, tombstone deletion, repeat migrations, real concurrent parent-deletion/comment write | Python3 stdlib/concurrent.futures; PHP_BIN; real SQL;8085 | Fixture reset; synthetic writes and repeated migrations; extra PHP locking process | Historic73 checks; **NOT RERUN** now. Its concurrency case is specifically post deletion, not every reservation |
| [browser.py](files/tests__browser_py.md) | Playwright actual Fetch-backed forms; student/admin independent contexts; approval, enrollments/bookings/clubCRUD, 2FA dialog/recovery, discussions/moderation, persisted theme and placement, role menus, drawerEscape, desktop/mobile dark/light layouts | Optional Python Playwright  + Chromium; PHP_BIN; real SQL;8083; optional CHROMIUM_PATH | Fixture reset and synthetic journeys; browser contexts/session state; temporary server | Historic workflow pass message, not a numbered test total; **NOT RERUN** now; not a full accessibility audit |

Do not add the historical numbers into a “today passed291” statement. Refer to the [legacy17October validation](../docs/validation.md) for historical context and the [current register](../Assemble/16-evidence-and-artifact-register.md) for what ran in this task. Native Windows/macOS, production MySQL 8, hosting, restore rehearsal and real student work remain separate checks.

## Why the fixture boundary matters

Actual guard from [fixtures.php](../tests/fixtures.php):

```php
if (config('DB_NAME')!=='sportshub_test' || config('APP_ENV')==='production') throw new RuntimeException('Fixtures require DB_NAME=sportshub_test in development.');
```

The exact database-name check and production refusal occur before the destructive SQL. The runner explicitly overrides DB_NAME with `sportshub_test`; inherited DB host/user credentials still determine **which server** contains that schema. A schema with that name can still contain wanted data if somebody misused it. Prove both server separation and disposability; the name alone is not permission to delete it.

The actual check rejects production but technically permits other nonproduction mode names; the Python runners explicitly use development. Do not “fix” a failure by bypassing or weakening the guard.

### Safe preflight without printing private values

1. Read the runner's environment construction and the guard in the linked source. Confirm the exact test name and development mode.
2. In your local SQL administration client, verify this is a deliberately separate disposable schema with only synthetic data. Do not dump user rows, password hashes or environment values into a report.
3. Confirm the configured database account has access to this test schema. Use a test-only account when practical; never borrow a production account to make a classroom test pass.
4. Confirm the intended server port is free. The runners own their newly started PHP process; never kill unrelated services occupying a port.
5. Save the preflight result as “separate disposable schema confirmed” and who checked it. If uncertain, record SKIPPED with reason. You can still run syntax/unit/documentation checks.
6. Run suites **sequentially**. All reset the same test schema; running them concurrently creates false failures and damages each other's fixtures.

## Non-destructive commands

From the repository root with compatible PHP on PATH:

```bash
php --version
php -m
php --ini
php -r 'var_export(password_algos());'
php -l public/index.php
php -l public/api.php
php tests/security.php
```

Module/version inspection does not reveal configured secrets. `php bin/doctor.php` additionally opens the configured database and reads representative table counts without printing rows or credentials. It does not reset tables. It is a readiness diagnostic, not a comprehensive schema/security audit: its table loop does not list every table.

```bash
php bin/doctor.php
node --check public/assets/app.js
node --check public/assets/theme-init.js
bash -n bin/cloud-start.sh
bash -n docker/entrypoint.sh
python3 -m json.tool public/assets/openapi.json > /dev/null
git diff --check
```

Node is optional for JavaScript syntax checking; the app itself does not require Node/npm. On Windows use the corresponding PHP executable or PowerShell invocation from the [Windows guide](../Assemble/04-local-development-windows-linux.md). Bash checks apply in a Bash-capable environment.

No `php bin/migrate.php` is included in the non-mutating list: it is data-preserving by design but does create schema objects and migration records when needed. Initialization and seeding are state changes even if repeatable.

## Optional disposable integration execution

The following instructions are for a human's **confirmed disposable** local setup; they were not run merely to author this chapter:

```bash
PHP_BIN=php python3 tests/integration.py
PHP_BIN=php python3 tests/api.py
PHP_BIN=php python3 tests/community.py
# Optional, after Playwright  + Chromium prerequisites:
PHP_BIN=php python3 tests/browser.py
```

PowerShell equivalent environment selection:

```powershell
$env:PHP_BIN = 'C:\xampp\php\php.exe'
py -3 tests/integration.py
py -3 tests/api.py
py -3 tests/community.py
# Optional after browser prerequisites:
py -3 tests/browser.py
```

A nonzero exit is a failure to investigate, even if many earlier PASS labels printed. Preserve the underlying exit status when capturing output. The form suite may wait up to the next 30-second TOTP step; this is intentional replay-safe behavior, not a reason to disable timing rules. Keep test logs private and redact any future failure payload that could expose secrets.

## Source helper coverage index

| Suite | Helpers / meaningful regions to understand |
|---|---|
| security | `check`; RFC vector loop; random secret and counter assertions; encryption bit-flip catch; password suffix check; `e()` output |
| fixtures | exact config guard; foreign-key-check toggles; table loop; migration/seed includes; synthetic inserts; IDs JSON |
| integration | `Browser.__init__`, `request`, `post`; `check`, `dbvalue`, `totp`; public/security block; student/admin domain blocks; CRUD loop/individual entities; 2FA lifecycle; cleanup |
| api | `Client.__init__`, `call`, `login`; `check`, `dbvalue`, `totp`; transport/validation/read blocks; auth/domain/CRUD; 2FA; demo repeat/refusal; rate-limit; cleanup |
| community | `Client.__init__`, `call`, `login`, `html`; `check`, `dbvalue`; role/membership matrix; text/ownership/idempotence; moderation; leave/deletion; migration preservation; locking subprocess/thread; cleanup |
| browser | `totp`, nested `login`; launch/context/listeners; all UI journeys; viewport/theme loops; error/overflow assertions; cleanup |

The [individual file guides](02-source-inventory.md) provide source-order tours, real excerpts and three reasoning questions for each file. These are actual script helpers, not an invented testing framework.

## Failure triage: identify the layer

| Symptom | First investigation | Safe next action |
|---|---|---|
| `could not find driver` | PHP_BIN/CLI modules differ from web PHP | Enable the proper PDO MySQL extension; rerun doctor |
| Fixture refused | Wrong DB_NAME or production mode | Stop; prove intended disposable config. Keep the guard |
| SQL access denied | Account/host-scoped grants or wrong server | Review private local DB settings/grants without printing them |
| HTTP connection refused | Temporary server exited or port conflict | Inspect PHP path/startup error; do not kill unrelated process |
| CSRF failure after login | Cookie/token rotation, stale client token or wrong Origin | Fetch fresh session token and retain correct cookie jar; do not weaken checks |
| Request expected403 but200 | Role/ownership enforcement regressed | Inspect server boundary; a hidden button is insufficient |
| Time-sensitive 2FA failure | Counter boundary, code replay or device clock | Wait for a fresh step; check clocks; never log keys/codes |
| Browser timeout after viewport change | Drawer CSS transition or wrong selector state | Wait for explicit visible/hidden or navigation state rather than adding a random sleep |
| Community race returns wrong status | Parent lock, isolation or stale status check | Inspect locking query/read order and current committed state; preserve failing scenario |
| All syntax passes but flow fails | Syntax says nothing about data/behavior | Follow the real route→action→SQL chain with a synthetic account |

## Practice: design an assertion before changing code

**Objective:** make a test fail for a meaningful defect. **Files:** choose one linked suite and the service it exercises. **Prerequisites:** know its fixture boundary, role matrix and expected status. **Setup:** write a prediction first; this is a design lab and needs no reset execution.

1. Pick a specific invariant, such as “a pending member cannot read the club roster.” Identify the existing assertion label and the PHP guard it depends on.
2. Describe a plausible defect that would violate the invariant. Do not actually remove the production guard as your classroom “improvement.”
3. Propose one additional edge case that is not a duplicate, such as membership revocation between two requests or a second club scope.
4. Define exact expected status, safe response fields and database outcome before implementing a test.
5. Ask a domain reviewer and Stage 04 reviewer whether the case represents real behavior. Only then manually implement on your approved feature branch and run on disposable data.

**Expected evidence:** prediction, source links, assertion wording, actual command/result if eventually run, reviewer feedback, and one limitation. **Revert:** there are no runtime changes in the design-only exercise; remove only your own temporary note/test diff if abandoning it. Do not reset the checkout or shared database.

<details><summary>Answer key: what makes the test meaningful?</summary>

An assertion must observe the invariant at its trusted boundary: HTTP status/allowed payload plus row effects where relevant. Merely checking that a button is absent does not prove server authorization; mirroring an implementation string can pass despite incorrect behavior. Tests should explain intended user and data outcomes, not force a preferred internal code shape.

</details>
