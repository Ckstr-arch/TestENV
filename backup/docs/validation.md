# Validation record · native MySQL package

Verified **2 November 2026 (Asia/Colombo)**. The application in `backup/` derives from runtime baseline `f81eab54ebe7179e1763f364d6b8d4f564c36541`; repository task base is `372d26a`. Results below belong to this edition and are not copied from historical prototype tests.

## Executed checks

| Check | Actual command / evidence | Outcome and scope |
|---|---|---|
| PHP syntax | PHP `-l` on every shipped PHP source | PASS — 25 files, including utilities and five test scripts |
| Security and engine boundaries | `php tests/security.php` | PASS — 23 checks: TOTP, drift/replay, encryption/tampering, password handling, escaping and MySQL/MariaDB version boundaries |
| Native HTTP/MySQL functional verification | `php tests/integration.php --confirm-test-reset` | PASS — 158 checks on actual MySQL; public pages, authentication/roles/CSRF, both REST and native forms, all four admin CRUD areas, membership, registrations, booking/cancellation, results/reports, community/moderation and full 2FA/recovery lifecycle |
| Concurrent review regressions | `php tests/concurrency.php --confirm-test-reset` | PASS — 10 checks; independent PHP/database connections establish a real parent-row lock wait, commit a competing approval, and verify last-slot capacity and overlapping-booking protection |
| Stack and standalone documentation | `php tests/audit.php` | PASS — 206 read-only checks, 45 package files, 16 runtime text files and 35 local Markdown file links; does not claim anchor/external-link/render validation |
| Doctor and engine | `php bin/doctor.php`; private `SELECT VERSION()` | PASS — 27 readiness checks; PHP 8.4.26 and actual MySQL **8.4.13**, required PHP extensions/Argon2id, timezone, key and all 17 tables |
| PHP-generated API contract | `php bin/build-openapi.php`; independent OpenAPI 3.1 validation | PASS — 77 operations; repeat generation stable; baseline semantic parity except isolated cookie and corrected 60-character sport limits |
| Browser UI | Private reviewer harness outside this package, using Chromium | PASS — 397 assertions over public, admin and approved-student/club pages at 1440px and 390px, both themes, navigation/focus, isolated preference/cookie, real TOTP login; zero third-party requests and uncaught JS errors |
| JavaScript syntax | Syntax checks for `app.js` and `theme-init.js` | PASS; Node was optional reviewer tooling and is not shipped or required by this application |
| Development sports seed | Production and staging refusal checks | PASS — sample data seed requires exact development mode before database writes |
| Optional development account script | Guard, repeatability and private-file checks | PASS — production/missing-flag refusal, existing-record preservation, three distinct random passwords, unique athlete factor and restricted private credential file; no secret values printed |
| Source preservation and staged scope | Original tracked-file fingerprints and reviewed paths | Original runtime, schema, tests, assets and documentation preserved. The sole parent change is `/backup/` exclusion in the legacy root `.dockerignore` to protect nested private state from its broad image copy |
| Whitespace and private-file exclusion | `git diff --cached --check`, tracked-path review and ignore checks | Required publication checks; final result and commit are reported after staging/commit, not invented in this file |

The independent OpenAPI/browser reviewer tools are outside `backup/`. They do not create an application dependency. The distributed code and its included tests/setup scripts use PHP, HTML, CSS, JavaScript and MySQL SQL; JSON/configuration/images are data/assets. No container manifest, Python source, Node package, Composer package or framework is distributed in this folder.

## Meaningful findings corrected

- The database connection now rejects MariaDB and MySQL versions below 8 instead of assuming compatibility satisfies the assignment.
- Review/reservation transactions use fresh READ COMMITTED checks and consistent parent-row locking. Both concurrent approval regressions passed on actual MySQL.
- Admin save/delete and their audit records commit atomically; sport validation and the contract match the SQL column width.
- The built-in server router returns actual 404s for private/unknown paths and only delegates validated public assets.
- Mobile navigation focus waits for visibility before moving to its first link. The final browser run verifies opening, focus and Escape return.
- The inherited tampering unit assertion previously could catch its own failure. The backup test now asserts rejection outside the catch; all 23 checks passed.
- The API contract's inherited sport limit was corrected from 100 to 60 after review. The initial browser harness also needed accurate navigation waits; harness corrections are not claimed as application defects.

## Isolation and test safety

Only the fresh `sportshub_backup_test` schema was reset, after checking the explicit CLI flag, exact database name, development mode, actual selected database and actual MySQL engine. The integration suite owns its temporary server on 8092; concurrency uses independent PHP connections. Run them sequentially and never store wanted data in the test schema.

The normal backup edition uses its own `sportshub_backup` database, generated key, `sportshub_backup_session` cookie, theme preference and package-local `var/sessions`. The original application database/key/session state and unrelated containers were not changed. Local testing created only synthetic accounts and sports data. Private `.env`, `var/demo-credentials.json`, session files and reviewer/server credentials are ignored or stored outside the source tree and must never be committed or published.

For this cloud validation only, actual MySQL ran as a separate official server instance with fresh storage and a private loopback port. This is test infrastructure outside the native package, not a Docker requirement for users. No existing database volume was deleted or replaced.

## Limits and human deliverables

- Native Windows and macOS instructions were inspected, not executed on those operating systems. The verified environment is Linux.
- Browser assertions do not certify accessibility: independent assistive-device, contrast, zoom and human usability checks remain appropriate.
- No new public deployment, real HTTPS-host acceptance, final report export, new screenshot set, video or lecturer-access proof is claimed. Follow the report/hosting guides with actual evidence.
- Known product limits remain: optional rather than mandatory 2FA, no email verification/reset or SSO, single-node file sessions, generic club standings, individual tournament enrollment and single-unit resources.
- Team identities, actual individual contributions, image/brand usage approval and the final deadline require real human confirmation. Existing assisted source does not become a student's independent authorship through this copy.

## Commit and release record

The user authorized creating this edition and committing it. The working branch is `feat/guideline-compliant-backup`. The exact resulting commit is shown by `git rev-parse HEAD` after commit and in the task handoff; it is not guessed in advance here. A local commit does not establish a GitHub push, merge or public deployment. Those outcomes must be stated separately and truthfully.

[Guidelines](guidelines-audit.md) · [Requirements](requirements-traceability.md) · [Setup and test gates](local-setup.md) · [Hosting/security](security-and-hosting.md)
