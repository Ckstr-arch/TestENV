# 🍎 macOS — isolated local development

Choose **Docker** to avoid changing host PHP/DB services. Your Docker image is a packaged filesystem; your Git clone is the editable source; the container is the running process; named volumes keep database/key/session state. Docker Desktop stores images internally, not in the clone.

## Docker Desktop route (preferred for existing Docker users)

1. Install supported Docker Desktop with Compose ≥2.24.4 (`!override` support); open it. Check `docker version` and `docker compose version`.
2. If you already cloned the repo, use that folder; do not clone again or overwrite `.env.docker`. Otherwise:

```bash
mkdir -p ~/Projects
cd ~/Projects
git clone https://github.com/Ckstr-arch/TestENV.git NSBM-SportsHub
cd NSBM-SportsHub
cp .env.docker.example .env.docker
open .
```

3. In `.env.docker` set **two distinct new private local database passwords**, DB_PASSWORD (app's database account) and DB_ROOT_PASSWORD (database administrator). They are neither Mac nor website login passwords. On an existing volume, editing them alone does not rotate DB accounts. Use safe administrator rotation and synchronized configuration instead.
4. Set APP_ENV=development, APP_URL=http://localhost:8090. Local APP_KEY can remain blank so persistent `app_data/var/app.key` is used. DEMO_MODE=1 is disposable-only. Do not copy demo data to production.
5. Inspect `docker ps --format 'table {{.Names}}\t{{.Ports}}'`. If8090 is free, create ignored `compose.mac.yaml`:

```yaml
services:
  app:
    ports: !override
      - "127.0.0.1:8090:80"
```

6. Use the **same** project, env and override on every operation:

```bash
docker compose -p nsbm-sportshub-local --env-file .env.docker -f compose.yaml -f compose.mac.yaml config --quiet
docker compose -p nsbm-sportshub-local --env-file .env.docker -f compose.yaml -f compose.mac.yaml up --build -d
docker compose -p nsbm-sportshub-local --env-file .env.docker -f compose.yaml -f compose.mac.yaml ps
docker compose -p nsbm-sportshub-local --env-file .env.docker -f compose.yaml -f compose.mac.yaml exec app php bin/doctor.php
```

Open the local address you configured; check `/api/v1/health` returns `data.status=ok`. The localhost page is a local test, not your lecturer's public live URL. Follow [data safety](05-docker-compose-and-data-safety.md) for stop/update/backups. No global prune, other-project stop or volume deletion is needed.

## Homebrew native alternative

Use this only after checking existing DB services/ports. Do not install/start a second database over an existing service.

```bash
brew update
brew install php mysql python
brew services start mysql
php --version
php -m
php -r 'var_export(password_algos());'
mysql -u root -p
```

Use your actual local administrator authentication, not an assumed blank password. MySQL 8 target aligns with the manual; Homebrew current defaults may differ: check `SELECT VERSION()`. Existing MariaDB development is compatible but lecturer compliance remains unresolved. Do not replace its data directory.

In the SQL shell, choose a new **local** database/user and private password. [Legacy common native setup](../docs/local-setup.md#3-common-native-setup) gives the exact SQL/grants and warns about host-scoped accounts. This is private local provisioning, not a script to run on existing shared DB data. Then:

```bash
php bin/setup.php
# Edit the newly generated .env privately; retain generated APP_KEY.
php bin/migrate.php
php bin/doctor.php
# Optional disposable development sports sample; never production:
php bin/seed.php
php -S localhost:8080 -t public public/router.php
```

Native `.env` values are literal: no quotes/inline comments. DB_HOST=127.0.0.1 uses TCP; use its actual port. APP_URL must exactly match browser origin, including port. `localhost` versus `127.0.0.1` matters to CSRF Origin checking. `which php`, `php --ini` and `php -m` diagnose the selected binary and extensions. Required: PHP 8.2+, pdo_mysql, mbstring, sodium, Argon2id.

## Safe stop and diagnosis

Ctrl+C stops only your terminal PHP server. Stop a Homebrew DB service only if you started it solely for this app and no other app uses it. `lsof -nP -iTCP:8080 -sTCP:LISTEN` identifies port use; choose a free web port and update APP_URL together. Never kill unknown processes. Apple Silicon images use native supported architecture; no forced amd64 needed by the source.

**Execution status:** macOS instructions inspected, not executed on a Mac in this task. No Homebrew installation is claimed.
