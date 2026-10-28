# 00 · Start with a student, a request and a row

A student sees a button, but the server decides what it means. Your first task is to connect those two facts. A pending membership badge comes from a `memberships` row; it is not a CSS-only state. An approved badge is insufficient if a forged request cannot pass `club_access()`.

## Before opening a lab

1. Use [macOS](../Assemble/03-local-development-macos.md), [Windows/Linux](../Assemble/04-local-development-windows-linux.md) or [Docker](../Assemble/05-docker-compose-and-data-safety.md) setup. Retain the existing project name, private key and volumes.
2. Run `php bin/doctor.php` in your configured source root. It checks rather than repairs schema. Do not print `.env` contents to troubleshoot.
3. Open the app using exactly `APP_URL`: changing `localhost` to `127.0.0.1` changes the Origin used by the browser.
4. Use invented identities in your own development data. Never experiment on a production account or share a password, setup key, recovery code, cookie or CSRF token.
5. Do not run integration/browser suites until the [test isolation gate](06-testing-and-debugging.md) is satisfied.

## A ten-minute source tour

```bash
rg -n "page==='clubs'|page==='activity'|action_button" app/views.php
rg -n "page==='club'|allowed=|community_posts" app/portal-views.php
rg -n "function club_access|function community_action|function reserve" app/community.php app/models.php
```

These read source only. Locate the club search form; read its `method="get"` and `name="q"`. Locate Apply to join: `action_button('join', ...)` emits a POST form. Follow `join` in `app/actions.php` to `reserve('memberships', ...)`. Compare the names seen by HTML, JavaScript, PHP and SQL before using the site.

## Vocabulary needed immediately

| Word | Meaning in SportsHub |
|---|---|
| GET | Read a page/API collection; filters become query parameters |
| POST | Submit a command or create a record |
| Session | Server-held login state found by an HttpOnly cookie |
| Authentication | Establish which account is signing in |
| Authorization | Check whether that account may perform this operation |
| CSRF | Protection against another site tricking a browser into a cookie-authenticated write |
| Escaping | Convert characters such as `<` into text-safe HTML output using `e()` |
| Foreign key | Database relationship, such as `memberships.club_id → clubs.id` |
| Progressive enhancement | Native forms work first; JavaScript can submit equivalent REST requests |

Continue to [architecture](01-architecture.md). If you cannot explain which file produces a form, do not start modifying its route mapping yet.
