# 🔍 Container startup and process handoff

## 1 · File card

| Field | Inspected value |
|---|---|
| Canonical file | [docker/entrypoint.sh](../../docker/entrypoint.sh) |
| Guide owner | Assemble · team jointly; stage specialists review related behavior |
| Language / size | configuration/SQL/shell · 8 physical lines · 170 bytes |
| Runtime baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |
| Specification | `096258d302367aba198a83147c5f985fa74477b7`; fetched/read without merging |
| Authorship | Existing Codex-assisted baseline; no student contribution assigned by this guide |

## 2 · Why it exists

Prepares private runtime state, initializes the app and transfers control to the official PHP/Apache process.

## 3 · Prerequisites

Read [integration overview](../00-integration-overview.md), then follow the dependencies below. Distinguish a source file, a running process and persistent state: a document describes behavior, configuration controls a tool, and stored database/key and session state survives separately from replaceable source.

## 4 · Dependencies and consumers

[Container init](../../Stage-04-Backend-Database-Security/files/bin__container-init_php.md), [Dockerfile](Dockerfile.md), [Compose](compose_yaml.md).

## 5 · Source-order coverage tour

1. `#!/bin/sh` selects POSIX shell; .gitattributes ensures LF.
2. `set -eu` stops on command failure and unset variable expansion instead of continuing with half-initialized state.
3. `cd /var/www/html` makes relative bin/var paths predictable.
4. `mkdir -p var/sessions` creates only the required runtime directory, preserving existing contents.
5. `php bin/container-init.php` checks key/demo policy and applies migrations; explicit development demo mode may seed known disposable data. This is a write to the configured DB, so upgrades need reviewed backups/migrations.
6. `chown -R www-data:www-data var` gives runtime ownership to Apache; `chmod700 var/sessions` makes private sessions accessible only to that owner.
7. `exec docker-php-entrypoint "$@"` replaces shell with official image entrypoint and preserves argument boundaries. Default CMD is apache2-foreground; PID and signal behavior matters for clean stops.

A failure before exec prevents serving a half-ready app. Restarting repeatedly does not authorize deleting data to escape a migration/key error.

## 6 · Real excerpt and annotation

```bash
php bin/container-init.php
chown -R www-data:www-data var
chmod 700 var/sessions
exec docker-php-entrypoint "$@"
```

Initialization must succeed before the server starts. Quoted "$@" preserves command arguments; exec replaces the current process rather than leaving a wrapper shell.

## 7 · Input → transformation → output

**Input:** Container environment, persistent var mount and command arguments.

**Output:** Migrated/initialized runtime, private sessions owned by www-data, and foreground server process.

The tour above identifies the intermediate steps and their authority; copying a document or template is not evidence those steps executed.

## 8 · Connections

Use the linked consumers above and the [complete assembly inventory](../01-repository-assembly-map.md). Public traffic enters `public/`; services/configuration/learning material stay outside its document root. Canonical implementation explanations live in their owning stage, while this guide owns operational/documentation interpretation.

## 9 · Why this implementation?

Startup migration supports portable local deployment and reuses additive migration history. It trades simplicity for needing careful permissions/privilege management; a production host may separate migration from runtime provisioning.

## 10 · Security, correctness and accessibility

The root container startup privileges shown here must not become blanket host chmod/chown instructions. Keep var persistent and private. APP_KEY loss prevents decrypting existing 2FA secrets.

For documentation, keep supported Markdown, meaningful link labels and diagrams whose meaning does not depend on color alone. For configuration, preserve the application trust boundaries rather than hiding failures in UI.

## 11 · Common mistakes and diagnosis

CRLF causes interpreter errors. Missing executable mode prevents startup. Migration credentials lacking DDL stop init. Wrong app_data ownership breaks sessions. A production missing key must be provisioned privately, not fixed by demo fallback.

## 12 · Read or run safely

`sh -n docker/entrypoint.sh` is safe syntax validation; it does not execute migrations. See root register for actual syntax outcome. Do not run this script directly on a host filesystem.

These are teaching instructions. Actual outcomes, including skipped suites, are centralized in [the current evidence register](../16-evidence-and-artifact-register.md). Do not relabel a legacy or suggested command as freshly passed.

## 13 · Debugging lesson

Trace the consumer named in section 4 and verify one boundary at a time. Record filenames, versions, route/status, rule names or synthetic counts. Do not dump resolved configuration, private env files, request cookies, factor values or identity-bearing SQL exports. Diagnose the first failed prerequisite before retrying.

## 14 · Guided experiment

Explain the last command before Apache starts and predict outcome if container-init exits nonzero. Use a flow drawing, not an intentional live failure.

Keep an observation worksheet outside the public webroot. If you later make a temporary source edit, save your own small patch first, then manually undo only your lines; never overwrite another contributor's shared work or reset a real database for a lesson.

## 15 · Challenge ladder

- **Understand:** explain section 6 using the source and identify its actual consumer.
- **Modify:** propose one narrowly scoped clarification or correction only after reproducing a misunderstanding; coordinate with the source owner and verify its linked contract.
- **Engineer:** evaluate one failure in section 11, describe its evidence and trade-off, and submit genuine reviewed work through the human workflow after authorization.

Expected evidence is a correct source-to-behavior explanation and any real test result, not a cosmetic commit or copied screenshot. The experiment in section 14 supplies the safe setup; future changes must have their own focused validation.

## 16 · Knowledge check

1. Does shell syntax check run migrations?
2. Why exec rather than start Apache in background?
3. What state survives image replacement?

<details>
<summary>Answer key · reason first</summary>

1. No; sh -n parses only.
2. The foreground process receives lifecycle signals and controls container lifetime.
3. Mounted var and database volumes, if retained.

</details>

## 17 · Contribution boundaries

Assemble coordinates integration; Stage 01 reviews presentation/accessibility, Stage 02 student behavior, Stage 03 admin operations and Stage 04 API/data/security. Identify the exact changed section before editing a shared source. Do not add frameworks, duplicate runtime copies, untested CI or public setup scripts for documentation convenience. Manual Git/PR/release actions require human authority and truthful identities; no such actions were performed for this guide.

## 18 · Status and limitations

**VERIFIED BY INSPECTION:** source-order sections and quoted text above. Current root-run safe checks include security/PHP / JS / shell and startup/doctor; see the central register for precise commands/outcomes. Database-reset suites were not rerun for this documentation task. Host/provider/MySQL 8 production compatibility, public deployment and native Windows/macOS execution remain unverified unless separately evidenced. **PLANNED:** learner experiments/contributions and production release; these are not completed merely because instructions exist.

## 19 · Glossary

**PID:** process identifier. **Exec:** replace current process. **Fail-fast:** stop on unmet prerequisite.

## 20 · One-minute explanation to personalize

Explain startup readiness, durable key and session storage and clean process handoff without suggesting a database reset.

Explain only what you actually studied or changed. Generated reference material is not proof of personal implementation, testing or authorship. Use the [contribution record](../templates/contribution-record.md) for your own evidence.
