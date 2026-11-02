# 🔍 `tests/integration.py` — source guide

[Stage dashboard](../README.md) · [Data dictionary](../data-dictionary.md) · [SQL and transactions](../sql-and-transactions.md) · [Verification suites](../verification-suites.md)

## 1. File card

| Field | Verified source fact |
|---|---|
| Canonical source | [tests/integration.py](../../tests/integration.py) |
| Primary learning owner | Stage 04; Member D **TBD**, no authorship assigned |
| Secondary reviewers | Stage 02 for student behavior; Stage 03 for administration; Assemble for operations |
| Language / size | python; 139 lines at inspection |
| Inspected baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |
| Role | Exercise native PHP forms through real HTTP/cookies and SQL writes. |

## 2. Why this file exists

Exercise native PHP forms through real HTTP/cookies and SQL writes. This file remains part of the existing Codex-assisted baseline. Studying or receiving this guide is not evidence of an individual's implementation or testing.

## 3. Prerequisites

Understand [PHP request boundaries](../01-architecture.md), [relational keys and transactions](../sql-and-transactions.md), and [test isolation](../verification-suites.md). A command-line process is a program launched in a terminal; a web request runs under a server process with potentially different permissions and PHP configuration.

## 4. Dependencies and inputs

Python 3; PHP_BIN selects compatible PHP; the migration and fixture helpers; isolated disposable sportshub_test; DB grants inherited privately.

Connected source: [tests/fixtures.php](../../tests/fixtures.php) · [app/actions.php](../../app/actions.php) · [public/index.php](../../public/index.php) · [tests/security.php](../../tests/security.php).

## 5. Detailed source-order tour and coverage index

This index covers the meaningful sections of this file at the inspected baseline. Line positions describe this version; follow the linked source after later changes.

| Region | Explanation |
|---|---|
| 1–10 | [Isolated environment/fixture/server](#isolated-environmentfixtureserver) |
| 11–35 | [Browser/check/dbvalue/totp helpers](#browsercheckdbvaluetotp-helpers) |
| 36–60 | [Public/security/student](#publicsecuritystudent) |
| 61–81 | [Approval/booking/club CRUD](#approvalbookingclub-crud) |
| 82–103 | [Other CRUD/results/attendance](#other-crudresultsattendance) |
| 104–128 | [2FA journey](#2fa-journey) |
| 129–139 | [Ownership/injection/throttle/cleanup](#ownershipinjectionthrottlecleanup) |

### Isolated environment/fixture/server

Source lines 1–10: Override DB_NAME, APP_ENV, APP_URL; random password; fixtures before the HTTP server; PHP temporary server at 127.0.0.1: 8081.

### Browser/check/dbvalue/totp helpers

Source lines 11–35: CookieJar retains sessions; post fetches hidden CSRF from HTML; check prints labels; dbvalue uses PHP bootstrap against the test DB; TOTP mirrors the SHA-1 format.

### Public/security/student

Source lines 36–60: Wait for the server; pages, CSP, HttpOnly, 419 CSRF, register/login, escaping, 403 role denial, join/duplicate and unapproved attendance.

### Approval/booking/club CRUD

Source lines 61–81: Approve pending membership, register for training, duplicate tournament registration, booking overlap, booking approval, create/edit/delete club and inspect admin pages.

### Other CRUD/results/attendance

Source lines 82–103: Tournament/resource/event create/update/delete, scored-results visibility, past attendance marking and student history.

### 2FA journey

Source lines 104–128: Enrollment, password/code, 8 one-time codes, encrypted secret, pending login/replay/recovery, new time step after a bounded wait, own booking cancellation.

### Ownership/injection/throttle/cleanup

Source lines 129–139: Another user’s cancellation cannot delete a record; SQL metacharacters; 8-failure rate limit; print count; finally terminates only its own server.

## 6. Real annotated excerpt

Copied from source lines 11–22; this is baseline code, not a proposed change.

```python
class Browser:
    def __init__(self): self.opener=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
    def request(self,page='home',data=None):
        request=urllib.request.Request('http://127.0.0.1:8081/?page='+page, data=None if data is None else urllib.parse.urlencode(data).encode())
        try: response=self.opener.open(request)
        except urllib.error.HTTPError as ex: response=ex
        return response.status,response.read().decode(),response.headers
    def post(self,page,action,**fields):
        status,html,_=self.request(page)
        csrf=re.search(r'name="csrf" value="([a-f0-9]+)"',html)
        if not csrf: raise AssertionError('No CSRF token on '+page)
        return self.request(page,dict(action=action,csrf=csrf.group(1),**fields))
```

A separate Browser instance owns its own cookiejar. request encodes form fields; urllib follows 303 redirects to HTML. post first reads CSRF from the actual form. The suite therefore checks native fallback without JS. It does not prove REST or CSS behavior.

## 7. Input → transformation → output

**Input:** Python 3; PHP_BIN selects compatible PHP; the migration and fixture helpers; isolated disposable sportshub_test; DB grants inherited privately.

**Transformation:** follow section 5 in order; guards run before the important side effects, helpers supply shared database/security behavior, and failures propagate rather than silently succeeding.

**Output:** Public/auth/admin/student form assertions and final count; temporary server on 8081 stopped in finally.

## 8. Call chain and connected evidence

[tests/fixtures.php](../../tests/fixtures.php) · [app/actions.php](../../app/actions.php) · [public/index.php](../../public/index.php) · [tests/security.php](../../tests/security.php)

Use the [complete assembly inventory](../../Assemble/01-repository-assembly-map.md) to find the canonical guide for each dependency. Source links above lead to the executable file; guides explain it without creating a second runtime copy.

## 9. Why this implementation?

The project uses direct PHP services and small CLI/Python helpers so students can trace the actual request and SQL without a framework or package build. Shared helpers prevent separate validation logic for REST and native forms. Trade-off: compact source lines and hand-maintained route/schema catalogs demand disciplined review. Do not introduce a framework merely to make this guide easier.

## 10. Security, correctness and accessibility

Keep private configuration, password hashes, authenticator material, cookies and recovery codes out of terminals, screenshots and reports. The configured document root must remain `public/`; these learning folders and private service/scripts are not webroots. Database constraints protect relationships, while server authorization and business validation protect who may change rows. Neither a green CLI check nor a JSON schema certifies production security.

## 11. Common failures and safe diagnosis

Connection refused: server startup/PHP path; fixture failure: verify the test DB guard/grants; missing CSRF regex: inspect form markup; timing wait: allow a fresh TOTP time step; a count difference must be explained, rather than edited away.

For HTTP status meaning and deployment diagnostics use [host troubleshooting](../../Assemble/11-host-troubleshooting-and-rollback.md). Diagnose the failing layer before changing a security control.

## 12. Run or verify safely

```bash
# From repository root. Read the safety classification before executing.
PHP_BIN=php python3 tests/integration.py
```

> [!WARNING]
> `tests/fixtures.php` and all Python integration/browser runners reset **only the deliberately disposable `sportshub_test` schema**. Prove that schema is separate and disposable before execution. Never point fixtures at real records. Run suites sequentially. Do not execute commands merely because they appear in a code fence.

Actual current-task results belong in the [evidence register](../../Assemble/16-evidence-and-artifact-register.md); commands in this guide are instructions, not claims of execution.

## 13. Debugging lesson

Start at the first guard or failing assertion in section 5. Inspect process exit status, exception category, boolean outcomes, schema name and route/method. Set a local debugger breakpoint before a side effect and predict the next call. Do not dump `$_POST`, environment values, session arrays, full JSON auth responses or SQL identity rows. Use synthetic accounts in disposable local data.

## 14. Guided experiment

First inspect env construction and the fixture guard. On approved disposable local test data only, run this suite alone, capture stdout check labels/exit status, and verify main DB untouched using nonpersonal aggregate counts. Rerunning replaces test data; no production rollback command applies.

Save a prediction, observed safe output and explanation in your [human evidence record](../09-completion-evidence.md). Revert only your temporary edits by comparing your recorded diff in the editor; remove only files you created in your isolated sandbox. Do not use a blanket checkout reset or a shared database deletion as cleanup.

## 15. Challenge ladder

| Level | Task | Expected evidence / hint |
|---|---|---|
| 1 — Understand | Trace one input through the excerpt to its next dependency. | A labeled call chain and predicted outcome, verified by reading source. |
| 2 — Modify | Propose a focused diagnostic or documentation improvement for this file. Implement manually only in your owned approved feature branch. | Before/after diff, a justified check, and no secret-bearing output. Preserve guards. |
| 3 — Engineer | Select a failure from section 11; explain its boundary and design a regression scenario. | Reproduction using synthetic disposable data or a non-mutating check, expected failure, reviewer discussion and honest trade-offs. |

## 16. Knowledge check

1. Why create 3 Browser instances?
2. Why is final HTTP 200 not proof that the original POST returned 200?
3. What can this say about mobile CSS?

<details><summary>Answer key — read after predicting</summary>

1. Independent cookies model separate roles.
2. Redirects follow to GET; inspect expected HTML and DB state.
3. Nothing; browser/visual checks are required.

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
