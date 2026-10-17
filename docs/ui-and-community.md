# Performance UI and club communities

The approved visual direction is Performance Dark (B), with the community layout inspired by Campus Social (E). The application uses the supplied athletics photograph rather than invented performance charts or placeholder statistics. Dashboard counts come from the database.

## Theme and navigation

Dark navy/lime is the default. The sun/moon control is immediately before the profile dropdown. Light mode replaces surfaces, text, borders, input styles, states and action colors with accessible white/slate/green equivalents. Theme preference is stored only in `localStorage['sportshub-theme']`; authentication data is never placed there. A small same-origin script initializes the theme before styles load, avoiding a dark flash during light-mode navigation. Storage failures fall back safely to dark mode.

Students see Overview, My clubs, Discover clubs, Schedule, Tournaments, Bookings, Participation and Results. Administrators see grouped sports-office management links, approvals, moderation, reports and audit history. Account security and sign out are in the profile dropdown. There is no stacked user/admin navigation strip. The header search searches clubs rather than pretending to be a global search.

On small screens, the sidebar becomes a drawer with an overlay, Escape-to-close, focus containment and return to the menu button. Desktop resizing clears modal state. Controls have accessible labels, current-page indicators and visible keyboard focus. Reduced-motion preferences are respected. Print layouts remove navigation and use readable light surfaces.

## Club spaces

Students open a club from Discover clubs, My clubs or their dashboard membership card. Overview describes the sport, coach, venue and membership state. Community, Members and Schedule are gated to approved members; administrators may enter without a club membership. Leaving a club immediately revokes access.

Members can post, edit/delete their own posts, react, comment, remove their own comments and privately report content. Administrators can remove comments and hide/restore posts with a recorded reason. The admin moderation queue exposes report details privately. No invented captain roles, direct messages, notification counts or analytics are shown.

## Database upgrade

`php bin/migrate.php` now runs versioned SQL files under `database/migrations/` after ensuring the core schema. `schema_migrations` tracks applied versions, with a database advisory lock to serialize concurrent deployments. The community migration creates four new tables: `club_posts`, `club_comments`, `club_reactions`, and `club_reports`. Existing account credentials, TOTP configuration, memberships, events, bookings and results are preserved. DDL statements are retry-safe because MySQL may auto-commit schema changes.

Docker invokes migration automatically at startup. Demo mode additionally seeds welcome posts through `bin/demo-community.php --confirm-demo`; it refuses production, requires the explicit development flag and preserves existing conversations. An existing development database can opt in with:

```bash
php bin/migrate.php
php bin/demo-community.php --confirm-demo
```

Migration is required before serving the new pages. Back up real databases before any deployment. Do not remove volumes to apply this upgrade.

## Mac Docker update without affecting other projects

Use the same directory, environment file, override file and project name you used before:

```bash
git pull --ff-only

docker compose -p nsbm-sportshub-local \
  --env-file .env.docker \
  -f compose.yaml -f compose.mac.yaml up --build -d
```

The website remains at the port in your override (for example 8090). Keep `APP_URL` consistent. Local `.env.docker` and `compose.mac.yaml` must stay private/local; they are ignored by Git. The rebuild retains named volumes, database passwords, sessions and the development encryption key.

Verify dashboard dark/light switching, profile/security/sign-out, club membership approval, community posting and moderation. Existing databases retain old demo dates; use admin scheduling to add future sessions rather than clearing wanted data.


## Implemented screenshots

These are real application captures using disposable development accounts, not generated design previews. All displayed statistics come from the development database. Authenticator keys and recovery codes are excluded.

| Student dashboard | Club community | Admin dashboard |
|---|---|---|
| [Dark](screenshots/student-dark.png) | [Dark](screenshots/community-dark.png) | [Dark](screenshots/admin-dark.png) |
| [Light](screenshots/student-light.png) | [Light](screenshots/community-light.png) | [Light](screenshots/admin-light.png) |

[Mobile club community](screenshots/community-mobile.png)
