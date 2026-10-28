# 🔍 Docker build context privacy

## 1 · File card

| Field | Inspected value |
|---|---|
| Canonical file | [.dockerignore](../../.dockerignore) |
| Guide owner | Assemble · team jointly; stage specialists review related behavior |
| Language / size | configuration/SQL/shell · 7 physical lines · 61 bytes |
| Runtime baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |
| Specification | `096258d302367aba198a83147c5f985fa74477b7`; fetched/read without merging |
| Authorship | Existing Codex-assisted baseline; no student contribution assigned by this guide |

## 2 · Why it exists

Filters files before Docker receives the build context; it does not control Git tracking or live database volumes.

## 3 · Prerequisites

Read [integration overview](../00-integration-overview.md), then follow the dependencies below. Distinguish a source file, a running process and persistent state: a document describes behavior, configuration controls a tool, and stored database/key and session state survives separately from replaceable source.

## 4 · Dependencies and consumers

[Dockerfile](Dockerfile.md), [Git ignore](_gitignore.md) and the local environment/volume policy.

## 5 · Source-order coverage tour

| Source pattern, in order | Effect and teaching point |
|---|---|
| `.git` | Excludes Git history/config from the image context |
| `.env` | Keeps native private config out of the image |
| `.env.docker` | Keeps local Docker settings/passwords out |
| `var` | Excludes session files and generated encryption key; Compose supplies persistent state separately |
| `__pycache__` | Excludes generated Python caches |
| `*.zip` | Avoids embedding source/archive artifacts |
| `compose.mac.yaml` | Keeps the local port override out of the image |

The newly generated learning folders are not excluded by these existing rules, so a normal whole-repository COPY can include them privately in the image. They remain outside `public/` and are not webroots. Excluding bulky documentation from future production builds is a reviewable optimization, not an edit performed by this guide.

## 6 · Real excerpt and annotation

```text
.git
.env
.env.docker
var
```

Each line is a separate exclusion. The dot is part of the filename, not a wildcard. Excluding var does not delete an existing named volume.

## 7 · Input → transformation → output

**Input:** Patterns matched against paths sent by `docker build` from the repository root.

**Output:** A smaller context for `COPY . /var/www/html`, without excluded local secrets/runtime/cache/archive files.

The tour above identifies the intermediate steps and their authority; copying a document or template is not evidence those steps executed.

## 8 · Connections

Use the linked consumers above and the [complete assembly inventory](../01-repository-assembly-map.md). Public traffic enters `public/`; services/configuration/learning material stay outside its document root. Canonical implementation explanations live in their owning stage, while this guide owns operational/documentation interpretation.

## 9 · Why this implementation?

Filtering at context creation prevents accidental copying earlier than a later image-cleanup step could. Removing a secret in a subsequent layer does not make earlier layers safe.

## 10 · Security, correctness and accessibility

Never assume `.gitignore` is used by Docker. A new secret filename needs both the appropriate context exclusion and a private storage policy. Keep keys in private settings/volumes.

For documentation, keep supported Markdown, meaningful link labels and diagrams whose meaning does not depend on color alone. For configuration, preserve the application trust boundaries rather than hiding failures in UI.

## 11 · Common mistakes and diagnosis

A private helper file with a new name can still enter COPY; review context inputs. A ZIP may contain secrets even if its name is innocuous; current wildcard excludes ZIPs. Changing context directory can change which ignore file applies.

## 12 · Read or run safely

Read patterns and Dockerfile COPY together; do not run a build merely to inspect exclusions. `git check-ignore` tests Git rules, not Docker rules.

These are teaching instructions. Actual outcomes, including skipped suites, are centralized in [the current evidence register](../16-evidence-and-artifact-register.md). Do not relabel a legacy or suggested command as freshly passed.

## 13 · Debugging lesson

Trace the consumer named in section 4 and verify one boundary at a time. Record filenames, versions, route/status, rule names or synthetic counts. Do not dump resolved configuration, private env files, request cookies, factor values or identity-bearing SQL exports. Diagnose the first failed prerequisite before retrying.

## 14 · Guided experiment

On paper, classify `.env`, `var/app.key`, `docs/security.md` and `compose.mac.yaml` as included/excluded. Compare each with this exact file. No changes or cleanup needed.

Keep an observation worksheet outside the public webroot. If you later make a temporary source edit, save your own small patch first, then manually undo only your lines; never overwrite another contributor's shared work or reset a real database for a lesson.

## 15 · Challenge ladder

- **Understand:** explain section 6 using the source and identify its actual consumer.
- **Modify:** propose one narrowly scoped clarification or correction only after reproducing a misunderstanding; coordinate with the source owner and verify its linked contract.
- **Engineer:** evaluate one failure in section 11, describe its evidence and trade-off, and submit genuine reviewed work through the human workflow after authorization.

Expected evidence is a correct source-to-behavior explanation and any real test result, not a cosmetic commit or copied screenshot. The experiment in section 14 supplies the safe setup; future changes must have their own focused validation.

## 16 · Knowledge check

1. Does this file remove a running volume?
2. Does Git ignore provide equivalent protection?
3. Why not COPY a secret then delete it?

<details>
<summary>Answer key · reason first</summary>

1. No. Build context filtering is separate from runtime volumes.
2. No; Git and Docker consume different ignore files.
3. Earlier image layers can retain it.

</details>

## 17 · Contribution boundaries

Assemble coordinates integration; Stage 01 reviews presentation/accessibility, Stage 02 student behavior, Stage 03 admin operations and Stage 04 API/data/security. Identify the exact changed section before editing a shared source. Do not add frameworks, duplicate runtime copies, untested CI or public setup scripts for documentation convenience. Manual Git/PR/release actions require human authority and truthful identities; no such actions were performed for this guide.

## 18 · Status and limitations

**VERIFIED BY INSPECTION:** source-order sections and quoted text above. Current root-run safe checks include security/PHP / JS / shell and startup/doctor; see the central register for precise commands/outcomes. Database-reset suites were not rerun for this documentation task. Host/provider/MySQL 8 production compatibility, public deployment and native Windows/macOS execution remain unverified unless separately evidenced. **PLANNED:** learner experiments/contributions and production release; these are not completed merely because instructions exist.

## 19 · Glossary

**Context:** files made available to a build. **Layer:** image filesystem change. **Volume:** runtime persistent storage.

## 20 · One-minute explanation to personalize

Explain when the ignore rules apply, then trace var exclusion to the separately mounted app_data volume.

Explain only what you actually studied or changed. Generated reference material is not proof of personal implementation, testing or authorship. Use the [contribution record](../templates/contribution-record.md) for your own evidence.
