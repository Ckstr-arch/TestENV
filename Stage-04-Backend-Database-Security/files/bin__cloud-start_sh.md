# 🔍 `bin/cloud-start.sh` — source guide

[Stage dashboard](../README.md) · [Data dictionary](../data-dictionary.md) · [SQL and transactions](../sql-and-transactions.md) · [Verification suites](../verification-suites.md)

## 1. File card

| Field | Verified source fact |
|---|---|
| Canonical source | [bin/cloud-start.sh](../../bin/cloud-start.sh) |
| Primary learning owner | Stage 04; Member D **TBD**, no authorship assigned |
| Secondary reviewers | Stage 02 for student behavior; Stage 03 for administration; Assemble for operations |
| Language / size | bash; 42 lines at inspection |
| Inspected baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |
| Role | Restart retained services in this prepared cloud snapshot; not a portable installer. |

## 2. Why this file exists

Restart retained services in this prepared cloud snapshot; not a portable installer. This file remains part of the existing Codex-assisted baseline. Studying or receiving this guide is not evidence of an individual's implementation or testing.

## 3. Prerequisites

Understand [PHP request boundaries](../01-architecture.md), [relational keys and transactions](../sql-and-transactions.md), and [test isolation](../verification-suites.md). A command-line process is a program launched in a terminal; a web request runs under a server process with potentially different permissions and PHP configuration.

## 4. Dependencies and inputs

Bash; /workspace/toolchain signed extracted tools/data; configured .env; free localhost ports 3307/8080.

Connected source: [app/bootstrap.php](../../app/bootstrap.php) · [public/router.php](../../public/router.php) · [public/api.php](../../public/api.php).

## 5. Detailed source-order tour and coverage index

This index covers the meaningful sections of this file at the inspected baseline. Line positions describe this version; follow the linked source after later changes.

| Region | Explanation |
|---|---|
| 1–6 | [Strict shell and prepared paths](#strict-shell-and-prepared-paths) |
| 7–13 | [Database launch if absent](#database-launch-if-absent) |
| 14–23 | [Readiness and authenticated query](#readiness-and-authenticated-query) |
| 24–26 | [Web launch if absent](#web-launch-if-absent) |
| 27–42 | [Functional health checks](#functional-health-checks) |

### Strict shell and prepared paths

Source lines 1–6: set -euo pipefail rejects failed/unset operations. Fixed workspace and local library paths are environment-specific; create ignored var/log.

### Database launch if absent

Source lines 7–13: Socket probe on 3307; only if absent start retained mariadbd with explicit directories, loopback bind and private log path.

### Readiness and authenticated query

Source lines 14–23: Bounded Python retry checks connection, then PHP SELECT 1 proves DB credentials work, not merely an open socket.

### Web launch if absent

Source lines 24–26: Start PHP dev router on 8080; existing process reused. Binding 0.0.0.0 is environment-specific, not a production recommendation.

### Functional health checks

Source lines 27–42: Retry clubs HTML contains expected text/no Warning/CSP; JSON health status ok. Readiness checks actual app behavior, not PID only.

## 6. Real annotated excerpt

Copied from source lines 24–26; this is baseline code, not a proposed change.

```bash
  nohup php -S 0.0.0.0:8080 -t public public/router.php >var/log/web.log 2>&1 &
fi
python - <<'PY'
```

A socket probe detects an occupied port; it does not identify the process. nohup leaves server running after shell exit and writes logs to ignored var/log. -t public sets safe root; router.php enables REST routing.

## 7. Input → transformation → output

**Input:** Bash; /workspace/toolchain signed extracted tools/data; configured .env; free localhost ports 3307/8080.

**Transformation:** follow section 5 in order; guards run before the important side effects, helpers supply shared database/security behavior, and failures propagate rather than silently succeeding.

**Output:** Retained MariaDB/PHP dev server plus functional DB/HTML/JSON readiness.

## 8. Call chain and connected evidence

[app/bootstrap.php](../../app/bootstrap.php) · [public/router.php](../../public/router.php) · [public/api.php](../../public/api.php)

Use the [complete assembly inventory](../../Assemble/01-repository-assembly-map.md) to find the canonical guide for each dependency. Source links above lead to the executable file; guides explain it without creating a second runtime copy.

## 9. Why this implementation?

The project uses direct PHP services and small CLI/Python helpers so students can trace the actual request and SQL without a framework or package build. Shared helpers prevent separate validation logic for REST and native forms. Trade-off: compact source lines and hand-maintained route/schema catalogs demand disciplined review. Do not introduce a framework merely to make this guide easier.

## 10. Security, correctness and accessibility

Keep private configuration, password hashes, authenticator material, cookies and recovery codes out of terminals, screenshots and reports. The configured document root must remain `public/`; these learning folders and private service/scripts are not webroots. Database constraints protect relationships, while server authorization and business validation protect who may change rows. Neither a green CLI check nor a JSON schema certifies production security.

## 11. Common failures and safe diagnosis

Wrong process on the port: inspect process identity, do not kill unrelated service; missing toolchain: use portable setup; DB SELECT 1 fails: private configuration/grants; readiness fails: inspect protected logs rather than public traces.

For HTTP status meaning and deployment diagnostics use [host troubleshooting](../../Assemble/11-host-troubleshooting-and-rollback.md). Diagnose the failing layer before changing a security control.

## 12. Run or verify safely

```bash
# From repository root. Read the safety classification before executing.
bash -n bin/cloud-start.sh
```

> [!WARNING]
> `tests/fixtures.php` and all Python integration/browser runners reset **only the deliberately disposable `sportshub_test` schema**. Prove that schema is separate and disposable before execution. Never point fixtures at real records. Run suites sequentially. Do not execute commands merely because they appear in a code fence.

Actual current-task results belong in the [evidence register](../../Assemble/16-evidence-and-artifact-register.md); commands in this guide are instructions, not claims of execution.

## 13. Debugging lesson

Start at the first guard or failing assertion in section 5. Inspect process exit status, exception category, boolean outcomes, schema name and route/method. Set a local debugger breakpoint before a side effect and predict the next call. Do not dump `$_POST`, environment values, session arrays, full JSON auth responses or SQL identity rows. Use synthetic accounts in disposable local data.

## 14. Guided experiment

Run bash -n to inspect syntax without starting any service. Explain why port-open alone is insufficient and name the later assertions that reject a wrong service.

Save a prediction, observed safe output and explanation in your [human evidence record](../09-completion-evidence.md). Revert only your temporary edits by comparing your recorded diff in the editor; remove only files you created in your isolated sandbox. Do not use a blanket checkout reset or a shared database deletion as cleanup.

## 15. Challenge ladder

| Level | Task | Expected evidence / hint |
|---|---|---|
| 1 — Understand | Trace one input through the excerpt to its next dependency. | A labeled call chain and predicted outcome, verified by reading source. |
| 2 — Modify | Propose a focused diagnostic or documentation improvement for this file. Implement manually only in your owned approved feature branch. | Before/after diff, a justified check, and no secret-bearing output. Preserve guards. |
| 3 — Engineer | Select a failure from section 11; explain its boundary and design a regression scenario. | Reproduction using synthetic disposable data or a non-mutating check, expected failure, reviewer discussion and honest trade-offs. |

## 16. Knowledge check

1. Can this replace macOS installation instructions?
2. Why check SELECT 1 after TCP?
3. Does localhost readiness prove public deployment?

<details><summary>Answer key — read after predicting</summary>

1. No; it hardcodes this retained cloud toolchain.
2. A port can be open while auth/schema/service is wrong.
3. No; localhost is only local readiness.

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
