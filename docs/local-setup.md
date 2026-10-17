# Complete local setup guide — Windows, Linux, macOS and Docker

This guide starts from the source ZIP or the project checkout. You do **not** need Node.js, npm, Composer or a paid service. The application uses PHP, MySQL-compatible SQL, HTML, CSS and vanilla JavaScript. Commands below assume your terminal is inside the extracted `NSBM-SportsHub` directory (the directory containing `README.md`). Paths on your own machine do not need to be `/workspace/TestENV`.

## 1. Pick a route

| Route | Recommended for | Tools you install |
|---|---|---|
| Docker Compose | Consistent setup on all three OSes | Docker Desktop/Engine + Compose |
| Windows native | Students already using XAMPP | XAMPP with compatible PHP, or PHP 8.2+ plus MySQL/MariaDB |
| Ubuntu/Debian native | Linux development | PHP CLI extensions + MariaDB |
| macOS native | Mac development | Homebrew PHP + MariaDB |

Docker avoids PHP extension/version differences. Native instructions use PHP's development server, so no Apache virtual-host editing is needed. REST URLs require **`public/router.php`** in that command. These are local development setups; see `security.md` for public hosting.

## 2. Test accounts (local only)

Run the demo account seeder after the database/schema/sports seed steps. Docker demo mode runs it automatically.

| Role | Email | Password | 2FA |
|---|---|---|---|
| Administrator | `admin@sportshub.test` | `AdminSport!2026` | Initially off; you can enroll it |
| Student | `student@sportshub.test` | `StudentSport!2026` | Initially off |
| Athlete | `athlete@sportshub.test` | `AthleteSport!2026` | Authenticator required |

The ordinary student's first-club membership starts pending, so you can demonstrate admin approval. The athlete's starts approved, so you can demonstrate session registration immediately.

For the **athlete demo account**, manually add a time-based account in Google Authenticator, Microsoft Authenticator, 2FAS or another TOTP app:

- Account name: `SportsHub demo athlete`
- Setup key: `JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP`
- Type: time-based; SHA-1; 6 digits; 30 seconds.

Sign in with the athlete's password, then enter the current code. The same code cannot be used twice after successful verification: wait for the app's next 30-second code if needed. This demo account has no pre-created recovery codes; disable/re-enroll 2FA to receive your own eight recovery codes. Its known demo setup key is intentionally disposable, not suitable for real accounts.

The seeder requires `APP_ENV=development` and `--confirm-demo`. Rerunning it preserves existing accounts/passwords/2FA and does not reset them. There is no hidden default administrator in production. Do not expose demo accounts on a public deployment. `.test` addresses are local identifiers; no email is sent.

## 3. Common native setup

Complete your OS installation section below, then perform these steps.

### A. Create a database and dedicated account

Open your SQL shell (or phpMyAdmin's SQL tab on Windows). As a local database administrator, run:

```sql
CREATE DATABASE sportshub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'sportshub'@'127.0.0.1' IDENTIFIED BY 'replace-with-your-local-db-password';
GRANT ALL PRIVILEGES ON sportshub.* TO 'sportshub'@'127.0.0.1';

-- Optional, needed only for the destructive integration tests:
CREATE DATABASE sportshub_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON sportshub_test.* TO 'sportshub'@'127.0.0.1';
```

Choose your own database password and put the same value in `.env`. This password is separate from website login passwords. Database usernames are host-scoped: connect to `127.0.0.1` as shown, not a Unix socket through `localhost`. The grants include schema privileges for local setup/testing; production should use separate migration and runtime accounts.

### B. Generate the private application configuration

```bash
php bin/setup.php
```

On Windows use `C:\xampp\php\php.exe` instead of `php` if it is not on PATH. The script creates `.env` from `.env.example` with a new random APP_KEY and never overwrites an existing `.env`.

Open `.env` in your editor and set:

```dotenv
APP_ENV=development
APP_URL=http://localhost:8080
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=sportshub
DB_USER=sportshub
DB_PASSWORD=replace-with-your-local-db-password
APP_KEY=keep-the-random-value-generated-by-setup
```

Use the generated APP_KEY unchanged, not the placeholder above. Native `.env` values are literal: do not surround them with quotes or add inline comments. Do not share or commit `.env`. Keep APP_URL exactly equal to the address you use in the browser: scheme, hostname and port. Mixing `localhost` with `127.0.0.1` can fail the API origin check. If you change ports, change APP_URL too.

### C. Create the schema and test data

```bash
php bin/migrate.php
php bin/seed.php
php bin/demo-users.php --confirm-demo
php bin/demo-community.php --confirm-demo
php bin/doctor.php
```

Migration creates missing tables without clearing existing data. Sports seeding skips when clubs already exist. Sample schedules use dates relative to seeding time; old demo schedules do not advance when you restart the app. To refresh them, edit via the admin interface or use a new disposable database. Do not drop a database containing work you want to keep.

The doctor checks extensions, encryption-key length, database connectivity/schema and session storage without printing credentials. Every check should say PASS before continuing.

### D. Start and use the app

```bash
php -S localhost:8080 -t public public/router.php
```

Leave this terminal running. Open `http://localhost:8080` in your browser. The API health check is `http://localhost:8080/api/v1/health`; it should return a JSON object containing `"status":"ok"`. Stop the development server with Ctrl+C. Restart it with the same command. MySQL/MariaDB must remain running too.

The document root is `public/`, never the whole project. Your private `.env`, SQL schema, PHP services and session files must not be served as static downloads.

## 4. Windows — XAMPP and PowerShell

1. Install a current XAMPP release with PHP **8.2 or newer**, or install compatible PHP/MySQL separately. Install only from the official providers. Check the actual version; the project does not support older PHP.
2. In the XAMPP Control Panel start **MySQL** (XAMPP commonly supplies MariaDB). Apache is optional when using the PHP development server below. If using phpMyAdmin to create the database, start Apache too and open the local phpMyAdmin page.
3. Extract the source to a normal folder, for example `C:\Projects\NSBM-SportsHub`. Avoid placing the whole checkout under `htdocs`; that risks exposing private files.
4. Open PowerShell in that folder:

   ```powershell
   Set-Location C:\Projects\NSBM-SportsHub
   $env:Path = 'C:\xampp\php;' + $env:Path
   php --version
   php --ini
   php -m
   php -r "var_export(password_algos());"
   ```

5. In the **CLI's loaded** `php.ini` (typically `C:\xampp\php\php.ini`), ensure `pdo_mysql`, `mbstring` and `sodium` are enabled. Uncomment the relevant `extension=` lines if necessary and save. Argon2id must appear in `password_algos()`; if it does not, use a PHP build that provides it or Docker. Do not replace the password algorithm merely to hide the failure.
6. Follow **Common native setup**. In phpMyAdmin, run the SQL as your local administrator; set DB_PORT to XAMPP's actual MySQL port if it differs from 3306.
7. Start the app:

   ```powershell
   php -S localhost:8080 -t public public/router.php
   ```

If PATH is inconvenient, run each PHP command with `& 'C:\xampp\php\php.exe'`, for example:

```powershell
& 'C:\xampp\php\php.exe' bin/setup.php
& 'C:\xampp\php\php.exe' -S localhost:8080 -t public public/router.php
```

Windows ACLs control private file access; PHP's `chmod` is not a substitute for Windows permissions. Keep this folder under your account and outside publicly shared directories. Another MySQL service or Skype/IIS may occupy ports; check XAMPP's logs and choose an unused web port, updating APP_URL.

### Windows test commands

Install Python 3 if you want the integration runner; otherwise the app does not need it.

```powershell
php tests/security.php
$env:PHP_BIN = 'C:\xampp\php\php.exe'
py -3 tests/integration.py
py -3 tests/api.py
py -3 tests/community.py
```

The separate `sportshub_test` database must exist and be accessible. The integration server uses port 8081. It resets that test database only; never put real work there.

## 5. Linux — Ubuntu/Debian

```bash
sudo apt update
sudo apt install php-cli php-mysql php-mbstring mariadb-server python3
sudo systemctl enable --now mariadb
php --version
php -m
php -r 'var_export(password_algos());'
```

Verify PHP is at least 8.2; your distribution's default can differ. PHP's sodium extension is usually bundled; confirm with `php -m` and install your distribution's supported sodium-enabled package if missing. No TLS or package-signature verification should be disabled during installation.

Create databases by entering the local administrator shell:

```bash
sudo mariadb
```

Paste the SQL from **Common native setup → A**, then type `exit`. Extract/clone the source into your own home/projects directory, `cd` there, and follow the remaining common steps. Run PHP as your regular user rather than root.

If Apache/PHP-FPM runs the app later, give only the service user write access to `var/sessions`; never use `chmod -R 777`. The CLI development server writes sessions as your current user.

For other distributions, install equivalent PHP/PDO MySQL/mbstring/sodium and MySQL/MariaDB packages through the distribution's supported package manager. The application commands remain the same; service/package names may differ.

## 6. macOS — Homebrew

Install Homebrew through its official instructions first, then:

```bash
brew update
brew install php mariadb python
brew services start mariadb
php --version
php -m
php -r 'var_export(password_algos());'
mariadb -u root
```

Homebrew's PHP should supply the needed extensions; confirm the actual module list. If your shell selects macOS's old PHP or a different install, use `which php` and fix PATH according to Homebrew's output. Intel and Apple Silicon Homebrew prefixes differ; do not hardcode another machine's prefix.

Local root authentication depends on your existing MariaDB installation. Use its configured administrator account; do not assume a previously configured database has a blank root password. In the SQL shell, run the common database/user creation SQL. Then extract/clone the project, `cd` to it and follow **Common native setup**.

When finished, stop the PHP server with Ctrl+C; optionally stop MariaDB with `brew services stop mariadb`.

## 7. Docker Compose — all platforms

Docker runs Apache/PHP and MariaDB in separate containers, with no PHP/MySQL installation on your host. The Compose file binds the website to host loopback only and does not publish the database port.

### Install prerequisites

- Windows/macOS: Docker Desktop with Linux containers; on Windows enable the supported WSL2 backend.
- Linux: Docker Engine and the Compose plugin through Docker's supported distribution instructions. Ensure your account is authorized to run Docker; use your organization's supported configuration.

Verify:

```bash
docker version
docker compose version
```

### Configure and launch

From the source root, copy the Docker settings file:

```bash
# Linux/macOS
cp .env.docker.example .env.docker
# Windows PowerShell
# Copy-Item .env.docker.example .env.docker
```

Edit `.env.docker`: replace DB_PASSWORD and DB_ROOT_PASSWORD with local passwords you choose. Keep APP_ENV=development and APP_URL=http://localhost:8080. DEMO_MODE=1 explicitly enables the disposable sample accounts/data. Leave APP_KEY blank for local Docker mode: the container generates a random key in the persistent private `app_data` volume. Production instead requires an explicit private APP_KEY and DEMO_MODE=0.

```bash
docker compose --env-file .env.docker config --quiet
docker compose --env-file .env.docker up --build -d
docker compose --env-file .env.docker ps
docker compose --env-file .env.docker logs --tail=100 app db
```

`config --quiet` validates without printing resolved environment values. The database health check gates app startup; the application entrypoint creates tables and seeds demo data only when explicitly enabled. First startup can take several minutes to download/build images. Open `http://localhost:8080`, test the accounts above, and check `/api/v1/health`.

```bash
docker compose --env-file .env.docker exec app php bin/doctor.php
docker compose --env-file .env.docker exec app php tests/security.php
docker compose --env-file .env.docker exec app python3 tests/integration.py
docker compose --env-file .env.docker exec app python3 tests/api.py
docker compose --env-file .env.docker exec app python3 tests/community.py
```

The database initialization includes a separate `sportshub_test` database for the integration suite. It is created on the **first** empty-volume initialization. If you reuse an older database volume, create that test database/grant manually rather than deleting wanted data. The app image includes Python only for development verification.

### Stop, restart and rebuild

```bash
docker compose --env-file .env.docker stop
docker compose --env-file .env.docker start
docker compose --env-file .env.docker up --build -d
```

Source code is copied into the image, not bind-mounted. After editing code, rebuild with `up --build -d`. MySQL data, sessions and the generated development encryption key remain in named volumes.

```bash
# Remove containers/network, retaining database and key volumes:
docker compose --env-file .env.docker down
```

`down --volumes` permanently deletes the database, sessions and generated key. Use it **only** to intentionally discard a disposable demo environment. Changing DB_PASSWORD in `.env.docker` does not change a user password in an existing database volume; update the database user through a secured administrator connection or use a new disposable volume. Losing the encryption-key volume makes old 2FA secrets unreadable.

If your network uses a managed TLS-inspecting proxy, the Dockerfile accepts an optional BuildKit `extra_ca` secret containing the organization's trusted PEM CA bundle. It is used only for verified HTTPS package downloads during build and is not baked into the image. Use your network administrator's trusted certificate/proxy settings; do not disable TLS or package-signature checks. Normal home networks need no extra CA.

Container names/volumes are namespaced by the directory/Compose project name. Keep using the same project name for restarts, or you may unintentionally launch a separate empty database. Docker on Apple Silicon uses the images' native multiarchitecture support; don't force amd64 unless required by your Docker installation.

### Optional browser test suite

For native development, install Python's Playwright package and its Chromium browser, then run:

```bash
python -m pip install playwright
python -m playwright install chromium
python tests/browser.py
```

Use `py -3` on Windows if Python is selected through its launcher. This suite resets only `sportshub_test` and exercises REST-backed UI login/registration, approvals, booking, admin CRUD, authenticator enrollment/recovery/disable and desktop/mobile layouts. It runs a temporary server on 8083. Browser tooling is optional and is not required to run the website.

## 8. Testing through the UI

1. Sign in as `student@sportshub.test`. Inspect **My activity** and the pending club membership.
2. In another browser/private window sign in as `admin@sportshub.test`. Open **Approvals**, approve the membership and refresh the student portal.
3. Register the student for that club's future training session; inspect history.
4. Register for an open tournament. Confirm duplicate registration is rejected.
5. Book a future facility slot (up to four hours). Try the same slot again; it should be rejected. Approve/reject from the admin portal.
6. Create/edit/delete a disposable club/resource. Add a match and paired scores; inspect Results and Reports.
7. Test the athlete's authenticator login. Alternatively enroll your own authenticator under **Account security**; save the eight recovery codes outside the app and verify each is single-use.
8. Open browser Developer Tools → Network. Form actions should use `/api/v1/...` with JSON, the appropriate POST/PUT/PATCH/DELETE method and an X-CSRF-Token header. With JavaScript disabled, native PHP forms remain usable.

## 9. Troubleshooting

| Symptom | Check/fix |
|---|---|
| `could not find driver` | Enable/install `pdo_mysql` in the PHP configuration selected by `php --ini`. |
| Missing `mb_strlen`/sodium function | Enable mbstring/sodium; check `php -m`. |
| Database connection refused | Start MySQL/MariaDB; verify host/port; Docker uses DB_HOST=db internally. |
| Access denied for database user | Match username, password and host grant; the website password is unrelated. |
| Table doesn't exist | Run `php bin/migrate.php` against the intended database. |
| API returns 404 | Use the exact router command, Apache mod_rewrite/.htaccess or documented web-server routing; do not launch only `php -S ... -t public`. |
| Cross-origin API request rejected | Browse with the exact APP_URL; don't mix localhost and 127.0.0.1. No wildcard CORS is enabled. |
| Invalid CSRF/session expired | Reload the form; API clients fetch auth/session and preserve cookies plus its fresh CSRF token. |
| TOTP rejected | Check device clock/time sync, use a fresh code and the correct account key. A used code cannot be replayed. |
| `.env already exists` | Setup deliberately preserves it; edit the existing file or back it up before replacing it. |
| Demo credentials don't work after rerunning seed | Existing passwords/2FA are preserved, not reset. Use the credentials you changed to or a fresh disposable database. |
| Too many attempts | Wait 15 minutes. Don't remove rate limits to work around a failed login. |
| Session directory permission failure | Give the current CLI/web user private write access to `var/sessions`; keep it outside public/. |
| Docker app restarts repeatedly | Check logs, DB health, APP_KEY/APP_ENV, required extensions and DEMO_MODE. |
| Port 8080 already in use | Stop the conflicting dev server or change the host port and APP_URL together. |

## 10. Before submitting or hosting

Use your actual team names, GitHub link, live PHP/MySQL hosted URL and demonstration video in `project-report.md`. Confirm all links are accessible to the lecturer. Demo credentials must not become production accounts. Public deployment additionally needs HTTPS, private secrets, least-privilege database access, backups, production PHP error settings and a tested administrator with 2FA. A locally running project is not a deployed public application.

For the Performance UI/community upgrade and the isolated Mac Compose command, see [ui-and-community.md](ui-and-community.md). The theme control is immediately before the profile menu; Account security and Sign out are inside the profile dropdown.
