# 05 · Predict, inspect, modify, verify

[Walkthroughs](04-guided-walkthroughs.md) → [Tests](06-testing-and-debugging.md)

> [!CAUTION]
> All data-changing exercises require a disposable **local study installation**, synthetic accounts and named lab records. Existing integration suites may reset only verified `sportshub_test`; do not point an interactive shared development server at it while a test runs. No live deletion, volume reset or secret capture is part of a lab.

## Lab1 · Understand a complete tournament/fixture CRUD flow

**Level:**1. **Objective:** predict which rows and outputs change. **Sources:** [models](../app/models.php), [views](../app/views.php), [schema](../database/schema.sql). **Prerequisites:** basic PHP arrays and SELECT; a read-only source checkout suffices.

**Setup and commands:** from source root:

```bash
rg -n 'tournaments|events|home_score|registration_deadline' app/models.php app/views.php database/schema.sql
```

1. Copy actual tournament field names into your notebook, without any real row values.
2. Draw create→read→edit→delete for a synthetic “Lab cup”; annotate every HTTP method and action.
3. Predict outcomes for end=start, deadline=start, one missing score, two0 scores and deleted tournament with an existing fixture.
4. Inspect the exact comparisons and FK actions, correcting your predictions in a different column.

**Expected:** deadline equality accepted; end equality rejected; scores must be paired; deleting tournament removes entries but sets fixture link NULL. **Questions:** Why isn't a fixture a tournament entry? Why does GET edit not write? **Evidence:** source-linked table and corrected predictions. **Undo:** no changes were made.

## Lab2 · One scoped attendance improvement

**Level:**2. **Objective:** implement an agreed usability fix without weakening the service. **Sources:** views `reports` branch, actions `attendance`, JS form mapping, relevant tests. **Prerequisites:** lab1, shared-file approval, disposable future and past event/registration fixtures; use your personal feature branch only after a human initiates the Git workflow.

**Setup:** record current behavior: the rendered report offers Mark attended for `registered` regardless of date, but service rejects future sessions. Capture synthetic expected/actual evidence. **PLANNED change:** propose readable disabled/explanatory behavior for future sessions. Do not solve this automatically by deleting the backend date test.

1. State an acceptance rule: future shows why unavailable, started registered can be marked, attended state remains clear.
2. Agree the exact `reports` branch with Stage 02/04 and accessibility with Stage 01.
3. Manually implement your small change, retaining field names and service validation.
4. Check keyboard focus, labels/contrast, dark/light390px and desktop, then test future and started sessions.
5. Check direct REST PATCH future remains422 even if the button is altered in DevTools.

**Commands:**

```bash
php -l app/views.php
php -l app/actions.php
git diff --check
git diff -- app/views.php
```

Run integration/API suites only after [isolation checks](06-testing-and-debugging.md#isolation-gate). **Expected:** improved explanation plus unchanged authoritative rejection. **Evidence:** own diff, synthetic before/after, actual test output and peer review. **Questions:** Why is a disabled control insufficient protection? How do you compare times in the app's timezone? **Undo:** manually reverse only your recorded hunk after comparing the current diff; if someone edited that area, stop and reconcile together. Do not reset shared files or use a blanket checkout/restore. Remove only your lab's new synthetic rows through eligible UI actions; preserve audit truth.

## Lab3 · Booking race reasoning

**Level:**3. **Objective:** explain the interval invariant and propose a focused concurrency regression. **Sources:** actions `book`, models `review_request`, booking schema. **Prerequisites:** transactions, parent row locks, isolated disposable DB verified by Stage 04. This default lab is a paper simulation; no destructive runner is needed.

**Setup:** use synthetic resourceR and intervalsA10:00–11:00, B10:30–11:30, C11:00–12:00 on a future date.

1. Evaluate `existing.start < requested.end AND existing.end > requested.start` for all pairs.
2. Draw two transactions each checking an empty slot without a lock; both could insert.
3. Add `SELECT resource FOR UPDATE` before the check; B must wait, then sees A's committed pending row and rejects.
4. Explain why a booking index helps locating rows but does not enforce non-overlap by itself.
5. Write a test proposal: two synthetic sessions; readiness signal after parent lock; second request waits; release first; assert one booking and the expected rejection. Get Stage 04 review before implementing/running it.

**Expected:** A/B collide; A/C touch but don't overlap; all writers need the same lock protocol. **Evidence:** interleaving diagram, predicate truth table, reviewed test plan. **Questions:** Why block pending too? Why reread request on review? **Undo:** paper-only means none; an implemented test must operate exclusively on `sportshub_test` and cleanup its synthetic rows or documented fixture reset. No main DB reset.

## Lab4 · Moderation without erasing authorship

**Levels:**1→2. **Objective:** distinguish reversible visibility from irreversible erasure. **Sources:** [community service](files/app__community_php__admin_sections.md), portal moderation. **Prerequisites:** two disposable approved members, local admin, synthetic post.

1. Member B reports Member A's synthetic post; predict queue/private fields and status.
2. Admin hides with a neutral synthetic reason; inspect visible/hidden/reported filters and audit.
3. Author edits their hidden post; verify member feed remains empty for it.
4. Admin restores after review; note resolved reports remain resolved.
5. Read deletion code; do not delete the post unless the planned lab explicitly calls for irreversible synthetic cleanup.

**Expected:** hidden edit cannot restore; member cannot read reasons; decision resolves pending reports. **Evidence:** status table and redacted synthetic screenshots; no cookies/2FA values. **Questions:** What remains after deletion? Could a foreign key alone stop new tombstone comments? **Undo:** restore only your reversible synthetic hide state; do not erase audit entries or rewrite reports to pretend no decision occurred.

## Lab5 · Reports and aggregation clinic

**Level:**1→3. **Objective:** avoid false performance claims. **Sources:** views `reports`, API `admin/reports`, event Results SQL. **Prerequisites:** SQL COUNT/JOIN basics; read-only source sufficient.

Create a synthetic notebook dataset: one club, two students, three attendance rows, one attended, one tournament entry. Predict approved memberships, total session registrations and attended separately; compare HTML/JSON report SQL. Explain how a naïve join memberships×attendance multiplies counts. A future improvement may add a distinct-athlete metric, but name it accurately and propose a test first.

**Expected:** registration rows are not distinct people; tournament enrollment is independent. **Evidence:** hand-calculated totals and SQL explanation, not invented live dashboard numbers. **Undo:** no code/data mutation required.

## Lab6 · CRUD failure clinic for every entity

| Entity | Safe failure to predict/reproduce on synthetic record | Diagnosis / expected result | Cleanup |
|---|---|---|---|
| clubs | capacity0; then two requests competing last slot | positive validation422; capacity at review under lock | Reject/cancel only synthetic pending entries; do not delete team club |
| tournaments | end<=start; deadline>start | cross-field validation422; old row unchanged | Restore only lab's original fields |
| events | only home_score present; unknown club; training+scores | pairing/existence/kind rule422 | Restore paired values on isolated fixture |
| resources | forged kind=vehicle; missing description | enum/text validation422 | No row created; if valid fixture created, verify no unrelated bookings before planned cleanup |

For every failure save the prediction, actual HTTP status, synthetic record ID and redacted message. Never “fix” a failure by changing the DB schema ad hoc, accepting arbitrary entity names or bypassing the server rule.

## Hints and answer key

<details><summary>Compare after completing the prediction columns</summary>

- Tournament deadline compares `>` against start, so equality passes; end compares `<=`, so equality fails.
- A missing score is NULL.0 is a legitimate integer. Both missing is unscored, both 0 is a draw.
- Deleting tournament SET NULL preserves event identity and its attendance; deleting the event itself cascades attendance.
- Booking intervals are half-open. Touching endpoints are allowed. Parent lock coordinates even when no booking row exists yet.
- Attendance button visibility is not authorization. The service rereads event start and the route/action enforce role.
- Restored posts keep resolved reports; a future report by the same member/post still conflicts with the unique record.

</details>

Proceed to [tests and debugging](06-testing-and-debugging.md), then record only work actually performed in [completion evidence](09-completion-evidence.md).
