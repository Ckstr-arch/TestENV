# 07 · A contribution is work you can explain

Proposed contributor: **Member A (TBD)**. Primary scope is layout, UI helpers, CSS, vanilla JS, accessibility and imagery/evidence. Baseline code remains baseline; these guides do not assign historical authorship.

## Shared review contract

| Editing | Notify/review with | Preserve |
|---|---|---|
| CSS only | Stage 02 or Stage 03 for affected page | Both themes, keyboard, 390 px/table overflow, print |
| Layout/menu | Stage 02/03 and 04 for role changes | Existing destinations, profile security/signout, theme order |
| Form fields/endpoint map | Stage 02/03 and 04 | Native fallback, CSRF, method/payload/redirect contracts |
| Homepage/portal markup | Stage 02 canonical view owner and 03 for admin sections | PHP escaping, semantic content, real data |
| New assets/screenshots | Team integrator | Usage rights, no secrets, documented provenance |

Before editing, name the exact selectors/functions/branches, agree the scope and keep one small feature per review. Ownership is by feature and review, not exclusive possession of a file. See [team workflow](../Assemble/02-git-github-team-workflow.md) for future human branch/PR commands.

## First three suggested human tasks — not assigned or completed

1. Trace eight homepage elements and write your own DOM/cascade explanation.
2. Run the viewport/keyboard/theme lab and document one reproducible issue or supported finding.
3. Propose and implement a minimal evidence-backed UI/accessibility patch on your own feature branch, then get a real peer review and record tests.

## Review checklist

- [ ] I understand every changed line and can explain why it belongs here.
- [ ] I inspected both themes, desktop/mobile, keyboard and affected native/REST forms.
- [ ] I preserved e(), csrf fields, server auth and meaningful labels/alt text.
- [ ] I scoped the diff and obtained shared-owner review where needed.
- [ ] I disclosed help/AI assistance according to course policy.
- [ ] I recorded actual results and limitations; no invented pass count or attribution.

Codex-generated reference material is not evidence of independent student implementation. A real contribution may be debugging, testing, design or documentation; describe it honestly. No automatic commit, push, merge, PR creation or deployment is authorized by this lesson.
