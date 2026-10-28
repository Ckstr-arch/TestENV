# 🔍 First-initialization disposable test database

## 1 · File card

| Field | Inspected value |
|---|---|
| Canonical file | [docker/init-test.sql](../../docker/init-test.sql) |
| Guide owner | Assemble · team jointly; stage specialists review related behavior |
| Language / size | configuration/SQL/shell · 3 physical lines · 252 bytes |
| Runtime baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |
| Specification | `096258d302367aba198a83147c5f985fa74477b7`; fetched/read without merging |
| Authorship | Existing Codex-assisted baseline; no student contribution assigned by this guide |

## 2 · Why it exists

Creates and grants a separate test schema during initial local database volume provisioning.

## 3 · Prerequisites

Read [integration overview](../00-integration-overview.md), then follow the dependencies below. Distinguish a source file, a running process and persistent state: a document describes behavior, configuration controls a tool, and stored database/key and session state survives separately from replaceable source.

## 4 · Dependencies and consumers

[Compose bind mount](compose_yaml.md), [test fixture](../../Stage-04-Backend-Database-Security/files/tests__fixtures_php.md), [test safety](../10-ci-tests-and-quality-gates.md).

## 5 · Source-order coverage tour

The opening comment states dedicated disposable development purpose and first-volume initialization. CREATE DATABASE IF NOT EXISTS creates sportshub_test using utf8mb4/unicode_ci; it does not copy production rows or clear a database. GRANT ALL PRIVILEGES on sportshub_test.* gives the dedicated local app user enough DDL/DML for fixtures, scoped to that schema. Host wildcard matches local container-network connections under this isolated setup.

The SQL contains no password; the main user is created by image environment initialization. This file is not a recurring migration and not a production permissions template. Existing volumes do not rerun it automatically. Create missing test schema/grants through an authorized local administrator instead of replacing wanted volumes.

## 6 · Real excerpt and annotation

```sql
CREATE DATABASE IF NOT EXISTS sportshub_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

IF NOT EXISTS makes schema creation repeatable, but says nothing about whether valuable data is inside an existing schema. That is why runners still need an isolation gate.

## 7 · Input → transformation → output

**Input:** Official MariaDB first-empty-volume entrypoint running SQL as a privileged initializer.

**Output:** sportshub_test schema and test access for the local application user.

The tour above identifies the intermediate steps and their authority; copying a document or template is not evidence those steps executed.

## 8 · Connections

Use the linked consumers above and the [complete assembly inventory](../01-repository-assembly-map.md). Public traffic enters `public/`; services/configuration/learning material stay outside its document root. Canonical implementation explanations live in their owning stage, while this guide owns operational/documentation interpretation.

## 9 · Why this implementation?

Separate schema permits realistic HTTP/database integration tests while protecting main development rows. Broad privileges are justified for disposable fixtures, not for a production web-user account.

## 10 · Security, correctness and accessibility

Do not point resetting fixtures at sportshub. Do not assume merely seeing the name proves it is disposable; inspect configuration and data ownership. Production runtime should not receive schema-destructive test privileges.

For documentation, keep supported Markdown, meaningful link labels and diagrams whose meaning does not depend on color alone. For configuration, preserve the application trust boundaries rather than hiding failures in UI.

## 11 · Common mistakes and diagnosis

Unreadable bind file skips/fails initialization. Reused volume lacks test schema because init scripts run once. Assigning test grants to production account breaks least privilege. Running tests concurrently races shared resets.

## 12 · Read or run safely

Study this file and test overrides first; no SQL is executed by reading it. Current task does not rerun database-reset suites. A future authorized test run must prove isolated sportshub_test and synthetic data.

These are teaching instructions. Actual outcomes, including skipped suites, are centralized in [the current evidence register](../16-evidence-and-artifact-register.md). Do not relabel a legacy or suggested command as freshly passed.

## 13 · Debugging lesson

Trace the consumer named in section 4 and verify one boundary at a time. Record filenames, versions, route/status, rule names or synthetic counts. Do not dump resolved configuration, private env files, request cookies, factor values or identity-bearing SQL exports. Diagnose the first failed prerequisite before retrying.

## 14 · Guided experiment

Explain which privilege is needed to recreate fixture tables and why runtime INSERT permission alone is insufficient. No SQL changes required.

Keep an observation worksheet outside the public webroot. If you later make a temporary source edit, save your own small patch first, then manually undo only your lines; never overwrite another contributor's shared work or reset a real database for a lesson.

## 15 · Challenge ladder

- **Understand:** explain section 6 using the source and identify its actual consumer.
- **Modify:** propose one narrowly scoped clarification or correction only after reproducing a misunderstanding; coordinate with the source owner and verify its linked contract.
- **Engineer:** evaluate one failure in section 11, describe its evidence and trade-off, and submit genuine reviewed work through the human workflow after authorization.

Expected evidence is a correct source-to-behavior explanation and any real test result, not a cosmetic commit or copied screenshot. The experiment in section 14 supplies the safe setup; future changes must have their own focused validation.

## 16 · Knowledge check

1. Does this file reset the main DB?
2. Will restarting an old db container rerun it?
3. Does IF NOT EXISTS make fixture resets harmless?

<details>
<summary>Answer key · reason first</summary>

1. No; it creates a distinct schema and grant.
2. Not on an already initialized persistent volume.
3. No; fixtures separately destroy test tables and require proof of disposable separation.

</details>

## 17 · Contribution boundaries

Assemble coordinates integration; Stage 01 reviews presentation/accessibility, Stage 02 student behavior, Stage 03 admin operations and Stage 04 API/data/security. Identify the exact changed section before editing a shared source. Do not add frameworks, duplicate runtime copies, untested CI or public setup scripts for documentation convenience. Manual Git/PR/release actions require human authority and truthful identities; no such actions were performed for this guide.

## 18 · Status and limitations

**VERIFIED BY INSPECTION:** source-order sections and quoted text above. Current root-run safe checks include security/PHP / JS / shell and startup/doctor; see the central register for precise commands/outcomes. Database-reset suites were not rerun for this documentation task. Host/provider/MySQL 8 production compatibility, public deployment and native Windows/macOS execution remain unverified unless separately evidenced. **PLANNED:** learner experiments/contributions and production release; these are not completed merely because instructions exist.

## 19 · Glossary

**Schema/database:** named collection of SQL tables. **Grant:** database permission. **Initialization hook:** first setup action, not every-start migration.

## 20 · One-minute explanation to personalize

Distinguish creating a disposable test schema from resetting fixtures, and explain why neither belongs on a production database.

Explain only what you actually studied or changed. Generated reference material is not proof of personal implementation, testing or authorship. Use the [contribution record](../templates/contribution-record.md) for your own evidence.
