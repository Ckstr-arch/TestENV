# 🟩 Stage 02 · Student experience

**Mission:** explain what a student can do, and prove which server rule permits each action. Suggested contributor: **Member B — TBD**. This is a learning and future contribution boundary, not a claim that Member B wrote the existing application.

The source remains in `app/`, `public/` and `database/`. Nothing in this folder is included by PHP or becomes a second application. Inspected runtime baseline: `f81eab54ebe7179e1763f364d6b8d4f564c36541`; specification: remote `096258d302367aba198a83147c5f985fa74477b7`, read without merging it.

## Your route through this stage

| Stop | Outcome | Open |
|---|---|---|
| 1 · Orient | Find the real page, form and service | [Start here](00-start-here.md) |
| 2 · Trace | Distinguish the native and REST paths | [Architecture](01-architecture.md) |
| 3 · Read | Account for every branch and shared file | [Source inventory](02-source-inventory.md) |
| 4 · Learn | Progress from observation to reviewed changes | [Roadmap](03-learning-roadmap.md) |
| 5 · Follow | Walk all eight student journeys | [Guided walkthroughs](04-guided-walkthroughs.md) |
| 6 · Practice | Predict a row change, then verify safely | [Labs](05-labs-and-exercises.md) |
| 7 · Diagnose | Separate UI failures from server refusals | [Testing](06-testing-and-debugging.md) |
| 8 · Contribute | Coordinate shared-file work honestly | [Contribution contract](07-contribution-contract.md) |
| 9 · Explain | Answer before looking at the key | [Knowledge check](08-knowledge-check.md) |
| 10 · Record | Save your own evidence | [Completion record](09-completion-evidence.md) |

Prerequisites: basic HTML forms, variables/conditionals, and an isolated local application from [Assemble](../Assemble/README.md). You do not need React, a Node build or a separate API implementation.

## 🔍 The three canonical file guides

- [app/views.php](files/app__views_php.md): public, account, activity and admin-template branches, with admin feature ownership cross-references.
- [app/portal-views.php](files/app__portal-views_php.md): student dashboard, club spaces and shared admin presentation.
- [app/community.php](files/app__community_php.md): approved-membership boundary, posts/comments/reactions/reports and moderation services.

## Evidence before credit

- [ ] Trace one UI action through its PHP service to SQL without guessing.
- [ ] Explain why hiding a button does not authorize a request.
- [ ] Complete the membership and mobile labs using synthetic accounts.
- [ ] Make one personally understood, reviewed improvement and record actual tests.
- [ ] Explain your real work in your own words; disclose assistance under module policy.

These generated guides and the existing Codex-assisted prototype are reference material. Reading them, having a named folder, or making a cosmetic commit does not establish independent authorship. See the [team workflow](../Assemble/02-git-github-team-workflow.md).
