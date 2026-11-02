# 00 — Start with one request

[Dashboard](README.md) · Next: [architecture](01-architecture.md)

A backend is the code that runs on the server and decides which data may be read or changed. SportsHub’s backend is PHP plus a relational database; a page and a JSON endpoint are two different ways to reach it. Learn the common rules before changing one transport.

## Prerequisites and a safe tour

You should recognize variables, loops and functions. You do not need a framework or a Node build. Read [PHP syntax/HTTP foundations](php-http-and-security.md) when a term is unfamiliar. Install PHP 8.2+ with PDO MySQL, mbstring, sodium and Argon2id and a supported local DB using [Assemble macOS](../Assemble/03-local-development-macos.md) or [Windows/Linux](../Assemble/04-local-development-windows-linux.md). Docker instructions are [separate](../Assemble/05-docker-compose-and-data-safety.md). Keep the route you choose consistent.

From the repository root, these inspection commands do not mutate the database:

```bash
php --version
php -m
php -l public/index.php
php -l public/api.php
```

Inspect `bin/doctor.php` before invoking it; it checks dependencies/schema/storage and may create a private session directory. Do not publish its environment, keys or DB credentials. A module list verifies CLI PHP only; Apache/FPM may load a different configuration.

## Five-minute inspection task

1. Open [public/index.php](../public/index.php) and find the POST branch.
2. Open [actions](files/app__actions_php.md), find `join`, then `reserve` in [models](files/app__models_php.md).
3. Read the memberships SQL definition in [the schema guide](files/database__schema_sql.md). Identify the unique user+club pair and default pending status.
4. Predict which row changes if a rejected request is resubmitted. Explain why a second pending request does not create another row.
5. Compare [REST dispatcher](files/app__api_php.md): the JSON body is `{}` for this action because the club ID is already in the route.

Expected output is a written trace with actual function names, not a new account or database reset. No production edit is needed; discard your scratch notes if you want to restart.

## Milestones

- [ ] I can distinguish request data from trusted server metadata.
- [ ] I can explain why hidden buttons are not access control.
- [ ] I can predict a native redirect and the matching JSON response.
- [ ] I know which tests reset only the disposable schema and how to prove that before running them.
- [ ] I can explain the difference between password hashing and secret encryption.

Keep evidence in [the human record](09-completion-evidence.md). Nothing is prefilled as your work.
