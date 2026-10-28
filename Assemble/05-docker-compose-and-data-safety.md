# 🐳 Compose and durable state

Compose starts PHP 8.4/Apache `app` and MariaDB11.8 `db`. DB has **no published host port**. App binds loopback8080 by default. `depends_on` waits for DB health; container-init checks key/demo settings and runs additive migrations before Apache.

| State | Storage | Consequence if lost |
|---|---|---|
| users, approvals, bookings, encrypted TOTP records | `db_data` mounted `/var/lib/mysql` | Data loss, not fixed by rebuilding image |
| file sessions, generated local key | `app_data` mounted `/var/www/html/var` | Logouts and unreadable existing encrypted secrets if key lost |
| editable PHP/CSS | source clone, then copied into image | Container rebuild required after source edit |
| private DB credentials/config | local ignored `.env.docker` / host secret configuration | New file alone doesn't update an existing DB user |

MariaDB is a MySQL-compatible local engine; **MySQL remains mandatory in the assignment**. Test real MySQL 8 separately before declaring submission compliance. Never attach an existing MariaDB data volume to MySQL 8. Logical migration requires a private backup/restore rehearsal and human approval.

## Predictable commands

For the isolated Mac project, use all three identifying options consistently; see [Mac override](03-local-development-macos.md). Define a shell helper in your current terminal (not a credential store):

```bash
sportscompose() {
  docker compose -p nsbm-sportshub-local --env-file .env.docker -f compose.yaml -f compose.mac.yaml "$@"
}
sportscompose config --quiet
sportscompose up --build -d
sportscompose ps
sportscompose logs --tail=80 app db
sportscompose exec app php bin/doctor.php
sportscompose stop
sportscompose up -d
```

On Windows repeat the full Compose prefix rather than paste a bash function. Without Mac override choose one consistent project name and `-f compose.yaml`. `config --quiet` validates without printing interpolated secrets. Logs can contain request context; inspect privately and redact before sharing.

## Upgrade and inspect

A human first reviews clean Git state and integration permissions, then fetches/integrates approved code in their checkout. Reuse the same project/options and run `up --build -d`. Source is copied, not bind-mounted, so `restart` alone cannot load a new image. Startup migrations preserve rows; DDL can still require backup and release review.

```bash
docker volume ls --filter label=com.docker.compose.project=nsbm-sportshub-local --format '{{.Name}}'
docker ps --filter label=com.docker.compose.project=nsbm-sportshub-local --format 'table {{.Names}}\t{{.Status}}\t{{.Ports}}'
```

These inspect names/status without resolving secret values. Never print full container environment or full Compose config into evidence. A changed project name can create a second empty set of volumes and look like missing data; restore the original project/options rather than reset.

## Backup and restore boundary

Use [the database and key backup guide](09-database-migration-backup-restore.md). An image export is **not** a backup of database volumes. Copying active raw database files is not a verified consistent backup. Restore to an isolated disposable destination and verify users/relationships/key-dependent 2FA before any approved production restore.

> [!CAUTION]
> Volume removal/global prune is never an upgrade step. It can erase users, bookings, sessions and the key needed for 2FA. This task executes no volume deletion. Stop only the named SportsHub project; other Docker projects are unrelated.

Compose init-test.sql runs only on first empty DB initialization. Older volumes may lack `sportshub_test`; provision that separate DB/grant safely if you choose test suites, rather than recreating wanted volumes. Known demo accounts are local-only; demo seed requires development mode and explicit opt-in.
