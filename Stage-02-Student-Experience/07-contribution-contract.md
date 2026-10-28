# 07 · Contribution contract · Member B (TBD)

This stage owns the student experience as a feature responsibility. It does not claim exclusive ownership of large shared PHP files, or transfer authorship of the pre-existing Codex-assisted prototype.

## Proposed responsibility and required review

| Area | You may propose | Required reviewers |
|---|---|---|
| views.php student branches | Form labels, empty states, student journey presentation | Stage 01; Stage 04 if fields/actions change |
| portal-views.php dashboard/my_clubs/club | Navigation context, member experience, truthful metrics | Stage 01 and Stage 04 for query/access changes |
| community.php member actions | Reproduce/define member behavior and focused tests | Stage 04 for locks/API/validation; Stage 03 for moderation impact |
| actions.php/models.php student services | Small justified change after domain analysis | Stage 04 primary owner and affected Stage 03 |
| Shared schema | Propose additive migration only | All affected stages; Stage 04 technical lead |
| Admin view branches | Review student-facing consequences | Stage 03 primary owner |

Before editing a shared file, identify the exact function/page branch, tell the other owner, agree on test scope and avoid formatting the entire file. Keep one canonical runtime. Never copy production PHP into this folder as a second app.

## A reviewable contribution

Write a small problem statement with reproducible behavior. Human teammate uses their own authenticated account and personally owned feature branch, understands the patch, runs focused tests and opens a genuine reviewed PR. Follow [manual team workflow](../Assemble/02-git-github-team-workflow.md). The agent has not created a branch, staged, committed, pushed or opened a PR for this material.

PR evidence should include actual paths/functions, reason, redacted before/after UI, exact tests/outcomes, API/native parity, shared-owner review, migration/data-safety notes and an excerpt explained in the student's own words. Add an honest assistance disclosure under module rules. Never set another person's Git identity or add fake coauthor metadata.

## Invariants you must preserve

- Student registration cannot choose admin role; session identity controls user IDs.
- Pending/rejected/wrong-club memberships never grant private community access.
- HTML user text remains escaped; private reports/contact/auth data stay private.
- JSON/native actions both retain CSRF and authorization checks.
- No duplicate registration or overlapping pending/approved reservation is silently accepted.
- Own cancellation cannot modify another user's row; completed activity is protected.
- Hidden edits cannot restore visibility; tombstone deletion cannot be undone as restore.
- Passwords, keys and codes are never stored in browser localStorage or evidence.

Generated documentation or a folder named for a stage is not proof that a student wrote or understood its baseline code. Contribution records must cite real reviewed implementation, debugging, testing, explanation or documentation work. If team size differs from four, distribute these responsibilities and secondary reviews honestly, without inventing people.
