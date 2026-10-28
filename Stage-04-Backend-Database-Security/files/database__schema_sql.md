# 🔍 `database/schema.sql` — source guide

[Stage dashboard](../README.md) · [Data dictionary](../data-dictionary.md) · [SQL and transactions](../sql-and-transactions.md) · [Verification suites](../verification-suites.md)

## 1. File card

| Field | Verified source fact |
|---|---|
| Canonical source | [database/schema.sql](../../database/schema.sql) |
| Primary learning owner | Stage 04; Member D **TBD**, no authorship assigned |
| Secondary reviewers | Stage 02 for student behavior; Stage 03 for administration; Assemble for operations |
| Language / size | sql; 67 lines at inspection |
| Inspected baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |
| Role | Define the 12 canonical core InnoDB tables; not a database dump of identities. |

## 2. Why this file exists

Define the 12 canonical core InnoDB tables; not a database dump of identities. This file remains part of the existing Codex-assisted baseline. Studying or receiving this guide is not evidence of an individual's implementation or testing.

## 3. Prerequisites

Understand [PHP request boundaries](../01-architecture.md), [relational keys and transactions](../sql-and-transactions.md), and [test isolation](../verification-suites.md). A command-line process is a program launched in a terminal; a web request runs under a server process with potentially different permissions and PHP configuration.

## 4. Dependencies and inputs

Migration executor and selected UTF-8 database; core DDL statement order.

Connected source: [bin/migrate.php](../../bin/migrate.php) · [app/models.php](../../app/models.php) · [app/actions.php](../../app/actions.php) · [database/migrations/20261017_club_communities.sql](../../database/migrations/20261017_club_communities.sql).

## 5. Detailed source-order tour and coverage index

This index covers the meaningful sections of this file at the inspected baseline. Line positions describe this version; follow the linked source after later changes.

| Region | Explanation |
|---|---|
| 1–7 | [users](#users) |
| 8–15 | [recovery_codes/login_attempts](#recovery_codeslogin_attempts) |
| 16–26 | [clubs/memberships](#clubsmemberships) |
| 27–38 | [tournaments/registrations](#tournamentsregistrations) |
| 39–46 | [events](#events) |
| 47–52 | [attendance](#attendance) |
| 53–64 | [resources/bookings](#resourcesbookings) |
| 65–67 | [audit_logs](#audit_logs) |

### users

Source lines 1–7: Identity, server-default student role, Argon2id password-hash storage, encrypted TOTP/replay counter.

### recovery_codes/login_attempts

Source lines 8–15: User-owned hashed single-use codes; indexed throttle bucket/time history.

### clubs/memberships

Source lines 16–26: Club capacity and many-to-many user/club association with a pending default and a unique pair.

### tournaments/registrations

Source lines 27–38: Tournament dates/status/capacity; individual user/tournament unique pair.

### events

Source lines 39–46: Club-required training/match, optional tournament, opponent/scores; club deletion cascades, tournament deletion uses SET NULL.

### attendance

Source lines 47–52: Unique user/event registration with registered/attended status; not automatically a tournament roster.

### resources/bookings

Source lines 53–64: Individual reservable unit; interval/purpose/status history; compound interval index but no SQL exclusion constraint.

### audit_logs

Source lines 65–67: A nullable actor preserves the log row on user deletion. See the complete field dictionary for precise line positions; compact source groups may shift after future changes.

## 6. Real annotated excerpt

Copied from source lines 21–26; this is baseline code, not a proposed change.

```sql
CREATE TABLE IF NOT EXISTS memberships (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NOT NULL, club_id BIGINT UNSIGNED NOT NULL,
 status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 UNIQUE(user_id,club_id), FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY(club_id) REFERENCES clubs(id) ON DELETE CASCADE
) ENGINE=InnoDB;
```

The membership ID gives an individual request identity. user_id/club_id are unsigned foreign keys rather than strings. The status default creates a pending workflow. The UNIQUE pair rejects duplicate races. ON DELETE CASCADE deletes membership when a parent is deleted; application authorization must guard parent CRUD.

## 7. Input → transformation → output

**Input:** Migration executor and selected UTF-8 database; core DDL statement order.

**Transformation:** follow section 5 in order; guards run before the important side effects, helpers supply shared database/security behavior, and failures propagate rather than silently succeeding.

**Output:** Core tables/PKs/FKs/unique/index/defaults; no sports/user rows.

## 8. Call chain and connected evidence

[bin/migrate.php](../../bin/migrate.php) · [app/models.php](../../app/models.php) · [app/actions.php](../../app/actions.php) · [database/migrations/20261017_club_communities.sql](../../database/migrations/20261017_club_communities.sql)

Use the [complete assembly inventory](../../Assemble/01-repository-assembly-map.md) to find the canonical guide for each dependency. Source links above lead to the executable file; guides explain it without creating a second runtime copy.

## 9. Why this implementation?

The project uses direct PHP services and small CLI/Python helpers so students can trace the actual request and SQL without a framework or package build. Shared helpers prevent separate validation logic for REST and native forms. Trade-off: compact source lines and hand-maintained route/schema catalogs demand disciplined review. Do not introduce a framework merely to make this guide easier.

## 10. Security, correctness and accessibility

Keep private configuration, password hashes, authenticator material, cookies and recovery codes out of terminals, screenshots and reports. The configured document root must remain `public/`; these learning folders and private service/scripts are not webroots. Database constraints protect relationships, while server authorization and business validation protect who may change rows. Neither a green CLI check nor a JSON schema certifies production security.

## 11. Common failures and safe diagnosis

Wrong engine: transactions/FK behavior unsupported; collation mismatch: review DB creation; missing business rule: the DB does not enforce future times/overlap; cascade surprise: inspect relationships first, use local disposable data.

For HTTP status meaning and deployment diagnostics use [host troubleshooting](../../Assemble/11-host-troubleshooting-and-rollback.md). Diagnose the failing layer before changing a security control.

## 12. Run or verify safely

Read DDL only; do not execute directly against a shared database.

> [!WARNING]
> `tests/fixtures.php` and all Python integration/browser runners reset **only the deliberately disposable `sportshub_test` schema**. Prove that schema is separate and disposable before execution. Never point fixtures at real records. Run suites sequentially. Do not execute commands merely because they appear in a code fence.

Actual current-task results belong in the [evidence register](../../Assemble/16-evidence-and-artifact-register.md); commands in this guide are instructions, not claims of execution.

## 13. Debugging lesson

Start at the first guard or failing assertion in section 5. Inspect process exit status, exception category, boolean outcomes, schema name and route/method. Set a local debugger breakpoint before a side effect and predict the next call. Do not dump `$_POST`, environment values, session arrays, full JSON auth responses or SQL identity rows. Use synthetic accounts in disposable local data.

## 14. Guided experiment

Read all CREATE blocks and trace one club deletion on paper. Count directly affected tables and second-level cascade effects. Do not execute DELETE in a shared DB.

Save a prediction, observed safe output and explanation in your [human evidence record](../09-completion-evidence.md). Revert only your temporary edits by comparing your recorded diff in the editor; remove only files you created in your isolated sandbox. Do not use a blanket checkout reset or a shared database deletion as cleanup.

## 15. Challenge ladder

| Level | Task | Expected evidence / hint |
|---|---|---|
| 1 — Understand | Trace one input through the excerpt to its next dependency. | A labeled call chain and predicted outcome, verified by reading source. |
| 2 — Modify | Propose a focused diagnostic or documentation improvement for this file. Implement manually only in your owned approved feature branch. | Before/after diff, a justified check, and no secret-bearing output. Preserve guards. |
| 3 — Engineer | Select a failure from section 11; explain its boundary and design a regression scenario. | Reproduction using synthetic disposable data or a non-mutating check, expected failure, reviewer discussion and honest trade-offs. |

## 16. Knowledge check

1. Does CREATE IF NOT EXISTS upgrade a column?
2. Is booking overlap prevented by INDEX alone?
3. Why use SET NULL for the audit actor?

<details><summary>Answer key — read after predicting</summary>

1. No; a new reviewed migration is needed.
2. No; a transaction, parent lock and SQL predicates enforce the rule.
3. Preserve history when the user row disappears.

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
