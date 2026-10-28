# 05 — Safe backend labs and capstones

[Dashboard](README.md) · [Answer keys](exercises/answer-key.md) · [Safety before tests](06-testing-and-debugging.md)

Each lab has three levels: explain the existing behavior, manually make one scoped improvement after review, then analyze an edge case. Suggested human work is **PLANNED**. These instructions have not been performed on your behalf or credited as student contributions.

## Shared lab contract

Use only a local development deployment and synthetic accounts/data. Before any suite that resets tables, read its source and prove the target is exactly `sportshub_test`; inspect fixtures too. Do not put wanted data there. Keep a private before-copy of any file you personally edit outside public/; after review, manually remove only your temporary lines and compare `git diff -- <that-file>`. Do not use reset/clean or discard collaborators’ changes. Save predictions, safe observations, exact test command/results and a short explanation; never save cookies, passwords, APP_KEY, tokens, factor secrets or recovery codes.

## Lab A — Native versus REST membership

**Objective:** prove that transport choice preserves domain rules. **Sources:** public/index.php, app/actions.php, app/models.php, app/api.php, memberships schema; [source guides](02-source-inventory.md). **Prerequisites/setup:** running local app, synthetic student, synthetic club with a place available; separate administrator session if testing approval.

1. **Understand:** before submitting, draw the two call chains and predict one pending row. Read the action hidden fields and the JS route mapping. With JS disabled, submit once and record POST→303→GET. On a different synthetic club, enable JS and record POST JSON→201→redirect. Do not export request headers.
2. **Modify:** propose clearer pending-membership copy or a regression assertion comparing native/REST status. Name whether Stage 02 or04 owns the change; obtain review before editing. Expected outcome: same SQL state, clearer observation.
3. **Engineer:** predict two concurrent applications and a rejected→pending resubmission. Point to parent lock and unique relationship. Explain why UI button removal alone cannot prevent duplication.

**Expected:** pending requires admin approval before gated community. **Questions:** which value chooses the user? Where is capacity rechecked? **Evidence:** safe status chain and user+club synthetic row state. **Rollback:** cancel only your own pending membership using the supported UI, or let the fixture suite own cleanup of disposable test data. Do not delete a shared club.

## Lab B — Typed boundaries and error diagnosis

**Objective:** distinguish transport shape from domain validity. **Sources:** app/api.php api_payload/api_fields, bootstrap positive/date_input, actions. **Prerequisites/setup:** local development endpoint and its own browser session; read-only DevTools inspection first.

1. **Understand:** predict errors for application/json array `[]`, unexpected registration role field, a datetime missing minutes and an unknown positive-ID route. Then inspect code to confirm400 vs422 vs404. No account creation is needed for the paper exercise.
2. **Modify:** manually add one focused negative assertion in an existing disposable test suite only after proving its fixture DB guard. Run that suite sequentially. Expected outcome: a test that fails if the actual server boundary is removed.
3. **Engineer:** assess a request over65536 bytes and nested JSON. Explain why front-end maxlength is not enough and why API field allowlists complement SQL binding.

**Commands:** syntax-check the file you changed with `php -l <actual-php-path>`; use the suite commands in [testing](06-testing-and-debugging.md) only after isolation. **Safety:** no personal payloads, no production endpoint. **Evidence:** expected/observed status and assertion name. **Rollback:** manually remove only your added assertion after comparing the diff, unless it becomes your reviewed genuine contribution.

## Lab C — Booking collision as an interleaving

**Objective:** explain why transactions and shared locks protect an interval. **Sources:** actions book, models review_request, bookings/resources schema. **Prerequisites:** read [SQL/transactions](sql-and-transactions.md); no SQL writes needed for Level1.

1. **Understand:** use paper or [the schedule worksheet](exercises/README.md). Requests A/B target the same future interval. Write A lock→check→insert→commit; B waits→checks→rejects. Then remove the lock on paper and demonstrate both empty checks before either insert. Expected: identify the lost invariant.
2. **Modify:** propose an assertion for an adjacent interval (end==other start) versus real overlap. Add it only to an isolated fixture suite after review. Expected: adjacency succeeds while strict intersection fails.
3. **Engineer:** read tests/community.py’s existing deletion race as a model of real blocking assertions; design, do not automatically implement, a two-connection booking race. Specify synchronization so it proves waiting rather than just two sequential requests.

**Guiding questions:** why lock the resource rather than a nonexistent booking row? Why do pending requests block? Which cancellation retains history? **Evidence:** timing table and lock/invariant explanation. **Safety:** never remove real code locks to demonstrate failure; never directly edit real bookings. **Rollback:** Level1 has no persistent changes; manually revert only your new disposable test assertion if not retained through review.

## Lab D — Login and 2FA without leaking factors

**Objective:** trace anonymous→pending→authenticated and distinguish code generation from replay rejection. **Sources:** actions login/verify/enable_2fa, security verify_second_factor, bootstrap session. **Prerequisites/setup:** read [security foundations](php-http-and-security.md); use a disposable account under your control and private authenticator. Avoid shared demo keys for personal learning accounts.

1. **Understand:** run `php tests/security.php` after inspecting it. Predict why TOTP must preserve leading zeros and why last_counter blocks reuse. Draw the session keys for each auth state without writing any values.
2. **Modify:** propose a focused expiry/replay regression or safer error guidance. Keep generic invalid-code responses; do not introduce a bypass, secret log or localStorage authentication token. Run only the reviewed isolated suite.
3. **Engineer:** explain a server restore with DB ciphertext from one backup and APP_KEY from another. Plan a secure restore/re-enrollment policy; do not rotate a live key as an experiment.

**Expected:** pending session cannot access dashboard; valid factor finishes login and rotates ID/CSRF. **Evidence:** safe status/state names and public test output only. **Safety:** never capture enrollment/recovery screens. **Rollback:** privately disable2FA for the disposable account through its supported password+factor flow if desired; remove only your temporary test patch. No direct secret SQL deletion.

## Lab E — Production release simulation

**Objective:** separate a healthy local process from a deployable release. **Sources:** bin/migrate.php, Dockerfile, compose.yaml, bootstrap/security; [runbook](../Assemble/07-production-deployment-runbook.md). **Setup:** offline worksheet, fresh synthetic configuration placeholders and a proposed host matrix, no live credentials.

1. **Understand:** mark private/public directories, migration user/runtime user and durable key/session/database state.
2. **Modify:** draft a release checklist referencing an actual commit and test evidence; leave unknown host/date/approver blank. No deploy command is executed by this exercise.
3. **Engineer:** rehearse a rollback decision when an additive migration exists but older code is restored. Explain compatibility and restore only into a separate reviewed target.

**Expected evidence:** filled feasibility questions, synthetic topology and recovery plan. **Safety:** no remote write, production migration or real restore. **Rollback:** worksheet only; remove its synthetic files if desired. Review with the whole team before a human-authorized release.
