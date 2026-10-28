# 🔍 Local Docker environment template

## 1 · File card

| Field | Inspected value |
|---|---|
| Canonical file | [.env.docker.example](../../.env.docker.example) |
| Guide owner | Assemble · team jointly; stage specialists review related behavior |
| Language / size | configuration/SQL/shell · 10 physical lines · 491 bytes |
| Runtime baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |
| Specification | `096258d302367aba198a83147c5f985fa74477b7`; fetched/read without merging |
| Authorship | Existing Codex-assisted baseline; no student contribution assigned by this guide |

## 2 · Why it exists

Provides explicit development configuration for Compose without becoming a production secret store.

## 3 · Prerequisites

Read [integration overview](../00-integration-overview.md), then follow the dependencies below. Distinguish a source file, a running process and persistent state: a document describes behavior, configuration controls a tool, and stored database/key and session state survives separately from replaceable source.

## 4 · Dependencies and consumers

[Compose](compose_yaml.md), [native template](_env_example.md), [container initialization](../../Stage-04-Backend-Database-Security/files/bin__container-init_php.md).

## 5 · Source-order coverage tour

| Source item | Interpretation |
|---|---|
| Local-only comment | Warns to replace both example database passwords |
| `DB_PASSWORD` | Dedicated application database user password; value omitted here |
| `DB_ROOT_PASSWORD` | Separate database administrator password; value omitted here |
| `APP_ENV` | Development mode for this template |
| `APP_URL` | Browser origin, initially localhost:8080; update alongside a port override |
| Blank key comment | Local Docker may generate its key in private persistent app_data |
| Production warning comment | Production must provision its private key and disable demo mode |
| `APP_KEY` | Omitted private field; do not copy from a teammate or an example |
| Demo comment and `DEMO_MODE=1` | Explicitly opt into disposable sample data/accounts in development |

Compose parses/interpolates env-file syntax, unlike the small native `.env` loader. The app connects to DB_HOST=db inside the Compose network; do not replace it with your Mac loopback address. Database initialization passwords apply when the data volume is first initialized; changing only an env-file value does not rotate an existing SQL user.

## 6 · Real excerpt and annotation

```text
APP_ENV=development
APP_URL=http://localhost:8080
```

These are local defaults, not production settings. Host port 8090 needs APP_URL=http://localhost:8090 plus a matching Compose port override.

## 7 · Input → transformation → output

**Input:** Local values supplied through `--env-file .env.docker`.

**Output:** Compose substitutions for db/app services, origin, key policy and explicit demo mode.

The tour above identifies the intermediate steps and their authority; copying a document or template is not evidence those steps executed.

## 8 · Connections

Use the linked consumers above and the [complete assembly inventory](../01-repository-assembly-map.md). Public traffic enters `public/`; services/configuration/learning material stay outside its document root. Canonical implementation explanations live in their owning stage, while this guide owns operational/documentation interpretation.

## 9 · Why this implementation?

Explicit demo opt-in prevents assuming sample accounts are normal production provisioning. A persisted generated local key allows 2FA across container rebuilds.

## 10 · Security, correctness and accessibility

Do not repeat legacy known demo credentials in new deployment documents. Provision a fresh production database, unique administrator and private key, with demo mode off. Keep both env files ignored.

For documentation, keep supported Markdown, meaningful link labels and diagrams whose meaning does not depend on color alone. For configuration, preserve the application trust boundaries rather than hiding failures in UI.

## 11 · Common mistakes and diagnosis

A new env password with an old volume yields SQL access denied. New project name silently selects new named volumes. Blank APP_KEY is only supported for development fallback. Copying this file unedited to production violates demo/key requirements.

## 12 · Read or run safely

After creating/editing a private copy, use the same isolated Compose base and `config --quiet`; do not print full resolved config. See the safe Docker chapter for launch commands.

These are teaching instructions. Actual outcomes, including skipped suites, are centralized in [the current evidence register](../16-evidence-and-artifact-register.md). Do not relabel a legacy or suggested command as freshly passed.

## 13 · Debugging lesson

Trace the consumer named in section 4 and verify one boundary at a time. Record filenames, versions, route/status, rule names or synthetic counts. Do not dump resolved configuration, private env files, request cookies, factor values or identity-bearing SQL exports. Diagnose the first failed prerequisite before retrying.

## 14 · Guided experiment

On paper distinguish image, container, project name, app_data and db_data. Explain where changes survive a rebuild and why credentials on a reused DB are not automatically changed.

Keep an observation worksheet outside the public webroot. If you later make a temporary source edit, save your own small patch first, then manually undo only your lines; never overwrite another contributor's shared work or reset a real database for a lesson.

## 15 · Challenge ladder

- **Understand:** explain section 6 using the source and identify its actual consumer.
- **Modify:** propose one narrowly scoped clarification or correction only after reproducing a misunderstanding; coordinate with the source owner and verify its linked contract.
- **Engineer:** evaluate one failure in section 11, describe its evidence and trade-off, and submit genuine reviewed work through the human workflow after authorization.

Expected evidence is a correct source-to-behavior explanation and any real test result, not a cosmetic commit or copied screenshot. The experiment in section 14 supplies the safe setup; future changes must have their own focused validation.

## 16 · Knowledge check

1. Which password is the website login?
2. Why does rebuilding retain enrolled 2FA?
3. Is template DEMO_MODE1 safe on a public host?

<details>
<summary>Answer key · reason first</summary>

1. Neither; both fields configure database accounts.
2. The key persists in app_data or privately supplied configuration.
3. No; disable demo mode and use fresh production data.

</details>

## 17 · Contribution boundaries

Assemble coordinates integration; Stage 01 reviews presentation/accessibility, Stage 02 student behavior, Stage 03 admin operations and Stage 04 API/data/security. Identify the exact changed section before editing a shared source. Do not add frameworks, duplicate runtime copies, untested CI or public setup scripts for documentation convenience. Manual Git/PR/release actions require human authority and truthful identities; no such actions were performed for this guide.

## 18 · Status and limitations

**VERIFIED BY INSPECTION:** source-order sections and quoted text above. Current root-run safe checks include security/PHP / JS / shell and startup/doctor; see the central register for precise commands/outcomes. Database-reset suites were not rerun for this documentation task. Host/provider/MySQL 8 production compatibility, public deployment and native Windows/macOS execution remain unverified unless separately evidenced. **PLANNED:** learner experiments/contributions and production release; these are not completed merely because instructions exist.

## 19 · Glossary

**Interpolation:** Compose substitution into configuration. **Named volume:** durable project-scoped storage. **Demo mode:** explicit disposable development seeding.

## 20 · One-minute explanation to personalize

Explain the two different database passwords, exact browser origin and why local key generation depends on persistent storage.

Explain only what you actually studied or changed. Generated reference material is not proof of personal implementation, testing or authorship. Use the [contribution record](../templates/contribution-record.md) for your own evidence.
