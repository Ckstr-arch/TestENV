# 🔍 Security controls and explicit product limits

## 1 · File card

| Field | Inspected value |
|---|---|
| Canonical file | [docs/security.md](../../docs/security.md) |
| Guide owner | Assemble · team jointly; stage specialists review related behavior |
| Language / size | Markdown · 38 physical lines · 6,353 bytes |
| Runtime baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |
| Specification | `096258d302367aba198a83147c5f985fa74477b7`; fetched/read without merging |
| Authorship | Existing Codex-assisted baseline; no student contribution assigned by this guide |

## 2 · Why it exists

Records implemented protections, deployment requirements and unimplemented institutional security/product decisions.

## 3 · Prerequisites

Read [integration overview](../00-integration-overview.md), then follow the dependencies below. Distinguish a source file, a running process and persistent state: a document describes behavior, configuration controls a tool, and stored database/key and session state survives separately from replaceable source.

## 4 · Dependencies and consumers

[Security implementation](../../Stage-04-Backend-Database-Security/files/app__security_php.md), [key and HTTPS policy](../08-secrets-security-2fa-and-https.md), [production runbook](../07-production-deployment-runbook.md).

## 5 · Source-order coverage tour

### Implemented controls

Argon2id passwords, bounded registration, server-only administrator provisioning, database-backed throttle, strict/rotated/idle-expiring HttpOnly SameSite sessions, production Secure/HSTS, CSRF/Origin/JSON allowlists, parameterized SQL, roles/ownership and transactional reservations. Password length text should be read alongside actual strlen byte enforcement. These are baseline protections, not independent review.

Authenticator enrollment checks password+fresh code; encrypted 160-bit TOTP secret uses private 256-bit APP_KEY, SHA-1 / 6 digits / 30 seconds/drift allowance. Used counter rejects replay; pending login expires; recovery codes are hashed/single-use/shown once. 2FA remains opt-in, so admins must choose enrollment; no email bypass is implemented.

### Production deployment

Eight operational steps require verified extensions/host, public-only root, private least-privilege DB, production env/HTTPS/logging, private writable durable sessions, secure CLI administrator, backups and real-host feature checks. Shared hosting capabilities are not established by these instructions. New Assemble expands release/restore diagnostics without claiming deployment.

### Remaining product decisions

Email verification/reset, mandatory admin 2FA, cross-device revocation, SSO/notifications and independent security review remain extensions. Tournaments enroll individuals and use manual fixtures; standings generic 3 / 1 / 0, no sport-specific brackets/ties. Each resource is one unit. Parent deletes may cascade; archival/retention is a future organizational policy.

### Disposable development accounts

Explicit demo mode refuses production and preserves existing identities; known demo credentials are not a live provisioning strategy. Fresh production data/admin required. Generated local key persists in app_data; source ZIP must omit it.

### Club community controls

Every service request rechecks exact-club approved membership/admin; own edit, private contact/report boundaries, escaped text, moderation reasons, theme-only localStorage and additive migrations. Distinguish these code-backed claims from operational guarantees such as complete logging/retention, which require deployment policy.

## 6 · Real excerpt and annotation

```markdown
This is a tested project implementation, not a certification of production security.
```

A test suite covers selected assertions. It cannot establish all threats, deployment configurations or institutional requirements.

## 7 · Input → transformation → output

**Input:** Security/action/bootstrap/service code and operational assumptions.

**Output:** A scoped security description, never a certification.

The tour above identifies the intermediate steps and their authority; copying a document or template is not evidence those steps executed.

## 8 · Connections

Use the linked consumers above and the [complete assembly inventory](../01-repository-assembly-map.md). Public traffic enters `public/`; services/configuration/learning material stay outside its document root. Canonical implementation explanations live in their owning stage, while this guide owns operational/documentation interpretation.

## 9 · Why this implementation?

Explicit limitations make a security document useful for review and honest submission; vague secure-by-default claims would hide unresolved work.

## 10 · Security, correctness and accessibility

Never disclose APP_KEY, factors, passwords or session identifiers while explaining their purpose. Keep existing key stable; rotation needs a tested migration/re-enrollment plan.

For documentation, keep supported Markdown, meaningful link labels and diagrams whose meaning does not depend on color alone. For configuration, preserve the application trust boundaries rather than hiding failures in UI.

## 11 · Common mistakes and diagnosis

Treating opt-in 2FA as mandatory misrepresents policy. Copying demo DB to production creates known access. Running multi-node file sessions without shared store breaks state. Lost key cannot be fixed by generating another with the same name.

## 12 · Read or run safely

Read implementation and execute only safe security checks/doctor as authorized. Destructive fixture suites and public-host probes need their explicit prerequisites; do not call inspected controls newly penetration-tested.

These are teaching instructions. Actual outcomes, including skipped suites, are centralized in [the current evidence register](../16-evidence-and-artifact-register.md). Do not relabel a legacy or suggested command as freshly passed.

## 13 · Debugging lesson

Trace the consumer named in section 4 and verify one boundary at a time. Record filenames, versions, route/status, rule names or synthetic counts. Do not dump resolved configuration, private env files, request cookies, factor values or identity-bearing SQL exports. Diagnose the first failed prerequisite before retrying.

## 14 · Guided experiment

Choose one claimed control and one stated limitation; identify exact source enforcement or absence and explain what operational evidence would be needed.

Keep an observation worksheet outside the public webroot. If you later make a temporary source edit, save your own small patch first, then manually undo only your lines; never overwrite another contributor's shared work or reset a real database for a lesson.

## 15 · Challenge ladder

- **Understand:** explain section 6 using the source and identify its actual consumer.
- **Modify:** propose one narrowly scoped clarification or correction only after reproducing a misunderstanding; coordinate with the source owner and verify its linked contract.
- **Engineer:** evaluate one failure in section 11, describe its evidence and trade-off, and submit genuine reviewed work through the human workflow after authorization.

Expected evidence is a correct source-to-behavior explanation and any real test result, not a cosmetic commit or copied screenshot. The experiment in section 14 supplies the safe setup; future changes must have their own focused validation.

## 16 · Knowledge check

1. Does encryption replace password hashing?
2. Does opt-in 2FA enforce administrator enrollment?
3. Why backup APP_KEY separately from source?

<details>
<summary>Answer key · reason first</summary>

1. No; secrets need recoverable encryption, passwords use one-way hashes.
2. No; mandatory policy is still a product decision.
3. Existing encrypted TOTP depends on a private stable key absent from public code.

</details>

## 17 · Contribution boundaries

Assemble coordinates integration; Stage 01 reviews presentation/accessibility, Stage 02 student behavior, Stage 03 admin operations and Stage 04 API/data/security. Identify the exact changed section before editing a shared source. Do not add frameworks, duplicate runtime copies, untested CI or public setup scripts for documentation convenience. Manual Git/PR/release actions require human authority and truthful identities; no such actions were performed for this guide.

## 18 · Status and limitations

**VERIFIED BY INSPECTION:** source-order sections and quoted text above. Current root-run safe checks include security/PHP / JS / shell and startup/doctor; see the central register for precise commands/outcomes. Database-reset suites were not rerun for this documentation task. Host/provider/MySQL 8 production compatibility, public deployment and native Windows/macOS execution remain unverified unless separately evidenced. **PLANNED:** learner experiments/contributions and production release; these are not completed merely because instructions exist.

## 19 · Glossary

**Threat boundary:** point where untrusted input crosses authority. **Replay:** reuse of accepted authentication proof. **Least privilege:** only necessary permissions.

## 20 · One-minute explanation to personalize

Name a concrete protection, its implementation boundary and a remaining limitation; avoid claiming production certification.

Explain only what you actually studied or changed. Generated reference material is not proof of personal implementation, testing or authorship. Use the [contribution record](../templates/contribution-record.md) for your own evidence.
