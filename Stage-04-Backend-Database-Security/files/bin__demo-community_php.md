# 🔍 `bin/demo-community.php` — source guide

[Stage dashboard](../README.md) · [Data dictionary](../data-dictionary.md) · [SQL and transactions](../sql-and-transactions.md) · [Verification suites](../verification-suites.md)

## 1. File card

| Field | Verified source fact |
|---|---|
| Canonical source | [bin/demo-community.php](../../bin/demo-community.php) |
| Primary learning owner | Stage 04; Member D **TBD**, no authorship assigned |
| Secondary reviewers | Stage 02 for student behavior; Stage 03 for administration; Assemble for operations |
| Language / size | php; 18 lines at inspection |
| Inspected baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |
| Role | Add welcome discussions to known demo clubs without overwriting conversations. |

## 2. Why this file exists

Add welcome discussions to known demo clubs without overwriting conversations. This file remains part of the existing Codex-assisted baseline. Studying or receiving this guide is not evidence of an individual's implementation or testing.

## 3. Prerequisites

Understand [PHP request boundaries](../01-architecture.md), [relational keys and transactions](../sql-and-transactions.md), and [test isolation](../verification-suites.md). A command-line process is a program launched in a terminal; a web request runs under a server process with potentially different permissions and PHP configuration.

## 4. Dependencies and inputs

CLI; development plus the explicit flag; existing demo admin/athlete and approved membership.

Connected source: [bin/demo-users.php](../../bin/demo-users.php) · [app/community.php](../../app/community.php) · [database/migrations/20261017_club_communities.sql](../../database/migrations/20261017_club_communities.sql).

## 5. Detailed source-order tour and coverage index

This index covers the meaningful sections of this file at the inspected baseline. Line positions describe this version; follow the linked source after later changes.

| Region | Explanation |
|---|---|
| 1–6 | [Explicit safety gate and admin lookup](#explicit-safety-gate-and-admin-lookup) |
| 7–11 | [Club welcome loop](#club-welcome-loop) |
| 12–17 | [Athlete conversation starter](#athlete-conversation-starter) |
| 18–18 | [Completion message](#completion-message) |

### Explicit safety gate and admin lookup

Source lines 1–6: Exact development and flag required. A known demo administrator must already exist; otherwise return.

### Club welcome loop

Source lines 7–11: Only four known demo club names are considered. Missing clubs skip. Any existing post by that admin in that club skips, including hidden/deleted rows.

### Athlete conversation starter

Source lines 12–17: Find first approved athlete membership; insert only when that author has no post in the club.

### Completion message

Source lines 18–18: No global reset or updates; this helper is intentionally narrow.

## 6. Real annotated excerpt

Copied from source lines 7–10; this is baseline code, not a proposed change.

```php
if (!$admin) { echo "No demo administrator found; community seed skipped.\n"; return; }
foreach (['Green Warriors Cricket','NSBM Football Club','Campus Basketball','Athletics Collective'] as $name) {
 $club=one('SELECT id FROM clubs WHERE name=?',[$name]);
 if (!$club || one('SELECT id FROM club_posts WHERE club_id=? AND user_id=?',[$club['id'],$admin['id']])) continue;
```

The foreach iterates a fixed synthetic list. The name parameter remains bound. The existing-post query deliberately has no status filter, preventing deleted conversations from reappearing. INSERT binds body text; rendering later escapes it.

## 7. Input → transformation → output

**Input:** CLI; development plus the explicit flag; existing demo admin/athlete and approved membership.

**Transformation:** follow section 5 in order; guards run before the important side effects, helpers supply shared database/security behavior, and failures propagate rather than silently succeeding.

**Output:** At most one welcome post per demo author/club; existing/tombstoned authored posts block reseeding.

## 8. Call chain and connected evidence

[bin/demo-users.php](../../bin/demo-users.php) · [app/community.php](../../app/community.php) · [database/migrations/20261017_club_communities.sql](../../database/migrations/20261017_club_communities.sql)

Use the [complete assembly inventory](../../Assemble/01-repository-assembly-map.md) to find the canonical guide for each dependency. Source links above lead to the executable file; guides explain it without creating a second runtime copy.

## 9. Why this implementation?

The project uses direct PHP services and small CLI/Python helpers so students can trace the actual request and SQL without a framework or package build. Shared helpers prevent separate validation logic for REST and native forms. Trade-off: compact source lines and hand-maintained route/schema catalogs demand disciplined review. Do not introduce a framework merely to make this guide easier.

## 10. Security, correctness and accessibility

Keep private configuration, password hashes, authenticator material, cookies and recovery codes out of terminals, screenshots and reports. The configured document root must remain `public/`; these learning folders and private service/scripts are not webroots. Database constraints protect relationships, while server authorization and business validation protect who may change rows. Neither a green CLI check nor a JSON schema certifies production security.

## 11. Common failures and safe diagnosis

Nothing created: demo admin/clubs absent or posts already exist; user gate blocks feed: check approved membership; production refusal: expected; disappearing old welcome: moderation state persists.

For HTTP status meaning and deployment diagnostics use [host troubleshooting](../../Assemble/11-host-troubleshooting-and-rollback.md). Diagnose the failing layer before changing a security control.

## 12. Run or verify safely

```bash
# From repository root. Read the safety classification before executing.
php -l bin/demo-community.php
```

> [!WARNING]
> `tests/fixtures.php` and all Python integration/browser runners reset **only the deliberately disposable `sportshub_test` schema**. Prove that schema is separate and disposable before execution. Never point fixtures at real records. Run suites sequentially. Do not execute commands merely because they appear in a code fence.

Actual current-task results belong in the [evidence register](../../Assemble/16-evidence-and-artifact-register.md); commands in this guide are instructions, not claims of execution.

## 13. Debugging lesson

Start at the first guard or failing assertion in section 5. Inspect process exit status, exception category, boolean outcomes, schema name and route/method. Set a local debugger breakpoint before a side effect and predict the next call. Do not dump `$_POST`, environment values, session arrays, full JSON auth responses or SQL identity rows. Use synthetic accounts in disposable local data.

## 14. Guided experiment

On disposable demo data, predict whether hiding or deleting the welcome post allows reseeding. Inspect predicate and verify no recreated post; preserve the tombstone.

Save a prediction, observed safe output and explanation in your [human evidence record](../09-completion-evidence.md). Revert only your temporary edits by comparing your recorded diff in the editor; remove only files you created in your isolated sandbox. Do not use a blanket checkout reset or a shared database deletion as cleanup.

## 15. Challenge ladder

| Level | Task | Expected evidence / hint |
|---|---|---|
| 1 — Understand | Trace one input through the excerpt to its next dependency. | A labeled call chain and predicted outcome, verified by reading source. |
| 2 — Modify | Propose a focused diagnostic or documentation improvement for this file. Implement manually only in your owned approved feature branch. | Before/after diff, a justified check, and no secret-bearing output. Preserve guards. |
| 3 — Engineer | Select a failure from section 11; explain its boundary and design a regression scenario. | Reproduction using synthetic disposable data or a non-mutating check, expected failure, reviewer discussion and honest trade-offs. |

## 16. Knowledge check

1. Why does status not appear in the skip query?
2. Does this grant membership?
3. Is it the production onboarding workflow?

<details><summary>Answer key — read after predicting</summary>

1. All authored rows, including tombstones, count as prior creation.
2. No; it reads existing approved membership only.
3. No; it is explicit disposable demonstration data.

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
