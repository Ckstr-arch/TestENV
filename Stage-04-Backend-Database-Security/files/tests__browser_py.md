# 🔍 `tests/browser.py` — source guide

[Stage dashboard](../README.md) · [Data dictionary](../data-dictionary.md) · [SQL and transactions](../sql-and-transactions.md) · [Verification suites](../verification-suites.md)

## 1. File card

| Field | Verified source fact |
|---|---|
| Canonical source | [tests/browser.py](../../tests/browser.py) |
| Primary learning owner | Stage 04; Member D **TBD**, no authorship assigned |
| Secondary reviewers | Stage 02 for student behavior; Stage 03 for administration; Assemble for operations |
| Language / size | python; 115 lines at inspection |
| Inspected baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |
| Role | Exercise actual JavaScript enhancement, focus/navigation/themes and responsive HTML through Chromium. |

## 2. Why this file exists

Exercise actual JavaScript enhancement, focus/navigation/themes and responsive HTML through Chromium. This file remains part of the existing Codex-assisted baseline. Studying or receiving this guide is not evidence of an individual's implementation or testing.

## 3. Prerequisites

Understand [PHP request boundaries](../01-architecture.md), [relational keys and transactions](../sql-and-transactions.md), and [test isolation](../verification-suites.md). A command-line process is a program launched in a terminal; a web request runs under a server process with potentially different permissions and PHP configuration.

## 4. Dependencies and inputs

Python 3; PHP_BIN selects compatible PHP; the migration and fixture helpers; isolated disposable sportshub_test; DB grants inherited privately; Python Playwright; installed Chromium or CHROMIUM_PATH.

Connected source: [tests/fixtures.php](../../tests/fixtures.php) · [public/assets/app.js](../../public/assets/app.js) · [app/views.php](../../app/views.php) · [app/portal-views.php](../../app/portal-views.php).

## 5. Detailed source-order tour and coverage index

This index covers the meaningful sections of this file at the inspected baseline. Line positions describe this version; follow the linked source after later changes.

| Region | Explanation |
|---|---|
| 1–12 | [Dependencies/fixtures/TOTP](#dependenciesfixturestotp) |
| 13–27 | [Launch and observability](#launch-and-observability) |
| 28–51 | [Registration/student/admin CRUD](#registrationstudentadmin-crud) |
| 52–63 | [2FA UI](#2fa-ui) |
| 64–90 | [Community interactions](#community-interactions) |
| 91–108 | [Themes/role/mobile matrix](#themesrolemobile-matrix) |
| 109–115 | [Public pages and cleanup](#public-pages-and-cleanup) |

### Dependencies/fixtures/TOTP

Source lines 1–12: Import Playwright, reset the test DB, temporary PHP server on 8083; TOTP generation test helper.

### Launch and observability

Source lines 13–27: Wait for the server; headless Chromium; optional executable path; 1440px viewport; collect page errors and method/URL only for API requests.

### Registration/student/admin CRUD

Source lines 28–51: Labels/selectors fill real forms; assert API feedback; independent admin browser context; approve, register, book, create/edit/delete club.

### 2FA UI

Source lines 52–63: Read the synthetic setup secret in memory, enable, recovery dialog with 8 codes, acknowledge, logout, challenge/recovery login, disable using the second unused code.

### Community interactions

Source lines 64–90: Real composer/Fetch posts, reaction, comment, edit/report, admin hide/restore and student visibility.

### Themes/role/mobile matrix

Source lines 91–108: Persistent light theme, theme before profile, no admin link for students, 390px drawer Escape behavior; 1440/390px, both themes across club tabs/admin screens; no horizontal overflow.

### Public pages and cleanup

Source lines 109–115: Sign out, public desktop/mobile loop, mobile navigation visibility, no page errors; close the browser; finally terminate its own server.

## 6. Real annotated excerpt

Copied from source lines 17–27; this is baseline code, not a proposed change.

```python
 with sync_playwright() as p:
  options={'headless':True}
  if os.environ.get('CHROMIUM_PATH'):options['executable_path']=os.environ['CHROMIUM_PATH']
  browser=p.chromium.launch(**options)
  context=browser.new_context(viewport={'width':1440,'height':1000})
  page=context.new_page();errors=[];requests=[]
  page.on('pageerror',lambda e:errors.append(str(e)))
  page.on('request',lambda req:requests.append((req.method,req.url)) if '/api/v1/' in req.url else None)
  page.goto('http://localhost:8083/?page=register')
  for label,value in [('Full name','Browser Athlete'),('Student ID','BROWSER001'),('Email address','browser@example.invalid'),('Password',env['TEST_PASSWORD'])]:page.get_by_label(label,exact=True).fill(value)
  page.get_by_label('Faculty').select_option('Computing');page.get_by_role('button',name='Create account').click();page.wait_for_url('**/?page=login')
```

Launch options remain headless; optional CHROMIUM_PATH does not install a browser. new_context isolates cookies and localStorage. pageerror captures uncaught JS errors. The request listener records verb/URL only, avoiding password payloads. Locators use accessible labels, which can reveal accessibility regressions.

## 7. Input → transformation → output

**Input:** Python 3; PHP_BIN selects compatible PHP; the migration and fixture helpers; isolated disposable sportshub_test; DB grants inherited privately; Python Playwright; installed Chromium or CHROMIUM_PATH.

**Transformation:** follow section 5 in order; guards run before the important side effects, helpers supply shared database/security behavior, and failures propagate rather than silently succeeding.

**Output:** Browser assertions and one final workflow message; temporary server on 8083.

## 8. Call chain and connected evidence

[tests/fixtures.php](../../tests/fixtures.php) · [public/assets/app.js](../../public/assets/app.js) · [app/views.php](../../app/views.php) · [app/portal-views.php](../../app/portal-views.php)

Use the [complete assembly inventory](../../Assemble/01-repository-assembly-map.md) to find the canonical guide for each dependency. Source links above lead to the executable file; guides explain it without creating a second runtime copy.

## 9. Why this implementation?

The project uses direct PHP services and small CLI/Python helpers so students can trace the actual request and SQL without a framework or package build. Shared helpers prevent separate validation logic for REST and native forms. Trade-off: compact source lines and hand-maintained route/schema catalogs demand disciplined review. Do not introduce a framework merely to make this guide easier.

## 10. Security, correctness and accessibility

Keep private configuration, password hashes, authenticator material, cookies and recovery codes out of terminals, screenshots and reports. The configured document root must remain `public/`; these learning folders and private service/scripts are not webroots. Database constraints protect relationships, while server authorization and business validation protect who may change rows. Neither a green CLI check nor a JSON schema certifies production security.

## 11. Common failures and safe diagnosis

Playwright is missing: install optional tooling locally; it is not an app dependency; browser is missing: install compatible Chromium; transition race: wait for visible/hidden state rather than using arbitrary sleep; layout assertion failure: inspect the actual viewport and scroll width. Do not screenshot setup/recoverydialog.

For HTTP status meaning and deployment diagnostics use [host troubleshooting](../../Assemble/11-host-troubleshooting-and-rollback.md). Diagnose the failing layer before changing a security control.

## 12. Run or verify safely

```bash
# From repository root. Read the safety classification before executing.
PHP_BIN=php python3 tests/browser.py
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

1. Why use a separate admin context?
2. Does no overflow prove WCAG compliance?
3. Does running a syntax check exercise Fetch?

<details><summary>Answer key — read after predicting</summary>

1. It isolates role credentials from the student context.
2. No; manual contrast, keyboard and screen-reader review remain.
3. No; a real browser journey does.

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
