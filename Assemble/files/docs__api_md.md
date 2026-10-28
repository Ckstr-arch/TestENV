# 🔍 Legacy human REST contract

## 1 · File card

| Field | Inspected value |
|---|---|
| Canonical file | [docs/api.md](../../docs/api.md) |
| Guide owner | Assemble · team jointly; stage specialists review related behavior |
| Language / size | Markdown · 145 physical lines · 11,714 bytes |
| Runtime baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |
| Specification | `096258d302367aba198a83147c5f985fa74477b7`; fetched/read without merging |
| Authorship | Existing Codex-assisted baseline; no student contribution assigned by this guide |

## 2 · Why it exists

Documents cookie/CSRF JSON transport, routes, payloads, errors and club community extension.

## 3 · Prerequisites

Read [integration overview](../00-integration-overview.md), then follow the dependencies below. Distinguish a source file, a running process and persistent state: a document describes behavior, configuration controls a tool, and stored database/key and session state survives separately from replaceable source.

## 4 · Dependencies and consumers

[API source](../../Stage-04-Backend-Database-Security/files/app__api_php.md), [OpenAPI guide](../../Stage-04-Backend-Database-Security/files/public__assets__openapi_json.md), [community guide](../../Stage-02-Student-Experience/files/app__community_php.md).

## 5 · Source-order coverage tour

### Opening and Authentication and request format

Base is /api/v1; bootstrap auth/session obtains cookie+CSRF, then mutations send JSON objects and token header. Login may create a pending five-minute 2FA challenge. New token after rotation matters. Origin must match APP_URL when present; cookies remain HttpOnly/SameSite and productionSecure. Dates are local Asia/Colombo minute input, SQL-style output.

### Browser example

Demonstrates same-origin Fetch and cookie/token preservation, then response.ok/error. The old snippet embeds disposable known login values; do not duplicate or reuse those on public hosts. A learner should enter private synthetic values locally without publishing them. Never save login/token-bearing console transcripts.

### Routes

Public readiness/auth/read collections, protected own history/cancellation/2FA, enrollment creation, admin CRUD/reviews/attendance/reports/audit. Collection placeholders mean fixed allowlists, not arbitrary table identifiers. Follow actual route allowlist/methods before interpreting a generic collection shorthand.

### Payloads

Lists all real fields and enum/date/score/capacity rules. PUT is complete replacement, not arbitrary partial PATCH; DELETE normally needs {} except disabling 2FA requires password+factor. Server fixes privileged IDs. Important precision: password length prose says characters, but actual registration uses strlen 12–128 bytes; validate multibyte expectations against code rather than silently relabel implementation.

### Responses, pagination and errors

Success/error envelopes include request ID and current CSRF. Collection default 20 / maximum 100; empty list is not 404, aggregate reports/standings are not paginated. Filters vary by entity. Error table distinguishes 401/403/404/405/409/413/415/422/429/500/503. Network failure is outcome-unknown, so read state before retrying a mutation.

### Club communities

Exact approved club membership/admin gate, privacy-limited roster, routes, text limits, author checks, private reports, moderation and tombstone deletion. Ten HTML posts/latest 20 comments/roster 100/admin 20 differ from API pagination. Author editing hidden content cannot restore it. REST moderation all means omitted status; the UI's literal all is not an accepted API filter.

## 6 · Real excerpt and annotation

```markdown
All mutation bodies must be JSON **objects**, including `{}` for actions with no fields.
```

An empty object is still a body. A JSON array, nested object or missing/incorrect Content-Type fails transport validation before business code.

## 7 · Input → transformation → output

**Input:** Actual app/api.php/community.php behavior and generated OpenAPI contract.

**Output:** Human-readable client instructions; it does not route requests itself.

The tour above identifies the intermediate steps and their authority; copying a document or template is not evidence those steps executed.

## 8 · Connections

Use the linked consumers above and the [complete assembly inventory](../01-repository-assembly-map.md). Public traffic enters `public/`; services/configuration/learning material stay outside its document root. Canonical implementation explanations live in their owning stage, while this guide owns operational/documentation interpretation.

## 9 · Why this implementation?

Human route/payload tables complement machine-readable OpenAPI and real source. Neither document is a reason to change working behavior to fit a stale example.

## 10 · Security, correctness and accessibility

Cookie-authenticated clients still need CSRF; do not replace with wildcard CORS or browser bearer storage. Setup/2FA responses contain secret material and must not be logged.

For documentation, keep supported Markdown, meaningful link labels and diagrams whose meaning does not depend on color alone. For configuration, preserve the application trust boundaries rather than hiding failures in UI.

## 11 · Common mistakes and diagnosis

Missing cookie makes CSRF/login state fail. Reusing token after rotation fails 403. PUT partial data triggers validation. Literal statusall fails community API 422. Wrong Content-Type yields 415.

## 12 · Read or run safely

Use a safe GET health or auth/session via a local client; do not print token/cookie fields as evidence. Contract regeneration writes generated source, so perform it only as a reviewed intended change.

These are teaching instructions. Actual outcomes, including skipped suites, are centralized in [the current evidence register](../16-evidence-and-artifact-register.md). Do not relabel a legacy or suggested command as freshly passed.

## 13 · Debugging lesson

Trace the consumer named in section 4 and verify one boundary at a time. Record filenames, versions, route/status, rule names or synthetic counts. Do not dump resolved configuration, private env files, request cookies, factor values or identity-bearing SQL exports. Diagnose the first failed prerequisite before retrying.

## 14 · Guided experiment

Choose one existing route, predict method/body/status/auth, then locate its allowlist and service in source. Compare with native form fields; no write request needed.

Keep an observation worksheet outside the public webroot. If you later make a temporary source edit, save your own small patch first, then manually undo only your lines; never overwrite another contributor's shared work or reset a real database for a lesson.

## 15 · Challenge ladder

- **Understand:** explain section 6 using the source and identify its actual consumer.
- **Modify:** propose one narrowly scoped clarification or correction only after reproducing a misunderstanding; coordinate with the source owner and verify its linked contract.
- **Engineer:** evaluate one failure in section 11, describe its evidence and trade-off, and submit genuine reviewed work through the human workflow after authorization.

Expected evidence is a correct source-to-behavior explanation and any real test result, not a cosmetic commit or copied screenshot. The experiment in section 14 supplies the safe setup; future changes must have their own focused validation.

## 16 · Knowledge check

1. Why do login/registration need CSRF?
2. Does DELETE mean no body?
3. Why read state after a network error?

<details>
<summary>Answer key · reason first</summary>

1. They mutate cookie-linked auth/account state and use the same protected transport.
2. Here actions generally require JSON{}, and 2FA deletion requires factors.
3. The server may have committed before the response was lost.

</details>

## 17 · Contribution boundaries

Assemble coordinates integration; Stage 01 reviews presentation/accessibility, Stage 02 student behavior, Stage 03 admin operations and Stage 04 API/data/security. Identify the exact changed section before editing a shared source. Do not add frameworks, duplicate runtime copies, untested CI or public setup scripts for documentation convenience. Manual Git/PR/release actions require human authority and truthful identities; no such actions were performed for this guide.

## 18 · Status and limitations

**VERIFIED BY INSPECTION:** source-order sections and quoted text above. Current root-run safe checks include security/PHP / JS / shell and startup/doctor; see the central register for precise commands/outcomes. Database-reset suites were not rerun for this documentation task. Host/provider/MySQL 8 production compatibility, public deployment and native Windows/macOS execution remain unverified unless separately evidenced. **PLANNED:** learner experiments/contributions and production release; these are not completed merely because instructions exist.

## 19 · Glossary

**REST:** HTTP resource-oriented contract. **Envelope:** common response wrapper. **PUT:** complete resource replacement here.

## 20 · One-minute explanation to personalize

Explain session/CSRF bootstrap, one real mutation and one refusal, without copying sensitive request headers into evidence.

Explain only what you actually studied or changed. Generated reference material is not proof of personal implementation, testing or authorship. Use the [contribution record](../templates/contribution-record.md) for your own evidence.
