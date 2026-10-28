# 03 · From operator to engineer

[Inventory](02-source-inventory.md) → [Walkthroughs](04-guided-walkthroughs.md)

Progress depends on explanations and evidence, not invented hours or completion dates. Complete each level before claiming the next.

## Level1 · Understand

**Goal:** identify the real decisions behind admin controls.

1. Read [views](files/app__views_php__admin_sections.md) and map every admin destination to a branch.
2. Read [models](files/app__models_php__admin_sections.md) and distinguish entity save from approval.
3. Trace the last-slot membership decision on paper, using the [sequence diagram](01-architecture.md).
4. Compare one registered row with one attended row and predict report totals.
5. Explain why only an administrator sees report reasons, even if a member guesses the URL.

**Exit evidence:** a source-linked diagram, a field/validation prediction and an oral explanation of401 versus403. Safe five-minute task: locate `require_admin()` in the API and action paths without sending requests.

## Level2 · Modify

**Goal:** personally improve one scoped operational behavior after review.

Suggested task: make future attendance controls communicate their unavailable state accurately while preserving server validation. This is **PLANNED**, not implemented by these guides. Agree the shared branch in `app/views.php`, check synthetic future/past sessions, ensure accessible explanatory text and compare native/REST behavior. Stage 02 reviews participation consequences, Stage 01 keyboard/contrast, Stage 04 time/authorization.

Use [lab2](05-labs-and-exercises.md#lab2--one-scoped-attendance-improvement), save a focused diff and before/after synthetic evidence, then ask a real peer to review it. Do not remove backend checks to make a button appear to work.

**Exit evidence:** genuine patch, narrow purpose, tests actually run, one denied case, your own code explanation and recorded review.

## Level3 · Engineer

**Goal:** reason about race, scale and retention, then propose an incremental solution.

Choose one: approval concurrency; pagination of HTML admin lists; archive-versus-cascade retention; atomic save+audit. Reproduce a concern safely in disposable data, inspect actual code/constraints, and document trade-offs. A proposal is not an implemented safeguard. Schema changes require all-stage review and a new additive migration, never an edit to applied migration history.

**Exit evidence:** threat/failure model, test that would detect the bug, measured observation rather than invented performance statistics, and a reviewed PR when personally implemented.

## Suggested review sharing

Stage 04 carries a large backend/security workload. Member C can review domain-specific tests and explain expected operational behavior while Stage 04 reviews locks/SQL. Stage 01 checks form semantics; Stage 02 checks the student journey. Shared review is genuine work when the reviewer records what they actually examined and found; it is not a way to claim someone else's baseline implementation.

## Capstones connected to other stages

| Capstone | Your part | Partner |
|---|---|---|
| Student membership lifecycle | Review pending→approved and capacity | Stage 02+04 |
| Admin CRUD lifecycle | Create tournament+fixture, scores, update, safe synthetic deletion | Stage 04 |
| Booking collision | Explain parent resource lock and interval predicate | Stage 02+04 |
| Login+2FA | Enroll admin privately, demonstrate role boundary without keys | Stage 04 |
| Genuine feature PR | Explain own patch and actual review | Assemble |
| Release simulation | Exercise synthetic operational smoke after migration plan | Assemble |

Use the [knowledge check](08-knowledge-check.md) to reveal gaps before the demonstration; do not memorize unearned authorship statements.
