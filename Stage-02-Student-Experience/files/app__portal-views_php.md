# 🔍 Portal views: dashboard, club spaces and moderation

## 1 · File card

| Field | Value |
|---|---|
| Canonical source | [app/portal-views.php](../../app/portal-views.php) |
| Language / size | PHP · 49 physical lines · 21,739 bytes; many physical lines contain several template statements |
| Primary guide / contributor boundary | Stage 02 · Member B (TBD); existing baseline authorship is not reassigned |
| Secondary reviewers | Stage 01 for presentation/accessibility; Stage 03 for admin/moderation branches; Stage 04 for API/data/security |
| Inspected baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |
| Role | Renders student and admin portal fragments from current database state; it does not manufacture separate apps or authorize writes. |

## 2 · Why this file exists

Renders student and admin portal fragments from current database state; it does not manufacture separate apps or authorize writes. Removing it would break its callers rather than produce a simpler independent frontend. The canonical application has one implementation; this guide is a map, not a replacement source file.

## 3 · Prerequisites

Read [student vocabulary](../00-start-here.md), [two transports](../01-architecture.md) and [all student journeys](../04-guided-walkthroughs.md). A PHP variable starts `$`; arrays store values by keys such as `$post['body']`; `if/elseif` chooses a branch; `foreach` repeats markup. `<?=...?>` emits an expression; leaving PHP mode emits literal HTML. Those operations happen on the server, before the browser constructs its DOM.

## 4 · Inputs and dependencies

Included by `app/views.php` only for dashboard/admin/my_clubs/club/moderation/audit. Receives `$page`, `$current`, `$isAdmin` and `$clubView` from the entry/layout context. Uses helpers from views/UI/bootstrap and reads users, clubs, memberships, attendance, events, tournaments, registrations, resources/bookings via metrics, club_posts/comments/reactions/reports, audit_logs. `community_posts/summary` live in community.php. `.community-layout`, `.post-card`, `.club-tabs`, `.metrics-grid` link to the frontend CSS. Forms depend on UI `community_form`, actions.php and app.js mappings.

## 5 · Detailed source-order tour and coverage index

**Coverage navigation:**

- [Dashboard · personal overview](#dashboard--personal-overview)
- [Admin dashboard · shared Stage 03 feature](#admin-dashboard--shared-stage-03-feature)
- [My clubs](#my-clubs)
- [Club context and tab routing](#club-context-and-tab-routing)
- [Overview tab](#overview-tab)
- [Gate before private tabs](#gate-before-private-tabs)
- [Members tab](#members-tab)
- [Schedule tab](#schedule-tab)
- [Community feed setup and composer](#community-feed-setup-and-composer)
- [Each post and post actions](#each-post-and-post-actions)
- [Comments and their window](#comments-and-their-window)
- [Feed pagination and community aside](#feed-pagination-and-community-aside)
- [Moderation · shared Stage 03 feature](#moderation--shared-stage-03-feature)
- [Audit · shared Stage 03 feature](#audit--shared-stage-03-feature)

### Dashboard · personal overview

The first branch builds four metrics using the current user ID four times: approved club memberships, all attendance rows, pending/approved future bookings and tournament registrations. The “Session registrations” label is deliberate; it does not claim that all rows are attended. It then reads the newest five memberships, next three campus events and first open tournament with future deadline. Upcoming events are campus-wide, not automatically a personalized eligible schedule.

The intro escapes the first name, and date label uses server's configured timezone. Performance hero uses the supplied track image, fixed campus copy and club-discovery link. `metric()` connects real counts to My clubs/activity routes. Upcoming cards show event facts; memberships link approved accounts to community and other statuses to overview; tournament feature has an empty state. Latest-five attendance history is joined to events. Missing authenticator shows account-security banner.

### Admin dashboard · shared Stage 03 feature

Counts pending memberships, pending bookings, future events and noncompleted tournaments. Upcoming events cap at 4; recent audit activity caps at 5. Flagged content aggregates pending club reports, excludes deleted posts and shows top 3 by report count. Admin shortcuts loop the same `entities()` metadata used for CRUD. Stage 03 owns operation semantics and all admin modifications; this index accounts for the branch without granting Stage 02 exclusive file ownership.

### My clubs

JOIN memberships to clubs for current user, order by club name; one card per own relation. Status decides community vs overview destination, not authorization. Zero relations prompts discovery. A rejected membership remains visible until removed or reapplied; it is not an approved club.

### Club context and tab routing

Use the club loaded by index, convert ID to integer, fetch current membership, and compute allowed = admin OR approved exact-club membership. Tab defaults community and is constrained to overview/community/members/schedule; an invalid tab becomes overview. Summary reads approved member/visible post/future-event counts. Breadcrumb/banner render escaped club sport/name and status; nav has `aria-current` for selected tab.

### Overview tab

Shows description, coach, ground, member count/capacity, and Apply only if no relation or rejected. Other relations show their current badge. Overview is available to signed-in nonmembers; the outer page route still requires login. Community invitation links to gated content rather than promising access.

### Gate before private tabs

If not allowed, return a clear membership gate before executing members/schedule/feed branches. A nonmember can see club context and summary, but private post bodies and roster do not render. The independent service/API `club_access()` protects forged requests; do not rely on this presentation condition alone.

### Members tab

Query approved members of this club, selecting only ID/name/faculty, sorted by name, limited100. The visible message states the display cap and privacy boundary. Initials and names/faculty are escaped. No email/student ID/auth columns are selected. API members provides its own paginated collection.

### Schedule tab

Query future club events by start, cap 50. Each row shows icon/title/date/location and native `attend` form. Eligibility and duplication still checked by reserve. The private club tab differs from global public schedules in presentation/access, not the event registration service.

### Community feed setup and composer

Clamp feed_page to1..100000; request ten visible posts with offset `(page-1)*10`, current-user reaction flag included. Composer uses `community_form('community_post',clubId)`; a visually hidden but accessible label targets post-body textarea. Body max 5000, required; service repeats limits. Initials are not an external avatar service.

### Each post and post actions

Article ID is post-ID for DOM targeting. Author/time/body/sport/counts are output; body is `e()` escaped plain text. React action switches add/remove according to `reacted`. Comments count is separate from actual rendered comment list. Native `<details>` provides post options. Author sees Edit; author/admin sees Delete; all allowed members can report privately with reason. Buttons reflect policy, but service enforces identity/ownership again.

### Comments and their window

Fetch latest 20 comments descending, reverse array to display that window chronologically. Escape author/body. Own/admin comment removal uses fixed club/post/comment IDs in native form; Add comment max 2000. At exactly20 a notice says latest 20; this is not an all-comments HTML view. API can paginate separately.

### Feed pagination and community aside

Newer/older links are derived from current page and visible-post summary; posts are ordered by descending ID. Sidebar selects six approved member names/faculties, next future training event and static respectful-community advice. Avoid interpreting sidebar6 as total membership: summary count is separate. IDs and labels are escaped/fixed as appropriate.

### Moderation · shared Stage 03 feature

Status filter allowlist: reported/visible/hidden/all; invalid HTML filter defaults reported. All excludes deleted; reported requires unresolved report. Page size 20 and count derive pagination. Each item shows escaped body/report details and previous reason to admin, then status/reason decision form. Fixed allowlist makes concatenated filter safe; do not replace it with raw GET. Report reasons shown here must never leak into student feed/API responses. HTML all corresponds to omitted REST status, not a REST `status=all` value.

### Audit · shared Stage 03 feature

LEFT JOIN logs to optional actor, latest 100, fallback System label, escaped action/details/date. API supports pagination; this screen is a capped history view. No write controls here. There are no additional functions declared in this template and no page branch omitted from this coverage index.


## 6 · Real excerpts, read one statement at a time

```php
$feedPage=min(100000,max(1,(int)($_GET['feed_page']??1))); $posts=community_posts($clubId,(int)$current['id'],10,($feedPage-1)*10);?>
```
`??1` supplies a default; `(int)` converts the query value to an integer; max/min keep page bounded. Offset skips previous pages in blocks of 10. The user ID is session-derived, so reaction state belongs to this account. Note that this HTML conversion differs from stricter API pagination validation; do not describe them as identical wire contracts.

```php
$membership=one('SELECT status FROM memberships WHERE user_id=? AND club_id=?',[$current['id'],$clubId]);
```
This is an exact fragment within the club-context line. Both user and club are bound. The result may be null; the following `$allowed` condition accounts for that. A generic “is a member somewhere” query would weaken the boundary.


## 7 · Input → transformation → output

Authenticated context plus page/tab/feed filters → scoped/capped SELECTs and service summaries → escaped dashboard cards, club tabs, forms, private moderation or audit rows. Posts are rendered text, not injected rich HTML. The selected tab is a view state; approved membership/admin role is access state.

## 8 · Follow the connections

The shared entry chain is [public/index.php](../../public/index.php) → [layout](../../app/layout.php) → [views](../../app/views.php) → [portal views](../../app/portal-views.php). Browser mutations use [app.js](../../public/assets/app.js) → [public/api.php](../../public/api.php) → [API dispatch](../../app/api.php) → [actions](../../app/actions.php) → [models](../../app/models.php) or [community](../../app/community.php). Database relations are defined in [schema](../../database/schema.sql) and [community migration](../../database/migrations/20261017_club_communities.sql). [Source inventory](../02-source-inventory.md) links every cross-stage technical guide.

## 9 · Why this implementation?

The portal is a readable server-rendered composition around the shared services and CSS system. Display caps keep initial HTML bounded, with API pagination for deeper collection access; comment fetching per post is simple but can produce an N+1 query pattern on busy feeds. A future performance change should measure query counts and preserve latest-window ordering/privacy, rather than add an unrelated frontend framework.

## 10 · Security, correctness and accessibility

Keep the membership gate ahead of private queries and service access checks independently enforced. Treat report reasons as admin-private and name/faculty roster data as intentionally limited. `e()` must remain around body/name/faculty/detail output. Each repeated form needs correct labels/unique target IDs; details/summary offers native keyboard interaction. Never infer stronger attendance/eligibility from a metric label than its query proves. Counts may change between render and submit, so backend rules remain mandatory.

## 11 · Common mistakes and diagnosis

| Mistake | Symptom | Safe diagnosis |
|---|---|---|
| Gate after feed query/output | Private content leaks before warning | Check branch order, then inspect API independently |
| Label registrations as attended | False participation claim | Read status filter/COUNT query |
| Assume roster 100 is complete | Missing members on large clubs | Preserve cap notice and use paginated API |
| Reverse all comment order wrongly | Latest window appears backwards | Trace DESC LIMIT20 then array_reverse |
| Pass HTML all to API status | REST 422 despite working HTML filter | Omit REST filter for all nondeleted posts |
| Copy report reasons into student cards | Privacy leak | Separate moderation response fields/rendering |

## 12 · Run it through the application

Do not request `app/...php` over HTTP or run a template as a standalone CLI page: its required request context is absent. Complete [local setup](../../Assemble/README.md), use document root `public/`, and run these **suggested checks, not a claim of execution here** from repository root:

```bash
php -l app/portal-views.php
php bin/doctor.php
php -S localhost:8080 -t public public/router.php
```

Choose that origin only when `APP_URL` matches. On an existing instance use its existing port/project; do not start another conflicting server. Read [test isolation](../06-testing-and-debugging.md) before optional database suites.

## 13 · Debug without recording private state

Place a debugger breakpoint at the relevant branch/function and inspect integer target IDs, branch decisions, status and synthetic row counts. Never dump `$_POST`, `$_SESSION`, full user rows, cookies, keys, passwords, recovery codes or private report text. Network evidence should contain method/path/status rather than secret-bearing headers. A generic503 is a reason to inspect protected server logs, not to expose stack traces publicly.

## 14 · Small guided experiment

With a synthetic pending membership open the club overview then members tab. Predict which SQL-dependent private markup should be absent. After a lab administrator approves only that request, reload and compare. Use DOM inspection to show a missing private post, not only a hidden CSS element. Cleanup is own membership cancellation and removal of own lab content through supported controls; no schema reset or `$allowed=true` edit.

## 15 · Challenge ladder

- **Understand:** trace a visible action to its exact form fields and service/table; explain the current success and failure response before touching code.
- **Modify:** after reproducing a real usability issue, improve one student-facing message/empty state in the relevant view. Predict the result, preserve fields/checks and compare native/REST plus mobile.
- **Engineer:** reason about a changed permission or capacity between render and submit. Propose a focused regression assertion and a reviewed patch only if it exposes a real gap. Consult Stage 04 before changing a transaction or API contract.

Use [full lab setup/cleanup](../05-labs-and-exercises.md). Hints are separated in the stage answer key; these challenges have not been performed or credited to a student.

## 16 · Knowledge check

1. Does dashboard Session registrations equal attended sessions?
2. Why can a signed-in nonmember open overview but not members?
3. What is the difference between latest 20 comments in HTML and API pagination?

<details>
<summary>Answer key · read after predicting</summary>

1. No. The metric counts all own attendance rows, regardless of registered/attended status.
2. Overview precedes the private-tab gate; private branches require approved exact-club membership or admin. The entire club page still requires login.
3. HTML shows a capped newest window ordered for reading; paginated API can navigate a larger collection.

</details>

## 17 · Contribution boundaries

Name the function/page branch before editing and coordinate [shared-file review](../07-contribution-contract.md). Preserve the existing navy/lime and light themes, keyboard controls, data-derived counts, native form fallback, session identity, escaping, CSRF and authorization. Stage 03 reviews all admin/moderation changes; Stage 04 reviews SQL/API/locking/security. No work is assigned or credited merely because this file has a primary guide here.

## 18 · Current status and limitations

**VERIFIED BY INSPECTION:** the branch/function coverage and excerpts above describe the inspected baseline. **NOT RUN in this individual guide:** runtime/browser/database commands; use the central [execution register](../../Assemble/16-evidence-and-artifact-register.md) for actual current outcomes. **PLANNED:** learner exercises and future PRs. No public host or independent security certification is claimed. All major source sections are indexed above; deeper shared security/database primitives belong to the linked Stage 04 guides, not an alternate implementation here.

## 19 · Glossary

**Gate:** a condition before restricted content. **N+1:** fetching a list then another query for each row. **Tombstone:** retained ID/status after body erasure. **Pagination:** divide a collection into bounded pages. **aria-current:** tells assistive technology which navigation destination is selected.

## 20 · One-minute explanation to personalize

Point to a student dashboard metric and explain exactly which rows it counts. Follow a club tab through membership lookup, view gate, escaped post body and server-checked form. Distinguish student sections from shared admin moderation/audit, and explain the display caps honestly.

This is a study prompt, not a first-person authorship claim. Replace it with your own explanation after understanding, implementing/reviewing and testing real work. Record the evidence in [your unfilled record](../09-completion-evidence.md).
