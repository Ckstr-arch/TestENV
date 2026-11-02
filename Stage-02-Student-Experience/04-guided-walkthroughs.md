# 04 · Eight real student journeys

All REST paths below start `/api/v1`. Native forms use `POST` to the current `/?page=...` URL, include `csrf` and the named `action`, and enter `handle_action()`. Enhanced forms remove routing-only fields, send JSON plus `X-CSRF-Token`, retain cookies and enter `api_dispatch()`. Both reach the existing service; JavaScript does not authorize the action. Query/row behavior below is **VERIFIED BY INSPECTION**, not a claim these steps were executed this session; use the [test record](../Assemble/16-evidence-and-artifact-register.md) for current results.

## 1 · Register a student account

**UI:** `/?page=register`. Actual fields: `name`, `student_id`, `faculty`, `email`, `password`, hidden `action=register` and `csrf`. The select offers Computing, Business, Engineering and Science; the server checks faculty as bounded nonempty text rather than enforcing those four values. Recognize this difference before changing either layer.

1. `app/views.php` calls `input()` and emits the registration form. `required`, email type and password length attributes help the browser, but can be bypassed.
2. `app.js` maps `register` to `POST /auth/register`; native POST runs `perform_action('register')` after CSRF.
3. The service trims name/email/student ID/faculty, lowercases email, validates email and the ID regex, requires password length 12–128 bytes, throttles registration and checks duplicate email or student ID.
4. `password_hash(..., PASSWORD_ARGON2ID)` creates a one-way password hash. `INSERT INTO users(...)` does not accept a role; the schema default is student. REST's field allowlist rejects an added `role` field.
5. Success: a user row and login destination; REST 201 with ID/message/redirect; native flash plus 303. Registration does not sign in the new account.

Failure: invalid text/ID/password or existing identity returns validation failure; REST role mass assignment is rejected. Do not add an admin checkbox. Tests: `tests/api.py` assertions “Role mass assignment rejected”, registration/duplicate cases; `tests/integration.py` native signup; `tests/browser.py` real registration form. Owner: Stage 02 UI; Stage 04 validation/schema.

## 2 · Password, session and optional second factor

**UI:** `/?page=login` → possibly `/?page=verify` → dashboard/admin. Login fields `email,password`; verify field `code`. REST `POST /auth/login` and `POST /auth/2fa`. Tables: `users`, `login_attempts`, `recovery_codes`, `audit_logs`.

1. `perform_action('login')` throttles by identity/IP scope and verifies password against the stored hash (with a dummy fallback for absent users). A valid old hash can be rehashed to Argon2id.
2. Without an enrolled authenticator, `finish_login()` rotates the session/token, records login state and chooses the role destination.
3. With 2FA, the password step rotates the session and stores `pending_user` with a five-minute `pending_until`. It does **not** create a full `user_id` login. REST includes `requires_2fa`; UI redirects to verify.
4. The verify action checks expiry, throttles attempts, uses `verify_second_factor()` and then calls `finish_login()`. The code may be a current authenticator code or an unused recovery code.
5. Incorrect/replayed code fails; missing/expired challenge requires login again. The page router also refuses an uninitialized verify page.

**Enrollment:** `/?page=security`, `setup_2fa` → `POST /me/2fa/setup` with `{}` creates temporary secret state; `enable_2fa` → `POST /me/2fa` with `password,code` verifies ownership and possession before storing encrypted secret and eight hashed recovery codes. JSON recovery codes are shown in a one-time dialog; native output consumes session-held display codes once. Disable is `DELETE /me/2fa` with password and second factor, not an unauthenticated recovery shortcut.

Never record credentials or enrollment/code screens. Test an account you created for the lab, not a known production identity. Tests: `tests/security.php` primitives; `tests/api.py` pending access, replay, recovery/enrollment; `tests/browser.py` real setup dialog and disable. Stage 02 owns instructions; Stage 04 owns security behavior.

## 3 · Discover, apply and enter a club

**UI:** `/?page=clubs&q=...&sport=...` → `/?page=club&club_id=ID&tab=overview` → community. Discovery GET form fields: `page=clubs,q,sport`. `q` becomes bound `%query%` values for name/sport; `sport` is an optional exact filter. Parameter binding makes query data stay data; SQL wildcard characters still have LIKE search meaning.

1. Public cards show approved member counts. Apply uses `action=join,id=CLUB_ID,csrf`.
2. REST: `POST /clubs/{id}/memberships` with `{}`. `api_dispatch()` fixes the URL ID into `$_POST`; session identity supplies the user, not a submitted user ID.
3. `perform_action('join') → reserve('memberships',id,uid)`. The club row is locked while capacity, duplicate/rejected status and pending insert/upsert are evaluated.
4. New request is `pending`; already pending/approved is rejected; rejected may reapply. Admin `PATCH /admin/memberships/{membership_id}` with `status=approved|rejected` calls `review_request()` and rechecks capacity/state under locks.

**INSPECTION RISK — not reproduced or tested in this task:** `review_request()` performs an initial nonlocking read before waiting on the parent lock. Under REPEATABLE READ, later nonlocking capacity/overlap reads may use an earlier snapshot. The final locked request reread protects the same request's pending status; parent-lock intent alone does not prove capacity safety across different requests. See [the isolation analysis and planned regression](../Stage-04-Backend-Database-Security/sql-and-transactions.md#isolation-sensitive-review-path-inspection-risk-not-reproduced).
5. `my_clubs` and dashboard show status. `portal-views.php` permits private tabs only for admin or approved membership. `club_access()` independently rechecks this on the API/service path.
6. Leaving via activity cancellation deletes the membership record. The next community request loses access; leaving does not promise erasure of every previous contribution.

Success: pending row → approved row → private feed/roster/schedule. Failures: unknown club, capacity full, duplicate request, anonymous login required, pending club API 403. The HTML gate can return a friendly page with HTTP 200 while API refuses private content. Tests: `tests/api.py` “REST membership application”/“Duplicate membership rejected”; `tests/community.py` pending/scoped/revoked membership cases; browser approval flow. Owner: Stage 02; Stage 03 decisions; Stage 04 reserve/locks.

## 4 · Post, comment, react and report privately

**UI:** `/?page=club&club_id=ID&tab=community`. Forms come from `community_form()` and include `club_id`, optional `post_id/comment_id`, `action`, `csrf`. Real text fields are `body` (post/comment) or `reason` (report).

| Interaction | REST method/path | Authoritative branch | Rows affected |
|---|---|---|---|
| Feed | GET `/clubs/{club}/posts` | community_api + community_posts | Read visible posts and counts |
| Post | POST `/clubs/{club}/posts` `{body}` | community_post action | Insert club_posts; audit |
| Edit own | PATCH `/clubs/{club}/posts/{post}` `{body}` | community_edit | Update body; status retained |
| Delete own/admin | DELETE same `{}` | community_delete | Erase post body, tombstone status; delete its comments/reactions/reports |
| Add comment | POST `.../{post}/comments` `{body}` | community_comment | Insert club_comments |
| Remove own/admin comment | DELETE `.../{post}/comments/{comment}` `{}` | community_comment_delete | Delete matching comment after ownership check |
| React/unreact | PUT/DELETE `.../{post}/reaction` `{}` | community_react/unreact | Unique reaction insert-ignore or own delete |
| Report | POST `.../{post}/reports` `{reason}` | community_report | Insert one private report per member/post |

Every write enters `community_action()` and a READ COMMITTED transaction. It checks membership. Actions on an existing post lock that parent post before dependent changes; creating a new post checks club access and inserts the new row, so there is no existing post to lock. Post text 1–5000 characters; comments 1–2000; reasons 1–500; service `text()` rejects empty/overlong values even if the browser maxlength is altered. Posts are escaped plain text, not trusted HTML.

Creates return REST 201; edits/reactions/deletions 200. The browser follows community redirect metadata; native path flashes and redirects. Repeated reaction is idempotent: one user and post reaction remains; repeated report returns 409. Non-author edit is 403; hidden/deleted post reads are 404; hidden edits cannot restore visibility. Only Stage 03's admin decision can restore hidden content. Deleted tombstones cannot be restored.

The feed displays ten posts/page, latest twenty comments (queried newest then reversed for display), sidebar six members and next training. Roster API omits email/student ID/auth data; reports stay admin-private. Tests: `tests/community.py` author-vs-other, HTML XSS, idempotent reaction, private reasons, hide/edit/restore/deletion and race assertions; `tests/browser.py` actual composer/comments/options. Owner: Stage 02 service semantics with Stage 04 transaction review and Stage 03 moderation review.

## 5 · Attend a training session or match

**UI:** `/?page=schedule&kind=training|match`, or a club's schedule tab. Filter fields `page=schedule,kind`; Register form `action=attend,id=EVENT_ID,csrf`. REST `POST /events/{id}/attendance` with `{}`.

1. Page joins `events` to `clubs`; only future rows show Register in the global schedule. Club tab lists up to fifty future club events.
2. `perform_action('attend') → reserve('attendance',...)` locks the event, rejects started sessions, requires approved membership in the event's own club and rejects duplicates.
3. Insert `attendance(user_id,event_id)` with status `registered` (not `attended`). Return success/dashboard.
4. Admin later uses attendance action/`PATCH /admin/attendance/{id}` after session start. The service enforces time even if an admin sees a premature Mark attended control.
5. Student `/?page=activity` reads own attendance; dashboard shows latest five. Cancelling attendance removes only a future registered row. Completed or started participation cannot be cancelled.

Tournament enrollment is a separate `registrations` row; it never automatically creates attendance for every fixture. Failures: not approved member, duplicate/started event, ownership violation on cancellation. Tests: integration/native session registration, API eligibility/time/attendance assertions, browser Register flow. Owner: Stage 02 journey; Stage 03 attendance; Stage 04 transactional rules.

## 6 · Enter a tournament and interpret results

**UI:** `/?page=tournaments&status=open`; fields `page,status`. Register form `action=register_tournament,id=TOURNAMENT_ID,csrf`. REST `POST /tournaments/{id}/registrations` with `{}`.

`reserve('registrations')` locks the tournament; requires open status and deadline not passed, no prior user registration and count below capacity. It writes `registrations` and returns dashboard. The UI deadline check is a convenience; a full tournament or changed deadline must still be rejected by the server. Cancellation uses `DELETE /me/registrations/{registration_id}` and is refused after tournament start.

`/?page=results` reads match events with scores, joins clubs and optionally tournaments, and aggregates club standings: win 3, draw 1, loss 0. REST read paths `/results` and `/standings` can filter `tournament_id`; the HTML standings shown here aggregate recorded club matches. An opponent is a text label, not a second normalized team entity. There is no automated knockout bracket, team-assignment engine, sport-specific tie-break logic or promised full league table.

Success: registration row and activity entry; separate scores appear after admin recording. Failures: closed/deadline/capacity/duplicate, started cancellation. Tests: `tests/api.py` tournament capacity/deadline/duplicate and score/standings tests; native integration/browser tournament flow. Owner: Stage 02 enrollment/display, Stage 03 events/scores, Stage 04 reserve/query contracts.

## 7 · Request and cancel a booking

**UI:** `/?page=facilities`, form fields `action=book,resource_id,starts_at,ends_at,purpose,csrf`. Time inputs are `datetime-local`. REST `POST /bookings` with those four data fields. `app/actions.php` parses strict minute input into Sri Lanka local timestamps.

1. Require a signed-in account, positive existing resource, purpose text and a future interval of at most four hours with end after start.
2. Begin transaction; lock the `resources` parent row. Test overlap against pending **and** approved bookings: `existing.starts_at < requested.end AND existing.ends_at > requested.start`.
3. Insert pending booking and audit. Two requests for the same resource serialize on the parent lock. Equality at interval boundaries is allowed: back-to-back intervals do not overlap.
4. Admin review `PATCH /admin/bookings/{booking_id}` with status approved/rejected checks status and overlap under locks. Booking request success does not guarantee approval.
5. Own activity cancellation `DELETE /me/bookings/{booking_id}` sets a future pending/approved record to cancelled. It retains history rather than deleting that row.

A resource record represents one reservable unit; do not promise stock quantities. Rejected/cancelled bookings no longer block intervals. Read views `/me/bookings`, student activity and dashboard upcoming count all derive from rows. Failures: past/too long/invalid interval, unknown resource, overlap, cancelling another user's or finished booking. Tests: native/API booking collision and ownership cases; browser facility form. Owner: Stage 02 form/state; Stage 03 review; Stage 04 lock/collision rule.

## 8 · Repeat two journeys on mobile

**Routes/fields/contracts:** the same club membership/community journey (sections 3–4) and booking/activity journey (section 7); mobile changes layout and navigation, not permissions, fields, REST paths or SQL. Use a synthetic account on a 390 px viewport, and compare with desktop 1440 px.

1. Open navigation with `.menu-toggle`; keyboard focus moves into a modal sidebar, Tab cycles links/buttons, Escape closes and restores focus. Choose Clubs, filter and apply. Observe pending badge and private tab gate.
2. After approval, open club composer, post and expand Comments. Confirm labels/textarea/buttons remain reachable and text does not force horizontal overflow. Server mutations should be exactly the same as desktop.
3. Open Facilities, select a future interval/purpose, submit, and inspect My activity. Open profile → Account security without revealing setup values.
4. Toggle light mode before profile, reload, and repeat navigation. User identity belongs in cookies/server state; only the theme preference belongs in localStorage.

Expected successes: usable drawer, clear gates, readable cards/tables, correct redirects/state. Failure diagnosis: overflow/focus is Stage 01; wrong labels/destinations Stage 02; 403/422 after a correct mobile submission still follows the same Stage 04 business rules. Tests: `tests/browser.py` viewport/theme/community tab loops, role navigation and no overflow checks; manual keyboard/touch observation remains required for a target device. Save observations, not secret-bearing HAR exports.
