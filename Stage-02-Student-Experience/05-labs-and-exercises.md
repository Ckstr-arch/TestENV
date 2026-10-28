# 05 · Safe student labs

These are learning tasks to perform manually, not work already completed or credited to a teammate. Use development-only synthetic accounts and records. Never reset `sportshub`, remove volumes, expose enrollment screens, or run a destructive suite without proving test isolation. Before a temporary code change, save your own patch outside the checkout and record a clean/dirty status; undo only lines you personally changed in the editor. Do not restore/reset an entire shared file over someone else's work.

## Lab A · Level 1: Predict a membership row in ten minutes

**Objective/source:** trace Apply to join in `app/views.php`, `app/actions.php` and `app/models.php`. **Prerequisites/setup:** working local app, disposable student and a development club with spare capacity; no code changes.

1. Read `action_button('join',...)`, the JS join map, and `reserve('memberships')`. Before clicking, write the expected form fields, REST JSON body, status and resulting row state.
2. In browser Network, click Apply once. Record only method, URL pattern, HTTP status and resulting badge; omit cookie/token/body credentials from evidence.
3. Read My clubs and private community tab before approval. Ask your lab partner using a disposable administrator to approve **that** membership, then refresh.
4. Explain why pending and approved have different access even with identical navigation links.

**Expected:** a pending membership, REST 201 or native 303, private-tab gate; after approval the same user sees the feed. **Questions:** which ID is club ID and which is membership ID? Where does `user_id` originate? **Evidence:** your written prediction plus redacted before/after screenshots. **Revert:** cancel only your own membership via activity; no database reset. Posts you made require their own cleanup; leaving is not a content-erasure mechanism.

## Lab B · Level 2: Compare native and REST transports

**Objective/source:** inspect the fallback, using `public/index.php`, `app.js`, `app/actions.php`. **Setup:** two distinct disposable clubs or use cancellation between requests; do not intentionally spam duplicate writes.

1. With JavaScript on, submit one membership. Explain `X-CSRF-Token`, JSON `{}` and club ID in the URL without saving secret values.
2. Disable JavaScript in developer tools, reload (to obtain fresh server-rendered state) and submit the other club's form.
3. Compare REST JSON/status/redirect handling with native POST303 followed by GET. Turn JavaScript back on.

**Expected:** both produce pending membership through `reserve()`. **Failure to diagnose:** API 404 from omitting `public/router.php` on PHP development server; do not change every form to paper over routing. **Evidence:** transport comparison table and shared call chain. **Revert:** cancel only the two own memberships. No production source needs editing.

## Lab C · Level 2: Improve a student empty state

**Objective/source:** propose a clearer message for an empty own activity section in `app/views.php`, without changing service rules. **Setup:** read [contribution contract](07-contribution-contract.md); coordinate Stage 01/04 reviews; save only your temporary diff.

1. Reproduce the state using a fresh local identity. Write what a learner currently misunderstands and your measurable expected result.
2. Manually change only the selected message/markup. Preserve `e()` and field/action names. If adding a link, use an existing route and a meaningful label.
3. Run `php -l app/views.php`, then compare desktop/mobile, keyboard, both themes and JavaScript-disabled behavior. A syntax pass does not prove accessibility.

**Expected:** an actionable empty state without false counts, private data, or changed enrollment behavior. **Questions:** is the state really empty, filtered or gated? Should an admin view change? **Evidence:** before/after screenshots and exact tests, reviewed explanation. **Revert:** manually undo your own edit from the saved patch; retain other contributors' edits. This is a task suggestion, not an implementation included here.

## Lab D · Level 3: Revoked membership and a stale tab

**Objective/source:** understand authorization after page load in `app/community.php` and `portal-views.php`. **Setup:** disposable approved member with a synthetic post; no real users.

1. Open two browser tabs as the same account. Leave a composer visible in tab1. In tab2 cancel that club membership through activity.
2. Predict the next write before trying it. Attempt a harmless synthetic comment in tab1 once.
3. Explain why a previously visible control cannot override `club_access()` on the next request. Reload and observe the gate.

**Expected:** private API refuses after revocation, even with a valid login; old content already displayed is not magically erased from browser memory. **Questions:** authentication or authorization failure? Does a token bypass membership? **Evidence:** method/status and your sequence sketch, no session dumps. **Revert:** reapply/approve only this lab membership if further practice needs it; delete your own test post only after appropriate access is restored. Do not remove access checks to force success.

## Lab E · Level 3: Booking boundary thought experiment

**Objective/source:** reason about the strict overlap predicate in `app/actions.php`. **Setup:** local test resource; use intervals in the future, each ≤4h. Do not alter team reservations.

Predict A=10:00–11:00, B=11:00–12:00 and C=10:30–11:30 on the same future day. Submit A then B and C using the UI. Ask whether pending A should already block C; read the actual status list.

**Expected:** B is adjacent, C overlaps; pending/approved block. **Questions:** what changes for another resource? Why must the lock be taken before checking overlap? **Evidence:** interval drawing and observed response. **Revert:** cancel only the lab's future bookings in activity; history remains cancelled. For concurrency, read Stage 04's transaction guide; do not flood a shared instance.

## Lab F · Mobile and 2FA oral demonstration

**Objective/source:** repeat membership/community and booking journeys at 390 px; explain account security without recording secrets. **Setup:** your synthetic account, authenticator app, secure offline storage for its recovery codes; Stage 01 navigation guide.

Use the drawer with keyboard, close with Escape, toggle light mode, open profile→Account security. Enroll 2FA privately; record only whether the process completed, never the setup key/codes. Sign out and back in with a fresh code, then explain why the password step alone cannot open the portal.

**Expected:** both mobile journeys follow the same server rules; enrollment requires password+possession, codes are not reusable. **Evidence:** screen captures outside enrollment/challenge values, focus/overflow checklist and a one-minute explanation. **Revert:** disable 2FA only on your lab account using its password+second factor if you wish; do not rotate APP_KEY. A lost factor is not a reason to bypass security.

[Separate hints and answers](08-knowledge-check.md#lab-hints-and-answers) · [Record real work](09-completion-evidence.md)
