# 03 · Three levels, with observable outcomes

Complete each checkpoint by explaining evidence; time spent reading is not proof of a contribution.

## Level 1 · Understand the existing system

1. Read [views](files/app__views_php.md), then locate `action_button`, `clubs`, `activity` and `security` in source.
2. Follow [membership journey](04-guided-walkthroughs.md#3--discover-apply-and-enter-a-club) and draw the user/club/membership relation yourself.
3. Inspect one native form and one REST request. State which value comes from the authenticated session and which from user input.
4. Explain all three membership states and why a student cannot approve their own application.

**Checkpoint:** in five to ten minutes, list the form's actual field names and the service/table written by Apply to join, without opening a generic tutorial. Expected answer: native `csrf/action/id`; API club ID in URL with `{}` body; `perform_action → reserve('memberships') → memberships`.

## Level 2 · Modify one bounded student experience

Choose a proposed improvement after reproducing a real issue: clearer pending-membership instructions, a distinct empty activity state, or a more understandable expired-registration message. These are PLANNED task candidates, not defects asserted without reproduction.

Before changing: write current behavior, expected behavior and affected branches. Coordinate with Stage 01 for labels/accessibility and Stage 04 for service changes. Preserve action names and form fields unless contract changes are reviewed together. Use [labs](05-labs-and-exercises.md); record native and REST behavior.

**Checkpoint:** reviewer can reproduce the improvement, and the student can explain a central code excerpt in their own words. Save before/after evidence with synthetic accounts only.

## Level 3 · Engineer an edge case

Reason about a membership revoked while a student keeps a community tab open; a tournament fills between page load and submission; or a booking overlaps after two concurrent requests. Document the interleaving and the authoritative check. Do not remove checks to make the UI flow pass.

Propose a small reviewed change only after identifying a gap. Explain whether it needs an API response, UI copy, a database constraint or no code change. Review query/display limits before promising complete exports or infinite scrolling.

**Checkpoint:** real PR evidence (after humans authorize/write it), focused tests actually run, cross-stage review, no hidden secrets, honest assistance disclosure. Use the [evidence record](09-completion-evidence.md).

## Suggested first three human tasks · Member B (TBD)

1. Trace and explain the membership lifecycle with real field names and the gated club tabs.
2. Run the isolated account/mobile labs and record observations and failed predictions honestly.
3. Reproduce one student-facing issue and implement a scoped, reviewed change on a personally owned feature branch.

These suggestions are not assignments or completed contributions. If fewer/more than four people contribute, redistribute sections and review duties openly; keep truthful activity records.
