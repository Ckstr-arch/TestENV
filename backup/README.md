# NSBM SportsHub · guideline-focused native package

This folder is a self-contained edition of the existing NSBM SportsHub prototype for **Topic 3 — Sports Club Management System**. Its application uses **HTML, CSS, vanilla JavaScript, PHP and actual MySQL 8+**. It retains the existing navy/lime interface, light theme, student/admin portals, REST endpoints, native form fallback, club communities and authenticator 2FA.

Run this directory as its own project. It does not load the parent repository's `.env`, database, sessions or source. The original prototype outside this folder remains the baseline; this package does not transfer its authorship to individual students. Its one parent configuration safeguard is a root Docker build-context exclusion for `/backup/`, described in the audit.

## Start here

1. Install **PHP 8.2+** with `pdo_mysql`, `mbstring`, `sodium` and Argon2id support, and a genuine **MySQL 8+** server. This edition intentionally rejects MariaDB. Many XAMPP installations bundle MariaDB under a “MySQL” label; use a separate actual MySQL server.
2. Follow [native Windows, macOS and Linux setup](docs/local-setup.md). Use a **fresh** `sportshub_backup` database, its own database user, and a new private application key. Existing prototype data is not imported automatically.
3. From this folder, run:

   ```bash
   php bin/setup.php
   # Edit the private .env with your own database settings first.
   php bin/migrate.php
   php bin/seed.php
   php bin/doctor.php
   php -S localhost:8091 -t public public/router.php
   ```

   Seeding is optional development sports data; it creates no shared passwords or user accounts. Run it only in development.
4. Use `http://localhost:8091` locally, matching `APP_URL` exactly. Health is `/api/v1/health`. Create students through registration; provision your own administrator through the private CLI procedure in the setup guide. No shared fixed demo password is shipped; optional random local accounts are described in the setup guide.

The document root is **`backup/public/`**, not `backup/` or the repository root. PHP's built-in server is only for local development. Do not run it as a production service.

## Included capabilities

- Student registration/login, server-enforced student/admin access and optional TOTP 2FA with single-use recovery codes.
- Club discovery/search/filter, membership requests, approvals and approved-member club spaces.
- Training/match schedules, event participation, tournament registration, scores and generic club standings.
- Facility/equipment booking requests, interval collision protection, review and eligible own cancellations.
- Admin CRUD for clubs, tournaments, events and resources; attendance, participation reports and audit history.
- Community posts, comments, reactions, private reports and administrator hide/restore moderation.
- Responsive desktop/mobile navigation, keyboard controls and dark/light themes.

## Documentation and evidence

| Guide | Purpose |
|---|---|
| [Guidelines audit](docs/guidelines-audit.md) | What the PDF requires, what changed and what remains a human deliverable |
| [Requirement traceability](docs/requirements-traceability.md) | Actual routes, source, data and evidence for Topic 3 |
| [Native setup](docs/local-setup.md) | Precise configuration, OS installation, private administrator and test setup |
| [Architecture](docs/architecture.md) | One application, request flows, real data relationships and preservation |
| [REST API](docs/api.md) | Methods, cookie/CSRF contract, payloads and response errors |
| [Security and hosting](docs/security-and-hosting.md) | Key handling, production prerequisites, backups, release and honest limits |
| [Report and video](docs/report-and-video.md) | Actual people/contributions, screenshots, rubric and final delivery checklist |
| [Validation](docs/validation.md) | Current commands/outcomes and checks still awaiting execution |

## Scope and contribution integrity

This package has no required Docker, Python, Node, Composer, frontend framework or PHP framework. Tests use PHP. REST is an HTTP interface implemented in PHP, not an extra application language or framework. PHP's PDO/mbstring/sodium extensions support the required stack.

The PDF does not say that using an external development tool alone invalidates an application. This package deliberately removes those tool dependencies to satisfy the requested narrow, native distribution; the meaningful database correction is using actual MySQL instead of relying on MariaDB compatibility.

The existing application was Codex-assisted. Record each person's real implementation, review, debugging, design, testing and documentation, with honest assistance disclosure under course policy. Do not assign pre-existing module authorship or manufacture contribution commits. Real live hosting, lecturer access, final report/video and confirmed deadline remain human tasks. Current validation is documented separately; there is no claim of a live deployment.
