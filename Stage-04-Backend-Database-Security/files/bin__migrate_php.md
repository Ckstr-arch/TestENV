# 🔍 `bin/migrate.php` — source guide

[Stage dashboard](../README.md) · [Data dictionary](../data-dictionary.md) · [SQL and transactions](../sql-and-transactions.md) · [Verification suites](../verification-suites.md)

## 1. File card

| Field | Verified source fact |
|---|---|
| Canonical source | [bin/migrate.php](../../bin/migrate.php) |
| Primary learning owner | Stage 04; Member D **TBD**, no authorship assigned |
| Secondary reviewers | Stage 02 for student behavior; Stage 03 for administration; Assemble for operations |
| Language / size | php; 20 lines at inspection |
| Inspected baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |
| Role | Apply core schema and ordered additive SQL migrations without clearing data. |

## 2. Why this file exists

Apply core schema and ordered additive SQL migrations without clearing data. This file remains part of the existing Codex-assisted baseline. Studying or receiving this guide is not evidence of an individual's implementation or testing.

## 3. Prerequisites

Understand [PHP request boundaries](../01-architecture.md), [relational keys and transactions](../sql-and-transactions.md), and [test isolation](../verification-suites.md). A command-line process is a program launched in a terminal; a web request runs under a server process with potentially different permissions and PHP configuration.

## 4. Dependencies and inputs

CLI; configured DB_NAME and migration-capable DB account; schema.sql; sorted migrations/*.sql.

Connected source: [database/schema.sql](../../database/schema.sql) · [database/migrations/20261017_club_communities.sql](../../database/migrations/20261017_club_communities.sql) · [app/bootstrap.php](../../app/bootstrap.php) · [tests/community.py](../../tests/community.py).

## 5. Detailed source-order tour and coverage index

This index covers the meaningful sections of this file at the inspected baseline. Line positions describe this version; follow the linked source after later changes.

| Region | Explanation |
|---|---|
| 1–6 | [Bootstrap and database-scoped advisory lock](#bootstrap-and-database-scoped-advisory-lock) |
| 7–10 | [Base schema and migration discovery](#base-schema-and-migration-discovery) |
| 11–17 | [Version checks and retry-safe DDL](#version-checks-and-retry-safe-ddl) |
| 18–20 | [Completion and finally release](#completion-and-finally-release) |

### Bootstrap and database-scoped advisory lock

Source lines 1–6: Require bootstrap, restrict to CLI, hash DB_NAME into a lock name and GET_LOCK with a 10-second wait. Same DB migrations serialize; different schemas have independent locks.

### Base schema and migration discovery

Source lines 7–10: Execute semicolon-separated core DDL; create version ledger; sort globbed migration filenames. This parser is intentionally simple, unsuitable for stored routines or semicolons in literals.

### Version checks and retry-safe DDL

Source lines 11–17: Skip already recorded versions. Run each statement then insert filename into ledger. DDL can auto-commit; a partial failure needs statements safe to repeat, not an assumed transaction rollback.

### Completion and finally release

Source lines 18–20: Readiness message comes after all work. finally attempts RELEASE_LOCK on success or failure.

## 6. Real annotated excerpt

Copied from source lines 5–9; this is baseline code, not a proposed change.

```php
$lock='sportshub:'.substr(hash('sha256',config('DB_NAME','sportshub')),0,48);
if ((int)query('SELECT GET_LOCK(?,10)',[$lock])->fetchColumn()!==1) throw new RuntimeException('Another schema migration is running.');
try {
 foreach (explode(';',file_get_contents(ROOT.'/database/schema.sql')) as $sql) if (trim($sql)!=='') db()->exec($sql);
 db()->exec('CREATE TABLE IF NOT EXISTS schema_migrations (version VARCHAR(190) PRIMARY KEY, applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB');
```

hash binds the lock to a database name without disclosing it. GET_LOCK is advisory, not a lock on every app writer. A failed return aborts before DDL. IF NOT EXISTS preserves tables but does not alter old column shapes. schema_migrations records filenames, not SQL hashes.

## 7. Input → transformation → output

**Input:** CLI; configured DB_NAME and migration-capable DB account; schema.sql; sorted migrations/*.sql.

**Transformation:** follow section 5 in order; guards run before the important side effects, helpers supply shared database/security behavior, and failures propagate rather than silently succeeding.

**Output:** Core tables, schema_migrations ledger, applied community tables and readiness message.

## 8. Call chain and connected evidence

[database/schema.sql](../../database/schema.sql) · [database/migrations/20261017_club_communities.sql](../../database/migrations/20261017_club_communities.sql) · [app/bootstrap.php](../../app/bootstrap.php) · [tests/community.py](../../tests/community.py)

Use the [complete assembly inventory](../../Assemble/01-repository-assembly-map.md) to find the canonical guide for each dependency. Source links above lead to the executable file; guides explain it without creating a second runtime copy.

## 9. Why this implementation?

The project uses direct PHP services and small CLI/Python helpers so students can trace the actual request and SQL without a framework or package build. Shared helpers prevent separate validation logic for REST and native forms. Trade-off: compact source lines and hand-maintained route/schema catalogs demand disciplined review. Do not introduce a framework merely to make this guide easier.

## 10. Security, correctness and accessibility

Keep private configuration, password hashes, authenticator material, cookies and recovery codes out of terminals, screenshots and reports. The configured document root must remain `public/`; these learning folders and private service/scripts are not webroots. Database constraints protect relationships, while server authorization and business validation protect who may change rows. Neither a green CLI check nor a JSON schema certifies production security.

## 11. Common failures and safe diagnosis

Access denied: use reviewed migration grants; lock timeout: inspect active migration before retrying; partial DDL: inspect ledger and tables, never drop live data; editing applied SQL: add a new migration instead.

For HTTP status meaning and deployment diagnostics use [host troubleshooting](../../Assemble/11-host-troubleshooting-and-rollback.md). Diagnose the failing layer before changing a security control.

## 12. Run or verify safely

```bash
# From repository root. Read the safety classification before executing.
php -l bin/migrate.php
```

> [!WARNING]
> `tests/fixtures.php` and all Python integration/browser runners reset **only the deliberately disposable `sportshub_test` schema**. Prove that schema is separate and disposable before execution. Never point fixtures at real records. Run suites sequentially. Do not execute commands merely because they appear in a code fence.

Actual current-task results belong in the [evidence register](../../Assemble/16-evidence-and-artifact-register.md); commands in this guide are instructions, not claims of execution.

## 13. Debugging lesson

Start at the first guard or failing assertion in section 5. Inspect process exit status, exception category, boolean outcomes, schema name and route/method. Set a local debugger breakpoint before a side effect and predict the next call. Do not dump `$_POST`, environment values, session arrays, full JSON auth responses or SQL identity rows. Use synthetic accounts in disposable local data.

## 14. Guided experiment

On sportshub_test only after fixtures isolation review, record user count and a synthetic post body, run migration twice, verify both remain and version count remains 1. Never introduce a test migration in production.

Save a prediction, observed safe output and explanation in your [human evidence record](../09-completion-evidence.md). Revert only your temporary edits by comparing your recorded diff in the editor; remove only files you created in your isolated sandbox. Do not use a blanket checkout reset or a shared database deletion as cleanup.

## 15. Challenge ladder

| Level | Task | Expected evidence / hint |
|---|---|---|
| 1 — Understand | Trace one input through the excerpt to its next dependency. | A labeled call chain and predicted outcome, verified by reading source. |
| 2 — Modify | Propose a focused diagnostic or documentation improvement for this file. Implement manually only in your owned approved feature branch. | Before/after diff, a justified check, and no secret-bearing output. Preserve guards. |
| 3 — Engineer | Select a failure from section 11; explain its boundary and design a regression scenario. | Reproduction using synthetic disposable data or a non-mutating check, expected failure, reviewer discussion and honest trade-offs. |

## 16. Knowledge check

1. Why not roll back DDL as normal data?
2. What if a file changes after its version was recorded?
3. Does GET_LOCK block ordinary booking SQL?

<details><summary>Answer key — read after predicting</summary>

1. MySQL/MariaDB DDL can implicitly commit, so retry safety matters.
2. The file is skipped; create a new reviewed migration.
3. No, app transactions still enforce their own row invariants.

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
