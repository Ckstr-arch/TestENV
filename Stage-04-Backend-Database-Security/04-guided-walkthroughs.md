# 04 — Follow complete writes, including failure

[Dashboard](README.md) · [Request diagram](diagrams/request-and-auth.md)

## 1. Membership request: two transports

**Visible route:** `/?page=clubs` or the club overview. The native form contains `action=join`, hidden `id` and `csrf`. With JS, app.js maps join to `POST /api/v1/clubs/{id}/memberships`, removes route-only fields from payload and sends `{}`. Cookie identifies the session; header carries the CSRF value. The browser cannot choose a different user_id.

1. Native `public/index.php` calls `handle_action()`: check_csrf, then perform_action(join). REST `public/api.php` calls api_dispatch: allowed method, signed-in identity, CSRF, supplied Origin, JSON and empty-field allowlist, then perform_action(join) with fixed path ID.
2. `perform_action` passes the session user ID and positive club ID to `reserve('memberships',...)`.
3. `reserve` begins transaction; locks the club parent with `FOR UPDATE`; verifies existing membership and approved count. Missing club/full club/duplicate pending or approved row throws validation failure.
4. `INSERT ... ON DUPLICATE KEY UPDATE status='pending'` creates a pending row or reactivates a rejected relationship. Audit and commit complete together. Unique user+club is the database backstop.
5. Native sends303 to dashboard and the next GET renders real state. REST returns201, the membership row and redirect metadata. JS follows the redirect; no client-only approved state is invented.
6. Admin approval is a separate request through `review_request` with club capacity lock and a fresh pending check. Only then does community access succeed.

**Observe:** membership row keyed by user+club, pending→approved state, safe HTTP status. **Do not capture:** cookies/CSRF/header dumps or actual student identities. [Student cases](../Stage-02-Student-Experience/04-guided-walkthroughs.md), [model tour](files/app__models_php.md), [API test guide](files/tests__api_py.md).

## 2. Admin save: from form to visible record

**Visible route:** `/?page=manage_tournaments`. Metadata in `entities()['tournaments']['fields']` builds fields name, sport, description, location, starts_at, ends_at, registration_deadline, capacity and status. Native sends `action=save`, `entity=tournaments`, `id` (0 for new), `csrf`. JS chooses POST `/api/v1/admin/tournaments` for create or PUT `/api/v1/admin/tournaments/{id}` for update, stripping transport-only hidden fields.

1. REST route checks administrator before parse/dispatch; native action reaches require_admin before save. Both services enforce admin even if someone manually forges a form.
2. API accepts only metadata fields, injects entity/id; `save_entity` checks text limits, strict datetimes, capacity1..10000, enumerated status and relationships for events.
3. Tournament end must be after start. Deadline may equal start in current code; it is rejected only when later. Do not teach stricter UI wording as a stricter server rule.
4. Trusted field names construct INSERT/UPDATE SQL; values remain placeholders. Save returns saved ID and writes audit. These two statements are **not one enclosing transaction** in this baseline.
5. REST201 for create/200 for update returns the saved record; native303 lands on the management list. That next read shows the actual row.
6. DELETE is separate, role-gated and may cascade child records. Exercise only on a synthetic entity created for the lab after checking FK impact.

**Failure prediction:** one missing score in a match fails422; a student calling admin CRUD fails403; unknown JSON role field fails422; missing record returns404 in REST precheck. See [admin scenarios](../Stage-03-Admin-Operations/04-guided-walkthroughs.md).

## 3. Tournament enrollment and session attendance

`POST /api/v1/tournaments/{id}/registrations` with `{}` maps to reserve registrations: lock tournament, require open/unexpired deadline, unique user+tournament and capacity, then insert+audit+commit. Cancellation after tournament starts is409. It does not create attendance for every fixture.

`POST /api/v1/events/{id}/attendance` maps to reserve attendance: lock event, require future session and approved membership in its club, reject duplicate user+event then insert status registered. Only an administrator after session start marks attended. Reports count registrations separately from attended rows. [Dictionary](data-dictionary.md) and [model tour](files/app__models_php.md) show the actual tables.

## 4. Booking request and collision

`POST /api/v1/bookings` accepts resource_id, starts_at, ends_at, purpose. It requires authenticated identity, correct CSRF/Origin and scalar JSON. The action rejects a past start, nonincreasing interval or duration over four hours. It locks the resource parent before querying pending/approved intersections with `starts_at < requested_end AND ends_at > requested_start`. Adjacent endpoints do not overlap. Only after an empty result does it insert and commit. Review locks the same resource and checks other approved rows. Cancellation updates future pending/approved rows to cancelled rather than erasing history.

A naive “check then insert” without the shared parent lock can let two empty-result reads both reserve a slot. [Lab C](05-labs-and-exercises.md#lab-c--booking-collision-as-an-interleaving) makes that interleaving visible without changing real bookings.

## 5. Authentication is a state transition

The initial anonymous session owns a CSRF token. Password success either calls finish_login (non-2FA account) or rotates into a five-minute pending_user challenge (enrolled account). Pending state has **no user_id**, so private/admin routes still reject it. `verify_second_factor` locks users, accepts only newer counter or consumes a recovery hash, then finish_login rotates the session again and creates fresh CSRF. The frontend must retain the newest response token. [Security guide](files/app__security_php.md) and [diagram](diagrams/request-and-auth.md) show failure and expiry.

> [!IMPORTANT]
> Client error messages improve usability; server state is authoritative. A timeout after POST is ambiguous: read current state before retrying a mutation.
