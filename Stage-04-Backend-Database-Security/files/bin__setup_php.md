# 🔍 `bin/setup.php` — source guide

[Stage dashboard](../README.md) · [Data dictionary](../data-dictionary.md) · [SQL and transactions](../sql-and-transactions.md) · [Verification suites](../verification-suites.md)

## 1. File card

| Field | Verified source fact |
|---|---|
| Canonical source | [bin/setup.php](../../bin/setup.php) |
| Primary learning owner | Stage 04; Member D **TBD**, no authorship assigned |
| Secondary reviewers | Stage 02 for student behavior; Stage 03 for administration; Assemble for operations |
| Language / size | php; 12 lines at inspection |
| Inspected baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |
| Role | Create a private local configuration once; absence prevents predictable DB/key setup. |

## 2. Why this file exists

Create a private local configuration once; absence prevents predictable DB/key setup. This file remains part of the existing Codex-assisted baseline. Studying or receiving this guide is not evidence of an individual's implementation or testing.

## 3. Prerequisites

Understand [PHP request boundaries](../01-architecture.md), [relational keys and transactions](../sql-and-transactions.md), and [test isolation](../verification-suites.md). A command-line process is a program launched in a terminal; a web request runs under a server process with potentially different permissions and PHP configuration.

## 4. Dependencies and inputs

CLI invocation; tracked .env.example; filesystem existence/permissions.

Connected source: [bin/migrate.php](../../bin/migrate.php) · [app/bootstrap.php](../../app/bootstrap.php) · [.env.example](../../.env.example).

## 5. Detailed source-order tour and coverage index

This index covers the meaningful sections of this file at the inspected baseline. Line positions describe this version; follow the linked source after later changes.

| Region | Explanation |
|---|---|
| 1–4 | [Strict CLI boundary](#strict-cli-boundary) |
| 5–6 | [Existing-file guard](#existing-file-guard) |
| 7–12 | [Generate, substitute, write](#generate-substitute-write) |

### Strict CLI boundary

Source lines 1–4: PHP strict typing follows the opening tag; PHP_SAPI prevents ordinary HTTP use. Files still belong outside public/.

### Existing-file guard

Source lines 5–6: Resolve repository root through __DIR__. An existing .env causes immediate exit before key generation or writes.

### Generate, substitute, write

Source lines 7–12: 32 random bytes become base64; multiline regular expression replaces only the APP_KEY line. LOCK_EX protects the write and mode 0600 requests owner-only access. Windows needs ACL review.

## 6. Real annotated excerpt

Copied from source lines 5–11; this is baseline code, not a proposed change.

```php
$root=dirname(__DIR__); $file=$root.'/.env';
if (is_file($file)) { echo ".env already exists; preserved unchanged.\n"; exit; }
$key=base64_encode(random_bytes(32));
$data=file_get_contents($root.'/.env.example');
$data=preg_replace('/^APP_KEY=.*$/m','APP_KEY='.$key,$data);
if (file_put_contents($file,$data,LOCK_EX)===false) throw new RuntimeException('Could not create .env');
chmod($file,0600);
```

$root locates the repository independent of shell directory. is_file is the preservation gate. random_bytes gives cryptographic entropy, not a human password. preg_replace uses ^ and $ per line because of m. file_put_contents failure throws instead of pretending setup succeeded. chmod applies after writing.

## 7. Input → transformation → output

**Input:** CLI invocation; tracked .env.example; filesystem existence/permissions.

**Transformation:** follow section 5 in order; guards run before the important side effects, helpers supply shared database/security behavior, and failures propagate rather than silently succeeding.

**Output:** A new private .env with random APP_KEY, or preservation message.

## 8. Call chain and connected evidence

[bin/migrate.php](../../bin/migrate.php) · [app/bootstrap.php](../../app/bootstrap.php) · [.env.example](../../.env.example)

Use the [complete assembly inventory](../../Assemble/01-repository-assembly-map.md) to find the canonical guide for each dependency. Source links above lead to the executable file; guides explain it without creating a second runtime copy.

## 9. Why this implementation?

The project uses direct PHP services and small CLI/Python helpers so students can trace the actual request and SQL without a framework or package build. Shared helpers prevent separate validation logic for REST and native forms. Trade-off: compact source lines and hand-maintained route/schema catalogs demand disciplined review. Do not introduce a framework merely to make this guide easier.

## 10. Security, correctness and accessibility

Keep private configuration, password hashes, authenticator material, cookies and recovery codes out of terminals, screenshots and reports. The configured document root must remain `public/`; these learning folders and private service/scripts are not webroots. Database constraints protect relationships, while server authorization and business validation protect who may change rows. Neither a green CLI check nor a JSON schema certifies production security.

## 11. Common failures and safe diagnosis

Existing file: expected preservation, edit privately; cannot create: check directory ownership; unchanged key placeholder: inspect template structure without printing its resulting value.

For HTTP status meaning and deployment diagnostics use [host troubleshooting](../../Assemble/11-host-troubleshooting-and-rollback.md). Diagnose the failing layer before changing a security control.

## 12. Run or verify safely

```bash
# From repository root. Read the safety classification before executing.
php -l bin/setup.php
```

> [!WARNING]
> `tests/fixtures.php` and all Python integration/browser runners reset **only the deliberately disposable `sportshub_test` schema**. Prove that schema is separate and disposable before execution. Never point fixtures at real records. Run suites sequentially. Do not execute commands merely because they appear in a code fence.

Actual current-task results belong in the [evidence register](../../Assemble/16-evidence-and-artifact-register.md); commands in this guide are instructions, not claims of execution.

## 13. Debugging lesson

Start at the first guard or failing assertion in section 5. Inspect process exit status, exception category, boolean outcomes, schema name and route/method. Set a local debugger breakpoint before a side effect and predict the next call. Do not dump `$_POST`, environment values, session arrays, full JSON auth responses or SQL identity rows. Use synthetic accounts in disposable local data.

## 14. Guided experiment

In an isolated temporary directory, copy only this script and .env.example preserving their bin/root relationship. Run twice, compare only file digest equality and preservation message; delete your temporary directory afterward.

Save a prediction, observed safe output and explanation in your [human evidence record](../09-completion-evidence.md). Revert only your temporary edits by comparing your recorded diff in the editor; remove only files you created in your isolated sandbox. Do not use a blanket checkout reset or a shared database deletion as cleanup.

## 15. Challenge ladder

| Level | Task | Expected evidence / hint |
|---|---|---|
| 1 — Understand | Trace one input through the excerpt to its next dependency. | A labeled call chain and predicted outcome, verified by reading source. |
| 2 — Modify | Propose a focused diagnostic or documentation improvement for this file. Implement manually only in your owned approved feature branch. | Before/after diff, a justified check, and no secret-bearing output. Preserve guards. |
| 3 — Engineer | Select a failure from section 11; explain its boundary and design a regression scenario. | Reproduction using synthetic disposable data or a non-mutating check, expected failure, reviewer discussion and honest trade-offs. |

## 16. Knowledge check

1. Why must existing configuration win?
2. Does LOCK_EX make this a general multi-process provisioning service?
3. Why is base64 not encryption?

<details><summary>Answer key — read after predicting</summary>

1. Existing keys decrypt enrolled TOTP secrets; overwriting breaks access.
2. No. There is still a check/create race; this is a local CLI helper, not concurrent provisioning.
3. It encodes random bytes for storage; the entropy is from random_bytes.

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
