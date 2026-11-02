# 🔍 `bin/seed.php` — source guide

[Stage dashboard](../README.md) · [Data dictionary](../data-dictionary.md) · [SQL and transactions](../sql-and-transactions.md) · [Verification suites](../verification-suites.md)

## 1. File card

| Field | Verified source fact |
|---|---|
| Canonical source | [bin/seed.php](../../bin/seed.php) |
| Primary learning owner | Stage 04; Member D **TBD**, no authorship assigned |
| Secondary reviewers | Stage 02 for student behavior; Stage 03 for administration; Assemble for operations |
| Language / size | php; 17 lines at inspection |
| Inspected baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |
| Role | Supply disposable sports content, never real-user identities. |

## 2. Why this file exists

Supply disposable sports content, never real-user identities. This file remains part of the existing Codex-assisted baseline. Studying or receiving this guide is not evidence of an individual's implementation or testing.

## 3. Prerequisites

Understand [PHP request boundaries](../01-architecture.md), [relational keys and transactions](../sql-and-transactions.md), and [test isolation](../verification-suites.md). A command-line process is a program launched in a terminal; a web request runs under a server process with potentially different permissions and PHP configuration.

## 4. Dependencies and inputs

CLI; nonproduction environment; migrated tables; current Asia/Colombo clock.

Connected source: [bin/container-init.php](../../bin/container-init.php) · [bin/demo-users.php](../../bin/demo-users.php) · [database/schema.sql](../../database/schema.sql).

## 5. Detailed source-order tour and coverage index

This index covers the meaningful sections of this file at the inspected baseline. Line positions describe this version; follow the linked source after later changes.

| Region | Explanation |
|---|---|
| 1–6 | [Guards and repeat behavior](#guards-and-repeat-behavior) |
| 7–10 | [Clubs and tournaments](#clubs-and-tournaments) |
| 11–14 | [Events and resources](#events-and-resources) |
| 15–17 | [Atomic completion](#atomic-completion) |

### Guards and repeat behavior

Source lines 1–6: Production refuses. A nonempty clubs table returns rather than exits so container-init can continue to later demo scripts.

### Clubs and tournaments

Source lines 7–10: Transaction encloses inserts. Club arrays bind all values through query. Tournament dates derive from execution time; they do not move on restarts.

### Events and resources

Source lines 11–14: Each club receives training; match examples include a historical scored match. Resources are individual reservable units, not stock quantities.

### Atomic completion

Source lines 15–17: Commit only after all inserts; catch rolls back the active transaction and rethrows.

## 6. Real annotated excerpt

Copied from source lines 3–7; this is baseline code, not a proposed change.

```php
require_once __DIR__.'/../app/bootstrap.php';
if (PHP_SAPI!=='cli') exit;
if (config('APP_ENV')==='production') { fwrite(STDERR,"Demo seeding is disabled in production.\n"); exit(1); }
if (query('SELECT COUNT(*) FROM clubs')->fetchColumn()) { echo "Existing clubs detected; demo seed skipped.\n"; return; }
db()->beginTransaction();
```

require_once provides DB/config/timezone. Production is the exact blocked mode; this helper is less restrictive than demo-users, so humans should explicitly use development. COUNT(*) prevents routine duplication. return keeps an including caller alive. beginTransaction makes all sports inserts one unit.

## 7. Input → transformation → output

**Input:** CLI; nonproduction environment; migrated tables; current Asia/Colombo clock.

**Transformation:** follow section 5 in order; guards run before the important side effects, helpers supply shared database/security behavior, and failures propagate rather than silently succeeding.

**Output:** Clubs, tournaments, training/matches, resources in one transaction; skips if clubs exist.

## 8. Call chain and connected evidence

[bin/container-init.php](../../bin/container-init.php) · [bin/demo-users.php](../../bin/demo-users.php) · [database/schema.sql](../../database/schema.sql)

Use the [complete assembly inventory](../../Assemble/01-repository-assembly-map.md) to find the canonical guide for each dependency. Source links above lead to the executable file; guides explain it without creating a second runtime copy.

## 9. Why this implementation?

The project uses direct PHP services and small CLI/Python helpers so students can trace the actual request and SQL without a framework or package build. Shared helpers prevent separate validation logic for REST and native forms. Trade-off: compact source lines and hand-maintained route/schema catalogs demand disciplined review. Do not introduce a framework merely to make this guide easier.

## 10. Security, correctness and accessibility

Keep private configuration, password hashes, authenticator material, cookies and recovery codes out of terminals, screenshots and reports. The configured document root must remain `public/`; these learning folders and private service/scripts are not webroots. Database constraints protect relationships, while server authorization and business validation protect who may change rows. Neither a green CLI check nor a JSON schema certifies production security.

## 11. Common failures and safe diagnosis

Seed skipped: clubs already exist by design; old dates: edit synthetic events in UI, never reset wanted DB; partial prerequisite tables: migrate first; wrong DB: stop before execution.

For HTTP status meaning and deployment diagnostics use [host troubleshooting](../../Assemble/11-host-troubleshooting-and-rollback.md). Diagnose the failing layer before changing a security control.

## 12. Run or verify safely

```bash
# From repository root. Read the safety classification before executing.
php -l bin/seed.php
```

> [!WARNING]
> `tests/fixtures.php` and all Python integration/browser runners reset **only the deliberately disposable `sportshub_test` schema**. Prove that schema is separate and disposable before execution. Never point fixtures at real records. Run suites sequentially. Do not execute commands merely because they appear in a code fence.

Actual current-task results belong in the [evidence register](../../Assemble/16-evidence-and-artifact-register.md); commands in this guide are instructions, not claims of execution.

## 13. Debugging lesson

Start at the first guard or failing assertion in section 5. Inspect process exit status, exception category, boolean outcomes, schema name and route/method. Set a local debugger breakpoint before a side effect and predict the next call. Do not dump `$_POST`, environment values, session arrays, full JSON auth responses or SQL identity rows. Use synthetic accounts in disposable local data.

## 14. Guided experiment

Read date offsets and predict which tournament closes first. On a fresh disposable study DB compare those predictions after one seed; repeat and verify counts unchanged. Do not refresh by clearing a shared DB.

Save a prediction, observed safe output and explanation in your [human evidence record](../09-completion-evidence.md). Revert only your temporary edits by comparing your recorded diff in the editor; remove only files you created in your isolated sandbox. Do not use a blanket checkout reset or a shared database deletion as cleanup.

## 15. Challenge ladder

| Level | Task | Expected evidence / hint |
|---|---|---|
| 1 — Understand | Trace one input through the excerpt to its next dependency. | A labeled call chain and predicted outcome, verified by reading source. |
| 2 — Modify | Propose a focused diagnostic or documentation improvement for this file. Implement manually only in your owned approved feature branch. | Before/after diff, a justified check, and no secret-bearing output. Preserve guards. |
| 3 — Engineer | Select a failure from section 11; explain its boundary and design a regression scenario. | Reproduction using synthetic disposable data or a non-mutating check, expected failure, reviewer discussion and honest trade-offs. |

## 16. Knowledge check

1. Why return rather than exit?
2. Does sports seed create shared admin passwords?
3. Does restart refresh dates?

<details><summary>Answer key — read after predicting</summary>

1. container-init includes this script and needs to continue.
2. No; separate explicit demo/provisioning helpers do.
3. No; inserts happen once and persisted dates remain.

</details>

## 17. Contribution boundaries

Stage 04 reviews technical data/security behavior; Stage 02/03 review observable domain changes, and Assemble reviews startup/provisioning changes. Name the exact function/region before shared-file edits, preserve the native/REST contract and existing data, and obtain a real peer review. Do not change an applied migration, weaken a fixture guard, overwrite `.env` or rotate an existing encryption key casually. [Team workflow](../../Assemble/02-git-github-team-workflow.md) is a future human procedure, not an automated action.

## 18. Current status and limitations

**VERIFIED BY INSPECTION:** source role, inputs, outputs and the section index above at baseline `f81eab5`. **NOT RUN by this guide's authoring step:** execution outcomes; consult the dated [evidence register](../../Assemble/16-evidence-and-artifact-register.md) for current results. Historical pass counts in legacy docs are not fresh results. Production MySQL 8 hosting, other OS runtimes, image usage rights and human contribution evidence remain separate verification work.

## 19. Glossary

- **Guard:** a condition that refuses unsafe input/state before work proceeds.
- **Invariant:** a rule that must stay true, such as one membership per user/club.
- **Fixture:** synthetic starting data for a test; this project's fixture resets its disposable schema.
- **Idempotent:** repeating an operation has the same final state as doing it once; it does not mean every operation is safe to replay.
- **SAPI:** the interface under which PHP runs, such as CLI or Apache.

## 20. One-minute explanation to personalize

Explain why this file exists, identify its first safety boundary, trace the excerpt into one linked dependency, and show one observed result from your own work. Distinguish “I studied this baseline” from “I changed and tested this region.” Name remaining limitations rather than claiming authorship of pre-existing code.
