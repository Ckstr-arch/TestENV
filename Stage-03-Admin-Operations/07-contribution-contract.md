# 07 · Contribution contract: operational ownership with shared review

[Tests](06-testing-and-debugging.md) → [Knowledge check](08-knowledge-check.md)

**Proposed human responsibility:** Member C (TBD). No person is assigned or credited by this document. Codex-generated guides and the pre-existing assisted prototype are reference/baseline material, not proof that a student independently wrote, understood or tested code. Follow the module's assistance disclosure policy.

## Scope by section

| Shared path | Stage 03 change boundary | Required peer review before integration |
|---|---|---|
| `app/views.php` | `manage_*`, `approvals`, `reports`, score display operational requirements |02 canonical view owner;04 validation;01 markup |
| `app/portal-views.php` | admin overview, moderation, audit; admin controls |02 canonical owner;04 privacy/security;01 UI |
| `app/models.php` | operational entity descriptors, validation, review semantics |04 canonical owner;02 student consequences |
| `app/actions.php` | save/delete/review/attendance and moderation dispatch contracts |04;02 on changed student behavior |
| `app/community.php` | moderation/deletion/privacy requirements |02 canonical owner+04 locks/security |
| `public/assets/app.js` | submit mapping/confirmation only if required by agreed feature |01 canonical owner+04 API |
| `database/schema.sql` / migrations | review cascade/retention effects; propose additive migrations |all stages;04 migration implementation |
| `tests/*` | admin assertions/fixtures and review of actual domain results |04 infrastructure owner+affected stage |

Ownership is not exclusive authorship of a whole file. A scope disagreement is resolved before editing shared lines, not by copying a runtime module into this learning folder.

## Before editing shared source

1. Name the exact function/page branch and proposed behavior, including failure cases and affected tables.
2. Notify the canonical owner and agree who edits which section; coordinate overlapping PRs.
3. Preserve field/route contracts unless a reviewed change updates all callers and docs.
4. Plan focused regression checks plus native/REST parity and student effects.
5. Make one reviewable personal feature patch. Resolve conflicts together, retaining actual contributors' metadata; never replace a whole shared file from an older copy.

This task does not run Git writes. Future humans follow the [manual team workflow](../Assemble/02-git-github-team-workflow.md), with their own authenticated GitHub accounts and truthful verified email identity. Do not set identities to another person or add fabricated Co-authored-by lines. Use scoped commits for actual work, not cosmetic commits to manipulate a contribution graph.

## Example future feature contract

**PLANNED:** explain future attendance unavailability in the report UI.

| Field | Agreement to make before coding |
|---|---|
| Why | Backend rejects future marking while current UI can show action |
| Source scope | `app/views.php` reports attendance action cell |
| Must preserve | Backend start-time/role/CSRF validation, synthetic privacy, actual attendance states |
| Review |02 flow,01 accessible explanation,04 date/auth semantics |
| Success evidence | Before/after synthetic UI; future rejection still422; started marking works |
| Not credited | Baseline attendance implementation or unrelated admin modules |

## PR review gate

- [ ] Genuine human identity, own feature branch, clear purpose and changed functions.
- [ ] Real before/after behavior; no hidden scope expansion or framework replacement.
- [ ] Prepared values/identifier allowlists, roles/ownership/CSRF/escaping preserved.
- [ ] Data/cascade/locking effects explained; no live reset or key loss.
- [ ] Relevant tests actually run or honestly skipped with reason.
- [ ] Native and REST actions remain consistent; transport differences documented.
- [ ] Synthetic screenshots omit passwords, setup/recovery codes, cookie/token values and real identities.
- [ ] Own explanation of central code plus truthful assistance disclosure.
- [ ] Real reviewer records what they examined; no fake approval.

## Integration and attribution

Prefer a reviewed merge strategy preserving genuine individual commits. A normal merge commit retains feature commits; squash combines them and may obscure per-commit authorship; rebase rewrites commit identities/hashes and needs agreement. GitHub attribution additionally depends on verified email/account/repository visibility rules and does not guarantee grading credit. After authorized integration, humans inspect logs and PR history to verify attribution. Do not retroactively distribute the Codex-assisted baseline among members.

If the team is not four people, distribute these responsibility tracks among real members and record the true split; do not invent a missing contributor. Assemble/report/video remain joint work, with each person explaining only actual contributions.
