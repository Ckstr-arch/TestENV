# 07 — Shared backend contribution contract

[Dashboard](README.md) · [Human Git workflow](../Assemble/02-git-github-team-workflow.md)

Member D is a suggested backend reviewer, **not an assigned author of existing code**. Codex-generated explanations/scaffolding are reference material, not proof a student independently wrote or understands the implementation. Record genuine implementation, debugging, tests, documentation/design reasoning and assistance according to module policy. Team identities remain TBD until humans fill them.

## Review by feature, not by pretending files are private

| Shared file/section | Primary guide | Proposed edit lead | Required review |
|---|---|---|---|
| app/bootstrap.php / security.php | Stage04 | D | B/C for auth impact; all for key/session policy |
| app/api.php and public entrypoints | Stage04 | D | A for JS contract; B/C for touched domain |
| app/actions.php student actions | Stage04 | B with D | D server/auth/SQL; A when form fields change |
| app/actions.php admin actions | Stage04 | C with D | D role/validation/transactions |
| app/models.php metadata/reservations | Stage04 | C/D; B for enrollment | All affected UI/domain reviewers |
| app/community.php | Stage02 | B; C moderation | D for transactions/authorization, A for UX |
| app/views.php / portal-views.php | Stage02 | A/B/C by branch | D if forms/reads/auth rules change |
| public/assets/app.js | Stage01 | A | D for route/payload/CSRF; domain owner |
| database/schema.sql / new migrations | Stage04 | D | All stages, backup/compatibility evidence |
| tests | Stage04 | Relevant feature owner | Another member reviews what assertion proves |
| Deployment settings | Assemble | Team-designated integrator TBD | D for privacy/runtime; all release approval |

Before editing a shared file, name exact functions/branches, expected behavior and affected UI/API/SQL contract; agree who edits each region. Keep feature PRs small. Don’t rename runtime files, add a framework or duplicate app copies to make ownership look simpler. A migration is additive, reviewed and versioned; never rewrite already-applied history to erase an awkward case.

## What a truthful PR needs

Use [the PR template](../Assemble/templates/pull-request.md). Supply your own username, actual changed paths, why, tests actually run, negative case, before/after safe evidence and an explanation of a central excerpt in your own words. Name shared reviewers and disclose assistance. No one receives credit simply from a folder/table/name label. The prior Codex-assisted implementation remains baseline, with its actual existing history preserved.

This documentation task does not authorize Git writes, branches, commits, PR creation, merging or deployment. Future human workflow uses their own authenticated account and verified identity, stages only their actual paths, obtains real review and favors an integration strategy preserving genuine commit metadata. [Assemble](../Assemble/02-git-github-team-workflow.md) explains those manual steps and merge trade-offs.

## Reject a proposed change when

It trusts client user/role/table names, removes escaping/CSRF or row locks, logs credentials, turns demo accounts into a live DB, resets wanted data, hides skipped tests, claims production certification, or retroactively assigns baseline authorship. A simpler explanation is never a reason to weaken an invariant.
