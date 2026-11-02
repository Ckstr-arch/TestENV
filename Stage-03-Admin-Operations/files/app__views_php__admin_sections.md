# Admin forms, approvals and reports

[Stage dashboard](../README.md) · [Canonical whole-file guide](../../Stage-02-Student-Experience/files/app__views_php.md) · [Actual source](../../app/views.php)

## File card and teaching boundary

| Field | Value |
|---|---|
| Canonical path | `app/views.php` |
| Baseline inspected | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |
| Approximate size | 75 lines; several PHP lines contain whole templates |
| Language | PHP with embedded HTML/SQL where shown |
| Primary Stage 03 scope | Admin operations described below; Member C remains TBD |
| Secondary review | Stage 04 for authorization/SQL; Stage 02 for student effects; Stage 01 for markup |
| Status | VERIFIED BY INSPECTION; execution evidence belongs to the [team register](../../Assemble/16-evidence-and-artifact-register.md) |

This section guide does not duplicate ownership of the whole file. The canonical guide linked above covers its other sections. Existing code is a Codex-assisted baseline, not new student-authored work.

## Why this section exists

The `manage_*`, `approvals` and `reports` branches let the sports office maintain the same records students read. A single descriptor-driven form serves four CRUD entities. Without these branches administrators would still have an API, but no practical rendered management workspace.

## Prerequisites

Read [architecture](../01-architecture.md), [CRUD walkthroughs](../04-guided-walkthroughs.md) and [terms](../../Assemble/17-glossary-and-faq.md). A request is one browser/server exchange; a transaction is a group of database changes that commits or rolls back together.

## Imports and dependencies

The native request starts at [public/index.php](../../public/index.php), loads [bootstrap](../../app/bootstrap.php) and [actions](../../app/actions.php), then renders [layout](../../app/layout.php) → [views](../../app/views.php) → [portal views](../../app/portal-views.php) when selected. REST starts at [public/api.php](../../public/api.php) → [API dispatch](../../app/api.php). Both use the same models/community services; database constraints live in [schema](../../database/schema.sql) and the [community migration](../../database/migrations/20261017_club_communities.sql).

## Tour and coverage index

| Source section | Operational purpose | Coverage anchor |
|---|---|---|
| Helpers `heading`, `action_button`, `empty_state`, `badge`, `input` | Escaped labels and consistent forms; `action_button` inserts CSRF/action/id plus extras | [Forms and helpers](#forms-and-helpers) |
| `manage_*` branch | Allowlisted entity descriptor, optional `edit` row, fields, record cards and deletion form | [Management form](#management-form) |
| `approvals` | Pending memberships/bookings joined to human-readable names; Approve/Reject forms | [Approval queue](#approval-queue) |
| `reports` | Club aggregates, attendance actions, tournament roster, recent audit and print | [Reports](#reports) |
| `results` (shared public read) | Paired scores and 3/1/0 club aggregate; not a tournament bracket | [Results](#results) |

### Forms and helpers

`action_button('review', id, 'Approve', ['entity'=>..., 'status'=>'approved'])` creates a POST form. Hidden inputs are transport, not trustworthy permissions. `e()` escapes values at output. The `input()` helper sets HTML `required`, but the server independently validates data.

### Management form

`substr($page,7)` maps `manage_clubs` to `clubs`; `public/index.php` has already allowlisted page names and required an admin. `entities()` supplies fields. GET `edit` loads one row by prepared ID; no edit means `id=0` create. On failed save, `$error` makes submitted values take precedence over stored values. Enum arrays produce selects; `clubs`/`tournaments` descriptor types produce relationship selects; textarea gets an explicit `for`/`id`; date values convert SQL format to `datetime-local` format. Scores and opponent are optional. Save and Delete use POST actions, never GET mutations. Record listing uses a fixed allowed entity name and descending ID; it is not paginated or search-filtered in HTML.

### Approval queue

Only pending rows are shown. Membership joins users+clubs; booking joins users+resources and shows interval/purpose. Two buttons submit the same `review` action with different status. A row vanishes from this pending-only list after review; that does not mean it was deleted. This HTML view has no status filter; REST lists support a status filter.

### Reports

The club report has three independent scalar subqueries per club: approved memberships, all session registration/attendance rows, attended rows only. `registered` is a row count including already attended participants, not a count of distinct students. Attendance list joins user/event names; it displays Mark attended for `registered`, even future events, while the service rejects marking before start. Tournament participants come from `registrations`, not `attendance`. Recent audit is limited to 50 rows. `#print-report` triggers the browser print UI via JS; it is not a backend PDF generator.

### Results

A result is an `events` row with `kind='match'` and non-NULL home score. Paired-score validation is in `save_entity()`. The score card joins its club and optional tournament. Standings sum club wins/draws/losses and points across recorded matches; the opponent is free text, so there is no automatic opponent-club mirror row or sport-specific bracket/tie-breaker.

## Real annotated excerpts

Real fragment from the `manage_*` branch:

```php
$entity=substr($page,7); $spec=entities()[$entity]; $edit=isset($_GET['edit'])?one("SELECT * FROM $entity WHERE id=?",[(int)$_GET['edit']]):null;
```

1. `substr` removes the prefix; it does not validate a table name itself.
2. The allowlisted page and `entities()` mapping bound the identifier.
3. `(int)` converts the edit ID; `?` binds its value separately.
4. `null` selects create mode. This is a read query, not an update.

Real report button excerpt:

```html
<button class="button secondary" id="print-report">Print / save PDF</button>
```

`id` connects this HTML to one JS listener; the browser controls the print/save dialog. No report file is silently stored on the server.

## Input → transformation → output and connections

GET `/?page=manage_events&edit=<id>` → load descriptor and row → emit named inputs. Save → enhanced `PUT /api/v1/admin/events/<id>` or native POST action `save` → `perform_action()` → `require_admin()` → `save_entity()` → `events` update + audit → JSON redirect metadata or native 303 → fresh management HTML. The complete entity field map is in the [walkthrough](../04-guided-walkthroughs.md#crud-field-and-validation-matrix).

## Why this implementation

Sharing service functions prevents native and REST actions from maintaining separate validation rules. PHP views render reads directly, so an HTML report is not necessarily fetched from its JSON report endpoint. Fixed entity/field allowlists make generated forms practical without accepting arbitrary SQL identifiers. The trade-off is shared-file coordination: changing one descriptor affects forms, API fields and persistence together.

## Security, correctness and accessibility

Escaping protects stored titles, descriptions, names and report text. Role enforcement occurs at page routing and mutation dispatch, not in the sidebar. Removing a button cannot remove endpoint access. Keep label/field associations, keyboard reachable actions and the JS `data-confirm` warning. That confirmation is a usability guard, not a server policy: disabling JS removes the browser confirmation but not authorization/CSRF checks.

## Common mistakes and failure diagnosis

| Symptom | Likely reason | Safe diagnosis |
|---|---|---|
| Edit shows empty date | SQL datetime not converted to `Y-m-d\TH:i` | Inspect synthetic date value and emitted input |
| Save fails although browser accepted text | Server max length/type differs from UI hints | Read `save_entity` then response error |
| Mark attended fails for future session | Server checks start time | Choose a disposable past session; never weaken validation |
| Approved request disappears | Pending-only query | Inspect own synthetic row status or REST filtered list |
| Large report seems slow | Unpaginated HTML joins/subqueries | Inspect query plan in test DB; propose pagination with review |
| Delete succeeded without JS confirmation | `data-confirm` is client enhancement | Keep CSRF/role checks and disclose native behavior |

## Run and verify safely

From the repository root, after [local setup](../../Assemble/README.md):

```bash
php -l app/views.php
php bin/doctor.php
```

Use synthetic disposable records for UI inspection. Read [test isolation rules](../06-testing-and-debugging.md) before running integration suites. The syntax check does not prove authorization or business behavior works.

## Debugging lesson

Place a debugger breakpoint before the relevant validation and after its SQL statement, then inspect control flow and synthetic record IDs. Check response status in browser Network. Record the action name, expected status and opaque API request ID; never log passwords, session cookies, CSRF tokens, authenticator/recovery codes or real student rows. Do not enable public stack traces.

## Guided experiment

Read the report SQL and predict whether one synthetic student registered for two sessions counts once or twice. On an isolated study setup register that account for two future sessions, open Reports, and compare the club count. Expected: two registration rows, not one unique athlete. Record only synthetic before/after counts. Cancel those two future registrations from that student's account to remove only the lab rows; do not reset the DB.

## Challenge ladder

- **Understand:** Draw the caller→service→table chain before opening the answer.
- **Modify:** Propose one narrow improvement to this admin section, predict success and failure behavior, and agree the shared-file boundary before editing.
- **Engineer:** Explain a forged request or concurrent edit; propose a focused regression check and ask Stage 04 to review it. Save only evidence you actually produced.

## Knowledge check

1. Why is interpolating `$entity` acceptable here while interpolating an arbitrary query parameter would be dangerous?
2. Does a visible Mark attended button prove the server will accept the action?
3. What is the difference between a printed report and the REST JSON report?

<details><summary>Answer key — compare after explaining aloud</summary>

1. The upstream page allowlist and fixed `entities()` descriptor choose a known identifier; record values still use bindings.
2. No. The server rereads the event and rejects future attendance.
3. The HTML report is rendered by PHP and printed by the browser; JSON returns aggregates/collections for API clients and is not itself a PDF.

</details>

## Contribution boundaries

Stage 03 owns the operational requirement, not exclusive edit rights. Name the specific branch/function in the [contribution contract](../07-contribution-contract.md), ask the canonical owner to review and run affected native/REST/community tests. Preserve existing field names, CSRF, role checks, escaping, transactions and foreign-key behavior unless a separately reviewed change explicitly addresses them. Never use a production delete to demonstrate learning.

## Current status and limitations

This guide was checked against the baseline source. No runtime change was made here. Manual experiments and proposed improvements are **PLANNED** until a human records execution. Historical test results in `docs/validation.md` are not fresh results for this guide. There is no production deployment claim. Consult the [current register](../../Assemble/16-evidence-and-artifact-register.md) for checks actually run by this task.

## Glossary

Descriptor: a data structure describing form fields. Scalar subquery: a nested SELECT returning one value. Aggregate: a summary such as COUNT or SUM. DOM: the browser's tree of rendered elements.

## One-minute explanation to personalize

“I can trace the generic management form from its descriptor to named inputs and the shared save action. I can explain why attendance totals count records and how a pending-only approvals queue changes after review.” Explain what you actually studied or changed; do not claim you originally implemented this baseline.
