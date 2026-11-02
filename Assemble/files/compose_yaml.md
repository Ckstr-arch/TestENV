# 🔍 Compose services, health and durable data

## 1 · File card

| Field | Inspected value |
|---|---|
| Canonical file | [compose.yaml](../../compose.yaml) |
| Guide owner | Assemble · team jointly; stage specialists review related behavior |
| Language / size | configuration/SQL/shell · 45 physical lines · 1,350 bytes |
| Runtime baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |
| Specification | `096258d302367aba198a83147c5f985fa74477b7`; fetched/read without merging |
| Authorship | Existing Codex-assisted baseline; no student contribution assigned by this guide |

## 2 · Why it exists

Runs the app and database as one isolated project while retaining database/session/key state in named volumes.

## 3 · Prerequisites

Read [integration overview](../00-integration-overview.md), then follow the dependencies below. Distinguish a source file, a running process and persistent state: a document describes behavior, configuration controls a tool, and stored database/key and session state survives separately from replaceable source.

## 4 · Dependencies and consumers

[Dockerfile](Dockerfile.md), [Docker env](_env_docker_example.md), [test init SQL](docker__init-test_sql.md), [data safety](../05-docker-compose-and-data-safety.md).

## 5 · Source-order coverage tour

### Database service

`db` uses MariaDB 11.8. The assignment mandates MySQL: compatibility is not automatic marking approval; verify MySQL 8 target or obtain explicit lecturer acceptance before final submission. Environment supplies fixed development DB/user names and required password substitutions; `:?` stops configuration when a required value is absent. Root password is distinct from app-user password.

Mount db_data at `/var/lib/mysql`, and bind init-test.sql read-only into the official entrypoint's initialization directory. Initial SQL runs on first empty-volume initialization, not every restart. Health check uses the image's `healthcheck.sh --connect --innodb_initialized`, every 5 seconds, timeout 5, retries 30. `restart: unless-stopped` restarts failures while respecting intentional stops. No database host port is published.

### Application service

`build: .` uses the current source context. Ports bind host 127.0.0.1:8080 to container 80, so local testing does not automatically expose the site to the internet. Environment defaults development/origin/empty key and demo off unless private file overrides; fixed DB_HOST=db uses service DNS, DB_PORT 3306 and development DB/user. app_data mounts private `/var/www/html/var`.

`depends_on` waits for healthy db before starting app. Health request uses PHP to read its internal `/api/v1/health` and find ok; Compose escapes dollar signs as `$$` so PHP, not interpolation, receives variables. Interval 10s, timeout 5, retries 10. Health is database-backed, but does not prove every student/admin flow or public TLS.

### Named volumes

Top-level db_data/app_data declarations are project-scoped by default. Database rows live in one, sessions/generated development APP_KEY in the other. Keep the same project name and override files across rebuilds. A new project may look like lost data because it selects different volumes; investigate names without deleting anything.

## 6 · Real excerpt and annotation

```text
    volumes:
      - app_data:/var/www/html/var
    depends_on:
      db:
        condition: service_healthy
```

The mount replaces var with persistent state. depends_on uses a health condition, not only container creation order. Neither statement creates a backup.

## 7 · Input → transformation → output

**Input:** Private env-file substitutions and source build configuration.

**Output:** app/db containers, project network, loopback HTTP binding and app_data / db_data volumes.

The tour above identifies the intermediate steps and their authority; copying a document or template is not evidence those steps executed.

## 8 · Connections

Use the linked consumers above and the [complete assembly inventory](../01-repository-assembly-map.md). Public traffic enters `public/`; services/configuration/learning material stay outside its document root. Canonical implementation explanations live in their owning stage, while this guide owns operational/documentation interpretation.

## 9 · Why this implementation?

Private DB networking and a loopback app port reduce local conflicts/exposure. Named volumes decouple mutable state from replaceable images, but require backup/restore planning.

## 10 · Security, correctness and accessibility

Never delete volumes as an upgrade step. Do not print resolved config with secrets; use config --quiet. Reusing a DB volume retains its existing password state. Other Docker projects must keep running untouched.

For documentation, keep supported Markdown, meaningful link labels and diagrams whose meaning does not depend on color alone. For configuration, preserve the application trust boundaries rather than hiding failures in UI.

## 11 · Common mistakes and diagnosis

Wrong project name shows empty data. App URL and published port mismatch causes 403 Origin. Changing env DB password alone causes access denied on old volume. Health dependency can fail if db storage is unhealthy; inspect logs instead of resetting.

## 12 · Read or run safely

Use [the complete isolated command base](../05-docker-compose-and-data-safety.md) for `config --quiet`, `ps`, `logs --tail`, `exec app php bin/doctor.php`, safe stop/start and up --build. No stack mutation is necessary to study this file.

These are teaching instructions. Actual outcomes, including skipped suites, are centralized in [the current evidence register](../16-evidence-and-artifact-register.md). Do not relabel a legacy or suggested command as freshly passed.

## 13 · Debugging lesson

Trace the consumer named in section 4 and verify one boundary at a time. Record filenames, versions, route/status, rule names or synthetic counts. Do not dump resolved configuration, private env files, request cookies, factor values or identity-bearing SQL exports. Diagnose the first failed prerequisite before retrying.

## 14 · Guided experiment

Draw app→db internal connection and browser→loopback mapping. Identify which two named volumes must be preserved to retain users and 2FA. Do not inspect secret values.

Keep an observation worksheet outside the public webroot. If you later make a temporary source edit, save your own small patch first, then manually undo only your lines; never overwrite another contributor's shared work or reset a real database for a lesson.

## 15 · Challenge ladder

- **Understand:** explain section 6 using the source and identify its actual consumer.
- **Modify:** propose one narrowly scoped clarification or correction only after reproducing a misunderstanding; coordinate with the source owner and verify its linked contract.
- **Engineer:** evaluate one failure in section 11, describe its evidence and trade-off, and submit genuine reviewed work through the human workflow after authorization.

Expected evidence is a correct source-to-behavior explanation and any real test result, not a cosmetic commit or copied screenshot. The experiment in section 14 supplies the safe setup; future changes must have their own focused validation.

## 16 · Knowledge check

1. Why is DB_HOST not localhost?
2. Does a healthy app prove deployment succeeded?
3. Why can a new project look empty?

<details>
<summary>Answer key · reason first</summary>

1. Inside app, localhost is that container; db is the other service DNS name.
2. No; readiness checks do not establish public hosting or full behavior.
3. Project-scoped volumes have different names.

</details>

## 17 · Contribution boundaries

Assemble coordinates integration; Stage 01 reviews presentation/accessibility, Stage 02 student behavior, Stage 03 admin operations and Stage 04 API/data/security. Identify the exact changed section before editing a shared source. Do not add frameworks, duplicate runtime copies, untested CI or public setup scripts for documentation convenience. Manual Git/PR/release actions require human authority and truthful identities; no such actions were performed for this guide.

## 18 · Status and limitations

**VERIFIED BY INSPECTION:** source-order sections and quoted text above. Current root-run safe checks include security/PHP / JS / shell and startup/doctor; see the central register for precise commands/outcomes. Database-reset suites were not rerun for this documentation task. Host/provider/MySQL 8 production compatibility, public deployment and native Windows/macOS execution remain unverified unless separately evidenced. **PLANNED:** learner experiments/contributions and production release; these are not completed merely because instructions exist.

## 19 · Glossary

**Service:** declared workload. **Health gate:** startup waits for a meaningful readiness probe. **Loopback:** host-local interface.

## 20 · One-minute explanation to personalize

Explain both traffic paths and both persistence paths; identify MariaDB-versus-mandatory-MySQL as a submission decision rather than quietly changing the DB.

Explain only what you actually studied or changed. Generated reference material is not proof of personal implementation, testing or authorship. Use the [contribution record](../templates/contribution-record.md) for your own evidence.
