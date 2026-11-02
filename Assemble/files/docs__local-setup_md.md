# 🔍 Legacy cross-platform setup and safety qualifications

## 1 · File card

| Field | Inspected value |
|---|---|
| Canonical file | [docs/local-setup.md](../../docs/local-setup.md) |
| Guide owner | Assemble · team jointly; stage specialists review related behavior |
| Language / size | Markdown · 313 physical lines · 19,316 bytes |
| Runtime baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |
| Specification | `096258d302367aba198a83147c5f985fa74477b7`; fetched/read without merging |
| Authorship | Existing Codex-assisted baseline; no student contribution assigned by this guide |

## 2 · Why it exists

Provides portable native/Docker setup instructions with local demo usage, tests and troubleshooting.

## 3 · Prerequisites

Read [integration overview](../00-integration-overview.md), then follow the dependencies below. Distinguish a source file, a running process and persistent state: a document describes behavior, configuration controls a tool, and stored database/key and session state survives separately from replaceable source.

## 4 · Dependencies and consumers

[Mac guide](../03-local-development-macos.md), [Windows/Linux guide](../04-local-development-windows-linux.md), [Docker safety](../05-docker-compose-and-data-safety.md), [production runbook](../07-production-deployment-runbook.md).

## 5 · Source-order coverage tour

### 1 · Pick a route

Compares Docker, Windows native, Linux native and macOS Homebrew. No Node/Composer requirement. Built-in PHP server requires public/router.php for clean API paths. A compatible PHP extension set matters more than package-brand assumptions.

### 2 · Test accounts

Lists known disposable development identities and an authenticator seed in the legacy source. Their values are intentionally not repeated here. These must never enter a production database; explicit demo seeding refuses production and preserves existing accounts rather than resetting changed passwords.

### 3 · Common native setup, A–D

A creates database/user and optional separate test schema; local grants include schema privileges while production separates migration/runtime. TCP host grants need to match the chosen connection host. B uses setup.php to create private env/key once and explains literal value parsing/exact APP_URL. C runs additive migrations and optional development seed/demo scripts, then doctor. Existing sample dates do not advance on restart. D starts the development server with public document root/router, health URL and Ctrl+C lifecycle.

### 4 · Windows and Windows tests

PowerShell/path examples, CLI php.ini identification, PDO/mbstring/sodium/Argon2id confirmation and explicit executable invocation. XAMPP often bundles MariaDB even when its control says MySQL; assignment compatibility must be checked. ACLs protect private files; avoid htdocs exposure and blanket permissions. Python integration commands need disposable schema gate.

### 5–6 · Linux and macOS

Distribution packages/services and Homebrew packages differ by OS. PHP version/extensions must be inspected, not assumed. Root SQL authentication and Homebrew prefix may already be configured; do not overwrite another project’s service/account. Legacy native examples use MariaDB; new Assemble highlights mandatory MySQL 8 target verification/lecturer approval.

### 7 · Docker and nested subsections

Prerequisites verify Docker/Compose; private settings choose both SQL passwords, local key persistence and explicit demo mode. `config --quiet` avoids showing resolved secrets. Launch/health/logs/doctor and test commands follow. Stop/start/rebuild retain state; COPY source requires rebuild. **Safety override:** the legacy prose discusses destructive volume deletion as an intentional disposable reset. It is never an upgrade instruction and this task does not authorize or execute it. Prefer only the new data-preserving Docker guide. Database initialization runs once; changing env passwords cannot rotate an old DB user. Optional browser tests need Playwright/Chromium and reset only verified test schema.

### 8–10 · UI verification, troubleshooting, submission

Manual signup/approval/enrollment/booking/results/reports/2FA flow, Network comparison and native fallback. Troubleshooting separates driver/module/DB/schema/router/Origin/CSRF/time/permissions/ports. Production submission needs actual people/links, HTTPS, private config, backups and real host validation; a laptop/local container is not the final public service.

## 6 · Real excerpt and annotation

```markdown
The document root is `public/`, never the whole project.
```

The path is an HTTP exposure boundary, not merely a convenient working directory. It keeps .env, services, SQL and sessions out of downloads.

## 7 · Input → transformation → output

**Input:** A cloned/extracted repository, supported OS tools and private local settings.

**Output:** A locally running development application when prerequisites succeed; not public hosting.

The tour above identifies the intermediate steps and their authority; copying a document or template is not evidence those steps executed.

## 8 · Connections

Use the linked consumers above and the [complete assembly inventory](../01-repository-assembly-map.md). Public traffic enters `public/`; services/configuration/learning material stay outside its document root. Canonical implementation explanations live in their owning stage, while this guide owns operational/documentation interpretation.

## 9 · Why this implementation?

One cross-platform reference preserves the original implementation setup. New Assemble splits OS/data/hosting concerns for safer learning rather than rewriting the runtime.

## 10 · Security, correctness and accessibility

Do not run legacy destructive reset examples, print known credentials in new evidence, or assume all package distributions satisfy mandatory MySQL. Keep production separate and require real verification.

For documentation, keep supported Markdown, meaningful link labels and diagrams whose meaning does not depend on color alone. For configuration, preserve the application trust boundaries rather than hiding failures in UI.

## 11 · Common mistakes and diagnosis

Launching PHP -S without router producesAPI 404. Editing the wrong php.ini leaves modules missing. Running seed on old data does not refresh dates. New Compose project name selects different volumes. A blanket chmod 777 hides permission ownership errors.

## 12 · Read or run safely

Follow one selected route, not all at once. Current task ran safe Linux/cloud checks only; Windows/macOS steps are documented but not executed here. See central evidence before claiming portability.

These are teaching instructions. Actual outcomes, including skipped suites, are centralized in [the current evidence register](../16-evidence-and-artifact-register.md). Do not relabel a legacy or suggested command as freshly passed.

## 13 · Debugging lesson

Trace the consumer named in section 4 and verify one boundary at a time. Record filenames, versions, route/status, rule names or synthetic counts. Do not dump resolved configuration, private env files, request cookies, factor values or identity-bearing SQL exports. Diagnose the first failed prerequisite before retrying.

## 14 · Guided experiment

Choose your OS, list exact selected PHP path/version/modules and identify the webroot/router/origin before starting. Record names/status only. Do not alter unrelated Docker stacks or existing DB credentials.

Keep an observation worksheet outside the public webroot. If you later make a temporary source edit, save your own small patch first, then manually undo only your lines; never overwrite another contributor's shared work or reset a real database for a lesson.

## 15 · Challenge ladder

- **Understand:** explain section 6 using the source and identify its actual consumer.
- **Modify:** propose one narrowly scoped clarification or correction only after reproducing a misunderstanding; coordinate with the source owner and verify its linked contract.
- **Engineer:** evaluate one failure in section 11, describe its evidence and trade-off, and submit genuine reviewed work through the human workflow after authorization.

Expected evidence is a correct source-to-behavior explanation and any real test result, not a cosmetic commit or copied screenshot. The experiment in section 14 supplies the safe setup; future changes must have their own focused validation.

## 16 · Knowledge check

1. Does XAMPP’s MySQL label guarantee MySQL 8?
2. Why keep the same Compose project?
3. Is a local health response proof of a public submission host?

<details>
<summary>Answer key · reason first</summary>

1. No; many versions bundle MariaDB.
2. It selects the same namespaced data/key volumes.
3. No; public PHP/MySQL/HTTPS/access still need validation.

</details>

## 17 · Contribution boundaries

Assemble coordinates integration; Stage 01 reviews presentation/accessibility, Stage 02 student behavior, Stage 03 admin operations and Stage 04 API/data/security. Identify the exact changed section before editing a shared source. Do not add frameworks, duplicate runtime copies, untested CI or public setup scripts for documentation convenience. Manual Git/PR/release actions require human authority and truthful identities; no such actions were performed for this guide.

## 18 · Status and limitations

**VERIFIED BY INSPECTION:** source-order sections and quoted text above. Current root-run safe checks include security/PHP / JS / shell and startup/doctor; see the central register for precise commands/outcomes. Database-reset suites were not rerun for this documentation task. Host/provider/MySQL 8 production compatibility, public deployment and native Windows/macOS execution remain unverified unless separately evidenced. **PLANNED:** learner experiments/contributions and production release; these are not completed merely because instructions exist.

## 19 · Glossary

**CLI:** terminal program interface. **ACL:** filesystem access-control list. **Router:** request-path dispatcher.

## 20 · One-minute explanation to personalize

Explain one exact native or Docker route for your machine, then state what was actually tested and what remains a host/assignment decision.

Explain only what you actually studied or changed. Generated reference material is not proof of personal implementation, testing or authorship. Use the [contribution record](../templates/contribution-record.md) for your own evidence.
