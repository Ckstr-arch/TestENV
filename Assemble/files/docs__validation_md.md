# 🔍 Historical validation record and evidence scope

## 1 · File card

| Field | Inspected value |
|---|---|
| Canonical file | [docs/validation.md](../../docs/validation.md) |
| Guide owner | Assemble · team jointly; stage specialists review related behavior |
| Language / size | Markdown · 35 physical lines · 3,573 bytes |
| Runtime baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |
| Specification | `096258d302367aba198a83147c5f985fa74477b7`; fetched/read without merging |
| Authorship | Existing Codex-assisted baseline; no student contribution assigned by this guide |

## 2 · Why it exists

Preserves a dated prior test record and its environmental limits without implying a new test execution.

## 3 · Prerequisites

Read [integration overview](../00-integration-overview.md), then follow the dependencies below. Distinguish a source file, a running process and persistent state: a document describes behavior, configuration controls a tool, and stored database/key and session state survives separately from replaceable source.

## 4 · Dependencies and consumers

[Current register](../16-evidence-and-artifact-register.md), [quality gates](../10-ci-tests-and-quality-gates.md), actual tests/ sources.

## 5 · Source-order coverage tour

### Heading, date and check table

The source explicitly says latest validation 17 October 2026. Table reports then-run PHP / JS / shell/security/native/REST/community/Docker/OpenAPI/browser/layout/seed/setup/initialization checks. This task is 28 October 2026 in client context; it reruns only safe checks and must not inherit old full-suite pass claims as new results. The new register separates executed, historical, skipped and unverified checks.

### Browser and runtime paragraphs

Describe the previous concrete workflows and no secret-bearing screenshots; PHP 8.4.26/MariaDB 11.8.6 native and official image/MariaDB 11.8 Docker context, with mutable image tags. Those versions explain the environment then tested, not an eternal lock or MySQL 8 compatibility proof.

### Restricted cloud build paragraph

Records prior trusted proxy/CA forwarding and retained TLS/signature verification. Do not disable verification or hardcode secret proxy settings to reproduce a build elsewhere. This task did not need a new Docker build to explain docs.

### Platform/deployment limitations and test database policy

Windows/macOS were not available for native execution, Docker ran on Linux, and no public hosting/environment publication/real email/security certification was proven. Disposable test schemas must be isolated and suites sequential. A successful syntax check cannot replace these behavior checks.

### Final UI/upgrade paragraphs

Prior browser checks covered communities/themes/navigation at stated sizes; screenshots show real local implementation. Upgrade checks compared user count and private key fingerprint without publishing key; deletion concurrency tested READ COMMITTED and parent-lock behavior. These remain historic source claims, while the current documentation task protects runtime unchanged and records suites not rerun.

## 6 · Real excerpt and annotation

```markdown
Latest validation: 17 October 2026.
```

The explicit date bounds the claim. Keeping it prevents a reader from confusing prior regression results with the current documentation-only task.

## 7 · Input → transformation → output

**Input:** Previous command outcomes, runtime versions, suite scope and screenshots.

**Output:** Historical evidence context; the current task has a separate execution register.

The tour above identifies the intermediate steps and their authority; copying a document or template is not evidence those steps executed.

## 8 · Connections

Use the linked consumers above and the [complete assembly inventory](../01-repository-assembly-map.md). Public traffic enters `public/`; services/configuration/learning material stay outside its document root. Canonical implementation explanations live in their owning stage, while this guide owns operational/documentation interpretation.

## 9 · Why this implementation?

Dated evidence is useful if scope, version and limits stay attached. A central new register avoids rewriting or erasing historical results.

## 10 · Security, correctness and accessibility

Do not fabricate counts, fresh screenshots or deployment success. Test evidence must omit credentials/private rows. A private key fingerprint used internally is not a reason to expose key material.

For documentation, keep supported Markdown, meaningful link labels and diagrams whose meaning does not depend on color alone. For configuration, preserve the application trust boundaries rather than hiding failures in UI.

## 11 · Common mistakes and diagnosis

Copying 291 into a current pass total without execution is false. Linux Docker does not establish nativeMac/Windows validation. A healthy DB does not establish browser accessibility. MariaDB success does not automatically satisfy mandatory MySQL.

## 12 · Read or run safely

Read the date and compare actual test files/current register. No executable command lives in this document. Root current checks include 17 security,23 PHP lint and JS/shell plus cloudstartup/doctor; resetting suites are recorded as not rerun.

These are teaching instructions. Actual outcomes, including skipped suites, are centralized in [the current evidence register](../16-evidence-and-artifact-register.md). Do not relabel a legacy or suggested command as freshly passed.

## 13 · Debugging lesson

Trace the consumer named in section 4 and verify one boundary at a time. Record filenames, versions, route/status, rule names or synthetic counts. Do not dump resolved configuration, private env files, request cookies, factor values or identity-bearing SQL exports. Diagnose the first failed prerequisite before retrying.

## 14 · Guided experiment

Classify five source claims as historical executed, current executed, skipped or unverified. Cite the dated record/current register, not a remembered count. No code or data changes needed.

Keep an observation worksheet outside the public webroot. If you later make a temporary source edit, save your own small patch first, then manually undo only your lines; never overwrite another contributor's shared work or reset a real database for a lesson.

## 15 · Challenge ladder

- **Understand:** explain section 6 using the source and identify its actual consumer.
- **Modify:** propose one narrowly scoped clarification or correction only after reproducing a misunderstanding; coordinate with the source owner and verify its linked contract.
- **Engineer:** evaluate one failure in section 11, describe its evidence and trade-off, and submit genuine reviewed work through the human workflow after authorization.

Expected evidence is a correct source-to-behavior explanation and any real test result, not a cosmetic commit or copied screenshot. The experiment in section 14 supplies the safe setup; future changes must have their own focused validation.

## 16 · Knowledge check

1. Can a historic pass be counted as this task’s run?
2. Does no runtime diff equal full regression rerun?
3. Why separate platform limits?

<details>
<summary>Answer key · reason first</summary>

1. No; preserve its date and label.
2. No; it is preservation evidence, not execution of every workflow.
3. Environment-specific checks cannot prove unexecuted OS/host behavior.

</details>

## 17 · Contribution boundaries

Assemble coordinates integration; Stage 01 reviews presentation/accessibility, Stage 02 student behavior, Stage 03 admin operations and Stage 04 API/data/security. Identify the exact changed section before editing a shared source. Do not add frameworks, duplicate runtime copies, untested CI or public setup scripts for documentation convenience. Manual Git/PR/release actions require human authority and truthful identities; no such actions were performed for this guide.

## 18 · Status and limitations

**VERIFIED BY INSPECTION:** source-order sections and quoted text above. Current root-run safe checks include security/PHP / JS / shell and startup/doctor; see the central register for precise commands/outcomes. Database-reset suites were not rerun for this documentation task. Host/provider/MySQL 8 production compatibility, public deployment and native Windows/macOS execution remain unverified unless separately evidenced. **PLANNED:** learner experiments/contributions and production release; these are not completed merely because instructions exist.

## 19 · Glossary

**Regression:** previously working behavior becoming broken. **Scope:** what a check covers. **Skipped:** not executed with a reason.

## 20 · One-minute explanation to personalize

Give one actual current result, one historical result and one skipped/unverified requirement with reasons; avoid turning a test table into a deployment claim.

Explain only what you actually studied or changed. Generated reference material is not proof of personal implementation, testing or authorship. Use the [contribution record](../templates/contribution-record.md) for your own evidence.
