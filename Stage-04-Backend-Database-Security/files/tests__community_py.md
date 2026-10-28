# 🔍 `tests/community.py` — source guide

[Stage dashboard](../README.md) · [Data dictionary](../data-dictionary.md) · [SQL and transactions](../sql-and-transactions.md) · [Verification suites](../verification-suites.md)

## 1. File card

| Field | Verified source fact |
|---|---|
| Canonical source | [tests/community.py](../../tests/community.py) |
| Primary learning owner | Stage 04; Member D **TBD**, no authorship assigned |
| Secondary reviewers | Stage 02 for student behavior; Stage 03 for administration; Assemble for operations |
| Language / size | python; 145 lines at inspection |
| Inspected baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |
| Role | Verify approved-club access, ownership, moderation, migration preservation and a real deletion/comment race. |

## 2. Why this file exists

Verify approved-club access, ownership, moderation, migration preservation and a real deletion/comment race. This file remains part of the existing Codex-assisted baseline. Studying or receiving this guide is not evidence of an individual's implementation or testing.

## 3. Prerequisites

Understand [PHP request boundaries](../01-architecture.md), [relational keys and transactions](../sql-and-transactions.md), and [test isolation](../verification-suites.md). A command-line process is a program launched in a terminal; a web request runs under a server process with potentially different permissions and PHP configuration.

## 4. Dependencies and inputs

Python 3; PHP_BIN selects compatible PHP; the migration and fixture helpers; isolated disposable sportshub_test; DB grants inherited privately; PHP CLI subprocess row locks; Python ThreadPoolExecutor.

Connected source: [tests/fixtures.php](../../tests/fixtures.php) · [app/community.php](../../app/community.php) · [bin/migrate.php](../../bin/migrate.php) · [database/migrations/20261017_club_communities.sql](../../database/migrations/20261017_club_communities.sql).

## 5. Detailed source-order tour and coverage index

This index covers the meaningful sections of this file at the inspected baseline. Line positions describe this version; follow the linked source after later changes.

| Region | Explanation |
|---|---|
| 1–9 | [Isolated setup and server log](#isolated-setup-and-server-log) |
| 10–33 | [Client/check/dbvalue](#clientcheckdbvalue) |
| 34–63 | [Membership boundary/input](#membership-boundaryinput) |
| 64–92 | [Post/comment/reaction/report](#postcommentreactionreport) |
| 93–121 | [Moderation/deletion/revocation](#moderationdeletionrevocation) |
| 122–127 | [Migration repeatability](#migration-repeatability) |
| 128–141 | [Concurrency fixture](#concurrency-fixture) |
| 142–145 | [Diagnostics/cleanup](#diagnosticscleanup) |

### Isolated setup and server log

Source lines 1–9: Overrides the disposable schema, creates fixtures and a temporary server log; API on 8085.

### Client/check/dbvalue

Source lines 10–33: Cookies+CSRF and response headers; the HTML method checks rendering; the query helper observes synthetic state.

### Membership boundary/input

Source lines 34–63: Guests receive 401, nonmembers/pending members receive 403, approved/admin access, cross-club access is blocked, roster privacy, CSRF/field/length validation.

### Post/comment/reaction/report

Source lines 64–92: Persist text/Location, plain-text escaping, pagination, own edits only, idempotent reactions, comment ownership and admin removal, duplicate report 409, report privacy.

### Moderation/deletion/revocation

Source lines 93–121: Private admin queue/reasons, hiding clears visibility and resolves pending reports, an author correction cannot unhide a post, restoration by an admin, leaving immediately revokes access, deletion erases the body and a tombstone cannot be restored.

### Migration repeatability

Source lines 122–127: Run migration twice, compare user count/post body and ledger count; moderation audit count is checked.

### Concurrency fixture

Source lines 128–141: A separate PHP transaction locks the post, the HTTP comment waits, deletion commits, the comment must return 404, no child is retained. The parent lock and READ COMMITTED service behavior are essential.

### Diagnostics/cleanup

Source lines 142–145: On failure, print a bounded server-log tail, then rethrow; finally terminate its own server and close the temporary file.

## 6. Real annotated excerpt

Copied from source lines 128–136; this is baseline code, not a proposed change.

```python
 # A deletion that commits while a comment waits for the parent lock must win.
 lockcode=f'require {json.dumps(str(ROOT/"app/bootstrap.php"))}; db()->beginTransaction(); query("SELECT id FROM club_posts WHERE id=? FOR UPDATE",[{post2}]); echo "ready",PHP_EOL; flush(); fgets(STDIN); query("UPDATE club_posts SET status=?,body=? WHERE id=?",["deleted","",{post2}]); db()->commit();'
 locked=subprocess.Popen([PHP,'-r',lockcode],env=env,stdin=subprocess.PIPE,stdout=subprocess.PIPE,stderr=subprocess.PIPE,text=True)
 if locked.stdout.readline().strip()!='ready':raise AssertionError('Race fixture could not lock parent')
 with concurrent.futures.ThreadPoolExecutor(max_workers=1) as pool:
  waiting=pool.submit(author.call,'/clubs/1/posts/'+str(post2)+'/comments','POST',{'body':'Racing comment'})
  try:waiting.result(timeout=.2)
  except concurrent.futures.TimeoutError:pass
  finally:locked.stdin.write('release\n');locked.stdin.flush()
```

The lock code uses a separate PDO connection, so contention is real. The parent-row lock is held until stdin release. ThreadPool lets the HTTP call wait concurrently. Finally releases the fixture even on timeout. No app code is short-circuited to manufacture a pass.

## 7. Input → transformation → output

**Input:** Python 3; PHP_BIN selects compatible PHP; the migration and fixture helpers; isolated disposable sportshub_test; DB grants inherited privately; PHP CLI subprocess row locks; Python ThreadPoolExecutor.

**Transformation:** follow section 5 in order; guards run before the important side effects, helpers supply shared database/security behavior, and failures propagate rather than silently succeeding.

**Output:** Community checks, private fields/escaping, 2 migration runs, concurrent 404 and no child row; server on 8085 cleanup.

## 8. Call chain and connected evidence

[tests/fixtures.php](../../tests/fixtures.php) · [app/community.php](../../app/community.php) · [bin/migrate.php](../../bin/migrate.php) · [database/migrations/20261017_club_communities.sql](../../database/migrations/20261017_club_communities.sql)

Use the [complete assembly inventory](../../Assemble/01-repository-assembly-map.md) to find the canonical guide for each dependency. Source links above lead to the executable file; guides explain it without creating a second runtime copy.

## 9. Why this implementation?

The project uses direct PHP services and small CLI/Python helpers so students can trace the actual request and SQL without a framework or package build. Shared helpers prevent separate validation logic for REST and native forms. Trade-off: compact source lines and hand-maintained route/schema catalogs demand disciplined review. Do not introduce a framework merely to make this guide easier.

## 10. Security, correctness and accessibility

Keep private configuration, password hashes, authenticator material, cookies and recovery codes out of terminals, screenshots and reports. The configured document root must remain `public/`; these learning folders and private service/scripts are not webroots. Database constraints protect relationships, while server authorization and business validation protect who may change rows. Neither a green CLI check nor a JSON schema certifies production security.

## 11. Common failures and safe diagnosis

Race hangs: inspect the lock-fixture process and timeout; MariaDB 1020: read isolation/locking query; hidden-post leak: visibility filter; reseeding a deleted body: demo guard predicate. Keep failure logs private because future errors could contain private context.

For HTTP status meaning and deployment diagnostics use [host troubleshooting](../../Assemble/11-host-troubleshooting-and-rollback.md). Diagnose the failing layer before changing a security control.

## 12. Run or verify safely

```bash
# From repository root. Read the safety classification before executing.
PHP_BIN=php python3 tests/community.py
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

1. Why a separate PHP process?
2. Why should a waiting comment return 404?
3. Does an author’s PATCH to a hidden post restore it?

<details><summary>Answer key — read after predicting</summary>

1. A separate connection can hold a lock while the HTTP connection waits.
2. Deletion committed before the lock was acquired; the service checks the new committed status.
3. No; only admin moderation can restore it.

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
