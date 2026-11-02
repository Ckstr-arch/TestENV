# 🔍 PHP views: public pages, accounts and activity

## 1 · File card

| Field | Value |
|---|---|
| Canonical source | [app/views.php](../../app/views.php) |
| Language / size | PHP · 75 physical lines · 25,344 bytes; many physical lines contain several template statements |
| Primary guide / contributor boundary | Stage 02 · Member B (TBD); existing baseline authorship is not reassigned |
| Secondary reviewers | Stage 01 for presentation/accessibility; Stage 03 for admin/moderation branches; Stage 04 for API/data/security |
| Inspected baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |
| Role | Produces page-specific HTML and dispatches portal sections, using real database reads and form contracts. |

## 2 · Why this file exists

Produces page-specific HTML and dispatches portal sections, using real database reads and form contracts. Removing it would break its callers rather than produce a simpler independent frontend. The canonical application has one implementation; this guide is a map, not a replacement source file.

## 3 · Prerequisites

Read [student vocabulary](../00-start-here.md), [two transports](../01-architecture.md) and [all student journeys](../04-guided-walkthroughs.md). A PHP variable starts `$`; arrays store values by keys such as `$post['body']`; `if/elseif` chooses a branch; `foreach` repeats markup. `<?=...?>` emits an expression; leaving PHP mode emits literal HTML. Those operations happen on the server, before the browser constructs its DOM.

## 4 · Inputs and dependencies

Requires context from index/layout: `$page`, `$current`, `$error`, `$isAdmin` where used; helpers from bootstrap/actions/models/UI. It uses `rows/one/query/e/csrf_field/when/club_url/icon`, SQL tables clubs/users/tournaments/events/resources/memberships/registrations/attendance/bookings/recovery_codes/audit_logs and private session enrollment/display state. CSS class names such as `.hero`, `.club-card`, `.auth-layout`, `.activity` are contracts with `public/assets/app.css`. Form action values are mapped in `app.js` and dispatched in actions.php.

## 5 · Detailed source-order tour and coverage index

**Coverage navigation:**

- [Helpers · before page dispatch](#helpers--before-page-dispatch)
- [Portal delegation](#portal-delegation)
- [Home](#home)
- [Login / register / verify](#login--register--verify)
- [Clubs](#clubs)
- [Tournaments](#tournaments)
- [Schedule](#schedule)
- [Results](#results)
- [Facilities](#facilities)
- [Activity](#activity)
- [Security](#security)
- [Manage entities · shared admin region](#manage-entities--shared-admin-region)
- [Approvals · shared admin region](#approvals--shared-admin-region)
- [Reports · shared admin region](#reports--shared-admin-region)
- [Fallback](#fallback)

### Helpers · before page dispatch

| Symbol | Inputs → output | Why it matters |
|---|---|---|
| `heading(tag,title,subtitle)` | Text → escaped page-heading/eyebrow/h1/p | Maintains visual and semantic page identity |
| `action_button(action,id,label,extra)` | Named command/record/extra fields → native POST form | Hidden action/id/CSRF establish the fallback and JS mapping contract |
| `empty_state(message)` | Text → escaped empty-state div | Explains zero records instead of implying failure |
| `badge(value)` | State string → escaped text and class | A view of state, never authority to change it |
| `input(name,label,type,value,required)` | Field description → labeled input | Escapes values; password gets length/autocomplete attributes |

### Portal delegation

The first branch recognizes dashboard/admin/my_clubs/club/moderation/audit and `require`s `portal-views.php`. It runs before the later public branches; do not add a duplicate dashboard further down and expect it to execute.

### Home

Counts come from SQL subqueries for clubs, student users, tournaments and facility resources. The next fixture is an actual future match joined to its club. The hero's marketing text is static; the club loop renders first three clubs ordered by ID; `.stats-strip` renders database counts; `.feature-banner` has a real upcoming-match fallback. `home.html` is not the production homepage. Stage 01 explains the DOM/CSS in depth.

### Login / register / verify

One auth layout switches fields by `$page`: registration adds name, student ID and faculty; login/register show email/password; verify shows only one-time/recovery `code`. Hidden action equals the page. The shared `input()` helper uses `autocomplete="current-password"` for password fields, including registration: document reality rather than claim a separate new-password helper exists. The UI is an instruction surface; Stage 04 services decide password/ID validity and second-factor state.

### Clubs

GET `q/sport` reads and binds search/filter values. The query counts approved memberships and returns every matching club ordered by name. Cards show description, coach, ground and capacity; View club links to signed-in overview, Apply invokes join. A missing search result uses `empty_state`.

### Tournaments

Optional status GET filter; query adds registration count. Cards show dates, capacity and deadline. Register is displayed only for open tournaments before deadline; other cards show closed. Capacity can change after render; `reserve()` checks again.

### Schedule

Optional kind filter, events joined with club name, descending starts_at. Rows show date, type, club/opponent, time/location. Future rows provide `attend`; past rows finished. Student participation still requires server-side approval in the event's own club.

### Results

Read scored matches (`kind=match` and home_score not null), join clubs and optionally tournament labels. Separate aggregate query counts played/wins/draws/losses and points3/1/0 grouped by club. Empty match list is explicit. Table describes club records across recorded matches, not a bracket or sport-specific ranking engine.

### Facilities

List resources by kind/name; each card has booking fields resource_id/starts_at/ends_at/purpose. The four-hour note does not enforce the rule; `perform_action('book')` does. No resources produces an empty state. Equipment is one unit per resource row.

### Activity

`$sections` maps memberships/registrations/attendance/bookings to own-user JOIN queries. Cards show relation labels/status/times and optional club link. Conditional cancel controls hide ineligible completed/past booking/participation actions. Server ownership/time checks still apply if a caller forges a form. Missing TOTP shows enrollment banner. These cards contain the complete query results; dashboard previews are separately limited.

### Security

Show enabled/disabled state from `totp_secret` presence, never encrypted value. Native recovery display reads then unsets session display codes; temporary valid setup state shows a manual enrollment key privately. Enable form requires password/code; expired/no setup shows setup action. Enabled state shows remaining recovery-code count and a details-contained disable form requiring password/code. Do not capture these values as screenshots.

### Manage entities · shared admin region

`manage_` derives the entity from an already allowlisted page, obtains `entities()` form metadata and optional edit row. Field types select options/relations/textarea/number/datetime inputs; failed submissions reuse posted values before edit defaults. List records with edit link and confirmed delete form. This entire branch is feature-owned by Stage 03, with Stage 04 validation/SQL review. See [Stage 03 walkthroughs](../../Stage-03-Admin-Operations/04-guided-walkthroughs.md).

### Approvals · shared admin region

Query pending memberships joined with student/club, and pending bookings joined with student/resource. Emit `review` controls with entity/id/status. Pending count is not auto-approval. Stage 03 explains `review_request()` capacity/locks and why decision requires an admin.

### Reports · shared admin region

Club aggregate subqueries count approved members, attendance registrations and attended rows; a print button triggers existing JS print behavior. Attendance table offers marking registered rows; the service prevents future-session marking. Tournament roster joins registrations/users/tournaments. Recent audit table is capped at 50. Results link is separate. Stage 03 owns operations/report correctness.

### Fallback

Unknown page is rendered with a 404 heading; public entrypoint sets the actual HTTP404. A heading text alone is not a status code. No page/function region in this file is omitted from this index.


## 6 · Real excerpts, read one statement at a time

```php
function empty_state(string $message): void { echo '<div class="empty">'.e($message).'</div>'; }
```
`function` declares reusable server behavior; its parameter is text; `void` promises no returned value. `echo` emits HTML, `.` concatenates strings, and `e()` makes a message text rather than executable markup. A PHP function may emit output without returning it.

```php
 'memberships'=>['Club memberships','SELECT m.*,c.name label FROM memberships m JOIN clubs c ON c.id=m.club_id WHERE m.user_id=?'],
```
This is one real array entry, not a complete standalone PHP program. `memberships` is the section key; its value pairs a heading with SQL. `m.*` returns relation columns; `c.name label` adds a display name; JOIN follows club_id; `?` receives current user's ID through prepared execution. Without the WHERE predicate, the view could disclose other students' relationships.


## 7 · Input → transformation → output

GET page/filter parameters and authenticated context → selected branch → parameterized SELECTs → escaped HTML/forms. Native writes happen earlier in index/actions and redirect before these views render on success. The views do not accept posted `user_id` or hash a password. They display service state; changes belong to service functions.

## 8 · Follow the connections

The shared entry chain is [public/index.php](../../public/index.php) → [layout](../../app/layout.php) → [views](../../app/views.php) → [portal views](../../app/portal-views.php). Browser mutations use [app.js](../../public/assets/app.js) → [public/api.php](../../public/api.php) → [API dispatch](../../app/api.php) → [actions](../../app/actions.php) → [models](../../app/models.php) or [community](../../app/community.php). Database relations are defined in [schema](../../database/schema.sql) and [community migration](../../database/migrations/20261017_club_communities.sql). [Source inventory](../02-source-inventory.md) links every cross-stage technical guide.

## 9 · Why this implementation?

A few shared helpers reduce repeated field/form markup; metadata generates admin forms from one entity definition. Server rendering offers useful HTML without JavaScript, while the same forms can enhance to REST. Large minified mixed PHP/HTML lines make maintenance harder, so identify semantic branches rather than treating physical line count as complexity. Splitting runtime files solely for ownership is outside this task.

## 10 · Security, correctness and accessibility

Escape user-visible strings and attribute values with `e()`. Keep CSRF hidden fields in POST forms, native labels, heading order and actual button/link semantics. Password values must never be repopulated or printed in logs. A visible Apply/Register/Cancel control is a hint, not proof of authorization. Protect role-gated pages in index and mutation services even if the view hides controls. Admin query identifiers are safe only because page/entity allowlists remain intact.

## 11 · Common mistakes and diagnosis

| Mistake | Symptom | Safe diagnosis |
|---|---|---|
| Change an input name only | Server says field missing | Compare name with actions/API allowlist, then coordinate contract |
| Remove `e()` for prettier text | User markup becomes HTML | Inspect DOM with synthetic escaped text; restore output escaping |
| Treat session count as attended | Misleading dashboard/report | Read attendance.status predicate; rename accurately if approved |
| Add duplicate portal branch | New code never runs | Follow delegation order at file top |
| Omit CSRF hidden input | Native419 | Restore helper; do not disable check |
| Alter table name from untrusted GET | SQL injection risk | Preserve page/entity allowlists; no raw table input |

## 12 · Run it through the application

Do not request `app/...php` over HTTP or run a template as a standalone CLI page: its required request context is absent. Complete [local setup](../../Assemble/README.md), use document root `public/`, and run these **suggested checks, not a claim of execution here** from repository root:

```bash
php -l app/views.php
php bin/doctor.php
php -S localhost:8080 -t public public/router.php
```

Choose that origin only when `APP_URL` matches. On an existing instance use its existing port/project; do not start another conflicting server. Read [test isolation](../06-testing-and-debugging.md) before optional database suites.

## 13 · Debug without recording private state

Place a debugger breakpoint at the relevant branch/function and inspect integer target IDs, branch decisions, status and synthetic row counts. Never dump `$_POST`, `$_SESSION`, full user rows, cookies, keys, passwords, recovery codes or private report text. Network evidence should contain method/path/status rather than secret-bearing headers. A generic503 is a reason to inspect protected server logs, not to expose stack traces publicly.

## 14 · Small guided experiment

Read the `activity` membership query and predict why only your relationships appear. On a disposable local account apply to one spare-capacity club; inspect the activity card and pending badge. No source change is required. Cancel only that membership to clean up; do not reset the database. For an optional markup experiment, modify a heading temporarily in browser DevTools (not source), observe layout, then reload to revert.

## 15 · Challenge ladder

- **Understand:** trace a visible action to its exact form fields and service/table; explain the current success and failure response before touching code.
- **Modify:** after reproducing a real usability issue, improve one student-facing message/empty state in the relevant view. Predict the result, preserve fields/checks and compare native/REST plus mobile.
- **Engineer:** reason about a changed permission or capacity between render and submit. Propose a focused regression assertion and a reviewed patch only if it exposes a real gap. Consult Stage 04 before changing a transaction or API contract.

Use [full lab setup/cleanup](../05-labs-and-exercises.md). Hints are separated in the stage answer key; these challenges have not been performed or credited to a student.

## 16 · Knowledge check

1. Why can a page show Register while the eventual request still fails?
2. Which statement scopes activity to the signed-in user?
3. Why would running this file directly not be a valid page test?

<details>
<summary>Answer key · read after predicting</summary>

1. Capacity/deadline/permission may change after rendering, and server validation remains authoritative.
2. Each own activity query binds `$current['id']` to its `user_id=?` condition.
3. Routing, authenticated variables, helpers and the layout are supplied by the entrypoint include chain.

</details>

## 17 · Contribution boundaries

Name the function/page branch before editing and coordinate [shared-file review](../07-contribution-contract.md). Preserve the existing navy/lime and light themes, keyboard controls, data-derived counts, native form fallback, session identity, escaping, CSRF and authorization. Stage 03 reviews all admin/moderation changes; Stage 04 reviews SQL/API/locking/security. No work is assigned or credited merely because this file has a primary guide here.

## 18 · Current status and limitations

**VERIFIED BY INSPECTION:** the branch/function coverage and excerpts above describe the inspected baseline. **NOT RUN in this individual guide:** runtime/browser/database commands; use the central [execution register](../../Assemble/16-evidence-and-artifact-register.md) for actual current outcomes. **PLANNED:** learner exercises and future PRs. No public host or independent security certification is claimed. All major source sections are indexed above; deeper shared security/database primitives belong to the linked Stage 04 guides, not an alternate implementation here.

## 19 · Glossary

**Template:** source producing markup. **Alias:** a SQL result name such as label. **Empty state:** explanation of no matching rows. **Escaping:** encoding text for HTML output. **Allowlist:** explicit approved values, not user-selected arbitrary identifiers.

## 20 · One-minute explanation to personalize

Explain how the clubs GET form changes a bound SELECT, how PHP loops emit cards, and how `action_button` bridges to the shared mutation services. Point out the difference between actual SQL-backed counts and static hero copy. Name the admin branches as shared responsibilities rather than claim to have written them.

This is a study prompt, not a first-person authorship claim. Replace it with your own explanation after understanding, implementing/reviewing and testing real work. Record the evidence in [your unfilled record](../09-completion-evidence.md).
