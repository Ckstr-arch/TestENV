# 🧠 Integration overview

The application is already assembled. Learning stages partition responsibility, not executables. PHP does not import the stage folders. Never copy stage guides over production files.

| Runtime layer | Canonical paths | Responsibility |
|---|---|---|
| Web boundary | `public/index.php`, `public/api.php`, `public/router.php`, `.htaccess` | Pages or REST; routing differs by server |
| Presentation | `app/layout.php`, `app/views.php`, `app/portal-views.php`, `app/ui.php`, assets | HTML and DOM behavior |
| Application rules | `app/actions.php`, `app/models.php`, `app/community.php` | Authoritative actions, state transitions, locks |
| Infrastructure | `app/bootstrap.php`, `app/security.php` | Config, PDO/session, validation, 2FA |
| Persistence | schema, migrations, MariaDB local/MySQL target | Relational constraints and durable rows |
| Operations | `bin/`, `docker/`, Compose, Dockerfile | Private setup and hosting; never public endpoints |

Open the [sequence diagram](diagrams/request-flow.md). A native join form submits `action=join,id,csrf` to the current page. `handle_action()` checks CSRF and calls `perform_action()`, which requires a user and invokes the shared domain logic. With JS, its action mapping selects `POST /api/v1/clubs/{id}/memberships`; the API validates JSON, session and CSRF before reaching the same rule. The browser receives a redirect or JSON metadata. Neither hiding a button nor editing HTML grants permission.

## Shared-file review contract

| Shared source | Primary technical guide | Review before changing |
|---|---|---|
| `app/views.php` | Stage 02 | Stage 01 presentation; Stage 03 admin branches; Stage 04 form/security |
| `app/portal-views.php` | Stage 02 | Stage 03 admin sections; Stage 01 shell; Stage 04 queries |
| `app/community.php` | Stage 02 | Stage 03 moderation; Stage 04 authorization, locking, API |
| actions/models/API/schema | Stage 04 | Stage 02 or 03 affected behavior; all for schema changes |
| JS form mapping | Stage 01 | Stage 04 methods/payload; Stage 02/03 journey |

Name the function/branch in the proposed task, notify the section owner, keep changes scoped, and agree review before parallel edits. Ownership is a future review responsibility, never a claim of historical authorship. If there are fewer than four humans, combine tracks and record actual work; if more, split scoped tasks with peer review.

## Baseline caveats

PHP files use dense long lines: line count understates complexity. Follow function/page names, not assumptions based on file size. Tournaments enroll individual players, not automated brackets. Resources are single reservable units. 2FA is opt-in. No email verification/reset/SSO is implemented. Legacy docs explain these limitations; students should not claim institution-ready security.

## First three suggested human tasks (not assigned or completed)

| Placeholder | 1 — inspect | 2 — practice | 3 — propose reviewed contribution |
|---|---|---|---|
| Member A | Trace real homepage DOM and tokens | Record keyboard/390px/dark-light observations | Choose and test one demonstrated accessibility improvement |
| Member B | Trace join → approval → community | Exercise pending/rejected/approved on disposable identity | Propose one evidence-based student journey improvement |
| Member C | Trace event CRUD and score rules | Test a disposable fixture plus role rejection | Propose one admin validation/usability improvement |
| Member D | Trace session/CSRF/PDO and schema | Explain collision locking and RFC tests | Propose a small security/DB/test improvement with review |

A task may legitimately end with a documented finding rather than a source patch if no defect exists. Save evidence of investigation; do not invent a bug to generate a commit.
