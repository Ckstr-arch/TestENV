# 🔍 Legacy UI/community release explanation

## 1 · File card

| Field | Inspected value |
|---|---|
| Canonical file | [docs/ui-and-community.md](../../docs/ui-and-community.md) |
| Guide owner | Assemble · team jointly; stage specialists review related behavior |
| Language / size | Markdown · 58 physical lines · 4,813 bytes |
| Runtime baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |
| Specification | `096258d302367aba198a83147c5f985fa74477b7`; fetched/read without merging |
| Authorship | Existing Codex-assisted baseline; no student contribution assigned by this guide |

## 2 · Why it exists

Describes the approved UI, access/state rules, additive migration, safe Mac update and real screenshot evidence.

## 3 · Prerequisites

Read [integration overview](../00-integration-overview.md), then follow the dependencies below. Distinguish a source file, a running process and persistent state: a document describes behavior, configuration controls a tool, and stored database/key and session state survives separately from replaceable source.

## 4 · Dependencies and consumers

[Frontend track](../../Stage-01-Frontend-UX/README.md), [student track](../../Stage-02-Student-Experience/README.md), [admin track](../../Stage-03-Admin-Operations/README.md), [Docker safety](../05-docker-compose-and-data-safety.md).

## 5 · Source-order coverage tour

### Opening and Theme and navigation

Records selected PerformanceDark direction, supplied athletics photo and SQL-backed counts. Dark navy/lime defaults; light theme updates all component surfaces/states. Theme control precedes profile; localStorage stores only sportshub-theme. Initializer before CSS avoids theme flash. Role sidebars, real club search, profile security/logout, mobile drawer/focus/Escape/resize, reduced motion and print behavior are actual features.

### Club spaces

Describes overview, private community/members/schedule and exact approved member/admin gate. Leaving revokes subsequent request access; already rendered browser text is not remotely erased. Members own posts/comments/reactions/reports, admin moderation reasons stay private. No captain roles/messages/notifications/analytics should be invented.

### Database upgrade

Migration ensures core tables then sorted additive SQL, records schema_migrations under advisory lock, creates four community tables. Existing user credentials/2FA/events/bookings/results remain data to preserve. MySQL DDL auto-commit explains retry-safe statements rather than pretending a transaction can roll back arbitrary schema changes. Demo-community seed requires explicit local opt-in and refuses production. Backup before release; never replace volumes.

### Mac Docker update

Manual future Git pull followed by same Compose project/env/override build-up. This task did not execute those Git writes or update a Mac. Port override and APP_URL must agree. Ignored local files and named volumes preserve credentials/key and sessions; old seeded dates remain old and should be edited through admin scheduling instead of reset.

### Implemented screenshots

Seven real development captures cover student/admin/community in themes/mobile. They are not image-generation mockups or fresh captures of this documentation task. No setup/recovery secrets are shown; see asset-specific guides for format/provenance/rights and final release evidence refresh.

## 6 · Real excerpt and annotation

```markdown
Theme preference is stored only in `localStorage['sportshub-theme']`; authentication data is never placed there.
```

Theme preference is low-sensitivity UI state. This sentence does not imply localStorage is a suitable place for session tokens, passwords or factors.

## 7 · Input → transformation → output

**Input:** Implemented frontend/community behavior and prior local validation.

**Output:** A reader-facing release/upgrade guide, not an executed deployment.

The tour above identifies the intermediate steps and their authority; copying a document or template is not evidence those steps executed.

## 8 · Connections

Use the linked consumers above and the [complete assembly inventory](../01-repository-assembly-map.md). Public traffic enters `public/`; services/configuration/learning material stay outside its document root. Canonical implementation explanations live in their owning stage, while this guide owns operational/documentation interpretation.

## 9 · Why this implementation?

A release explanation connects visible changes, schema prerequisites and safe upgrades. Deep code tours belong to stage guides; migration/deployment safety belongs to Assemble.

## 10 · Security, correctness and accessibility

Keep old validation/screenshots clearly dated/contextual. Do not include a new known demo password or setup key in a teaching guide. Confirm supplied imagery/branding rights before public use.

For documentation, keep supported Markdown, meaningful link labels and diagrams whose meaning does not depend on color alone. For configuration, preserve the application trust boundaries rather than hiding failures in UI.

## 11 · Common mistakes and diagnosis

Rebuilding without applying migration can break community queries. Changing project/env files can select wrong data. Theme-only testing does not prove access control. Treating hidden as deleted misstates moderation restoration.

## 12 · Read or run safely

Inspect current code/screenshots; do not call linked screenshots newly captured. Use safe stage/manual checks after authorized setup, and central register for actual current results.

These are teaching instructions. Actual outcomes, including skipped suites, are centralized in [the current evidence register](../16-evidence-and-artifact-register.md). Do not relabel a legacy or suggested command as freshly passed.

## 13 · Debugging lesson

Trace the consumer named in section 4 and verify one boundary at a time. Record filenames, versions, route/status, rule names or synthetic counts. Do not dump resolved configuration, private env files, request cookies, factor values or identity-bearing SQL exports. Diagnose the first failed prerequisite before retrying.

## 14 · Guided experiment

Compare one dark and light real capture, identify the matching CSS tokens and explain one unchanged server permission despite theme change. No runtime change is necessary.

Keep an observation worksheet outside the public webroot. If you later make a temporary source edit, save your own small patch first, then manually undo only your lines; never overwrite another contributor's shared work or reset a real database for a lesson.

## 15 · Challenge ladder

- **Understand:** explain section 6 using the source and identify its actual consumer.
- **Modify:** propose one narrowly scoped clarification or correction only after reproducing a misunderstanding; coordinate with the source owner and verify its linked contract.
- **Engineer:** evaluate one failure in section 11, describe its evidence and trade-off, and submit genuine reviewed work through the human workflow after authorization.

Expected evidence is a correct source-to-behavior explanation and any real test result, not a cosmetic commit or copied screenshot. The experiment in section 14 supplies the safe setup; future changes must have their own focused validation.

## 16 · Knowledge check

1. Does theme change grant a different role?
2. Why are migrations needed before new pages?
3. Can old screenshots stand in for the final live host?

<details>
<summary>Answer key · reason first</summary>

1. No; permissions are server/session state.
2. The queries require new community tables.
3. No; they show a prior local development context.

</details>

## 17 · Contribution boundaries

Assemble coordinates integration; Stage 01 reviews presentation/accessibility, Stage 02 student behavior, Stage 03 admin operations and Stage 04 API/data/security. Identify the exact changed section before editing a shared source. Do not add frameworks, duplicate runtime copies, untested CI or public setup scripts for documentation convenience. Manual Git/PR/release actions require human authority and truthful identities; no such actions were performed for this guide.

## 18 · Status and limitations

**VERIFIED BY INSPECTION:** source-order sections and quoted text above. Current root-run safe checks include security/PHP / JS / shell and startup/doctor; see the central register for precise commands/outcomes. Database-reset suites were not rerun for this documentation task. Host/provider/MySQL 8 production compatibility, public deployment and native Windows/macOS execution remain unverified unless separately evidenced. **PLANNED:** learner experiments/contributions and production release; these are not completed merely because instructions exist.

## 19 · Glossary

**Theme token:** named visual value. **Advisory lock:** cooperative database synchronization. **Release note:** description of a particular change/context.

## 20 · One-minute explanation to personalize

Explain one visible UI change and its actual source, then connect community access and additive migration to safe upgrade practices.

Explain only what you actually studied or changed. Generated reference material is not proof of personal implementation, testing or authorship. Use the [contribution record](../templates/contribution-record.md) for your own evidence.
