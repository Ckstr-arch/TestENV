# 🖥️ Windows and Linux local setup

Both use one checkout and `public/` as webroot. No npm/Composer build is required. Follow [legacy detailed setup](../docs/local-setup.md) for private DB creation and exact local configuration. Never overwrite an existing `.env`; `bin/setup.php` preserves it and generates a key only for a new file.

## Windows PowerShell + XAMPP/native PHP

XAMPP commonly labels a MariaDB service “MySQL”; inspect server version. That does not automatically meet mandatory MySQL marking. Docker Desktop with supported WSL2 Linux backend is an alternative; use [the Compose guide](05-docker-compose-and-data-safety.md).

```powershell
Set-Location C:\Projects\NSBM-SportsHub
$env:Path = 'C:\xampp\php;' + $env:Path
php --version
php --ini
php -m
php -r "var_export(password_algos());"
php bin/setup.php
```

Enable pdo_mysql, mbstring, sodium in the CLI's loaded `php.ini`; confirm Argon2id is available. Do not downgrade hashing to conceal a missing build capability. Start the local DB service and create a fresh local UTF8MB4 database and dedicated account via a private SQL console. Edit `.env` privately with DB host/port/user/password, APP_ENV=development, exact APP_URL and generated APP_KEY. Do not put the whole clone in `htdocs`.

```powershell
php bin/migrate.php
php bin/doctor.php
php bin/seed.php # optional disposable sports sample
php -S localhost:8080 -t public public/router.php
# If PATH is inconvenient:
& 'C:\xampp\php\php.exe' -l public/index.php
```

Use Windows ACLs to keep private files under your account. Do not treat Unix chmod calls as sufficient Windows protection. Ctrl+C stops your local web server. `Get-NetTCPConnection -LocalPort 8080 -State Listen` helps inspect port use; do not terminate another project's service.

## Ubuntu/Debian native

Package versions vary by distribution. Inspect before installing; use official signed packages and keep TLS verification.

```bash
sudo apt update
sudo apt install php-cli php-mysql php-mbstring mysql-server python3
php --version
php -m
php -r 'var_export(password_algos());'
sudo systemctl status mysql
```

If your distribution offers MariaDB instead, use supported installation docs and record that difference; do not silently replace an existing engine. Sodium may be bundled; verify. Confirm PHP≥8.2 and MySQL 8 against the assignment. Existing local service credentials must be reused privately, never dumped.

From your own writable clone, run `php bin/setup.php`, edit private values, `php bin/migrate.php`, `php bin/doctor.php`, optional `php bin/seed.php`, then `php -S localhost:8080 -t public public/router.php`. Native development runs as your regular user; only private var/sessions needs write permission. Never blanket-chmod the project777. Production uses Apache/PHP-FPM, not `php -S`.

## Smoke and optional tests

```bash
php tests/security.php
curl --fail http://localhost:8080/api/v1/health
```

Expected JSON has `data.status` equal to `ok`; health tests connectivity, not all functionality. Python integration/browser suites reset `sportshub_test` tables and must first pass [isolation review](10-ci-tests-and-quality-gates.md). On PowerShell select Python with `py -3` and set `$env:PHP_BIN` if necessary. Python/browser dependencies are for verification, not app runtime.

**Execution status:** native Windows and package installation steps were not executed here. Linux retained toolchain checks are recorded separately; no claim of fresh apt installation.
