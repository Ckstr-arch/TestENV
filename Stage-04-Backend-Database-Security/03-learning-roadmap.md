# 03 — A progressive backend curriculum

[Dashboard](README.md) · Next: [walkthroughs](04-guided-walkthroughs.md)

Move by demonstrated understanding, not by hours spent. For each module, predict a result before running anything and retain one redacted observation.

| Module | Level 1 — Understand | Level 2 — Modify manually | Level 3 — Engineer/review |
|---|---|---|---|
| PHP boot | Read [bootstrap tour](files/app__bootstrap_php.md), explain require/config/PDO | Propose a clearer diagnostic without printing values | Compare CLI vs Apache configuration and secure writable storage |
| HTTP adapters | Draw native POST→303 and REST→JSON | Add a focused invalid-field regression in disposable tests | Review transport parity when a new field/route is proposed |
| Database relationships | Read all tables in [dictionary](data-dictionary.md) | Write a read-only JOIN over synthetic study rows | Explain FK cascades, indexes and safe additive migration/rollback |
| Reservation state | Trace join/tournament/attendance and booking | Verify a rejected input leaves no new row | Interleave two requests and reason about parent locking |
| Auth/security | Explain hashing vs encryption, session vs challenge | Test a public vector or synthetic expiry case | Analyze replay, recovery consumption and key-restoration failure |
| REST contract | Find allowlists, cookies and response statuses | Extend a focused assertion with expected unknown-field422 | Review JS/OpenAPI/action alignment and backward compatibility |
| Runtime utilities | Explain setup/migrate/seed/doctor differences | Reproduce setup on a disposable local environment | Rehearse a release with stable key, backups and test evidence |
| Verification | Classify [every suite](verification-suites.md) | Record actual pass/fail/skip, not copied historical totals | Review isolation, race orchestration and meaningful test assertions |

## Capstone links across the four stages

1. Homepage PHP→DOM→CSS: [Stage 01](../Stage-01-Frontend-UX/README.md).
2. Grid/Flex/mobile drawer: [Stage 01 labs](../Stage-01-Frontend-UX/05-labs-and-exercises.md).
3. Membership lifecycle: [walkthrough 1](04-guided-walkthroughs.md#1-membership-request-two-transports), [student track](../Stage-02-Student-Experience/README.md).
4. Admin CRUD: [walkthrough 2](04-guided-walkthroughs.md#2-admin-save-from-form-to-visible-record), [admin track](../Stage-03-Admin-Operations/README.md).
5. Booking collision: [transaction lesson](sql-and-transactions.md), [lab C](05-labs-and-exercises.md#lab-c--booking-collision-as-an-interleaving).
6. Login/2FA: [foundations](php-http-and-security.md), [lab D](05-labs-and-exercises.md#lab-d--login-and-2fa-without-leaking-factors).
7. Genuine feature PR: [Git workflow](../Assemble/02-git-github-team-workflow.md).
8. Release simulation: [production runbook](../Assemble/07-production-deployment-runbook.md).

The proposed modifications are exercises, not precompleted contributions. Request domain review before shared changes and never weaken security controls to make a demonstration succeed.
