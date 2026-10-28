# Admin mutation dispatch and native/REST parity

[Stage dashboard](../README.md) · [Canonical whole-file guide](../../Stage-04-Backend-Database-Security/files/app__actions_php.md) · [Actual source](../../app/actions.php)

## File card and teaching boundary

| Field | Value |
|---|---|
| Canonical path | `app/actions.php` |
| Baseline inspected | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |
| Approximate size | 104 lines; several PHP lines contain whole templates |
| Language | PHP with embedded HTML/SQL where shown |
| Primary Stage 03 scope | Admin operations described below; Member C remains TBD |
| Secondary review | Stage 04 for authorization/SQL; Stage 02 for student effects; Stage 01 for markup |
| Status | VERIFIED BY INSPECTION; execution evidence belongs to the [team register](../../Assemble/16-evidence-and-artifact-register.md) |

This section guide does not duplicate ownership of the whole file. The canonical guide linked above covers its other sections. Existing code is a Codex-assisted baseline, not new student-authored work.

## Why this section exists

Shared action dispatch makes admin rules consistent for the native website and REST-enhanced forms. The key operational skill is identifying where authorization happens before a write, including moderation's earlier dispatch path.

## Prerequisites

Read [architecture](../01-architecture.md), [CRUD walkthroughs](../04-guided-walkthroughs.md) and [terms](../../Assemble/17-glossary-and-faq.md). A request is one browser/server exchange; a transaction is a group of database changes that commits or rolls back together.

## Imports and dependencies

The native request starts at [public/index.php](../../public/index.php), loads [bootstrap](../../app/bootstrap.php) and [actions](../../app/actions.php), then renders [layout](../../app/layout.php) → [views](../../app/views.php) → [portal views](../../app/portal-views.php) when selected. REST starts at [public/api.php](../../public/api.php) → [API dispatch](../../app/api.php). Both use the same models/community services; database constraints live in [schema](../../database/schema.sql) and the [community migration](../../database/migrations/20261017_club_communities.sql).

## Tour and coverage index

| Source-order function/branch | Operational role |
|---|---|
| `require_once models.php`, `community.php` | Load shared services once |
| `action_result()` | Standard redirect/message/data result |
| `handle_action()` | Native CSRF → action → flash →303 redirect |
| `perform_action`: community prefix / `moderate_post` | Early service dispatch; moderation checks admin inside service |
| register/login/verify | Public authentication branches; canonical Stage 04 guide |
| `$u=require_user()` and personal actions | Signed-in operations; canonical Stage 02/04 explanations |
| `require_admin()` | Gate all subsequent core admin actions |
| `save` | Allowlisted entity → `save_entity()` |
| `delete` | Allowlisted entity+positiveID → DELETE + audit |
| `review` | Membership/booking + status → `review_request()` |
| `attendance` | Attendance/event lookup → reject future/missing → attended+audit |
| final exception | Unknown action fails, not ignored |

### Admin dispatch boundary

A forged POST sent from a student dashboard still reaches `require_admin()` before save/delete/review/attendance. The page the attacker submits to does not grant privileges. The early `moderate_post` return does not cross this later gate, so its own service guard is essential.

### Delete behavior

`delete` uses `choice` for entity and `positive` for ID, deletes by ID and audits. Foreign keys determine child effects. The native action does not verify that the row existed; deleting a nonexistent ID can still report success/audit. REST checks `api_record` first and returns 404 for missing rows. Core DELETE and audit are not one explicit transaction. Browser confirmation is in JS and can be bypassed; it is not an authorization control.

### Attendance behavior

The action joins attendance to its event and checks `starts_at > time()`. Before start gives422; at/after start may become attended. It does not check a second `registered` state before UPDATE, so a repeated attended update is harmless to the row but adds another audit entry. No “absent” state or attendance reversal UI exists.

## Real annotated excerpts

Real admin dispatch excerpt:

```php
 require_admin();
 if ($action==='save') { $entity=choice('entity',array_keys(entities())); $savedId=save_entity($entity,(int)($_POST['id']??0)); return action_result('manage_'.$entity,'Record saved.',['id'=>$savedId]); }
```

First line checks the authenticated session's role. Then `choice` bounds the table key; the suppliedID selects create/update semantics; validation/persistence live in the model. The return describes navigation, it does not directly render a page.

Native CSRF entrypoint:

```php
 check_csrf();
 $result=perform_action((string)($_POST['action']??''));
```

The API uses its own header/origin validation before calling `perform_action`; it does not call this native wrapper.

## Input → transformation → output and connections

Native: POST current page → `handle_action` → hidden-token CSRF → `perform_action` → model/SQL → flash +303. Enhanced: form map in `app.js` selects URL+verb → JSON/X-CSRF-Token → API guards → `api_mutation` fixes route IDs/entity in `$_POST` → same `perform_action` → JSON response+redirect. Native errors may return HTML422; REST exposes documented status/envelope. See [CRUD walkthrough](../04-guided-walkthroughs.md).

## Why this implementation

Sharing service functions prevents native and REST actions from maintaining separate validation rules. PHP views render reads directly, so an HTML report is not necessarily fetched from its JSON report endpoint. Fixed entity/field allowlists make generated forms practical without accepting arbitrary SQL identifiers. The trade-off is shared-file coordination: changing one descriptor affects forms, API fields and persistence together.

## Security, correctness and accessibility

Never move `require_admin()` below a write. Route IDs must override body IDs, not the opposite. A public form's hidden `role=admin` is never a valid authorization mechanism. Native `check_csrf` uses419 while REST invalid token uses403. Duplicate mutation retries after a network failure may already have committed; reread synthetic state before retrying. Do not present native/REST as byte-for-byte identical transports.

## Common mistakes and failure diagnosis

| Symptom | Check |
|---|---|
| Native form419, enhanced form403 | Different CSRF transport/status contracts |
| Save posted to student page returns 403 | Expected service role gate |
| Unknown action422 | Action spelling/map; do not add permissive fallback |
| Deleting missing ID native looks successful | Native no existence precheck; REST checks first |
| Duplicate audit attendance entry | Repeated action is row-idempotent but audit not idempotent |
| Moderation guard bypass after refactor | Early dispatch requires its own `require_admin` |

## Run and verify safely

From the repository root, after [local setup](../../Assemble/README.md):

```bash
php -l app/actions.php
php bin/doctor.php
```

Use synthetic disposable records for UI inspection. Read [test isolation rules](../06-testing-and-debugging.md) before running integration suites. The syntax check does not prove authorization or business behavior works.

## Debugging lesson

Place a debugger breakpoint before the relevant validation and after its SQL statement, then inspect control flow and synthetic record IDs. Check response status in browser Network. Record the action name, expected status and opaque API request ID; never log passwords, session cookies, CSRF tokens, authenticator/recovery codes or real student rows. Do not enable public stack traces.

## Guided experiment

Trace a student-generated `save` request in source without sending it to shared/live data. Predict `require_user` succeeds, personal actions do not match, and `require_admin` rejects before SQL. Then review the existing negative tests in `tests/integration.py` and `tests/api.py`. Optional execution only after the test DB safety gate; save the actual result. There is no source change or row to undo for the read-only exercise.

## Challenge ladder

- **Understand:** Draw the caller→service→table chain before opening the answer.
- **Modify:** Propose one narrow improvement to this admin section, predict success and failure behavior, and agree the shared-file boundary before editing.
- **Engineer:** Explain a forged request or concurrent edit; propose a focused regression check and ask Stage 04 to review it. Save only evidence you actually produced.

## Knowledge check

1. Why can the same action run from two transports without two copies of business validation?
2. Why is `moderate_post()` responsible for checking admin itself?
3. Does a repeated attendance action necessarily return409?

<details><summary>Answer key — compare after explaining aloud</summary>

1. Both call `perform_action` after their transport-specific guards.
2. It is dispatched before the later common admin gate.
3. No; it updates attended again and audits if the event has started. Do not invent a stricter state machine than the source.

</details>

## Contribution boundaries

Stage 03 owns the operational requirement, not exclusive edit rights. Name the specific branch/function in the [contribution contract](../07-contribution-contract.md), ask the canonical owner to review and run affected native/REST/community tests. Preserve existing field names, CSRF, role checks, escaping, transactions and foreign-key behavior unless a separately reviewed change explicitly addresses them. Never use a production delete to demonstrate learning.

## Current status and limitations

This guide was checked against the baseline source. No runtime change was made here. Manual experiments and proposed improvements are **PLANNED** until a human records execution. Historical test results in `docs/validation.md` are not fresh results for this guide. There is no production deployment claim. Consult the [current register](../../Assemble/16-evidence-and-artifact-register.md) for checks actually run by this task.

## Glossary

Dispatch: choosing behavior from an action name. Transport: HTTP/JSON or HTML-form mechanism carrying the request. 303: tell browser to GET another page after POST. Idempotent: repeating an operation leaves its target state unchanged.

## One-minute explanation to personalize

“I can point to the native CSRF gate and the shared admin role gate, and explain how REST injects route-derived IDs before executing the same action.” Explain what you actually studied or changed; do not claim you originally implemented this baseline.
