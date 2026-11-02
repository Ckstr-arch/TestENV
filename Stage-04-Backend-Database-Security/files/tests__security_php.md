# 🔍 `tests/security.php` — source guide

[Stage dashboard](../README.md) · [Data dictionary](../data-dictionary.md) · [SQL and transactions](../sql-and-transactions.md) · [Verification suites](../verification-suites.md)

## 1. File card

| Field | Verified source fact |
|---|---|
| Canonical source | [tests/security.php](../../tests/security.php) |
| Primary learning owner | Stage 04; Member D **TBD**, no authorship assigned |
| Secondary reviewers | Stage 02 for student behavior; Stage 03 for administration; Assemble for operations |
| Language / size | php; 22 lines at inspection |
| Inspected baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |
| Role | Run fast cryptography, password and escaping assertions without resetting SQL data. |

## 2. Why this file exists

Run fast cryptography, password and escaping assertions without resetting SQL data. This file remains part of the existing Codex-assisted baseline. Studying or receiving this guide is not evidence of an individual's implementation or testing.

## 3. Prerequisites

Understand [PHP request boundaries](../01-architecture.md), [relational keys and transactions](../sql-and-transactions.md), and [test isolation](../verification-suites.md). A command-line process is a program launched in a terminal; a web request runs under a server process with potentially different permissions and PHP configuration.

## 4. Dependencies and inputs

PHP bootstrap/security; valid APP_KEY; no HTTP server or DB fixture required.

Connected source: [app/security.php](../../app/security.php) · [app/bootstrap.php](../../app/bootstrap.php) · [tests/api.py](../../tests/api.py).

## 5. Detailed source-order tour and coverage index

This index covers the meaningful sections of this file at the inspected baseline. Line positions describe this version; follow the linked source after later changes.

| Region | Explanation |
|---|---|
| 1–5 | [Boot and check function](#boot-and-check-function) |
| 6–7 | [Published RFC 6238 vectors](#published-rfc-6238-vectors) |
| 8–14 | [Random-key TOTP checks](#random-key-totp-checks) |
| 15–18 | [Encryption integrity](#encryption-integrity) |
| 19–22 | [Password/HTML guards](#passwordhtml-guards) |

### Boot and check function

Source lines 1–5: Counter increments only when assertion succeeds; false throws RuntimeException.

### Published RFC 6238 vectors

Source lines 6–7: Known SHA-1 vectors cover six timestamps with 8 digits, unlike normal 6-digit application codes.

### Random-key TOTP checks

Source lines 8–14: 160-bit base32 length, current step, replay rejection, malformed input, ±1-step acceptance/excess drift.

### Encryption integrity

Source lines 15–18: Roundtrip hides plaintext; bit flip must throw. Catch RuntimeException records rejected tampering.

### Password/HTML guards

Source lines 19–22: Argon2id compares strings beyond 72 bytes to guard bcrypt truncation; e escapes markup. The final count is reached only after every assertion passes.

## 6. Real annotated excerpt

Copied from source lines 8–13; this is baseline code, not a proposed change.

```php
$key=base32_encode(random_bytes(20)); check(strlen($key)===32,'160-bit authenticator key');
$now=intdiv(time(),30); $code=totp($key,$now);
check(totp_counter($key,$code)===$now,'Current TOTP accepted');
check(totp_counter($key,$code,$now)===null,'Replay rejected');
check(totp_counter($key,'abc123')===null,'Malformed code rejected');
check(totp_counter($key,totp($key,$now-1))===$now-1,'One-step drift accepted');
```

random_bytes creates a fresh test secret; no real account. intdiv(time(), 30) is moving counter; passing already-used counter returns null. Malformed text must fail before HMAC code comparison. On rare time boundary flakes rerun with timing diagnosis, never widen replay policy.

## 7. Input → transformation → output

**Input:** PHP bootstrap/security; valid APP_KEY; no HTTP server or DB fixture required.

**Transformation:** follow section 5 in order; guards run before the important side effects, helpers supply shared database/security behavior, and failures propagate rather than silently succeeding.

**Output:** Assertion exceptions on failure or executed check count.

## 8. Call chain and connected evidence

[app/security.php](../../app/security.php) · [app/bootstrap.php](../../app/bootstrap.php) · [tests/api.py](../../tests/api.py)

Use the [complete assembly inventory](../../Assemble/01-repository-assembly-map.md) to find the canonical guide for each dependency. Source links above lead to the executable file; guides explain it without creating a second runtime copy.

## 9. Why this implementation?

The project uses direct PHP services and small CLI/Python helpers so students can trace the actual request and SQL without a framework or package build. Shared helpers prevent separate validation logic for REST and native forms. Trade-off: compact source lines and hand-maintained route/schema catalogs demand disciplined review. Do not introduce a framework merely to make this guide easier.

## 10. Security, correctness and accessibility

Keep private configuration, password hashes, authenticator material, cookies and recovery codes out of terminals, screenshots and reports. The configured document root must remain `public/`; these learning folders and private service/scripts are not webroots. Database constraints protect relationships, while server authorization and business validation protect who may change rows. Neither a green CLI check nor a JSON schema certifies production security.

## 11. Common failures and safe diagnosis

Wrong PHP module: doctor; key invalid: private configuration; timing boundary: observe time step without logging codes; tampering roundtrip fails: investigate the algorithm/config, never weaken assertion.

For HTTP status meaning and deployment diagnostics use [host troubleshooting](../../Assemble/11-host-troubleshooting-and-rollback.md). Diagnose the failing layer before changing a security control.

## 12. Run or verify safely

```bash
# From repository root. Read the safety classification before executing.
php tests/security.php
```

> [!WARNING]
> `tests/fixtures.php` and all Python integration/browser runners reset **only the deliberately disposable `sportshub_test` schema**. Prove that schema is separate and disposable before execution. Never point fixtures at real records. Run suites sequentially. Do not execute commands merely because they appear in a code fence.

Actual current-task results belong in the [evidence register](../../Assemble/16-evidence-and-artifact-register.md); commands in this guide are instructions, not claims of execution.

## 13. Debugging lesson

Start at the first guard or failing assertion in section 5. Inspect process exit status, exception category, boolean outcomes, schema name and route/method. Set a local debugger breakpoint before a side effect and predict the next call. Do not dump `$_POST`, environment values, session arrays, full JSON auth responses or SQL identity rows. Use synthetic accounts in disposable local data.

## 14. Guided experiment

Predict why two passwords identical for 72 bytes but different afterward should differ. Run this non-DB suite; retain summary only. No source edits or DB rollback needed.

Save a prediction, observed safe output and explanation in your [human evidence record](../09-completion-evidence.md). Revert only your temporary edits by comparing your recorded diff in the editor; remove only files you created in your isolated sandbox. Do not use a blanket checkout reset or a shared database deletion as cleanup.

## 15. Challenge ladder

| Level | Task | Expected evidence / hint |
|---|---|---|
| 1 — Understand | Trace one input through the excerpt to its next dependency. | A labeled call chain and predicted outcome, verified by reading source. |
| 2 — Modify | Propose a focused diagnostic or documentation improvement for this file. Implement manually only in your owned approved feature branch. | Before/after diff, a justified check, and no secret-bearing output. Preserve guards. |
| 3 — Engineer | Select a failure from section 11; explain its boundary and design a regression scenario. | Reproduction using synthetic disposable data or a non-mutating check, expected failure, reviewer discussion and honest trade-offs. |

## 16. Knowledge check

1. Are RFC test vectors live credentials?
2. Does this exercise HTTP cookies?
3. Why test long password suffixes?

<details><summary>Answer key — read after predicting</summary>

1. No; published reproducible test material.
2. No; integration/API/browser suites cover those.
3. To detect unintended truncation such as bcrypt’s 72-byte behavior.

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
