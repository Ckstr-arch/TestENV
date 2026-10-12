# SportsHub REST API v1

Base path: `/api/v1`. The website's JavaScript submits forms through this API; native PHP forms remain a fallback. JSON transport and rendered forms share the same business services, validation, database constraints and authorization checks.

Machine-readable contract: [`public/assets/openapi.json`](../public/assets/openapi.json), served at `/assets/openapi.json`. It is OpenAPI 3.1 and can be imported into Postman, Insomnia or Swagger-compatible tools. Regenerate it with `python bin/build-openapi.py` after changing the contract.

## Authentication and request format

1. `GET /auth/session` creates/reuses the session cookie and returns `meta.csrf_token` plus a safe current-user profile (null when anonymous).
2. Send `Content-Type: application/json`, preserve the cookie, and send `X-CSRF-Token: <returned token>` for **every** POST, PUT, PATCH and DELETE, including login/registration.
3. `POST /auth/login` with `email` and `password`. When `data.requires_2fa` is true, password authentication has not granted access. Call `POST /auth/2fa` with `code` to complete the five-minute challenge.
4. Read the response's fresh CSRF token after session rotation. Never store password/TOTP secrets or session identifiers in localStorage.
5. `POST /auth/logout` ends authenticated access.

Cookie sessions are HttpOnly and SameSite=Lax, with Secure cookies in production. The API is same-origin: it does not enable wildcard CORS. If an Origin header is present on a mutation, it must exactly match configured APP_URL. A non-browser client such as curl/Postman must still preserve cookies and use CSRF.

All mutation bodies must be JSON **objects**, including `{}` for actions with no fields. Request bodies are limited to 64 KiB. Nested values and unknown fields are rejected; clients cannot assign roles during registration. Local input dates are `YYYY-MM-DDTHH:MM` in **Asia/Colombo**, for example `2026-12-01T16:00`. Response SQL datetimes use `YYYY-MM-DD HH:MM:SS` in that timezone.

### Browser example

```javascript
const base = '/api/v1';
const session = await fetch(`${base}/auth/session`, {credentials: 'same-origin'}).then(r => r.json());
const response = await fetch(`${base}/auth/login`, {
  method: 'POST',
  credentials: 'same-origin',
  headers: {'Content-Type': 'application/json', 'X-CSRF-Token': session.meta.csrf_token},
  body: JSON.stringify({email: 'student@sportshub.test', password: 'StudentSport!2026'})
});
const login = await response.json();
if (!response.ok) throw new Error(login.error.message);
// Keep login.meta.csrf_token for the next mutation. If login.data.requires_2fa,
// prompt for a code and POST it to /auth/2fa before using protected routes.
```

These are known local demo credentials, not production secrets. Never paste real credentials into public consoles/examples.

## Routes

| Method | Path | Access / purpose |
|---|---|---|
| GET | `/health` | Public, database-backed readiness |
| GET | `/auth/session` | Public, identity and CSRF bootstrap |
| POST | `/auth/register` | Public, student-only account creation |
| POST | `/auth/login` | Public, password authentication |
| POST | `/auth/2fa` | Pending login, authenticator/recovery verification |
| POST | `/auth/logout` | Signed-in user, logout |
| GET | `/me` | Signed-in user, safe account profile |
| GET | `/me/security` | Signed-in user, 2FA status and code count |
| POST | `/me/2fa/setup` | Signed-in user, pending secret and otpauth URI |
| POST | `/me/2fa` | Signed-in user, enroll with password and code |
| DELETE | `/me/2fa` | Signed-in user, disable with password and second factor |
| GET | `/clubs`, `/tournaments`, `/events`, `/resources` | Public, paginated browse/filter |
| GET | `/clubs/{id}`, `/tournaments/{id}`, `/events/{id}`, `/resources/{id}` | Public detail |
| POST | `/clubs/{id}/memberships` | Request membership approval |
| POST | `/tournaments/{id}/registrations` | Register an individual participant |
| POST | `/events/{id}/attendance` | Register for training/match after club approval |
| POST | `/bookings` | Request resource reservation |
| GET | `/me/memberships`, `/me/registrations`, `/me/attendance`, `/me/bookings` | Own paginated history |
| DELETE | `/me/{collection}/{id}` | Cancel own eligible participation; booking records retained as cancelled |
| GET | `/results` | Public, paginated recorded match scores |
| GET | `/standings` | Public, club standings; optional tournament filter |
| GET, POST | `/admin/clubs`, `/admin/tournaments`, `/admin/events`, `/admin/resources` | Admin list/create |
| GET, PUT, DELETE | `/admin/{collection}/{id}` | Admin detail/full replacement/deletion |
| GET | `/admin/memberships`, `/admin/bookings` | Admin paginated request lists |
| PATCH | `/admin/memberships/{id}`, `/admin/bookings/{id}` | Approve/reject pending request |
| GET | `/admin/attendance` | Admin participant/session list |
| PATCH | `/admin/attendance/{id}` | Mark attendance after session starts |
| GET | `/admin/reports` | Admin club/tournament/booking aggregates |
| GET | `/admin/audit-logs` | Admin paginated audit history |

`{collection}` above is a fixed allowlist, not an arbitrary table name. Personal collections are memberships/registrations/attendance/bookings; admin CRUD collections are clubs/tournaments/events/resources.

## Payloads

- Register: `name`, `email`, `student_id`, `faculty`, `password` (12–128 characters).
- Login: `email`, `password`. Verify: `code`.
- 2FA setup: `{}`; enroll/disable: `password`, `code`. Setup returns a secret and otpauth URI; protect them like passwords. Enrollment returns eight recovery codes once; GET security never returns them.
- Clubs: `name`, `sport`, `description`, `coach`, `location`, `capacity`.
- Tournaments: `name`, `sport`, `description`, `location`, `starts_at`, `ends_at`, `registration_deadline`, `capacity`, `status` (`open`, `ongoing`, `completed`). Deadline must not be after start; end must follow start.
- Events: `title`, `club_id`, `kind` (`training`, `match`), `location`, `starts_at`, `ends_at`; optional `tournament_id` (or null), `opponent`, `home_score` and `away_score` (paired nonnegative integers, or null). Scores require a match and opponent.
- Resources: `name`, `kind` (`facility`, `equipment`), `description`, `location`.
- Booking: `resource_id`, `starts_at`, `ends_at`, `purpose`. Must be future, at most four hours and not overlap a pending/approved reservation.
- Membership/booking review: `status` (`approved`, `rejected`). Only pending requests can be reviewed.
- Attendance update: `status: "attended"`.

PUT supplies the complete record, not a partial edit; optional event fields can be omitted. IDs and privileged fields are determined by the route/server. DELETE requests use `{}`, except 2FA deletion which requires both factors. Creating resources returns 201 with a Location header where applicable.

## Responses, pagination and errors

Success envelope:

```json
{"data": {"example": "resource"}, "meta": {"request_id": "opaque-id", "csrf_token": "token"}}
```

Paginated lists return `data` as an array with `meta.pagination`: `page`, `per_page`, `total`, `pages`. Default page size is 20, maximum 100. Query parameters `page` and `per_page` select a page; no record means an empty list, not an error. `/standings` and aggregate reports are unpaginated.

Browse filters: `q` for all four public collections; `sport` for clubs/tournaments; `status` for tournaments and admin requests; `kind` for events/resources; `club_id` and `tournament_id` for events. Results/standings accept `tournament_id`. Administrative CRUD lists support pagination. UI-oriented mutations may include `meta.redirect`; API clients do not need to follow it.

Error envelope:

```json
{"error":{"status":422,"message":"End time must be after start time."},"meta":{"request_id":"opaque-id","csrf_token":"token"}}
```

| Status | Meaning |
|---|---|
| 400 | Malformed JSON/object |
| 401 | Sign-in required, invalid login/second factor or expired challenge |
| 403 | Role restriction, invalid CSRF or foreign Origin |
| 404 | Unknown route/resource or a participation record you do not own |
| 405 | Unsupported HTTP method; check Allow header |
| 409 | Database/state conflict, including an ineligible cancellation |
| 413 | Body exceeds 64 KiB |
| 415 | Missing/unsupported JSON Content-Type |
| 422 | Field validation or business rule, including capacity/duplicate/overlap |
| 429 | Authentication rate limit; Retry-After: 900 seconds |
| 500 / 503 | Unexpected server/database service failure; use request ID for diagnosis |

Errors never return SQL, password hashes, stored authenticator secrets or stack traces. The server logs an internal request ID for unexpected errors. Clients must not automatically replay a mutation after a network failure: it may already have been committed. Fetch current state before retrying.
