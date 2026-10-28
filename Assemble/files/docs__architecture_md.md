# 🔍 Legacy architectural overview and ER limitations

## 1 · File card

| Field | Inspected value |
|---|---|
| Canonical file | [docs/architecture.md](../../docs/architecture.md) |
| Guide owner | Assemble · team jointly; stage specialists review related behavior |
| Language / size | Markdown · 31 physical lines · 3,427 bytes |
| Runtime baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |
| Specification | `096258d302367aba198a83147c5f985fa74477b7`; fetched/read without merging |
| Authorship | Existing Codex-assisted baseline; no student contribution assigned by this guide |

## 2 · Why it exists

Summarizes runtime layers, relationships, concurrency, timezone, assets and API/community extensions.

## 3 · Prerequisites

Read [integration overview](../00-integration-overview.md), then follow the dependencies below. Distinguish a source file, a running process and persistent state: a document describes behavior, configuration controls a tool, and stored database/key and session state survives separately from replaceable source.

## 4 · Dependencies and consumers

[Current assembly map](../01-repository-assembly-map.md), Stage 04 schema dictionary, community migration and stage request-flow guides.

## 5 · Source-order coverage tour

The opening traces server rendering and enhanced REST through actual entrypoints, actions, models and security, with PDO prepared statements. The ER diagram shows users/memberships/clubs/events/tournaments/registrations/attendance/bookings/resources/recovery/audit relationships, but omits login-attempt and community tables and schema_migrations. Use the Stage 04 complete dictionary/ER for exhaustive schema coverage; do not claim this short diagram contains every table.

Subsequent prose explains one club per event, optional tournament and scores/opponent text; tournament registration is separate from event attendance. Concurrency paragraph identifies parent locks, unique constraints and own cancellation. Timezone paragraph aligns PHP Asia/Colombo with SQL+05:30. Asset paragraph records supplied archive origin while requiring rights confirmation. Final paragraphs add 77-operation contract, cookie/CSRF/errors, persistent Docker MariaDB and community/theme modules.

This guide is an overview, not a substitute for per-function guides. It accurately describes a MySQL-compatible local runtime; the assignment's mandatory MySQL target still requires separate verification.

## 6 · Real excerpt and annotation

```markdown
Time displayed and entered is Sri Lanka time.
```

This sentence is a design contract backed by PHP timezone and SQL session timezone settings, not browser locale conversion.

## 7 · Input → transformation → output

**Input:** Inspected PHP include/service structure and relational schema.

**Output:** A concise system map and an intentionally incomplete older ER overview.

The tour above identifies the intermediate steps and their authority; copying a document or template is not evidence those steps executed.

## 8 · Connections

Use the linked consumers above and the [complete assembly inventory](../01-repository-assembly-map.md). Public traffic enters `public/`; services/configuration/learning material stay outside its document root. Canonical implementation explanations live in their owning stage, while this guide owns operational/documentation interpretation.

## 9 · Why this implementation?

A compact architecture document helps new readers navigate layers before studying implementation. Detailed data relationships now live in canonical technical stage guides.

## 10 · Security, correctness and accessibility

Do not imply a diagram proves authorization or concurrency. Confirm code checks and constraints. Preserve rights/provenance warnings and the distinction between private source paths and public routes.

For documentation, keep supported Markdown, meaningful link labels and diagrams whose meaning does not depend on color alone. For configuration, preserve the application trust boundaries rather than hiding failures in UI.

## 11 · Common mistakes and diagnosis

Treating tournament registration as automatic fixture attendance breaks domain meaning. Expanding ER by guess invents relationships. Calling a text opponent a normalized team is inaccurate. Removing timezone alignment causes deadline discrepancies.

## 12 · Read or run safely

Inspect linked PHP include chains and SQL definitions; render Mermaid if tooling exists. Current Mermaid visual rendering must be distinguished from Markdown link validation.

These are teaching instructions. Actual outcomes, including skipped suites, are centralized in [the current evidence register](../16-evidence-and-artifact-register.md). Do not relabel a legacy or suggested command as freshly passed.

## 13 · Debugging lesson

Trace the consumer named in section 4 and verify one boundary at a time. Record filenames, versions, route/status, rule names or synthetic counts. Do not dump resolved configuration, private env files, request cookies, factor values or identity-bearing SQL exports. Diagnose the first failed prerequisite before retrying.

## 14 · Guided experiment

Find one community relationship absent from the legacy diagram and trace it to a real foreign key in the additive migration. Explain it in a worksheet rather than editing runtime schema.

Keep an observation worksheet outside the public webroot. If you later make a temporary source edit, save your own small patch first, then manually undo only your lines; never overwrite another contributor's shared work or reset a real database for a lesson.

## 15 · Challenge ladder

- **Understand:** explain section 6 using the source and identify its actual consumer.
- **Modify:** propose one narrowly scoped clarification or correction only after reproducing a misunderstanding; coordinate with the source owner and verify its linked contract.
- **Engineer:** evaluate one failure in section 11, describe its evidence and trade-off, and submit genuine reviewed work through the human workflow after authorization.

Expected evidence is a correct source-to-behavior explanation and any real test result, not a cosmetic commit or copied screenshot. The experiment in section 14 supplies the safe setup; future changes must have their own focused validation.

## 16 · Knowledge check

1. Is the legacy ER exhaustive?
2. Where do output timezone assumptions come from?
3. Does a PDO claim prove every query safe?

<details>
<summary>Answer key · reason first</summary>

1. No; later/community and operational tables need the complete Stage 04 map.
2. PHP default and SQL session+05:30.
3. No; inspect values, identifier allowlists and calls.

</details>

## 17 · Contribution boundaries

Assemble coordinates integration; Stage 01 reviews presentation/accessibility, Stage 02 student behavior, Stage 03 admin operations and Stage 04 API/data/security. Identify the exact changed section before editing a shared source. Do not add frameworks, duplicate runtime copies, untested CI or public setup scripts for documentation convenience. Manual Git/PR/release actions require human authority and truthful identities; no such actions were performed for this guide.

## 18 · Status and limitations

**VERIFIED BY INSPECTION:** source-order sections and quoted text above. Current root-run safe checks include security/PHP / JS / shell and startup/doctor; see the central register for precise commands/outcomes. Database-reset suites were not rerun for this documentation task. Host/provider/MySQL 8 production compatibility, public deployment and native Windows/macOS execution remain unverified unless separately evidenced. **PLANNED:** learner experiments/contributions and production release; these are not completed merely because instructions exist.

## 19 · Glossary

**ER:** entity relationship. **Layer:** responsibility boundary. **Contract:** agreed behavior between components.

## 20 · One-minute explanation to personalize

Trace a request across layers, then name the boundaries of this short architecture overview and point to the full schema guide.

Explain only what you actually studied or changed. Generated reference material is not proof of personal implementation, testing or authorship. Use the [contribution record](../templates/contribution-record.md) for your own evidence.
