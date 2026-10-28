# 00 · Start with a sports-office decision

[Dashboard](README.md) → [Architecture](01-architecture.md)

A student asks to join a club. The administrator sees their request, but clicking Approve is only the beginning: PHP must verify the actor's role, reread pending status, check capacity and persist a consistent decision. This stage teaches that decision path and the administrative records behind it.

## Baseline and scope

Inspected canonical code: `f81eab54ebe7179e1763f364d6b8d4f564c36541` on local `main`. The fetched master specification is from `096258d302367aba198a83147c5f985fa74477b7`; it only adds the master prompt. The root audit found no pre-existing local changes. No runtime module is copied into this folder.

The four stages divide one Topic 3 application, not four assignments. Stage 03 owns operational requirements and relevant sections of shared files. Stage 04 reviews PHP/SQL/security; Stage 02 checks student consequences; Stage 01 reviews markup/accessibility.

## Prerequisites and vocabulary

| Know first | Example here | Learn it in |
|---|---|---|
| PHP array/key lookup | `entities()['clubs']['fields']` | [models section guide](files/app__models_php__admin_sections.md) |
| GET versus POST | edit link reads; save form writes | [actions section guide](files/app__actions_php__admin_sections.md) |
| Database row and key | membershipID differs from clubID | [data guide](../Stage-04-Backend-Database-Security/files/database__schema_sql.md) |
| Authentication versus authorization | signed in does not mean admin | [architecture](01-architecture.md) |
| HTML escaping | user description becomes text | [views section guide](files/app__views_php__admin_sections.md) |

## Get a local environment without touching others

Follow [macOS](../Assemble/03-local-development-macos.md) or [Windows/Linux](../Assemble/04-local-development-windows-linux.md). Reuse the same Compose project name and override for existing installs. Keep `.env`, application key, sessions and database private. The development website lives at the exact configured `APP_URL`; changing `localhost` to `127.0.0.1` can break Origin checks.

From source root the development server command is:

```bash
php -S localhost:8080 -t public public/router.php
```

Use it only if local setup has prepared PHP extensions, DB, migrations and private configuration. Docker users instead use the documented Compose workflow, not a second PHP server. First inspect `/api/v1/health`, then sign in with your disposable admin account. Demo credentials remain in legacy dev-only setup documentation and must never go live or appear in submitted screenshots.

## Five-minute inspection task

1. Open [public/index.php](../public/index.php) and find the admin page guard.
2. Open [app/actions.php](../app/actions.php) and locate the second admin guard before `save`.
3. Open [app/models.php](../app/models.php) and name the four allowed entities.
4. Predict whether sending a `save` POST from the student dashboard grants admin access.
5. Explain why there are guards in both the page and service paths.

Expected: a student gets403 before the core admin write; hiding the menu alone would be insufficient. No request, code edit or database reset is required.

## Your study rhythm

Read a source excerpt, predict the result, then run a safe synthetic experiment. Keep the prediction even if it was wrong: a corrected explanation is learning evidence. Use [labs](05-labs-and-exercises.md), then answer the [knowledge check](08-knowledge-check.md) without its answer key. Only then agree a small implementation task and its [review boundary](07-contribution-contract.md).
