# 06 — Verify the boundary you changed

[Dashboard](README.md) · [Detailed suite guide](verification-suites.md) · [Current run evidence](../Assemble/16-evidence-and-artifact-register.md)

A syntax check detects parse errors; a functional test proves a behavior under specified conditions. Neither alone proves deployment security. Historic counts in `docs/validation.md` belong to their recorded run. Use the current Assemble register for this task’s actual execution status.

## Non-destructive first pass

From repository root:

```bash
php --version
php -m
php -l public/index.php
php -l public/api.php
php tests/security.php
git diff --check
```

`tests/security.php` uses public RFC vectors, synthetic encryption/password data and escaping assertions. Inspect it first; it does not run a fixture DB reset. It still needs sodium/Argon2id and bootstrap support. `bin/doctor.php` verifies runtime requirements, DB/schema and private writable sessions; read its guide, since a storage check may create a private directory.

## Reset-capable suites: stop at the isolation gate

Before running any command below:

1. Read the selected Python file and `tests/fixtures.php`, following its environment construction and PHP subprocess calls.
2. Prove the selected fixture DB is **exactly `sportshub_test`**, that the fixture refuses any other DB name and that your real DB name differs. Verify the effective target without printing credential values.
3. Confirm `sportshub_test` contains only disposable synthetic data and its DB user has the intended test grants. If any condition is uncertain, mark SKIPPED and explain the missing proof.
4. Run suites sequentially: they share and reset that schema. Confirm their temporary ports are available.

```bash
python3 tests/integration.py
python3 tests/api.py
python3 tests/community.py
# Optional installed Playwright + Chromium required:
python3 tests/browser.py
```

When PHP is not on PATH, use the supported `PHP_BIN` binding, for example the prepared cloud wrapper path documented in the original setup guide. Never substitute a real database into the fixture name to “fix” permissions. These suites are optional for a docs-only change if isolation cannot be established; report the reason honestly.

| Kind | Suite | Main evidence | Limit |
|---|---|---|---|
| Primitive/security | tests/security.php | TOTP vectors, encryption, password/escape behavior | Not a real browser login |
| Native HTTP/DB | tests/integration.py | Shared forms, approvals, CRUD, reservations/auth | Resets test DB; local server |
| REST/DB | tests/api.py | JSON statuses/fields/roles/CSRF/state | Resets test DB; not a host HTTPS audit |
| Community/DB | tests/community.py | Club access/ownership/moderation/deletion race | Controlled synthetic concurrency scenario |
| Browser | tests/browser.py | Actual DOM/fetch, roles/themes/mobile/auth flows | Needs Playwright/Chromium; not every browser/OS |
| Manual real host | Runbook checklist | HTTPS/cookie/webroot/live-role behavior | NOT RUN until a human-authorized deployment |

## Focused debugging procedure

Record expected status and row state first. In browser Network inspect method, URL and response status, but do not export requests containing secrets. Step through dispatcher→action→model with a debugger. At each branch ask “which input is trusted, and which is being validated?” Check `db()->inTransaction()` at the intended boundary and observe only safe row counts/status fields for synthetic IDs.

If API returns403, distinguish missing/stale CSRF, wrong supplied Origin and wrong role before changing code. If native returns419, reload and compare form/session lifetime.422 means validation/business state, not necessarily SQL.409 maps certain integrity/state conflicts.503 suggests DB/config/service availability;500 unexpected server failure. The JSON request ID helps locate private logs; it must not be confused with a session token.

## Reviewer quality gate

- [ ] Changed function/route and expected invariant named.
- [ ] SQL values remain prepared and identifiers remain allowlisted.
- [ ] Native+REST behavior reviewed when a shared action changes.
- [ ] Relevant positive and meaningful negative case demonstrated.
- [ ] Test DB separation proven before resets; no real records in captures.
- [ ] Executed, failed, skipped and planned checks reported separately.
- [ ] Runtime limits and migration consequences disclosed.
- [ ] Only the learner’s temporary changes reverted; no global reset/cleanup.
