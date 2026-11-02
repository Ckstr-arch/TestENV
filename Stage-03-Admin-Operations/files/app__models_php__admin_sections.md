# Entity validation and review transactions

[Stage dashboard](../README.md) · [Canonical whole-file guide](../../Stage-04-Backend-Database-Security/files/app__models_php.md) · [Actual source](../../app/models.php)

## File card and teaching boundary

| Field | Value |
|---|---|
| Canonical path | `app/models.php` |
| Baseline inspected | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |
| Approximate size | 78 lines; several PHP lines contain whole templates |
| Language | PHP with embedded HTML/SQL where shown |
| Primary Stage 03 scope | Admin operations described below; Member C remains TBD |
| Secondary review | Stage 04 for authorization/SQL; Stage 02 for student effects; Stage 01 for markup |
| Status | VERIFIED BY INSPECTION; execution evidence belongs to the [team register](../../Assemble/16-evidence-and-artifact-register.md) |

This section guide does not duplicate ownership of the whole file. The canonical guide linked above covers its other sections. Existing code is a Codex-assisted baseline, not new student-authored work.

## Why this section exists

`entities()` is the shared record contract; `save_entity()` converts that contract into validated writes; `review_request()` protects approval state and capacity. This is the operational boundary between an admin's intention and durable rows. It is not an authentication layer on its own: callers must require an admin.

## Prerequisites

Read [architecture](../01-architecture.md), [CRUD walkthroughs](../04-guided-walkthroughs.md) and [terms](../../Assemble/17-glossary-and-faq.md). A request is one browser/server exchange; a transaction is a group of database changes that commits or rolls back together.

## Imports and dependencies

The native request starts at [public/index.php](../../public/index.php), loads [bootstrap](../../app/bootstrap.php) and [actions](../../app/actions.php), then renders [layout](../../app/layout.php) → [views](../../app/views.php) → [portal views](../../app/portal-views.php) when selected. REST starts at [public/api.php](../../public/api.php) → [API dispatch](../../app/api.php). Both use the same models/community services; database constraints live in [schema](../../database/schema.sql) and the [community migration](../../database/migrations/20261017_club_communities.sql).

## Tour and coverage index

| Function in source order | Meaningful sections | Admin coverage |
|---|---|---|
| `entities()` | Four table keys, labels, field names and type descriptors | All descriptors in [field matrix](../04-guided-walkthroughs.md#crud-field-and-validation-matrix) |
| `save_entity()` | Descriptor lookup; type conversion; cross-field checks; event relationships/scores; create vs update; audit | [Save lifecycle](#save-lifecycle) |
| `reserve()` | Membership, tournament, event registration parent locks/capacity | Student primary [canonical guide](../../Stage-04-Backend-Database-Security/files/app__models_php.md); admin impact below |
| `review_request()` | Entity/status allowlist; transaction; parent lock; capacity/overlap; locked status reread; update+audit; rollback | [Review lifecycle](#review-lifecycle) |

### Save lifecycle

Only descriptors in `entities()` become columns. Enum arrays use `choice`; datetime uses strict parsing; capacity is positive <=10000; club ID is a positive integer; blank tournament ID becomes NULL. Scores accept blank or integers 0..100000, and opponent is optional <=120 chars. Ordinary fields use `text`, textarea max 5000, `name` max 120 and other ordinary fields max 100. Notice this does not always equal SQL column widths: `sport` SQL is60 but generic text accepts100; see mismatch notes in the field matrix. End must be after start. Registration deadline may equal start because code rejects only `>` (despite error text saying “before”). Events require existing club/optional tournament, paired scores and match+opponent for scores.

ID0 creates; nonzero updates after existence check. Parameterized values accompany dynamic SQL assembled only from fixed descriptors. It returns the saved ID and audits `save_<entity>`. Core record write and audit are not wrapped by `save_entity` in one transaction; an audit failure after a write is a documented atomicity limitation, not permission to remove auditing.

### Review lifecycle

**INSPECTION RISK — not reproduced or tested in this task:** `review_request()` performs an initial nonlocking read before waiting on the parent lock. Under REPEATABLE READ, later nonlocking capacity/overlap reads may use an earlier snapshot. The final locked request reread protects the same request's pending status; parent-lock intent alone does not prove capacity safety across different requests. See [the isolation analysis and planned regression](../../Stage-04-Backend-Database-Security/sql-and-transactions.md#isolation-sensitive-review-path-inspection-risk-not-reproduced).

Only memberships/bookings and approved/rejected statuses are accepted. Initial row read checks pending, then club/resource parent locks serialize competing requests. Club approval counts approved memberships versus capacity; reject skips capacity requirement. Booking approval rejects overlap with another approved booking using half-open intervals. The actual request is reread `FOR UPDATE` after acquiring its parent, so two simultaneous reviewers cannot both apply stale status changes. Status update and audit commit together; any exception rolls back. `reserve()` separately checks capacity/duplicates at request time; review must check again because conditions may change while a request waits.

## Real annotated excerpts

Copied review lock/reread fragment:

```php
  $r=one("SELECT * FROM $entity WHERE id=? FOR UPDATE",[$id]); if ($r['status']!=='pending') throw new DomainException('Request is no longer pending.');
  query("UPDATE $entity SET status=? WHERE id=?",[$status,$id]); audit('review_'.$entity,$id.': '.$status); db()->commit();
```

The lock waits for a conflicting writer. The status test runs after that wait. The UPDATE binds the chosen status/ID; audit joins the transaction; `commit` makes both visible. The `$entity` identifier came from an exact allowlist, not a request string concatenated unchecked.

Copied score pairing rule:

```php
if (($data['home_score']===null)!==($data['away_score']===null)) throw new DomainException('Enter both scores.');
```

Each side evaluates “missing?”. `!==` requires those booleans to differ, which means exactly one score is missing. Both NULL is an unscored event; both integers is a recorded result.

## Input → transformation → output and connections

Admin POST/PUT → API injects fixed entity/id or native `choice('entity', array_keys(entities()))` → `save_entity` validates all fields → INSERT/UPDATE + audit → saved ID. Review PATCH/native POST → `review_request` → parent lock + pending reread → memberships/bookings status and audit. [Actions section](app__actions_php__admin_sections.md) explains the authorization caller.

## Why this implementation

Sharing service functions prevents native and REST actions from maintaining separate validation rules. PHP views render reads directly, so an HTML report is not necessarily fetched from its JSON report endpoint. Fixed entity/field allowlists make generated forms practical without accepting arbitrary SQL identifiers. The trade-off is shared-file coordination: changing one descriptor affects forms, API fields and persistence together.

## Security, correctness and accessibility

Keep identifier allowlists and prepared value parameters as different protections. A transaction needs a consistent locking protocol followed by every writer; `FOR UPDATE` alone does not validate roles. Parent deletes cascade according to SQL; there is no archival flag for clubs/tournaments/resources. Capacity reductions below existing enrollments are currently not rejected by entity save; disclose that edge case rather than claiming the model fixes it. A tournament fixture is only existence-checked, not validated to lie within tournament dates or match its sport.

## Common mistakes and failure diagnosis

| Failure | Diagnostic lesson |
|---|---|
| `Invalid score` | Blank differs from zero; use integer 0, not empty string when score is zero |
| Only one score accepted in browser but save fails | Backend enforces pairing |
| Club full on approval after pending request | Capacity is rechecked under lock |
| Second review fails “no longer pending” | Expected stale-state rejection, not lost approval |
| Text accepted by PHP but SQL rejects/truncates | Compare descriptor max and actual column width; preserve SQL strictness |
| Audit write fails after save | Core save lacks encompassing transaction; investigate with synthetic record reread |

## Run and verify safely

From the repository root, after [local setup](../../Assemble/README.md):

```bash
php -l app/models.php
php bin/doctor.php
```

Use synthetic disposable records for UI inspection. Read [test isolation rules](../06-testing-and-debugging.md) before running integration suites. The syntax check does not prove authorization or business behavior works.

## Debugging lesson

Place a debugger breakpoint before the relevant validation and after its SQL statement, then inspect control flow and synthetic record IDs. Check response status in browser Network. Record the action name, expected status and opaque API request ID; never log passwords, session cookies, CSRF tokens, authenticator/recovery codes or real student rows. Do not enable public stack traces.

## Guided experiment

Without changing code, construct two synthetic score payloads on paper: `(home_score=0, away_score='')` and `(home_score=0, away_score=0, kind=match, opponent=Visitors)`. Predict the first rejects and second records a draw. On an isolated synthetic fixture, submit each, check Results and the audit action, then manually restore the original paired scores through the UI. Keep before/after evidence; do not delete a club just to reset a score lab.

## Challenge ladder

- **Understand:** Draw the caller→service→table chain before opening the answer.
- **Modify:** Propose one narrow improvement to this admin section, predict success and failure behavior, and agree the shared-file boundary before editing.
- **Engineer:** Explain a forged request or concurrent edit; propose a focused regression check and ask Stage 04 to review it. Save only evidence you actually produced.

## Knowledge check

1. Why must capacity be checked at approval as well as application?
2. Does prepared SQL bind a table name using `?`?
3. Why is a locked reread needed after the initial pending read?

<details><summary>Answer key — compare after explaining aloud</summary>

1. Other approvals can consume places between application and review.
2. No; placeholders bind values. Fixed allowlisted identifiers are required.
3. Another reviewer may have committed while this request waited for its parent lock; the latest locked status decides whether transition remains valid.

</details>

## Contribution boundaries

Stage 03 owns the operational requirement, not exclusive edit rights. Name the specific branch/function in the [contribution contract](../07-contribution-contract.md), ask the canonical owner to review and run affected native/REST/community tests. Preserve existing field names, CSRF, role checks, escaping, transactions and foreign-key behavior unless a separately reviewed change explicitly addresses them. Never use a production delete to demonstrate learning.

## Current status and limitations

This guide was checked against the baseline source. No runtime change was made here. Manual experiments and proposed improvements are **PLANNED** until a human records execution. Historical test results in `docs/validation.md` are not fresh results for this guide. There is no production deployment claim. Consult the [current register](../../Assemble/16-evidence-and-artifact-register.md) for checks actually run by this task.

## Glossary

Invariant: a rule that must remain true after a change. Row lock: temporary exclusion for a selected row during a transaction. NULL: missing SQL value, distinct from0. Atomicity: all-or-nothing completion.

## One-minute explanation to personalize

“I can explain how record descriptors constrain writes, why a zero score is valid, and why membership approvals lock the club then reread the request before changing status.” Explain what you actually studied or changed; do not claim you originally implemented this baseline.
