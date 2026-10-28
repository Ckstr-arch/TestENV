# 🔍 Portable text and binary handling

## 1 · File card

| Field | Inspected value |
|---|---|
| Canonical file | [.gitattributes](../../.gitattributes) |
| Guide owner | Assemble · team jointly; stage specialists review related behavior |
| Language / size | configuration/SQL/shell · 6 physical lines · 96 bytes |
| Runtime baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |
| Specification | `096258d302367aba198a83147c5f985fa74477b7`; fetched/read without merging |
| Authorship | Existing Codex-assisted baseline; no student contribution assigned by this guide |

## 2 · Why it exists

Tells Git how to normalize text line endings and recognize image binaries across operating systems.

## 3 · Prerequisites

Read [integration overview](../00-integration-overview.md), then follow the dependencies below. Distinguish a source file, a running process and persistent state: a document describes behavior, configuration controls a tool, and stored database/key and session state survives separately from replaceable source.

## 4 · Dependencies and consumers

Git checkout settings, shell entrypoint and source-image evidence.

## 5 · Source-order coverage tour

| Directive | Meaning |
|---|---|
| `* text=auto` | Detect text and normalize according to Git attributes/config |
| `*.sh text eol=lf` | Shell scripts use LF; CRLF can break the interpreter line |
| `Dockerfile text eol=lf` | Stable container build syntax across Windows/Linux |
| `*.sql text eol=lf` | Stable migration source text |
| `*.png binary` | Treat PNG as binary, not line-oriented text |
| `*.jpg binary` | Same for JPEG photographs |

These rules affect repository content handling, not image compression or database collation. They do not authorize repository-wide renormalization or replacing another contributor's files.

## 6 · Real excerpt and annotation

```text
*.sh text eol=lf
Dockerfile text eol=lf
```

The glob matches all .sh paths; `text` enables normalization and `eol=lf` requests the required working-tree ending. Dockerfile is an exact name pattern.

## 7 · Input → transformation → output

**Input:** Path patterns and Git attribute assignments.

**Output:** Consistent LF-sensitive shell/SQL/Docker files and binary image handling.

The tour above identifies the intermediate steps and their authority; copying a document or template is not evidence those steps executed.

## 8 · Connections

Use the linked consumers above and the [complete assembly inventory](../01-repository-assembly-map.md). Public traffic enters `public/`; services/configuration/learning material stay outside its document root. Canonical implementation explanations live in their owning stage, while this guide owns operational/documentation interpretation.

## 9 · Why this implementation?

Explicit per-file requirements are safer than depending on each OS default. Binary flags avoid misleading text diffs of images.

## 10 · Security, correctness and accessibility

Do not run a sweeping renormalization just to remove a noisy diff during collaboration; inspect actual line-ending changes and coordinate.

For documentation, keep supported Markdown, meaningful link labels and diagrams whose meaning does not depend on color alone. For configuration, preserve the application trust boundaries rather than hiding failures in UI.

## 11 · Common mistakes and diagnosis

CRLF in an entrypoint can cause bad interpreter errors. Treating PNG as text gives meaningless diffs. Changing system Git settings can affect unrelated projects; these repository attributes are scoped.

## 12 · Read or run safely

`git check-attr text eol -- docker/entrypoint.sh Dockerfile database/schema.sql` reads attributes without changing files.

These are teaching instructions. Actual outcomes, including skipped suites, are centralized in [the current evidence register](../16-evidence-and-artifact-register.md). Do not relabel a legacy or suggested command as freshly passed.

## 13 · Debugging lesson

Trace the consumer named in section 4 and verify one boundary at a time. Record filenames, versions, route/status, rule names or synthetic counts. Do not dump resolved configuration, private env files, request cookies, factor values or identity-bearing SQL exports. Diagnose the first failed prerequisite before retrying.

## 14 · Guided experiment

Inspect script line endings in your editor and predict behavior on Linux. Do not resave every file. Close without changes if this is only an inspection.

Keep an observation worksheet outside the public webroot. If you later make a temporary source edit, save your own small patch first, then manually undo only your lines; never overwrite another contributor's shared work or reset a real database for a lesson.

## 15 · Challenge ladder

- **Understand:** explain section 6 using the source and identify its actual consumer.
- **Modify:** propose one narrowly scoped clarification or correction only after reproducing a misunderstanding; coordinate with the source owner and verify its linked contract.
- **Engineer:** evaluate one failure in section 11, describe its evidence and trade-off, and submit genuine reviewed work through the human workflow after authorization.

Expected evidence is a correct source-to-behavior explanation and any real test result, not a cosmetic commit or copied screenshot. The experiment in section 14 supplies the safe setup; future changes must have their own focused validation.

## 16 · Knowledge check

1. Why is LF important for entrypoint.sh?
2. Does binary optimize images?
3. Should a line-ending issue justify rewriting all shared files?

<details>
<summary>Answer key · reason first</summary>

1. A Unix interpreter directive may treat a carriage return as part of the executable name.
2. No; it changes Git treatment.
3. No; make the smallest reviewed correction.

</details>

## 17 · Contribution boundaries

Assemble coordinates integration; Stage 01 reviews presentation/accessibility, Stage 02 student behavior, Stage 03 admin operations and Stage 04 API/data/security. Identify the exact changed section before editing a shared source. Do not add frameworks, duplicate runtime copies, untested CI or public setup scripts for documentation convenience. Manual Git/PR/release actions require human authority and truthful identities; no such actions were performed for this guide.

## 18 · Status and limitations

**VERIFIED BY INSPECTION:** source-order sections and quoted text above. Current root-run safe checks include security/PHP / JS / shell and startup/doctor; see the central register for precise commands/outcomes. Database-reset suites were not rerun for this documentation task. Host/provider/MySQL 8 production compatibility, public deployment and native Windows/macOS execution remain unverified unless separately evidenced. **PLANNED:** learner experiments/contributions and production release; these are not completed merely because instructions exist.

## 19 · Glossary

**LF:** line-feed ending. **CRLF:** carriage-return plus line-feed. **Attribute:** Git path-specific rule.

## 20 · One-minute explanation to personalize

Use the shell entrypoint as the concrete example of why portable source needs explicit line-ending rules.

Explain only what you actually studied or changed. Generated reference material is not proof of personal implementation, testing or authorship. Use the [contribution record](../templates/contribution-record.md) for your own evidence.
