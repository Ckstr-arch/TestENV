# 🗄️ Migrations, backup and restore

A migration is a reviewed schema change; a backup is recoverable state; a rollback is an operational decision. None means “reset the database”. `bin/migrate.php` creates missing base tables, uses schema_migrations versions and applies community DDL additively under an advisory lock. Existing data/key stay intact, but DDL can auto-commit. Never edit an already-applied migration to change its meaning.

## Before a migration

1. Record actual selected database name/server version privately without credential values. Confirm intended environment.
2. Read SQL and relationships in [data dictionary](../Stage-04-Backend-Database-Security/data-dictionary.md); consider locks, rows, indexes and cascade behavior.
3. Back up DB and stable APP_KEY/private state; keep export outside checkout/public root with restrictive permissions and encryption.
4. Restore into a **new isolated destination**; run migrations, doctor and synthetic behavior there. Real MySQL 8 compatibility is not proven by MariaDB passing.
5. Obtain human release approval; use migration privileges only for the intended operation.

## Practical native logical backup — human-only procedure

Run from a **private directory outside Git/public root**. Use your actual DB server/user/name; this example prompts for a password, never embeds it. The symbolic example target must be replaced deliberately. MySQL client required; MariaDB uses `mariadb-dump` on that engine.

```bash
umask 077
mysqldump --host=127.0.0.1 --user=sportshub_backup --password \
  --single-transaction --routines --triggers --no-tablespaces \
  --result-file=./sportshub-private-backup.sql sportshub
```

Review least-privilege requirements with host/version (routines/triggers may need extra privileges). Exit 0 and file existence are not enough: verify file nonempty privately, encrypt/store under restricted access, and restore rehearsal. Logical exports contain identities/password hashes/encrypted 2FA, so never attach to PR/chat or commit. Single-transaction protects InnoDB consistency while no concurrent DDL runs; schedule accordingly.

## Compose backup

Use the exact project prefix. For automation use a private client option file/host secret mount with correct ownership and restrictive permissions, not `-pPASSWORD` or expanded environment credentials in argv. Provisioning that secret mount is **PLANNED host-specific operations**, not present in compose.yaml. `docker compose exec -it db mariadb-dump ... --password --result-file=/private-mounted-backup/...` is viable only after you add and verify that private backup destination. It is not safe to assume such a mount already exists. Avoid raw-volume copies of a running DB and avoid image-export-as-backup misconceptions.

## Isolated restore rehearsal

Human creates a new restricted empty restore database, never the working `sportshub`. Use mysql/mariadb interactive `--password`, select that intended destination and import the private dump. A restore writes data; require explicit target approval and check the dump for USE/CREATE DATABASE statements that could override your target. Test schema counts, relationships, login/2FA with corresponding stable key and bookings. Record only redacted pass/fail and artifact identifier, not records or hashes. This task did not execute a backup/restore or destructive reset.

## Production rollback choices

If only code changed and schema remains backward-compatible, revert release pointer to previously approved build while retaining state/key. If new schema is incompatible, maintenance window and reviewed corrective migration or verified backup restoration may be needed. Restoring old DB loses writes since backup; state the recovery-point trade-off and get human approval. Never assume automatic down migrations exist: they do not.

## Retention

Define who can read backups, encryption/key recovery, retention, deletion approval, restore frequency and audit/session/log cleanup. The app does not implement a complete institutional retention policy. Store APP_KEY recovery separately but map it securely to correct environment. Do not expose backup manifests with secrets.
