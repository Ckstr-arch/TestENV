# 🔍 Native environment template

## 1 · File card

| Field | Inspected value |
|---|---|
| Canonical file | [.env.example](../../.env.example) |
| Guide owner | Assemble · team jointly; stage specialists review related behavior |
| Language / size | configuration/SQL/shell · 9 physical lines · 212 bytes |
| Runtime baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |
| Specification | `096258d302367aba198a83147c5f985fa74477b7`; fetched/read without merging |
| Authorship | Existing Codex-assisted baseline; no student contribution assigned by this guide |

## 2 · Why it exists

Names the native runtime configuration fields without storing a working user’s password or encryption key.

## 3 · Prerequisites

Read [integration overview](../00-integration-overview.md), then follow the dependencies below. Distinguish a source file, a running process and persistent state: a document describes behavior, configuration controls a tool, and stored database/key and session state survives separately from replaceable source.

## 4 · Dependencies and consumers

[Bootstrap](../../Stage-04-Backend-Database-Security/files/app__bootstrap_php.md), [setup](../../Stage-04-Backend-Database-Security/files/bin__setup_php.md), [key safety](../08-secrets-security-2fa-and-https.md).

## 5 · Source-order coverage tour

| Variable / comment, source order | Purpose |
|---|---|
| `APP_ENV` | Development default; production enables secure operational behavior |
| `APP_URL` | Exact browser origin, including scheme/host/port; native default is localhost:8080 |
| `DB_HOST` | Native TCP connection target, initially loopback 127.0.0.1 |
| `DB_PORT` | SQL service TCP port, initially 3306 |
| `DB_NAME` | Main development database name; never redirect destructive tests here |
| `DB_USER` | Dedicated app database account |
| `DB_PASSWORD` | Private database password field, intentionally not reproduced here |
| Key generation comment | Uses cryptographically random 32 bytes encoded base64 |
| `APP_KEY` | Private encryption key field, intentionally not reproduced here |

Native loader splits the first `=` and trims values. It does not implement shell evaluation, surrounding-quote removal or inline comments. Enter literal values without quotes/inline comments. Existing process environment wins over the file. `bin/setup.php` preserves an existing `.env`; it must not silently replace enrolled users' key.

## 6 · Real excerpt and annotation

```text
APP_ENV=development
APP_URL=http://localhost:8080
```

Development is a mode value; APP_URL is an origin, not a filesystem path. No shell expansion is performed by the native loader.

## 7 · Input → transformation → output

**Input:** Human-reviewed local environment values, or bin/setup.php generating a new private copy.

**Output:** A private `.env` consumed by bootstrap; the example itself is not automatically secret configuration.

The tour above identifies the intermediate steps and their authority; copying a document or template is not evidence those steps executed.

## 8 · Connections

Use the linked consumers above and the [complete assembly inventory](../01-repository-assembly-map.md). Public traffic enters `public/`; services/configuration/learning material stay outside its document root. Canonical implementation explanations live in their owning stage, while this guide owns operational/documentation interpretation.

## 9 · Why this implementation?

Separating template and private copy makes setup reproducible without sharing credentials. Process environment precedence allows host/container injection.

## 10 · Security, correctness and accessibility

Database password is unrelated to website login or the Mac password. Keep APP_KEY stable once 2FA users enroll. Never paste actual values in PRs, command arguments, logs or screenshots.

For documentation, keep supported Markdown, meaningful link labels and diagrams whose meaning does not depend on color alone. For configuration, preserve the application trust boundaries rather than hiding failures in UI.

## 11 · Common mistakes and diagnosis

Quotes become literal native values and cause auth/key parsing failures. Wrong port or localhost-versus 127.0.0.1 causes Origin failures. Blank production APP_KEY prevents secure initialization. Editing ignored .env changes local behavior, not a collaborator’s environment.

## 12 · Read or run safely

Run `php bin/doctor.php` after private setup; inspect only variable names/presence, not values. `php bin/setup.php` creates a private file only when absent; it is not a credential reset.

These are teaching instructions. Actual outcomes, including skipped suites, are centralized in [the current evidence register](../16-evidence-and-artifact-register.md). Do not relabel a legacy or suggested command as freshly passed.

## 13 · Debugging lesson

Trace the consumer named in section 4 and verify one boundary at a time. Record filenames, versions, route/status, rule names or synthetic counts. Do not dump resolved configuration, private env files, request cookies, factor values or identity-bearing SQL exports. Diagnose the first failed prerequisite before retrying.

## 14 · Guided experiment

Compare this template’s field names with bootstrap config calls. Explain which are origins, database identifiers and secrets. Use a worksheet with names only; no settings need changing.

Keep an observation worksheet outside the public webroot. If you later make a temporary source edit, save your own small patch first, then manually undo only your lines; never overwrite another contributor's shared work or reset a real database for a lesson.

## 15 · Challenge ladder

- **Understand:** explain section 6 using the source and identify its actual consumer.
- **Modify:** propose one narrowly scoped clarification or correction only after reproducing a misunderstanding; coordinate with the source owner and verify its linked contract.
- **Engineer:** evaluate one failure in section 11, describe its evidence and trade-off, and submit genuine reviewed work through the human workflow after authorization.

Expected evidence is a correct source-to-behavior explanation and any real test result, not a cosmetic commit or copied screenshot. The experiment in section 14 supplies the safe setup; future changes must have their own focused validation.

## 16 · Knowledge check

1. Does editing this example update an existing .env?
2. Why cannot two hostnames be mixed casually?
3. Can APP_KEY be regenerated on every restart?

<details>
<summary>Answer key · reason first</summary>

1. No; private configuration is separate.
2. Mutation Origin must match APP_URL exactly.
3. No; existing encrypted 2FA secrets depend on it.

</details>

## 17 · Contribution boundaries

Assemble coordinates integration; Stage 01 reviews presentation/accessibility, Stage 02 student behavior, Stage 03 admin operations and Stage 04 API/data/security. Identify the exact changed section before editing a shared source. Do not add frameworks, duplicate runtime copies, untested CI or public setup scripts for documentation convenience. Manual Git/PR/release actions require human authority and truthful identities; no such actions were performed for this guide.

## 18 · Status and limitations

**VERIFIED BY INSPECTION:** source-order sections and quoted text above. Current root-run safe checks include security/PHP / JS / shell and startup/doctor; see the central register for precise commands/outcomes. Database-reset suites were not rerun for this documentation task. Host/provider/MySQL 8 production compatibility, public deployment and native Windows/macOS execution remain unverified unless separately evidenced. **PLANNED:** learner experiments/contributions and production release; these are not completed merely because instructions exist.

## 19 · Glossary

**Environment:** settings supplied to a process. **Origin:** scheme/host/port. **Base64:** encoding, not encryption.

## 20 · One-minute explanation to personalize

Explain the path from template to private configuration to PDO/session behavior, and distinguish key persistence from password choice.

Explain only what you actually studied or changed. Generated reference material is not proof of personal implementation, testing or authorship. Use the [contribution record](../templates/contribution-record.md) for your own evidence.
