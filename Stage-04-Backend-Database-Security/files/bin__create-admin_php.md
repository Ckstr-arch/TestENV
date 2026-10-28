# 🔍 `bin/create-admin.php` — source guide

[Stage dashboard](../README.md) · [Data dictionary](../data-dictionary.md) · [SQL and transactions](../sql-and-transactions.md) · [Verification suites](../verification-suites.md)

## 1. File card

| Field | Verified source fact |
|---|---|
| Canonical source | [bin/create-admin.php](../../bin/create-admin.php) |
| Primary learning owner | Stage 04; Member D **TBD**, no authorship assigned |
| Secondary reviewers | Stage 02 for student behavior; Stage 03 for administration; Assemble for operations |
| Language / size | php; 9 lines at inspection |
| Inspected baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |
| Role | Provision an administrator through a private server-side CLI boundary. |

## 2. Why this file exists

Provision an administrator through a private server-side CLI boundary. This file remains part of the existing Codex-assisted baseline. Studying or receiving this guide is not evidence of an individual's implementation or testing.

## 3. Prerequisites

Understand [PHP request boundaries](../01-architecture.md), [relational keys and transactions](../sql-and-transactions.md), and [test isolation](../verification-suites.md). A command-line process is a program launched in a terminal; a web request runs under a server process with potentially different permissions and PHP configuration.

## 4. Dependencies and inputs

CLI; ADMIN_EMAIL/ADMIN_PASSWORD and optional ADMIN_NAME process values; migrated users table.

Connected source: [app/actions.php](../../app/actions.php) · [database/schema.sql](../../database/schema.sql) · [bin/doctor.php](../../bin/doctor.php).

## 5. Detailed source-order tour and coverage index

This index covers the meaningful sections of this file at the inspected baseline. Line positions describe this version; follow the linked source after later changes.

| Region | Explanation |
|---|---|
| 1–4 | [Bootstrap and CLI gate](#bootstrap-and-cli-gate) |
| 5–6 | [Private inputs and validation](#private-inputs-and-validation) |
| 7–9 | [Preserve existing identity and insert](#preserve-existing-identity-and-insert) |

### Bootstrap and CLI gate

Source lines 1–4: Load shared config/security; refuse HTTP invocation.

### Private inputs and validation

Source lines 5–6: Email is lowercased, name defaults to a generic role label. Require valid email and a 12–128 byte password; report field names only, never values.

### Preserve existing identity and insert

Source lines 7–9: Existing email stops safely. Prepared INSERT explicitly sets role admin; password_hash uses Argon2id. New admin is prompted to enroll 2FA through account security.

## 6. Real annotated excerpt

Copied from source lines 6–9; this is baseline code, not a proposed change.

```php
if (!filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($password)<12 || strlen($password)>128) { fwrite(STDERR,"Set ADMIN_EMAIL and ADMIN_PASSWORD (12–128 characters) in the process environment.\n"); exit(1); }
if (one('SELECT id FROM users WHERE email=?',[$email])) { fwrite(STDERR,"Account already exists; no changes made.\n"); exit(1); }
query('INSERT INTO users(name,email,password_hash,role) VALUES(?,?,?,"admin")',[$name,$email,password_hash($password,PASSWORD_ARGON2ID)]);
echo "Admin created. Sign in and enroll 2FA in Account security.\n";
```

FILTER_VALIDATE_EMAIL checks format, not inbox ownership. strlen here measures bytes. The existence test prevents overwriting an account. VALUES placeholders bind runtime values. role is server-set rather than a browser field. password_hash generates its own salt.

## 7. Input → transformation → output

**Input:** CLI; ADMIN_EMAIL/ADMIN_PASSWORD and optional ADMIN_NAME process values; migrated users table.

**Transformation:** follow section 5 in order; guards run before the important side effects, helpers supply shared database/security behavior, and failures propagate rather than silently succeeding.

**Output:** One Argon2id-hashed admin, or refusal without altering an existing account.

## 8. Call chain and connected evidence

[app/actions.php](../../app/actions.php) · [database/schema.sql](../../database/schema.sql) · [bin/doctor.php](../../bin/doctor.php)

Use the [complete assembly inventory](../../Assemble/01-repository-assembly-map.md) to find the canonical guide for each dependency. Source links above lead to the executable file; guides explain it without creating a second runtime copy.

## 9. Why this implementation?

The project uses direct PHP services and small CLI/Python helpers so students can trace the actual request and SQL without a framework or package build. Shared helpers prevent separate validation logic for REST and native forms. Trade-off: compact source lines and hand-maintained route/schema catalogs demand disciplined review. Do not introduce a framework merely to make this guide easier.

## 10. Security, correctness and accessibility

Keep private configuration, password hashes, authenticator material, cookies and recovery codes out of terminals, screenshots and reports. The configured document root must remain `public/`; these learning folders and private service/scripts are not webroots. Database constraints protect relationships, while server authorization and business validation protect who may change rows. Neither a green CLI check nor a JSON schema certifies production security.

## 11. Common failures and safe diagnosis

Missing inputs: supply private process environment; duplicate email: investigate existing account, no overwrite; unsupported Argon2id: select compatible PHP; no table: migrate intended DB.

For HTTP status meaning and deployment diagnostics use [host troubleshooting](../../Assemble/11-host-troubleshooting-and-rollback.md). Diagnose the failing layer before changing a security control.

## 12. Run or verify safely

```bash
# From repository root. Read the safety classification before executing.
php -l bin/create-admin.php
```

> [!WARNING]
> `tests/fixtures.php` and all Python integration/browser runners reset **only the deliberately disposable `sportshub_test` schema**. Prove that schema is separate and disposable before execution. Never point fixtures at real records. Run suites sequentially. Do not execute commands merely because they appear in a code fence.

Actual current-task results belong in the [evidence register](../../Assemble/16-evidence-and-artifact-register.md); commands in this guide are instructions, not claims of execution.

## 13. Debugging lesson

Start at the first guard or failing assertion in section 5. Inspect process exit status, exception category, boolean outcomes, schema name and route/method. Set a local debugger breakpoint before a side effect and predict the next call. Do not dump `$_POST`, environment values, session arrays, full JSON auth responses or SQL identity rows. Use synthetic accounts in disposable local data.

## 14. Guided experiment

Trace this helper and public registration side by side: list every route by which role can become admin. Save a written trust-boundary explanation, not a real password or admin screenshot.

Save a prediction, observed safe output and explanation in your [human evidence record](../09-completion-evidence.md). Revert only your temporary edits by comparing your recorded diff in the editor; remove only files you created in your isolated sandbox. Do not use a blanket checkout reset or a shared database deletion as cleanup.

## 15. Challenge ladder

| Level | Task | Expected evidence / hint |
|---|---|---|
| 1 — Understand | Trace one input through the excerpt to its next dependency. | A labeled call chain and predicted outcome, verified by reading source. |
| 2 — Modify | Propose a focused diagnostic or documentation improvement for this file. Implement manually only in your owned approved feature branch. | Before/after diff, a justified check, and no secret-bearing output. Preserve guards. |
| 3 — Engineer | Select a failure from section 11; explain its boundary and design a regression scenario. | Reproduction using synthetic disposable data or a non-mutating check, expected failure, reviewer discussion and honest trade-offs. |

## 16. Knowledge check

1. Why not expose this script as setup.php over HTTP?
2. Can this reset an existing password?
3. Why does public registration omit role?

<details><summary>Answer key — read after predicting</summary>

1. It is privileged provisioning and belongs outside document root.
2. No; duplicate identity is refused.
3. DB default student plus server allowlist prevents role mass assignment.

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
