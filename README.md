# NSBM SportsHub

A PHP/MySQL sports club management application for NSBM WAD Batch 26.1 IFSL, topic 3. HTML, CSS, and vanilla JavaScript provide the interface; PHP renders pages and exposes a versioned JSON REST API. JavaScript sends website mutations through that API; native forms remain a fallback. The API and rendered interface share validation and business services.

## Learn, contribute and assemble

Start with the [learning map](LEARNING_MAP.md) and [baseline audit](Assemble/01-repository-assembly-map.md). The existing application remains the single canonical runtime.

- [Stage 01 — Frontend and UX](Stage-01-Frontend-UX/README.md)
- [Stage 02 — Student experience](Stage-02-Student-Experience/README.md)
- [Stage 03 — Admin operations](Stage-03-Admin-Operations/README.md)
- [Stage 04 — Backend, database and security](Stage-04-Backend-Database-Security/README.md)
- [Assemble — integration, local setup, hosting, report and video](Assemble/README.md)

These assisted learning guides do not assign student authorship. See the [genuine contribution workflow](Assemble/02-git-github-team-workflow.md) and [current validation evidence](Assemble/16-evidence-and-artifact-register.md). Compose uses MariaDB locally; the assignment mandates MySQL, so verify MySQL 8 or obtain lecturer approval before declaring compliance.

## Performance UI and club communities

The app now uses the approved navy/lime Performance Dark design with a complete light theme, a theme switch before the profile menu, student/admin sidebars, responsive drawer navigation and live dashboard counts. Open a club for its community, member directory and schedule. Approved members can post/comment/react/report; administrators have a private moderation queue. See [UI, migration and safe Mac Docker update instructions](docs/ui-and-community.md).

Existing Docker installations: pull the changes and run your usual `up --build -d` command with the **same** Compose project name and files. Startup applies the additive migration automatically; **do not remove database volumes**. Native installations run `php bin/migrate.php` before starting the new code.

## What works

- Student registration and login; distinct student and administrator portals.
- Club browsing, search/filtering, membership applications and administrator approvals.
- Training schedules, match fixtures, approved-member registration and recorded attendance.
- Tournaments, registration deadlines/capacities, player rosters, fixtures and scores.
- Match results and club performance standings (win 3, draw 1, loss 0).
- Facility/equipment reservations, overlap checks, approval/rejection and cancellation.
- Student participation history; administrator participation reports, print-to-PDF and audit activity.
- Administrator create/read/update/delete for clubs, tournaments, sessions/fixtures and resources.
- Authenticator-app TOTP 2FA with encrypted secrets, replay prevention and eight single-use hashed recovery codes.

## Requirements

PHP 8.2+ with PDO MySQL, mbstring, sodium and Argon2id support; MySQL 8+ or MariaDB 10.6+; a web server. Python 3 is needed only for the integration test runner. No Composer or npm packages are required.

### Standard local setup

1. Create a UTF-8 MySQL database and a dedicated database user. Give it schema privileges for initial setup; restrict the runtime user to SELECT/INSERT/UPDATE/DELETE for production.
2. Copy `.env.example` to `.env`; enter your local database settings. Generate `APP_KEY` with `php -r 'echo base64_encode(random_bytes(32)), PHP_EOL;'` and store it privately. Never commit `.env`.
3. Run `php bin/migrate.php`. This creates missing tables and applies versioned additive migrations while preserving existing data.
4. Optionally run `php bin/seed.php` for development sports data. It creates no shared login credentials. Do not seed production. For disposable local test logins, run `php bin/demo-users.php --confirm-demo`. Sample dates are relative to the time seeding runs.
5. Create the first administrator without putting credentials into command arguments or shell history:

   ```bash
   read -r -p 'Admin email: ' ADMIN_EMAIL
   read -r -s -p 'Admin password: ' ADMIN_PASSWORD
   export ADMIN_EMAIL ADMIN_PASSWORD
   php bin/create-admin.php
   unset ADMIN_EMAIL ADMIN_PASSWORD
   ```

6. Run `php -S localhost:8080 -t public public/router.php` for development. Register student accounts through the application. Administrators should enroll 2FA immediately under **Account security**.

### Prepared Codex cloud workspace

This workspace has a locally extracted, signed Debian PHP 8.4/MariaDB 11.8 toolchain outside the checkout at `/workspace/toolchain`. System installation was unavailable. Private `.env` configuration is already prepared, with the database on port 3307. Development-only administrator/student/2FA demo accounts are available; see the login table below. They must not be carried into a public deployment.

```bash
cd /workspace/TestENV
export PATH="/workspace/toolchain/bin:$PATH"
bash bin/cloud-start.sh
php bin/migrate.php
php bin/seed.php  # skips when existing clubs are present
```

The cloud startup script is specific to this prepared workspace. Use standard setup elsewhere. Live processes must restart after a snapshot restore. Use the existing checkout; no Git worktree is needed. The built-in PHP server is for development only.

## Tests

```bash
php tests/security.php
# Create the separate sportshub_test database and grant the configured DB user access first.
# These suites reset that disposable database. Run them sequentially.
python tests/integration.py
python tests/api.py
python tests/community.py
# In the prepared cloud workspace, supply the PHP path, for example:
PHP_BIN=/workspace/toolchain/bin/php python tests/community.py
# Optional Playwright coverage (see the local setup guide):
python tests/browser.py
```

**The integration runners drop/recreate tables in `sportshub_test` only.** Never store real data there. It runs a temporary HTTP server on port 8081, creates random test passwords and exercises actual forms and MySQL writes. It does not modify the main `sportshub` database. Run suites sequentially because they share the same disposable schema. There is a short wait for a fresh TOTP time step.

Validated in this workspace: 17 security checks, 71 HTTP/database checks, 130 REST API checks, and 73 community checks, including all four administrator CRUD areas, authorization, CSRF, approvals, scores, attendance, booking collisions, TOTP login/replay handling, recovery codes, pagination, malformed input and rate limiting. All 291 checks also passed in Docker, including an upgrade that preserved existing users and the private encryption key. Chromium checks exercised REST-backed registration, login, club approval, session/tournament registration, booking, admin CRUD and 2FA enrollment/recovery/disable, plus public pages and mobile navigation at 1440px and 390px, with no horizontal overflow or JavaScript errors.

## Structure

- `public/`: web document root, CSS/JavaScript and supplied visual assets.
- `app/`: configuration/database helpers, security, mutations, models, layout and views.
- `database/`: core schema, foreign keys, uniqueness constraints and versioned additive migrations.
- `bin/`: migration, opt-in demo seed, administrator creation and cloud startup.
- `tests/`: RFC TOTP/security checks, form/REST database integration suites, and optional Chromium workflows.
- `docs/`: platform setup, REST/OpenAPI, security, architecture, deployment, validation and submission guidance.

API mutations use POST/PUT/PATCH/DELETE with CSRF headers; native fallback forms use POST with CSRF checks. All SQL values are parameterized. The admin entity/table names are taken from a fixed allowlist. Output is escaped. Database and PHP session dates use Asia/Colombo consistently.

## Development demo logins

| Account | Email | Password |
|---|---|---|
| Admin | `admin@sportshub.test` | `AdminSport!2026` |
| Student | `student@sportshub.test` | `StudentSport!2026` |
| Athlete with 2FA | `athlete@sportshub.test` | `AthleteSport!2026` |

Create them with `php bin/demo-users.php --confirm-demo` after migration/seed. They are already prepared in this cloud workspace. The athlete's manual authenticator key is documented in the setup guide. Demo seeding refuses production and preserves existing identities.

## Detailed guides

Start with the [Windows/Linux/macOS/Docker setup guide](docs/local-setup.md), [REST API guide](docs/api.md), and [OpenAPI contract](public/assets/openapi.json). Docker quick start: copy `.env.docker.example` to `.env.docker`, replace its local database passwords, then run `docker compose --env-file .env.docker up --build -d`.

See [architecture](docs/architecture.md), [security and deployment](docs/security.md), and the [report/submission template](docs/project-report.md).
