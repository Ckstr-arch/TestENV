# 🧪 Tests and quality gates

Read the [current execution register](16-evidence-and-artifact-register.md) before quoting results. The historical 291 checks from 17 October are not a new run. Documentation changes do not certify security or production hosting.

| Layer | Command | Required prerequisites | State affected |
|---|---|---|---|
| Syntax | PHP `-l` over tracked PHP; Node `--check`; shell `-n` | Compatible binaries | None |
| Security unit-like | `php tests/security.php` | Compatible PHP and private valid APP_KEY | No SQL writes; random cryptographic samples |
| Readiness | `php bin/doctor.php`; GET health | DB/schema and private config | Read-only database queries; web sessions may be created |
| Native integration | `python3 tests/integration.py` | Private DB access, isolated sportshub_test and free port 8081 | Drops/rebuilds test tables |
| REST | `python3 tests/api.py` | Same, with free port 8082 | Drops/rebuilds test tables |
| Community | `python3 tests/community.py` | Same, with free port 8085 and a second PDO connection | Drops/rebuilds test tables; race fixture |
| Browser | `python3 tests/browser.py` | Same, with Playwright/Chromium and free port 8083 | Drops/rebuilds test tables |
| Documentation | `python3 Assemble/checks/check_learning_docs.py` | Python standard library | Read-only validation |
| Production smoke | Planned manual HTTPS checks with synthetic accounts | Authorized host | Limited controlled writes; no reset suites |

## Safety before optional database tests

Inspect tests/fixtures.php: its exact DB_NAME=sportshub_test and nonproduction guards precede table drops. Each runner explicitly overrides DB_NAME and APP_ENV in its child environment. Verify the selected account/server really points to a disposable schema of that exact name, with no wanted records. Do not merely trust an environment filename. Run suites **sequentially** because they share the schema. Never execute fixtures against a real database. This task intentionally uses nondestructive runtime checks.

```bash
# On a human's reviewed isolated test setup only:
PHP_BIN=/path/to/php python3 tests/integration.py
PHP_BIN=/path/to/php python3 tests/api.py
PHP_BIN=/path/to/php python3 tests/community.py
CHROMIUM_PATH=/path/to/chromium PHP_BIN=/path/to/php python3 tests/browser.py
```

Distinguish a missing driver/database/server from an assertion failure. Preserve the result count and command exit status; do not pipe to a command that masks failures. Confirm tests actually executed rather than selecting zero cases. Never weaken assertions for a green badge. Do not log cookies, passwords, authenticator secrets or personal data.

## Proposed CI design — PLANNED, not installed

No `.github/workflows` file was added or run. A real workflow needs PHP 8.2+ with the required extensions/Argon2id, Python 3, a disposable MySQL 8 service with sportshub_test, and synthetic short-lived credentials injected into the runner environment. Set APP_KEY to an ephemeral random 32-byte value privately, APP_ENV to development, DB_HOST to the mapped service address and DB_PORT to the actual port. Do not print secrets. Run syntax/security, then isolated integration/API/community suites sequentially. An optional browser job installs a reviewed pinned Playwright/browser combination. Upload redacted outcomes, never environment files, sessions or SQL dumps.

Before activating CI, reproduce against MySQL 8, validate service health and schema grants (fixtures create/drop tables), verify free ports and server-process cleanup, and review trust rules for fork PRs. Do not expose production secrets to untrusted PR jobs or use `pull_request_target` with checked-out untrusted code. Add a workflow only after it is testable; host-specific scripts remain proposals.

## Human manual gates

- Desktop/mobile at 390px and 1440px, both themes, no overflow; keyboard skip link, profile, drawer, focus, Escape and reduced motion.
- JavaScript disabled: native forms still work; a forged student request to an admin API is rejected.
- Pending/approved/former club access; author versus other-user editing; report privacy; hidden post cannot restore itself through author editing.
- Paired scores, valid times, capacity, collision rejection and cancellation ownership.
- New-user 2FA lifecycle without screenshots of keys or recovery codes.

Save actual observations in [contribution evidence](templates/contribution-record.md). Suggested checks are not automatically claims that they ran in this task.
