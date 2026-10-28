# 🧠 Stage 04 — Backend, database, REST and security

[Learning map](../LEARNING_MAP.md) · [Integration](../Assemble/README.md) · [Existing API reference](../docs/api.md)

The server protects the rules that every screen depends on. Your mission is to explain an HTTP request down to the exact SQL row, then make one bounded improvement with evidence. This is **one shared PHP application**, not a new backend copied into this folder.

| Outcome | Start here | Show understanding by |
|---|---|---|
| Follow PHP and HTTP | [Foundations](php-http-and-security.md) | Trace cookie, CSRF and 303/JSON response |
| Understand the shared request contract | [Architecture](01-architecture.md), [walkthroughs](04-guided-walkthroughs.md) | Compare native and REST membership writes |
| Read every backend file | [Inventory](02-source-inventory.md) | Find caller, validation and persistent invariant |
| Explain all SQL tables | [Data dictionary](data-dictionary.md), [SQL lessons](sql-and-transactions.md) | Draw real keys and predict a row change |
| Verify behavior safely | [Testing](06-testing-and-debugging.md), [suite guide](verification-suites.md) | Record executed vs skipped evidence |
| Contribute honestly | [Contract](07-contribution-contract.md), [evidence](09-completion-evidence.md) | Real reviewed patch plus personal explanation |

Suggested contributor: **Member D — TBD by the team**. The existing Codex-assisted prototype remains baseline work. Creating these guides does not establish anyone’s independent authorship or competence. This track is large: ask Member B to review student effects, Member C to review admin effects and Member A to review form/API behavior.

## Study order

1. [Start here](00-start-here.md) → [roadmap](03-learning-roadmap.md).
2. Bootstrap/entrypoints → actions/models → REST/security.
3. Schema/data dictionary → migrations/utilities → test suites.
4. [Labs](05-labs-and-exercises.md) → [knowledge check](08-knowledge-check.md) → a real human feature task.

## First three suggested human tasks — unassigned and not completed

- [ ] Trace a membership request through both transports and explain the prepared SQL and role boundary in your own words.
- [ ] Reproduce one invalid-input or concurrency edge case in isolated disposable data and save redacted evidence.
- [ ] Propose one small server validation or regression-test improvement, obtain domain review and implement it on your own feature branch after the team integrates this learning material.

## Scope and safety

**EXISTING:** PHP server rendering, vanilla JS enhancement, cookie-session REST, prepared PDO, Argon2id, TOTP, role/ownership checks and InnoDB transactions. **PLANNED:** learner patches, MySQL 8 production verification, real hosting and submission evidence. Local Compose uses MariaDB; the assignment specifically mandates MySQL. Compatibility is not lecturer approval.

Never reset the real database. Reset-capable suites are limited to `sportshub_test` after inspecting their configuration. Do not display APP_KEY, authenticator secrets, recovery codes, cookies or personal records. Keep `public/` as the only webroot. Runtime source and existing private data are unchanged by this curriculum.

Legend: 🧠 concept · 🔍 source · 🧪 experiment · ⚠️ safety · ✅ evidence. Colors in diagrams supplement text labels rather than replacing them.
