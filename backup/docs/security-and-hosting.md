# Security, state and real PHP/MySQL hosting

The package retains the prototype's security mechanisms and makes actual MySQL and local isolation explicit. It is an educational implementation, not an independent security certification or a claim that a public host is configured.

## Implemented boundaries

| Boundary | Actual mechanism |
|---|---|
| Password storage | Argon2id one-way hashes; registration12–128 bytes; no public administrator role assignment |
| Session state | Strict mode, HttpOnly/SameSite=Lax, rotation after authentication,30-minute idle expiry, production Secure cookies |
| Package isolation | Separate `sportshub_backup_session` cookie, package-local `var/sessions`, separate database and new fresh-dataset key |
| Request forgery | Random CSRF; REST token header and exact Origin check when supplied; native hidden field |
| SQL values | Native PDO prepared statements; entity/table identifiers from fixed allowlists |
| Data access | Server-enforced signed-in/admin role, own cancellation, exact-club approved membership and content ownership |
| XSS prevention | User text escaped with `e()` at HTML output; community is plain text |
| Capacity/collision | Parent locks and transactional reservation/review; pending/approved booking overlap prevention |
| Admin writes | Shared validation and transactional persistence/audit, preserving atomicity |
| Login abuse | Database-backed scoped identity/IP throttling, generic credential errors |
| Browser defense | Same-origin CSP, frame restriction, no-store, nosniff and production HSTS |
| Engine requirement | PDO checks actual version; MariaDB and MySQL below 8 are rejected before caching connection |

Frontend validation is a usability aid; all meaningful rules remain authoritative on the server. A hidden button is not an access control. Cookies are not isolated by port, so the distinct package cookie name is important alongside the different local URL. Requests still need CSRF even when cookies are SameSite.

## Authenticator 2FA and the private key

New students start with 2FA disabled. After sign-in, profile → Account security → Set up authenticator creates a unique temporary manual setup key. Enter it privately in a TOTP app, then confirm current password and current code. The implementation uses SHA-1, six digits,30-second steps with one-step drift allowance. The secret is encrypted at rest with sodium secretbox and the private base64-encoded32-byte `APP_KEY`.

Password completion for an enrolled account creates only a five-minute pending challenge. It grants no portal access until valid second-factor/recovery verification. Used counters cannot be replayed. Eight random recovery codes are hashed, shown once and consumed once. Save them privately outside the application; never in source, screenshots or shared chats. Disable requires password and a valid second factor, rather than an insecure bypass.

Keep an existing dataset's key stable. Database backups and the matching key must be recoverable securely together; losing the key makes its enrolled secrets unreadable. Changing a key without a planned migration/re-enrollment is not password rotation. This package may generate a **new** key because its database starts fresh; do not use that as permission to rotate the original prototype's key.

2FA remains opt-in; require/enforce administrator enrollment by operational policy before real data, or review a future mandatory-admin change. Email verification/reset, institutional SSO, cross-device session revocation and an independent security review remain unimplemented. File sessions are suitable for the current single-instance design; multiple nodes need a reviewed shared session store.

## Choose a host by capability, not an old provider example

The manual lists possible PHP/MySQL-compatible hosts as examples. Their current plans, limits and pricing are **UNVERIFIED** here. A provider name does not prove required extensions, actual engine, SSH/CLI, private state or HTTPS. GitHub Pages/static hosting alone cannot execute this backend; a laptop running PHP's development server is not a reliable public submission service.

| Candidate | Must verify before selection | Responsibility |
|---|---|---|
| Shared PHP hosting | PHP 8.2+, PDO MySQL/mbstring/sodium/Argon2id, actual MySQL 8+, custom public root, private files/sessions, CLI migration/provisioning, TLS/logs/backups | Team verifies plan restrictions and retention |
| Managed PHP hosting | Same capabilities, durable private storage and supported process/release model | Team verifies actual features, not marketing label |
| Linux VPS | Supported PHP/Apache or Nginx-PHP-FPM, actual MySQL 8+, TLS/firewall, minimal grants, monitoring/backups/updates | Team operates/patches the server |
| Free plan | Every required capability, quotas, sleeping/expiry policy, lecturer access, persistence and pricing changes | Mark UNVERIFIED until checked on the current plan |

Remote database TLS must be configured through a trusted supported provider/server setup. The current PDO connection does not magically configure every host's CA/TLS options; verify the actual requirement and review a private connection change before using an untrusted network.

## Release procedure · human-controlled, not executed by this guide

1. Select and review the exact package commit; run source/security/functional/concurrency checks on disposable data. Audit tracked secrets and dependencies. Confirm image/branding rights. Keep an assistance/actual-contribution record.
2. Verify host extensions/Argon2id, actual MySQL version and UTF8MB4/InnoDB support, private durable writable sessions, logs, migrations/provisioning CLI, backups and public root. An unsupported capability blocks release.
3. Create a **fresh production database** and private users; do not import development identities/sample data. Use a migration account for intended schema changes and runtime DML-only grants where supported.
4. Generate an entirely new production key for this fresh dataset and store it securely. Set private `APP_ENV=production`, exact HTTPS `APP_URL`, actual DB values, error display off and private logging on. Preserve the chosen key afterward.
5. Deploy source privately and point the webserver document root to **this package's `public/`**. Do not upload `app/`, `database/`, `bin/`, `tests/`, `.env`, `var/` or docs into a downloadable root. On fixed-public_html hosting, request a supported mapping/symlink to the private project's public directory; if unavailable, treat layout compatibility as UNVERIFIED and review/test any entrypoint path adaptation separately. Do not blindly move the whole checkout into public_html.
6. Configure Apache rewrite/AllowOverride for shipped `.htaccess`, or verified equivalent Nginx routing. Route `/api/*` to `public/api.php`, normal page requests to `index.php`, static assets to their actual files. The development `router.php` is not the production webserver.
7. Create private `var/sessions` with only the required service-user permissions and persistence; never use 777. Back up any existing valued production dataset plus matching key before applying additive migrations. Inspect schema version history first.
8. Run intended migration with private privileges, then switch to runtime grants. Use private CLI `bin/create-admin.php`, unique human-chosen credentials, immediate administrator 2FA and safely stored recovery codes. Never publish a web setup URL.
9. Check real HTTPS health/assets, correct Host/Origin/CSRF, signup/login/pending 2FA/recovery/roles, CRUD, membership/booking review, score/report/history and community moderation with synthetic data. Test mobile/keyboard/light/dark. Private paths such as `/.env`, `/app/bootstrap.php` and `/var/sessions/` must not be downloadable.
10. Confirm error messages/logs do not disclose settings, no known development accounts exist, lecturer can access the source/app/video and backup/restore works. Record actual release SHA, approver, date, URL and migration version only now.
11. Arrange patching, uptime/error review, safe retention/cleanup of sessions/login attempts/audit logs, periodic private backups and a rollback plan. Remove synthetic production verification accounts if policy permits without destroying valued records.

No release is completed by writing this checklist. Human authorization and actual verified hosting are still required.

## Backup and restore safely

Back up actual MySQL data and matching private key/config outside webroot and source control, with restricted access and encrypted storage. Prefer the host's supported encrypted backup facility. For a local MySQL dump, `--password` prompts rather than exposes a value, and `--result-file` avoids Windows PowerShell text-redirection encoding problems:

```bash
mysqldump --host=127.0.0.1 --port=3306 --user=sportshub_backup \
  --password --single-transaction --no-tablespaces \
  --result-file=/PRIVATE-BACKUP-DIRECTORY/sportshub-backup.sql \
  sportshub_backup
```

The path above is an explicit placeholder; create/select a private protected directory appropriate to your OS first. Do not copy a dump to `public/`, commit it, attach it to a report or print identity rows. A logical snapshot needs a compatible strategy for schema changes/other transactions; coordinate release backup timing.

Rehearse restoration into a **new isolated scratch database** with a separately configured app origin/cookie/key copy protected privately. Inspect the dump for database-selection statements before importing, verify the selected target, and never import over the original `sportshub` or wanted production data as a casual test. Validate row counts/relations, real login/2FA under the matching key, health and migration history. A file existing is not proof its restoration works. Promotion of a recovered database requires separate approval and downtime/data-loss planning.

For rollback, retain the previous reviewed code release and a verified backup. Additive compatible schema may permit code rollback, but first check which columns/tables old code expects and whether new writes would be lost. Do not automatically run reverse/destructive SQL, erase volumes or restore an old snapshot over new user work. Keep exact release/key/database pairing documented privately.

## Troubleshooting without bypasses

| Failure | Inspect |
|---|---|
| HTTP403 | Role/club ownership, Origin/CSRF; preserve controls |
|404 | Correct document root and rewrite versus a legitimately missing resource |
|419 native | Fresh form/session, current state before resubmitting |
|422 | Actual field/domain/date/capacity/collision rule |
|500/503 | Private logs, MySQL engine/connectivity/schema, required extensions, session permissions |
| Missing CSS/JS | Assets under configured public root and root-relative URLs; avoid unsupported subdirectory hosting |
| Production session absent | HTTPS origin and Secure cookie, durable private session storage, proxy termination setup |
|2FA failures | Matching key/dataset, clock/current account/fresh code/pending expiry; never log/bypass factors |

[Setup](local-setup.md) · [Architecture](architecture.md) · [Report and video](report-and-video.md) · [Validation](validation.md)
