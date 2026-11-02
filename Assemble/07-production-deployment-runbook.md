# 🚦 Production deployment runbook — planned human release

**NOT DEPLOYED.** This is a sequence for a separately authorized release, not permission to deploy now. Prerequisite: reviewed real commit, approved host, synthetic smoke data and a recoverable private backup.

## 0 — Feasibility matrix

| Capability | Required proof | Current host state |
|---|---|---|
| PHP≥8.2, PDO MySQL, mbstring, sodium, Argon2id | CLI + HTTP runtime check; CLI alone insufficient | UNVERIFIED |
| MySQL 8 UTF8MB4/InnoDB and +05:30 session timezone | version, schema rehearsal, doctor | UNVERIFIED; local is MariaDB |
| HTTPS and exact APP_URL | real external request/cookie/Origin checks | UNVERIFIED |
| public-only webroot/private writable sessions | root configuration + negative HTTP tests | UNVERIFIED |
| private config, stable key, logs/backups | access policies + isolated restore evidence | UNVERIFIED |
| migration/admin CLI | private commands and least privilege | UNVERIFIED |

## 1 — Freeze and review

Choose an actual release SHA only after all stage PRs are genuinely reviewed. Record tests actually run. Inspect tracked filenames for `.env`, dumps, credentials and sessions without dumping file contents. Exclude private artifacts and confirm supplied logo/photo usage rights. Obtain human release approval.

## 2 — Fresh production database

Provision a new MySQL 8 database with utf8mb4_unicode_ci and InnoDB. Use dedicated app credentials; migrations need schema privileges, runtime normally SELECT/INSERT/UPDATE/DELETE. Confirm host-specific privileges for advisory locks and schema history. Never import demo user tables or production from a local demo snapshot. Use separate migration credentials where possible; the current code uses one configured connection, so switch private process bindings for CLI, then restore runtime bindings.

## 3 — Private configuration

APP_ENV=production; APP_URL is the exact real HTTPS origin; DEMO_MODE=0. Create a **new** production APP_KEY using base64 of 32 random bytes and place directly into the host secret store privately. Never reuse the known demo authenticator secret or print a key into report/screenshots. DB_PASSWORD is a private database account password, not website login. Keep existing-production APP_KEY unchanged during upgrades.

`php bin/setup.php` is a local helper, not a production public URL. Native env loader treats values literally: no quoting or inline comments. Set PHP display_errors=Off/log_errors=On and protect logs.

## 4 — Physical deployment

Preferred layout:

```text
/srv/sportshub/releases/<approved-sha>/
  app/ bin/ database/ public/
  .env        (private, or host environment)
  var/        (private durable session/key state; host-managed mapping)
web document root = .../public
```

Ship all required private PHP dependencies; serve only `public/`. Existing Apache config illustrates it. Do not change `ROOT` casually: it controls env/session/schema paths. Deploying only index.php is insufficient.

### Fixed public_html hosts

Use only if private directories and routing are supported. Example mapping:

```text
/home/account/sportshub-private/{app,bin,database,.env,var}
/home/account/public_html/{index.php,api.php,.htaccess,assets/}
```

In a **release copy only**, change every entrypoint include in public index.php/api.php from `__DIR__.'/../app/…'` to `dirname(__DIR__).'/sportshub-private/app/…'`. Three includes in index and three in api need mapping (including layout and the pre-try API include). Do not change `app/bootstrap.php` ROOT; private app still has its root above app. CLI bin paths remain relative to the private root. Do not deploy router.php as a substitute for Apache rewrite.

The include-resolution example is checked in this task via an isolated layout smoke fixture; **full host Apache behavior remains UNVERIFIED**. Before selecting this layout on a real host, lint all patched release entrypoints, run private doctor and HTTP functional/negative-path smoke. No canonical runtime was patched here. If host cannot keep private files outside public_html or securely run provisioning, select another host.

## 5 — Durable writable state

Web user alone can access private var/sessions (0700 on Unix); key files 0600 where service ownership permits. Keep sessions/key across redeploy. Do not make source tree world-writable. Verify the effective HTTP user, not only CLI ownership. Multi-node deployments need shared session storage or a reviewed redesign; baseline file sessions are single-node.

## 6 — Migrations

For an upgrade, create and verify backup before DDL. Inspect schema_migrations and migration files, use `php bin/migrate.php` with the intended private DB context. It takes an advisory lock, applies sorted versions, then records them; DDL may auto-commit and must be retry-safe. No production resets. Fresh production uses migrations, never demo seed.

## 7 — Real administrator

Use private CLI `bin/create-admin.php` with ADMIN_EMAIL/ADMIN_PASSWORD process environment from a secure private input/secret mechanism. Do not put passwords in command arguments/history or public scripts. Create a unique administrator, immediately enroll authenticator 2FA and privately save recovery codes. 2FA remains opt-in in the application; enrollment is an operational requirement here, not an implemented mandatory-admin policy.

## 8–10 — Synthetic operational acceptance

From actual HTTPS origin check `/api/v1/health`, home/assets, cookie Secure/HttpOnly/SameSite, CSRF Origin rejection, registration/login/2FA/recovery and role separation. Exercise disposable club CRUD, approval, event/tournament registration, score/report, booking/collision and mobile flow. Do not replay full reset-based suites on production. Check `.env`, app/, database/, bin/, tests/, var/ and stage docs are not served. Verify errors do not reveal SQL/stack traces. Capture redacted evidence; remove synthetic records if policy requires it without cascading real records. Ensure lecturer access.

## 11–12 — Operate and record

Configure encrypted DB/key backups, isolated restore rehearsal, session and throttle cleanup/retention, protected audit/log review and uptime alerts. Record actual release SHA, applied migration versions, approver, date, live URL and rollback target only after execution. Use [release notes](templates/release-notes.md) and [rollback guide](11-host-troubleshooting-and-rollback.md). Unknowns stay TODO, not successful checkboxes.
