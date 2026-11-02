# Exercise hints and expected outcomes

[Return to labs](../05-labs-and-exercises.md)

## A — Membership

Identity comes from require_user/session, not JSON user_id. Native wrapper checks hidden csrf and redirects303; API middleware checks header plus supplied Origin and returns201. reserve locks club, checks approved capacity and uses unique user+club to insert/reset rejected→pending. Only approval unlocks community access.

## B — Boundary errors

JSON [] fails400 because the source requires an object opening brace; unknown role fails422 allowlist; invalid exact datetime fails422; unsupported route404 and supported route/wrong method405. More than64KiB fails413, nested structures fail422, wrong Content-Type415. A valid CSRF is still needed to reach mutation parsing, so predict the **first** failing gate rather than assuming payload error wins.

## C — Booking

Both requests serialize on resources.id before checking for pending/approved overlap. Strict start<end/end>start means adjacent intervals are allowed. Without the lock, both can see no booking then insert distinct rows; neither primary-key uniqueness nor simple validation prevents interval conflicts. A meaningful concurrent test must synchronize requests around the lock, not merely run two requests one after another.

## D — 2FA

Pending session has pending_user/pending_until but no authenticated user_id. Successful verification updates the counter or consumes one recovery row under a user lock. finish_login rotates session and CSRF. A mismatch between encrypted DB state and APP_KEY cannot be fixed by clock synchronization; restore the matching private key/data or follow a reviewed recovery policy. Do not publish enrollment or recovery artifacts.

## E — Release

Only public contents may be web-visible. Keep database and stable key backed up separately and privately, ensure durable sessions/state, use real MySQL8 or obtain explicit assignment approval for MariaDB, verify host extensions/HTTPS/rewrite/CLI and record real release evidence. A local health response is not public deployment. Rollback must consider schema compatibility; restoring a database is a separately authorized high-impact operation into a reviewed target.
