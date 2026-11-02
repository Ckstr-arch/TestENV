# 🔍 `tests/api.py` — source guide

[Stage dashboard](../README.md) · [Data dictionary](../data-dictionary.md) · [SQL and transactions](../sql-and-transactions.md) · [Verification suites](../verification-suites.md)

## 1. File card

| Field | Verified source fact |
|---|---|
| Canonical source | [tests/api.py](../../tests/api.py) |
| Primary learning owner | Stage 04; Member D **TBD**, no authorship assigned |
| Secondary reviewers | Stage 02 for student behavior; Stage 03 for administration; Assemble for operations |
| Language / size | python; 172 lines at inspection |
| Inspected baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |
| Role | Verify same-origin REST contract using real cookie/CSRF sessions and database. |

## 2. Why this file exists

Verify same-origin REST contract using real cookie/CSRF sessions and database. This file remains part of the existing Codex-assisted baseline. Studying or receiving this guide is not evidence of an individual's implementation or testing.

## 3. Prerequisites

Understand [PHP request boundaries](../01-architecture.md), [relational keys and transactions](../sql-and-transactions.md), and [test isolation](../verification-suites.md). A command-line process is a program launched in a terminal; a web request runs under a server process with potentially different permissions and PHP configuration.

## 4. Dependencies and inputs

Python 3; PHP_BIN selects compatible PHP; the migration and fixture helpers; isolated disposable sportshub_test; DB grants inherited privately.

Connected source: [tests/fixtures.php](../../tests/fixtures.php) · [app/api.php](../../app/api.php) · [public/api.php](../../public/api.php) · [bin/demo-users.php](../../bin/demo-users.php).

## 5. Detailed source-order tour and coverage index

This index covers the meaningful sections of this file at the inspected baseline. Line positions describe this version; follow the linked source after later changes.

| Region | Explanation |
|---|---|
| 1–11 | [Setup](#setup) |
| 12–38 | [Client/check/dbvalue/totp](#clientcheckdbvaluetotp) |
| 39–73 | [Transport/read boundary](#transportread-boundary) |
| 74–109 | [Auth/domain journeys](#authdomain-journeys) |
| 110–130 | [AdminCRUD/read aggregates](#admincrudread-aggregates) |
| 131–152 | [Second-factor security](#second-factor-security) |
| 153–172 | [Disposable accounts/repeatability/throttle](#disposable-accountsrepeatabilitythrottle) |

### Setup

Source lines 1–11: Disposable DB environment, synthetic password, fixtures, explicit demo seeder, temporary server on 8082.

### Client/check/dbvalue/totp

Source lines 12–38: Client.call bootstraps CSRF, sets JSON+Accept, optionally forges Origin/token/type/body. Captures HTTPError as a response, updates rotated CSRF. Helper queries and TOTP stay test-only.

### Transport/read boundary

Source lines 39–73: Health, session, 401/403, Origin checks, JSON 400/413/415, nested fields 422,404/405 Allow, pagination, filters, SQL metacharacters.

### Auth/domain journeys

Source lines 74–109: Reject role mass assignment; safe profile; admin authorization; memberships/attendance/tournaments/bookings; own lists and ownership/cancellations.

### AdminCRUD/read aggregates

Source lines 110–130: 4-entity payload loop: create, 201, Location, detail, PUT replacement, unknown fields, delete and 404; admin lists/report/results filters.

### Second-factor security

Source lines 131–152: Setup, enrollment, recovery codes once, logout, pending authentication, replay, single use, password+second factor required to disable.

### Disposable accounts/repeatability/throttle

Source lines 153–172: Known local demo accounts are checked without treating them as production accounts; seeder idempotence/refusal; 429 Retry-After; finally server cleanup.

## 6. Real annotated excerpt

Copied from source lines 12–28; this is baseline code, not a proposed change.

```python
class Client:
 def __init__(self):
  self.opener=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar())); self.csrf=None
 def call(self,path,method='GET',data=None,token=True,content_type='application/json',origin=None,raw=None):
  if method!='GET' and self.csrf is None: self.call('/auth/session')
  headers={'Accept':'application/json'}
  if method!='GET':
   headers['Content-Type']=content_type
   if token: headers['X-CSRF-Token']=self.csrf
  if origin: headers['Origin']=origin
  body=raw if raw is not None else (None if method=='GET' else json.dumps(data or {}).encode())
  request=urllib.request.Request('http://127.0.0.1:8082/api/v1'+path,method=method,headers=headers,data=body)
  try: response=self.opener.open(request)
  except urllib.error.HTTPError as ex: response=ex
  result=json.loads(response.read()); self.csrf=result.get('meta',{}).get('csrf_token',self.csrf)
  return response.status,result,response.headers
 def login(self,email,password):return self.call('/auth/login','POST',dict(email=email,password=password))
```

Client.call uses a cookie jar as browser credentials. It fetches auth/session before a mutation if no token is cached. data is JSON-encoded, even {} for otherwise bodyless mutations. HTTPError still has a JSON payload. A fresh response CSRF value prevents a stale token after login rotation.

## 7. Input → transformation → output

**Input:** Python 3; PHP_BIN selects compatible PHP; the migration and fixture helpers; isolated disposable sportshub_test; DB grants inherited privately.

**Transformation:** follow section 5 in order; guards run before the important side effects, helpers supply shared database/security behavior, and failures propagate rather than silently succeeding.

**Output:** REST status/header/payload/persistence assertions; server on 8082 lifecycle.

## 8. Call chain and connected evidence

[tests/fixtures.php](../../tests/fixtures.php) · [app/api.php](../../app/api.php) · [public/api.php](../../public/api.php) · [bin/demo-users.php](../../bin/demo-users.php)

Use the [complete assembly inventory](../../Assemble/01-repository-assembly-map.md) to find the canonical guide for each dependency. Source links above lead to the executable file; guides explain it without creating a second runtime copy.

## 9. Why this implementation?

The project uses direct PHP services and small CLI/Python helpers so students can trace the actual request and SQL without a framework or package build. Shared helpers prevent separate validation logic for REST and native forms. Trade-off: compact source lines and hand-maintained route/schema catalogs demand disciplined review. Do not introduce a framework merely to make this guide easier.

## 10. Security, correctness and accessibility

Keep private configuration, password hashes, authenticator material, cookies and recovery codes out of terminals, screenshots and reports. The configured document root must remain `public/`; these learning folders and private service/scripts are not webroots. Database constraints protect relationships, while server authorization and business validation protect who may change rows. Neither a green CLI check nor a JSON schema certifies production security.

## 11. Common failures and safe diagnosis

403: expected missing-CSRF/Origin negative case or a broken client; unexpected 401: cookie/challenge; fixture permissions: do not use production grants; generated contract drift: compare actual dispatch. Never print secret response bodies.

For HTTP status meaning and deployment diagnostics use [host troubleshooting](../../Assemble/11-host-troubleshooting-and-rollback.md). Diagnose the failing layer before changing a security control.

## 12. Run or verify safely

```bash
# From repository root. Read the safety classification before executing.
PHP_BIN=php python3 tests/api.py
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

1. Why refresh CSRF from every response?
2. Why 401 versus 403?
3. Does request-schema success grant an admin role?

<details><summary>Answer key — read after predicting</summary>

1. Login/logout rotate it; a stale cache fails.
2. 401 means unauthenticated; 403 means authenticated but forbidden, or a CSRF/Origin failure.
3. No; the role is checked server-side and the schema forbids role in registration.

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
