# 04 · Operate the real application, then trace its code

[Roadmap](03-learning-roadmap.md) → [Labs](05-labs-and-exercises.md)

All examples are **instructions for future synthetic local practice**, not claims that a human has performed them. Base API prefix is `/api/v1`. Form input names below are actual source names.

## Admin navigation and page coverage

| Sidebar group / destination | Page route | Rendering branch | Behavior |
|---|---|---|---|
| Workspace · Overview | `/?page=admin` | portal `admin` | Real pending/future/status counts; recent audit; report preview |
| Community · Clubs | `/?page=manage_clubs` | views `manage_*` | Club descriptor CRUD |
| Community · Membership & booking approvals | `/?page=approvals` | views `approvals` | Two pending queues |
| Community · Community moderation | `/?page=moderation` | portal `moderation` | Reported/visible/hidden/all; private decisions |
| Competitions · Training & matches | `/?page=manage_events` | views `manage_*` | Training and match fixtures, optional tournament |
| Competitions · Tournaments | `/?page=manage_tournaments` | views `manage_*` | Dates/deadlines/status/capacity |
| Competitions · Results | `/?page=results` | views `results` | Public derived scores/club standings |
| Resources · Facilities & equipment | `/?page=manage_resources` | views `manage_*` | One reservable unit per row |
| Insights · Reports & attendance | `/?page=reports` | views `reports` | Counts/rosters/attendance/print |
| Insights · Audit history | `/?page=audit` | portal `audit` | Latest100 events; API pagination |

The profile's Account security is shared and can enroll admin 2FA. There is no separate captain role, email approval notification system or automatic bracket generator.

## CRUD route matrix

For each entityE=`clubs|tournaments|events|resources`, the same contract applies:

| Operation | UI/read route | Enhanced HTTP | Native form/action | Service and SQL | Expected failure/test evidence |
|---|---|---|---|---|---|
| Create | `/?page=manage_E` | `POST /admin/E` | POST current page; `action=save, entity=E, id=0` + all fields +csrf | `save_entity(E,0)` → INSERT E, audit save_E |422 invalid fields; integration CRUD groups and API payload loop |
| Read list | `/?page=manage_E` | API available `GET /admin/E` | GET | View `SELECT * FROM E ORDER BY id DESC`; API `paginated('*',E)` |401 guest/403 student REST; HTML login redirect/403 |
| Read edit | `/?page=manage_E&edit=ID` | API available `GET /admin/E/ID` | GET | Prepared ID lookup | REST missing404; HTML edit lookup missing falls back create-style form |
| Update | edit page then Save | `PUT /admin/E/ID` | POST `save,entity=E,id=ID` + full fields | `save_entity(E,ID)` → UPDATE; audit |422 invalid/unknown fields; API replaces loop |
| Delete | management record card | `DELETE /admin/E/ID` with `{}` | POST `delete,entity=E,id=ID` | DELETE E + audit; FK cascades | REST404 missing; native may report success missing; fixtures verify removal |

`E` is notation in this table, never a literal route. Values use prepared parameters; identifiers come from `entities()`. API `PUT` supplies the complete entity record fields, not just one changed field. POST create returns201 with Location, replacement/deletion200. Native success goes303 then a browser-followed GET; tests may observe final200.

## CRUD field and validation matrix

| Entity and actual fields | Validation in source | Related rows / visible outputs |
|---|---|---|
| **clubs**: `name,sport,description,coach,location,capacity` | Required trimmed text; name<=120; description<=5000; sport/coach/location generic<=100; capacity positive integer<=10000 | Clubs browse/search; membership capacity; events/community |
| **tournaments**: `name,sport,description,location,starts_at,ends_at,registration_deadline,capacity,status` | name<=120 in PHP although SQL 140; ordinary text<=100; description<=5000; strict datetime-local; end>start; deadline<=start; capacity1..10000; status open/ongoing/completed | Tournament list/registrations; optional event linkage; roster/report |
| **events**: `title,club_id,tournament_id,kind,location,starts_at,ends_at,opponent,home_score,away_score` | title/location<=100 in PHP (SQL title140/location120); existing club required; tournament optionalNULL/existing; kind training/match; end>start; opponent blank or<=120; scores both blank or integers 0..100000; scores require match+nonempty opponent | Public/club schedule; attendance; results/standings |
| **resources**: `name,kind,description,location` | name<=120; kind facility/equipment; description<=5000; location<=100 | Facilities page and booking requests; one resource row is one reservable unit |

> [!IMPORTANT]
> Source and schema limits are not perfectly aligned. `sport` is VARCHAR60 but generic PHP text accepts100; invalid long values may fail at SQL depending on SQL mode. Deadline equality is accepted despite “before” wording. Capacity can be reduced below existing registrations/members without a save guard. Fixtures are not checked against tournament dates/sport and scored events need not be in the past. Document these as baseline limitations; do not silently claim stricter validation or change the runtime while writing guides.

## Walkthrough1 · Club CRUD and a membership decision

1. Create a synthetic club using all six fields. `save_entity()` inserts it; note the returnedID. Verify it appears in public Clubs and management list.
2. Edit coach/location or capacity. The descriptor builds the same named controls, and the update preserves identity/relationships.
3. A disposable student applies through `action=join,id=clubID` orPOST `/clubs/clubID/memberships`. `reserve('memberships',...)` locks club, checks existing request and approved count, creates pending (or reopens rejected as pending).
4. Admin opens Approvals, chooses review with **membershipID**, not clubID. PATCH `/admin/memberships/membershipID` body `{"status":"approved"}` shares `review_request()` with native review.
5. Under a club lock, count approved; reread pending request under lock; update+audit+commit. On failure full/stale→422 and rollback.
6. Student refreshes club: approved membership unlocks private community/members/schedule. Rejected members may reapply; approval is not an email notification.
7. Do not delete a team club to undo this demonstration. Only remove a newly-created isolated fixture/club after checking the [cascade matrix](#delete-impact-matrix).

**Source chain:** [views](../app/views.php) → [app.js](../public/assets/app.js) or [index](../public/index.php) → [API](../app/api.php)/[actions](../app/actions.php) → [models](../app/models.php) → memberships/clubs/audit. **Tests:** `Admin approves membership`, `REST membership approval`, `Student cannot submit admin mutation` and community gating tests. **Owner:**03 decision,02 student effect,04 security/locking.

## Walkthrough2 · Tournament, fixture, result and enrollment

1. Create a synthetic future tournament. Inputs are strict `YYYY-MM-DDTHH:MM`; choose end after start and registration_deadline before start. Status=open and capacity positive.
2. Student registration creates one `registrations(user_id,tournament_id)` row after parent lock, deadline/status/capacity/duplicate checks. Tournament enrollment is individual; it does not populate every event's attendance.
3. Create a match event with required `club_id`, optional tournament, opponent, interval/location and either both scores blank or both given. `events.tournament_id` is only a nullable relationship, not a generated bracket.
4. Update paired scores through event Save. `home_score=0` is a valid zero, not missing. The actual action is `save` on events, not `record_score`.
5. Results shows the score card. Club standings aggregate **recorded club matches** using win3/draw1/loss0. The opponent string is not a foreign key, and no mirrored opponent standings row is inferred. Public HTML standings are across clubs; API supports a tournament_id filter.
6. Reports reads tournament participants from registrations; it does not provide a team-assignment admin workflow. To cancel an eligible entry, the owning student uses their own participation control/API; admin deletes a tournament only with full awareness of effects.
7. Test update then synthetic deletion of the fixture and tournament when the exercise plan calls for it. Deleting tournament preserves its event rows with tournament_id=NULL but removes registrations.

**Tests:** native `Admin creates tournament`, updates/deletes; creates scored fixture/updates scores/`Recorded scores visible on results`; API four-entity create/read/replace/delete loop. **Failure states:** invalid dates422; unknown relationship422; one missing score422; unknown API field422; duplicate enrollment422. **Owners:**03 CRUD/results,02 enrollment,04 data rules.

## Walkthrough3 · Resource booking and collisions

1. Admin creates a disposable resource with name/kind/description/location. “equipment” represents one reservable row, not an available stock count.
2. Student requests future starts_at/ends_at/purpose/resource_id. `perform_action('book')` requires end>start, duration<=4hours and a future start.
3. It locks the resource parent row, then checks existing **pending or approved** intervals: `existing.starts_at < requested.end AND existing.ends_at > requested.start`. Adjacent bookings are allowed: `[10:00,11:00)` and `[11:00,12:00)` do not overlap. Pending requests already reserve the slot, so normal submission prevents an overlapping pending pair.
4. Admin reviews bookingID. Approval additionally checks other **approved** intervals under the resource lock and pending reread. Reject frees the interval for future submissions. Review does not silently reschedule a request.
5. Student can cancel an owned pending/approved future booking; cancellation keeps history with status=cancelled. Rejected/cancelled or started bookings cannot be cancelled again.
6. Inspect the student's participation record and admin pending queue, then cancel only your synthetic future booking if cleanup is needed. Do not delete the resource merely to “clear its calendar”; that cascades all bookings.

The parent lock is needed because checking “no overlap” in an ordinary SELECT then inserting allows two requests to both observe an empty interval. There is no standalone SQL exclusion constraint guaranteeing interval uniqueness here; all writers must obey the resource-lock protocol. **Tests:** native/API overlapping booking rejected; booking approval; own cancellation and completed/stale rejection cases. A true parallel booking collision exercise is a proposal unless actual concurrency evidence is recorded; the implemented concurrency regression suite focuses on community deletion.

## Walkthrough4 · Attendance and reports

Event registration inserts `attendance.status='registered'`; tournament registration inserts a different table. Once its event has started, admin POST `action=attendance,id=attendanceID` orPATCH `/admin/attendance/attendanceID` `{status:attended}` sets attended and audits.

The rendered report includes:

- approved memberships per club;
- all session registration rows per club, **including attended rows**;
- attended subset;
- tournament participants and their faculty;
- recent50 audit entries.

A student registering for two sessions contributes two registration rows, not one unique person. NULL club activity becomes zero from COUNT. Independent subqueries avoid accidental multiplication from joining several one-to-many relationships simultaneously. The REST report returns clubs, tournament registration counts and booking counts by status; its JSON shape is different from the rendered report.

Read-only report output does not certify performance. “Attended” is a recorded administrative fact, not automatically captured presence. Print/savePDF is browser print (`window.print()`); no backend PDF export is implemented. **Tests:** future attendance rejected, completed session attendance recorded, admin reports GET, browser report layouts. **Limits:** no absent state/reversal UI, no unique-athlete aggregate, HTML attendance list unpaginated.

## Walkthrough5 · Private moderation and audit

A member reports a visible post with reason 1..500. Reports are private and unique per member/post; duplicate409. Admin queue defaults unresolved; GET report details requires admin. Decide hidden/visible with nonempty reason → `moderate_post()` → direct parent lock → status+reason, reports resolved, audit →commit. Hidden author's edit remains hidden. Restoring makes it visible. Deletion erases body, comments/reactions/reports and leaves a tombstone; restore then 404.

Audit history shows latest 100, REST paginates. Action details identify post/status, not full private report text. Audit is valuable operational evidence but not tamper-proof external logging. **Tests:** member queue/decision/report-details403; hide/restore; author edit hidden; deleted restore404; audit count; deletion race. **Owners:**03 decision/UI,02 club behavior,04 transaction/security.

## Delete impact matrix

| Deleted parent | Database impact actually defined | Operational implication |
|---|---|---|
| club | memberships, events, club_posts CASCADE; events' attendance and posts' comments/reactions/reports cascade transitively | Club history/community/participation can disappear |
| tournament | registrations CASCADE; events.tournament_id SET NULL | Fixtures/scores survive but lose tournament association |
| event | attendance CASCADE | Session participation rows disappear |
| resource | bookings CASCADE | Reservation history disappears |
| community post via service | post retained deleted; body/reason cleared; comments/reactions/reports explicitly deleted | Irreversible content removal, unlike Hide |

Deletion is not an archive policy. The confirmation warns about linked data; future archival/retention work needs an agreed schema/behavior design. Do not invent soft deletion for core entities.

## Status codes and rejected manual requests

| Status | Concrete meaning here |
|---|---|
|401 | REST guest accessing admin; invalid/expired login challenge |
|403 | Signed-in student accessing admin; invalid REST CSRF or foreignOrigin |
|404 | Missing REST admin record; deleted community post; record not owned in personal cancellation |
|409 | Duplicate report or ineligible personal cancellation; some DB conflicts map409 |
|422 | Full club, stale review, invalid date/score/fields, future attendance |
|419 | Native form CSRF expired |

On a disposable local student account, use DevTools with that student's own fresh session/CSRF to attempt `GET /api/v1/admin/reports`; expected403. Do not paste cookie/token values into a report. Existing tests also send a student admin-write request and assert403 before the SQL mutation. Never use a real service/admin password or disable security to demonstrate this boundary.
