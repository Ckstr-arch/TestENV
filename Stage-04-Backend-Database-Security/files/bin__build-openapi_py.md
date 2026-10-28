# 🔍 `bin/build-openapi.py` — source guide

[Stage dashboard](../README.md) · [Data dictionary](../data-dictionary.md) · [SQL and transactions](../sql-and-transactions.md) · [Verification suites](../verification-suites.md)

## 1. File card

| Field | Verified source fact |
|---|---|
| Canonical source | [bin/build-openapi.py](../../bin/build-openapi.py) |
| Primary learning owner | Stage 04; Member D **TBD**, no authorship assigned |
| Secondary reviewers | Stage 02 for student behavior; Stage 03 for administration; Assemble for operations |
| Language / size | python; 93 lines at inspection |
| Inspected baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |
| Role | Generate checked-in client-readable REST contract from compact schema/route declarations. |

## 2. Why this file exists

Generate checked-in client-readable REST contract from compact schema/route declarations. This file remains part of the existing Codex-assisted baseline. Studying or receiving this guide is not evidence of an individual's implementation or testing.

## 3. Prerequisites

Understand [PHP request boundaries](../01-architecture.md), [relational keys and transactions](../sql-and-transactions.md), and [test isolation](../verification-suites.md). A command-line process is a program launched in a terminal; a web request runs under a server process with potentially different permissions and PHP configuration.

## 4. Dependencies and inputs

Python 3 stdlib; schemas dict; add() route declarations.

Connected source: [app/api.php](../../app/api.php) · [public/api.php](../../public/api.php) · [public/assets/openapi.json](../../public/assets/openapi.json) · [tests/api.py](../../tests/api.py).

## 5. Detailed source-order tour and coverage index

This index covers the meaningful sections of this file at the inspected baseline. Line positions describe this version; follow the linked source after later changes.

| Region | Explanation |
|---|---|
| 1–21 | [Roots and request schemas](#roots-and-request-schemas) |
| 22–29 | [Components and base specification](#components-and-base-specification) |
| 30–44 | [add function](#add-function) |
| 45–53 | [Authentication and profile routes](#authentication-and-profile-routes) |
| 54–75 | [Core resources/domain operations](#core-resourcesdomain-operations) |
| 76–91 | [Community/moderation routes](#communitymoderation-routes) |
| 92–93 | [Serialization](#serialization) |

### Roots and request schemas

Source lines 1–21: Pathlib resolves root. Each schema property states type/length/enum; these describe contracts, actual PHP still authorizes and validates.

### Components and base specification

Source lines 22–29: Build required fields, event optional exceptions, additionalProperties: false, local datetime, envelopes and cookie security. Relative server /api/v1 follows deployment origin.

### add function

Source lines 30–44: Generate operationId, public security override, numeric path params, required mutation CSRF header, pagination/filters, request body and standard error responses.

### Authentication and profile routes

Source lines 45–53: Session bootstrap, register/login/second factor, logout and security enrollment/disable.

### Core resources/domain operations

Source lines 54–75: Loops enumerate public/admin CRUD, membership/tournament/session registration, bookings, cancellations, reviews/reports/results.

### Community/moderation routes

Source lines 76–91: Approved-member feed/comments/reactions/reports and admin decisions. Access policy remains server responsibility.

### Serialization

Source lines 92–93: json.dumps indent 2 newline; overwrite generated artifact and print count.

## 6. Real annotated excerpt

Copied from source lines 30–36; this is baseline code, not a proposed change.

```python
def add(path,method,summary,body=None,public=False,paginated=False,filters=(),created=False):
 op={'summary':summary,'operationId':method+'_'+path.strip('/').replace('/','_').replace('{','').replace('}','').replace('-','_'),'responses':{str(201 if created else 200):{'description':'Success','content':{'application/json':{'schema':{'$ref':'#/components/schemas/Response'}}}}}}
 if public:op['security']=[]
 params=[]
 import re
 for name in re.findall(r'\{([^}]+)\}',path):params.append({'name':name,'in':'path','required':True,'schema':{'type':'integer','minimum':1}})
 if method!='get':params.append({'name':'X-CSRF-Token','in':'header','required':True,'description':'Fetch from auth/session; preserve its session cookie. Token rotates after login/logout.','schema':{'type':'string'}})
```

add declares a function with optional body/security/pagination arguments. setdefault creates each path map once. operationId combines method/path placeholders. Public operations replace security with [] but still need CSRF when mutating. re.findall extracts URI identifiers.

## 7. Input → transformation → output

**Input:** Python 3 stdlib; schemas dict; add() route declarations.

**Transformation:** follow section 5 in order; guards run before the important side effects, helpers supply shared database/security behavior, and failures propagate rather than silently succeeding.

**Output:** public/assets/openapi.json overwritten deterministically with OpenAPI 3.1, currently 77 operations.

## 8. Call chain and connected evidence

[app/api.php](../../app/api.php) · [public/api.php](../../public/api.php) · [public/assets/openapi.json](../../public/assets/openapi.json) · [tests/api.py](../../tests/api.py)

Use the [complete assembly inventory](../../Assemble/01-repository-assembly-map.md) to find the canonical guide for each dependency. Source links above lead to the executable file; guides explain it without creating a second runtime copy.

## 9. Why this implementation?

The project uses direct PHP services and small CLI/Python helpers so students can trace the actual request and SQL without a framework or package build. Shared helpers prevent separate validation logic for REST and native forms. Trade-off: compact source lines and hand-maintained route/schema catalogs demand disciplined review. Do not introduce a framework merely to make this guide easier.

## 10. Security, correctness and accessibility

Keep private configuration, password hashes, authenticator material, cookies and recovery codes out of terminals, screenshots and reports. The configured document root must remain `public/`; these learning folders and private service/scripts are not webroots. Database constraints protect relationships, while server authorization and business validation protect who may change rows. Neither a green CLI check nor a JSON schema certifies production security.

## 11. Common failures and safe diagnosis

Hand-edited JSON lost: edit generator instead; mismatch: compare PHP route/schema; generator passes but server fails: docs not execution; accidental generated diff: inspect and coordinate, no blind revert.

For HTTP status meaning and deployment diagnostics use [host troubleshooting](../../Assemble/11-host-troubleshooting-and-rollback.md). Diagnose the failing layer before changing a security control.

## 12. Run or verify safely

```bash
# From repository root. Read the safety classification before executing.
python3 -m json.tool public/assets/openapi.json > /dev/null
```

> [!WARNING]
> `tests/fixtures.php` and all Python integration/browser runners reset **only the deliberately disposable `sportshub_test` schema**. Prove that schema is separate and disposable before execution. Never point fixtures at real records. Run suites sequentially. Do not execute commands merely because they appear in a code fence.

Actual current-task results belong in the [evidence register](../../Assemble/16-evidence-and-artifact-register.md); commands in this guide are instructions, not claims of execution.

## 13. Debugging lesson

Start at the first guard or failing assertion in section 5. Inspect process exit status, exception category, boolean outcomes, schema name and route/method. Set a local debugger breakpoint before a side effect and predict the next call. Do not dump `$_POST`, environment values, session arrays, full JSON auth responses or SQL identity rows. Use synthetic accounts in disposable local data.

## 14. Guided experiment

Safely regenerate in a disposable mirror containing only bin/build-openapi.py and public/assets directory. Compare artifact bytes to checked-in JSON; no runtime checkout write required.

Save a prediction, observed safe output and explanation in your [human evidence record](../09-completion-evidence.md). Revert only your temporary edits by comparing your recorded diff in the editor; remove only files you created in your isolated sandbox. Do not use a blanket checkout reset or a shared database deletion as cleanup.

## 15. Challenge ladder

| Level | Task | Expected evidence / hint |
|---|---|---|
| 1 — Understand | Trace one input through the excerpt to its next dependency. | A labeled call chain and predicted outcome, verified by reading source. |
| 2 — Modify | Propose a focused diagnostic or documentation improvement for this file. Implement manually only in your owned approved feature branch. | Before/after diff, a justified check, and no secret-bearing output. Preserve guards. |
| 3 — Engineer | Select a failure from section 11; explain its boundary and design a regression scenario. | Reproduction using synthetic disposable data or a non-mutating check, expected failure, reviewer discussion and honest trade-offs. |

## 16. Knowledge check

1. Does this enforce server validation?
2. Why not manually edit 13,015 JSON lines?
3. Why do public POSTs still need CSRF?

<details><summary>Answer key — read after predicting</summary>

1. No; PHP is authoritative.
2. Generator is compact source; manual edits drift or disappear.
3. Anonymous sessions can still be targeted by cross-site mutations.

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
