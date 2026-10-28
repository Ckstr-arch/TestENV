# 🔍 `public/assets/openapi.json` — source guide

[Stage dashboard](../README.md) · [Data dictionary](../data-dictionary.md) · [SQL and transactions](../sql-and-transactions.md) · [Verification suites](../verification-suites.md)

## 1. File card

| Field | Verified source fact |
|---|---|
| Canonical source | [public/assets/openapi.json](../../public/assets/openapi.json) |
| Primary learning owner | Stage 04; Member D **TBD**, no authorship assigned |
| Secondary reviewers | Stage 02 for student behavior; Stage 03 for administration; Assemble for operations |
| Language / size | json; 13015 lines at inspection |
| Inspected baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |
| Role | Publish the generated machine-readable API 3.1 contract for clients and review. |

## 2. Why this file exists

Publish the generated machine-readable API 3.1 contract for clients and review. This file remains part of the existing Codex-assisted baseline. Studying or receiving this guide is not evidence of an individual's implementation or testing.

## 3. Prerequisites

Understand [PHP request boundaries](../01-architecture.md), [relational keys and transactions](../sql-and-transactions.md), and [test isolation](../verification-suites.md). A command-line process is a program launched in a terminal; a web request runs under a server process with potentially different permissions and PHP configuration.

## 4. Dependencies and inputs

bin/build-openapi.py schema and route catalog.

Connected source: [bin/build-openapi.py](../../bin/build-openapi.py) · [app/api.php](../../app/api.php) · [docs/api.md](../../docs/api.md).

## 5. Detailed source-order tour and coverage index

This index covers the meaningful sections of this file at the inspected baseline. Line positions describe this version; follow the linked source after later changes.

| Region | Explanation |
|---|---|
| 1–17 | [Version/info/server/security](#versioninfoserversecurity) |
| 18–12520 | [paths](#paths) |
| 12521–13015 | [components](#components) |

### Version/info/server/security

Source lines 1–17: OpenAPI 3.1, version 1.0, relative /api/v1 server and cookie name.

### paths

Source lines 18–12520: Repeated method-operation maps cover authentication, core CRUD, student participation, admin reviews/reports, community/moderation. Use the generated route catalog below rather than a manual 13,015-line review.

### components

Source lines 12521–13015: Request schemas, Response/Error, LocalDateTime, and cookie SecurityScheme. Exact component line boundaries are generated, so inspect top-level keys rather than relying on stable line markers.

## 6. Real annotated excerpt

Copied from source lines 1–11; this is baseline code, not a proposed change.

```json
{
  "openapi": "3.1.0",
  "info": {
    "title": "NSBM SportsHub REST API",
    "version": "1.0.0",
    "description": "Same-origin cookie sessions, JSON requests, CSRF protection, student/admin authorization and TOTP. All local timestamps use Asia/Colombo. PUT replaces an administrative record. No bearer tokens or wildcard CORS."
  },
  "servers": [
    {
      "url": "/api/v1"
    }
```

openapi is the format version; info.version is the API-document version. These are not PHP versions. The relative servers URL means the same-origin prefix. The security declaration describes a cookie requirement; individual public operations override it. PHP still checks roles/CSRF.

## 7. Input → transformation → output

**Input:** bin/build-openapi.py schema and route catalog.

**Transformation:** follow section 5 in order; guards run before the important side effects, helpers supply shared database/security behavior, and failures propagate rather than silently succeeding.

**Output:** Static JSON documentation of 77 operations; does not execute or secure any route.

## 8. Call chain and connected evidence

[bin/build-openapi.py](../../bin/build-openapi.py) · [app/api.php](../../app/api.php) · [docs/api.md](../../docs/api.md)

Use the [complete assembly inventory](../../Assemble/01-repository-assembly-map.md) to find the canonical guide for each dependency. Source links above lead to the executable file; guides explain it without creating a second runtime copy.

## 9. Why this implementation?

The project uses direct PHP services and small CLI/Python helpers so students can trace the actual request and SQL without a framework or package build. Shared helpers prevent separate validation logic for REST and native forms. Trade-off: compact source lines and hand-maintained route/schema catalogs demand disciplined review. Do not introduce a framework merely to make this guide easier.

## 10. Security, correctness and accessibility

Keep private configuration, password hashes, authenticator material, cookies and recovery codes out of terminals, screenshots and reports. The configured document root must remain `public/`; these learning folders and private service/scripts are not webroots. Database constraints protect relationships, while server authorization and business validation protect who may change rows. Neither a green CLI check nor a JSON schema certifies production security.

## 11. Common failures and safe diagnosis

Artifact edits lost on regeneration: edit the generator; a tool rejects the schema: ensure OpenAPI 3.1 support; route works but is absent: update the route catalog with review; JSON is valid but maxLength is wrong: compare PHP validators and SQL capacity.

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

Use Python json.load to read keys and operation count only. Regenerate in a temporary mirror and compare bytes. No runtime DB or account is required.

Save a prediction, observed safe output and explanation in your [human evidence record](../09-completion-evidence.md). Revert only your temporary edits by comparing your recorded diff in the editor; remove only files you created in your isolated sandbox. Do not use a blanket checkout reset or a shared database deletion as cleanup.

## 15. Challenge ladder

| Level | Task | Expected evidence / hint |
|---|---|---|
| 1 — Understand | Trace one input through the excerpt to its next dependency. | A labeled call chain and predicted outcome, verified by reading source. |
| 2 — Modify | Propose a focused diagnostic or documentation improvement for this file. Implement manually only in your owned approved feature branch. | Before/after diff, a justified check, and no secret-bearing output. Preserve guards. |
| 3 — Engineer | Select a failure from section 11; explain its boundary and design a regression scenario. | Reproduction using synthetic disposable data or a non-mutating check, expected failure, reviewer discussion and honest trade-offs. |

## 16. Knowledge check

1. Is 77 a run-test count?
2. Does JSON schema hide private response fields?
3. Which file is canonical for edits?

<details><summary>Answer key — read after predicting</summary>

1. No; it counts declared HTTP operations.
2. No; the server projection must exclude private data.
3. bin/build-openapi.py; the generated JSON must match.

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

## Generated method/path coverage catalog

This catalog was read from the checked-in JSON. It is documentation coverage, not an execution result. Routes are relative to `/api/v1`; server authorization and CSRF still apply.

| Method | Exact route | Declared purpose |
|---|---|---|
| GET | `/health` | Database-backed readiness |
| GET | `/auth/session` | Session identity and CSRF bootstrap |
| POST | `/auth/register` | Create a student account |
| POST | `/auth/login` | Password login; may return requires_2fa |
| POST | `/auth/2fa` | Complete pending login with a second factor |
| POST | `/auth/logout` | End the authenticated session |
| GET | `/me` | Current safe user profile |
| GET | `/me/security` | 2FA status and remaining recovery-code count |
| POST | `/me/2fa/setup` | Generate pending authenticator setup key (10-minute expiry) |
| POST | `/me/2fa` | Enroll authenticator and return recovery codes exactly once |
| DELETE | `/me/2fa` | Disable 2FA with password and second factor |
| GET | `/clubs` | List clubs |
| GET | `/clubs/{id}` | Read clubs detail |
| GET | `/admin/clubs` | Admin list clubs |
| POST | `/admin/clubs` | Create clubs |
| GET | `/admin/clubs/{id}` | Admin read clubs |
| PUT | `/admin/clubs/{id}` | Replace clubs |
| DELETE | `/admin/clubs/{id}` | Delete clubs (linked data may cascade) |
| GET | `/tournaments` | List tournaments |
| GET | `/tournaments/{id}` | Read tournaments detail |
| GET | `/admin/tournaments` | Admin list tournaments |
| POST | `/admin/tournaments` | Create tournaments |
| GET | `/admin/tournaments/{id}` | Admin read tournaments |
| PUT | `/admin/tournaments/{id}` | Replace tournaments |
| DELETE | `/admin/tournaments/{id}` | Delete tournaments (linked data may cascade) |
| GET | `/events` | List events |
| GET | `/events/{id}` | Read events detail |
| GET | `/admin/events` | Admin list events |
| POST | `/admin/events` | Create events |
| GET | `/admin/events/{id}` | Admin read events |
| PUT | `/admin/events/{id}` | Replace events |
| DELETE | `/admin/events/{id}` | Delete events (linked data may cascade) |
| GET | `/resources` | List resources |
| GET | `/resources/{id}` | Read resources detail |
| GET | `/admin/resources` | Admin list resources |
| POST | `/admin/resources` | Create resources |
| GET | `/admin/resources/{id}` | Admin read resources |
| PUT | `/admin/resources/{id}` | Replace resources |
| DELETE | `/admin/resources/{id}` | Delete resources (linked data may cascade) |
| POST | `/clubs/{id}/memberships` | Apply for club membership |
| POST | `/tournaments/{id}/registrations` | Register an individual tournament participant |
| POST | `/events/{id}/attendance` | Register for an approved club session |
| POST | `/bookings` | Request a future resource reservation (up to four hours) |
| GET | `/me/memberships` | List own memberships |
| DELETE | `/me/memberships/{id}` | Cancel own eligible memberships |
| GET | `/me/registrations` | List own registrations |
| DELETE | `/me/registrations/{id}` | Cancel own eligible registrations |
| GET | `/me/attendance` | List own attendance |
| DELETE | `/me/attendance/{id}` | Cancel own eligible attendance |
| GET | `/me/bookings` | List own bookings |
| DELETE | `/me/bookings/{id}` | Cancel own eligible bookings |
| GET | `/admin/memberships` | List memberships requests |
| PATCH | `/admin/memberships/{id}` | Review pending memberships |
| GET | `/admin/bookings` | List bookings requests |
| PATCH | `/admin/bookings/{id}` | Review pending bookings |
| GET | `/admin/attendance` | Student session registrations |
| PATCH | `/admin/attendance/{id}` | Mark attendance after a session starts |
| GET | `/admin/reports` | Club, tournament and booking aggregates |
| GET | `/admin/audit-logs` | Paginated administration audit history |
| GET | `/results` | Recorded match scores |
| GET | `/standings` | Club standings with win=3/draw=1/loss=0 |
| GET | `/clubs/{club_id}/community` | Approved-member club summary |
| GET | `/clubs/{club_id}/members` | Approved-member roster without contact details |
| GET | `/clubs/{club_id}/posts` | Visible club feed for approved members |
| POST | `/clubs/{club_id}/posts` | Share a plain-text post with your club |
| GET | `/clubs/{club_id}/posts/{post_id}` | Read one visible club post |
| PATCH | `/clubs/{club_id}/posts/{post_id}` | Edit your own post; hidden posts stay hidden |
| DELETE | `/clubs/{club_id}/posts/{post_id}` | Erase your post and its linked comments/reactions/reports |
| GET | `/clubs/{club_id}/posts/{post_id}/comments` | Read visible-post comments |
| POST | `/clubs/{club_id}/posts/{post_id}/comments` | Comment on a club post |
| DELETE | `/clubs/{club_id}/posts/{post_id}/comments/{comment_id}` | Remove own comment; administrators may moderate comments |
| PUT | `/clubs/{club_id}/posts/{post_id}/reaction` | Add one reaction idempotently |
| DELETE | `/clubs/{club_id}/posts/{post_id}/reaction` | Remove your reaction idempotently |
| POST | `/clubs/{club_id}/posts/{post_id}/reports` | Privately report a post once |
| GET | `/admin/community` | Admin-only post moderation queue |
| GET | `/admin/community/posts/{post_id}/reports` | Admin-only report details |
| PATCH | `/admin/community/posts/{post_id}` | Hide or restore a post and resolve reports |
