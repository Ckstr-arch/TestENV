# Native setup · Windows, macOS and Linux

The application needs PHP and **actual MySQL 8 or newer**. A compatible MariaDB server is intentionally rejected by this edition. No container, package framework, frontend build or external test runner is required. Commands run from the directory containing this package's `README.md` and `bin/`; if using the repository, enter `backup/` first.

Do not connect this edition to the original application's database or reuse its sessions/key. Use the separate names and port below. Existing projects/services do not need to be removed.

## 1 · Install and verify your platform

### Windows · compatible PHP plus actual MySQL

1. Install a PHP 8.2+ Windows build with `pdo_mysql`, `mbstring`, `sodium` and **Argon2id**. XAMPP can supply PHP, but its bundled database is frequently MariaDB; install/run genuine MySQL 8+ separately instead of assuming its “MySQL” button is sufficient.
2. Install genuine MySQL Community Server through Oracle's supported Windows distribution. Record its service name, TCP port and private administrator credentials during setup. If another database already uses 3306, choose a separate unused port rather than overwriting or stopping another project's service.
3. Keep this project in a normal private directory, such as `C:\Projects\SportsHub\backup`, outside a public `htdocs` folder. Open PowerShell there:

   ```powershell
   Set-Location C:\Projects\SportsHub\backup
   # If using the compatible PHP supplied by XAMPP:
   $env:Path = 'C:\xampp\php;' + $env:Path
   php --version
   php --ini
   php -m
   php -r "var_export(password_algos());"
   ```

4. In the CLI's loaded `php.ini`, enable the required modules using your build's supported configuration. Verify `argon2id` appears. A missing algorithm requires a suitable PHP build, not weakening the application's hash algorithm. If PATH is inconvenient, use `& 'C:\xampp\php\php.exe'` before each PHP argument list.
5. Open your actual MySQL client's secure administrator connection. Check `SELECT VERSION();` before proceeding. A value containing MariaDB is the wrong server for this package. Use your installed MySQL CLI/client and configured port; do not assume root has an empty password.

PowerShell can inspect service/port ownership with `Get-Service *mysql*` and `Get-NetTCPConnection -State Listen -LocalPort 8091`. Starting the correct existing service depends on the service name you configured; do not issue broad stop/remove commands. Windows ACLs, rather than blanket chmod workarounds, should keep private configuration/session state limited to your account or web service.

### macOS · Homebrew PHP and MySQL

Install Homebrew from its official instructions first. Review the available versioned MySQL formula and use an actual 8.x release. For example, where `mysql@8.4` is available:

```bash
brew info php mysql@8.4
brew install php mysql@8.4
brew services start mysql@8.4
php --version
php -m
php -r 'var_export(password_algos());'
```

These are setup instructions, not a claim that this task executed on macOS. Formula availability/package paths depend on your Homebrew installation. Use the installed formula's reported `bin` path for `mysql`; Intel and Apple Silicon prefixes differ. Do not install/start a second service on a port another project needs. Use that service's existing private administrator setup, or a separate MySQL instance/port configured under its official instructions.

Check `SELECT VERSION();` in the MySQL client. `brew install mariadb` is not the route for this edition. PHP's selected CLI must have PDO MySQL, mbstring, sodium and Argon2id. Locate it with `command -v php`; do not assume another project's PHP version is compatible.

Use `lsof -nP -iTCP:8091 -sTCP:LISTEN` to inspect the web port. If occupied, choose a free port for this app and update `APP_URL` and the server command together. Do not stop an unrelated process merely to reclaim the default.

### Linux · Ubuntu native example

On a supported Ubuntu release supplying PHP 8.2+ and genuine MySQL 8+, the native package route is:

```bash
sudo apt update
sudo apt install php-cli php-mysql php-mbstring mysql-server
sudo systemctl start mysql
php --version
php -m
php -r 'var_export(password_algos());'
```

Check actual PHP versions/modules and sodium support in the selected build; package defaults vary. Use your distribution's supported sodium-enabled PHP package/build if required. A version older than 8.2 must be upgraded through a trusted supported source. Keep TLS/package signature checks enabled.

Some Debian/distribution “default MySQL” packages install MariaDB. For those systems, use the supported genuine MySQL 8+ packages or official MySQL distribution for the OS instead of assuming compatibility meets this package's engine guard. Package/service names may differ. Inspect `SELECT VERSION();` and your configured port before proceeding.

Open the secure MySQL administrator client using your configured authentication; `sudo mysql` is appropriate only when your local installation supports socket-based administrative access. Avoid running the PHP development server as root. Keep the checkout owned by your normal account, with minimal private runtime permissions.

## 2 · Create fresh separate databases and a dedicated user

Use a local administrator connection to **the actual MySQL 8+ server**. Replace the password placeholder below with a unique private value through your secure client; it is not a shared demo password. Disable/purge sensitive SQL history according to your client's supported policy, avoid screen recording, and do not paste passwords into shell command arguments.

```sql
SELECT VERSION();
CREATE DATABASE sportshub_backup
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'sportshub_backup'@'127.0.0.1'
  IDENTIFIED BY 'YOUR_PRIVATE_DATABASE_PASSWORD';
GRANT ALL PRIVILEGES ON sportshub_backup.*
  TO 'sportshub_backup'@'127.0.0.1';
```

Local development grants include schema changes for migrations. Production should separate a migration user from a runtime user with only required DML permissions. Account host grants must match the connection method; these instructions use TCP `127.0.0.1`. This database password is unrelated to a website login or the computer login.

Do **not** drop the original `sportshub` database, import its private records, or point this configuration at it. If the separate database already exists, investigate who owns its contents rather than treating a failed CREATE as permission to reset it. An intentional migration of old data/key is a separate reviewed human task.

## 3 · Create private configuration once

From this package root:

```bash
php bin/setup.php
```

The script creates a private `.env` with a random base64 32-byte `APP_KEY` only if absent. Existing settings are preserved. Edit the private file in your editor:

```dotenv
APP_ENV=development
APP_URL=http://localhost:8091
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=sportshub_backup
DB_USER=sportshub_backup
DB_PASSWORD=YOUR_PRIVATE_DATABASE_PASSWORD
```

Retain the generated `APP_KEY` line already present; do not replace it with a documentation placeholder or the original app's key. `DB_PORT` must be your MySQL instance's actual port. Native values are literal: no surrounding quotes or inline comments. A `#` inside a value is text, not an inline-comment facility; leading full-line comments are supported. Existing process environment values take precedence over `.env`, so check inherited variable **names/presence** when unexpectedly targeting another local configuration.

Keep scheme/hostname/port exactly consistent between `APP_URL` and the browser. `http://localhost:8091` and `http://127.0.0.1:8091` are different origins. The app requires production `APP_URL` to be HTTPS. Never print or commit `.env`, keys, sessions, private SQL exports or real account details.

## 4 · Migrate, optionally seed and verify

```bash
php bin/migrate.php
php bin/seed.php
php bin/doctor.php
```

The sports seed is optional and development-only; omit it for an empty dataset or production. It creates fictional sports records but no user accounts or shared passwords. It skips when clubs already exist; restarting does not advance old sample dates. Use admin scheduling for new dates rather than resetting wanted data.

Migration ensures core tables and applies unapplied additive community versions. It preserves existing rows but is still a database write; back up any valued dataset first. MySQL schema DDL may auto-commit, so do not edit applied migration files and assume arbitrary rollback is available.

Doctor checks actual MySQL/version, PHP/extensions/Argon2id, private key, schema/timezone and session storage. Every required check should pass. A driver/module/engine failure is not solved by disabling checks.

## 5 · Provision your own administrator privately

There is no default administrator or fixed test password. Use a unique password of 12–128 bytes and an email identifier of your choosing; email delivery is not implemented.

**macOS/Linux · Bash**, from the package root:

```bash
read -r -p 'Administrator email: ' ADMIN_EMAIL
read -r -s -p 'Administrator password: ' ADMIN_PASSWORD
export ADMIN_EMAIL ADMIN_PASSWORD
php bin/create-admin.php
unset ADMIN_EMAIL ADMIN_PASSWORD
```

**Windows · PowerShell:**

```powershell
$env:ADMIN_EMAIL = Read-Host 'Administrator email'
$adminSecret = Read-Host 'Administrator password' -AsSecureString
$env:ADMIN_PASSWORD = [System.Net.NetworkCredential]::new('', $adminSecret).Password
php bin/create-admin.php
Remove-Item Env:ADMIN_EMAIL, Env:ADMIN_PASSWORD
Remove-Variable adminSecret
```

Process environment must be private to your trusted local account; unset it afterward. These commands avoid embedding passwords in command arguments/history. The CLI refuses an existing identity instead of silently resetting its credentials. Only this private provisioning path can create admins; public registration creates students.

### Optional random development logins

If you want quick synthetic admin/student/athlete access without choosing shared test passwords, run this only after development migration and optional sports seeding:

```bash
php bin/demo-users.php --confirm-demo
```

This CLI requires `APP_ENV=development`. It creates random per-installation passwords and a unique athlete authenticator secret, saving them only in ignored private `var/demo-credentials.json`. Open that file privately in your local editor to obtain your own values; never commit, upload, screenshot or print it in shared logs. The CLI prints the private path, not credential values. Existing identities and any existing credential record are preserved rather than reset. No known universal password or authenticator seed is shipped.

These accounts are disposable development aids, never production provisioning. The administrator/student start without 2FA; the athlete requires the unique secret and settings in your private record. If the sports seed created a club, the student membership is pending and the athlete's approved; otherwise join/approve through ordinary workflows. A fresh production database must contain none of these demonstration identities.

## 6 · Start and exercise the app

```bash
php -S localhost:8091 -t public public/router.php
```

Keep the terminal open. Use the exact local origin from `.env`; check `/api/v1/health` for database-backed `status: ok`. Create a synthetic student through Register. Sign in with your own administrator to create/manage records, approve membership, review bookings and record attendance/results. Enroll administrator 2FA under profile → Account security; store recovery codes privately, once, and never put them in a screenshot.

Use Ctrl+C in this server's terminal to stop it, and the same command to restart. MySQL must remain running. Other projects are separate; no global cleanup, volume or service removal is needed. This edition uses a separate `sportshub_backup_session` cookie and private `var/sessions` under its own root. Cookies do not use ports for isolation; the distinct cookie name matters when the original app also runs on localhost.

For PHP's built-in server, keep the router argument. For Apache, set document root to this package's public directory, enable mod_rewrite and permit the shipped `.htaccess` rules. Nginx/PHP-FPM requires equivalent explicit routing; `.htaccess` is an Apache file. See [hosting](security-and-hosting.md).

## 7 · PHP tests, with explicit disposable data

Security checks do not reset database tables:

```bash
php tests/security.php
php tests/audit.php
```

The functional suite uses PHP itself, HTTP and actual MySQL; no separate testing framework is needed. It **drops/rebuilds tables in the literal `sportshub_backup_test` database only** and uses port 8092. Only run after proving that schema contains no wanted data and after granting the dedicated local account access:

```sql
CREATE DATABASE sportshub_backup_test
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON sportshub_backup_test.*
  TO 'sportshub_backup'@'127.0.0.1';
```

```bash
php tests/integration.php --confirm-test-reset
php tests/concurrency.php --confirm-test-reset
```

The concurrency suite uses independent PHP processes/connections to verify last-slot membership and overlapping-booking review under contention. It resets the same isolated schema, so run it only sequentially after the HTTP suite.

The HTTP runner fixes the test database name and development/test origin, while inheriting private host/port/user/password settings. Its fixtures verify `SELECT DATABASE()`, genuine MySQL 8+ and the explicit flag before resetting. Synthetic credentials are generated per run and not printed. It owns/cleans its temporary server; do not run another suite concurrently against this schema or occupy 8092. A valid private `APP_KEY` is used if supplied, otherwise a temporary test key is generated. `PHP_BIN` can select the intended PHP binary when necessary.

Run test commands sequentially, and record actual output/version/results in [validation](validation.md). Do not copy historical parent-suite counts as new execution. Live-host UI/HTTPS/browser checks remain separate from PHP functional checks.

## 8 · Diagnose without weakening security

| Symptom | Safe diagnosis |
|---|---|
| MariaDB/version rejected | Confirm actual connected server; choose genuine MySQL 8+, not a compatibility setting |
| PDO driver or mbstring/sodium missing | Inspect the CLI/web PHP configuration and supported extension packages |
| Database access denied | Match private SQL password, TCP port and host-specific account grant |
| Missing table | Verify DB name, then apply intended migration; do not reset unrelated data |
| API 404 | Use router argument or correct Apache/Nginx rewrite; an unknown route is also a legitimate 404 |
| API 403 Origin/CSRF | Reload/token bootstrap, retain cookies and use exact APP_URL |
| Native 419 | Reload an expired form and verify current state before resubmitting |
| 2FA invalid/already used | Correct account, device clock, fresh time step, valid pending challenge; never bypass checks |
| Sessions unavailable | Give the selected CLI/web user minimal private access to this package's var/sessions |
| Port 8091/8092 occupied | Identify owner, choose a new app port with matching origin; test suite needs its own fixed port free |
| Generic 500/503 | Inspect protected server logs with request context; do not expose stack traces publicly |

Do not use `chmod -R 777`, global service termination or secret-bearing environment dumps. The native instructions were not executed on Windows/macOS in this Linux task; final platform testing must be recorded honestly.
