# Admin overview, moderation queue and audit history

[Stage dashboard](../README.md) · [Canonical whole-file guide](../../Stage-02-Student-Experience/files/app__portal-views_php.md) · [Actual source](../../app/portal-views.php)

## File card and teaching boundary

| Field | Value |
|---|---|
| Canonical path | `app/portal-views.php` |
| Baseline inspected | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |
| Approximate size | 49 lines; several PHP lines contain whole templates |
| Language | PHP with embedded HTML/SQL where shown |
| Primary Stage 03 scope | Admin operations described below; Member C remains TBD |
| Secondary review | Stage 04 for authorization/SQL; Stage 02 for student effects; Stage 01 for markup |
| Status | VERIFIED BY INSPECTION; execution evidence belongs to the [team register](../../Assemble/16-evidence-and-artifact-register.md) |

This section guide does not duplicate ownership of the whole file. The canonical guide linked above covers its other sections. Existing code is a Codex-assisted baseline, not new student-authored work.

## Why this section exists

The portal file separates an operational overview from record-entry pages. Its admin branches show genuine counts, recent activity and a private moderation workspace; they do not create artificial analytics or notification counts.

## Prerequisites

Read [architecture](../01-architecture.md), [CRUD walkthroughs](../04-guided-walkthroughs.md) and [terms](../../Assemble/17-glossary-and-faq.md). A request is one browser/server exchange; a transaction is a group of database changes that commits or rolls back together.

## Imports and dependencies

The native request starts at [public/index.php](../../public/index.php), loads [bootstrap](../../app/bootstrap.php) and [actions](../../app/actions.php), then renders [layout](../../app/layout.php) → [views](../../app/views.php) → [portal views](../../app/portal-views.php) when selected. REST starts at [public/api.php](../../public/api.php) → [API dispatch](../../app/api.php). Both use the same models/community services; database constraints live in [schema](../../database/schema.sql) and the [community migration](../../database/migrations/20261017_club_communities.sql).

## Tour and coverage index

| Branch | Source-order tour | Anchor |
|---|---|---|
| `admin` | Four counts → upcoming events → latest five audit entries → three most reported posts → four CRUD shortcuts | [Admin overview](#admin-overview) |
| `moderation` | Status allowlist → count/page/offset → pending reports and previous reason → decision form → pagination | [Moderation queue](#moderation-queue) |
| `audit` | Latest 100 audit events with nullable actor join | [Audit history](#audit-history) |
| Student dashboard/`my_clubs`/`club` | Owned by Stage 02; admin may enter club pages and delete posts/comments | [Cross-stage effect](#cross-stage-effect) |

### Admin overview

A scalar SELECT counts pending memberships, pending bookings, future events and tournaments whose status is not completed. “Active tournaments” therefore includes open and ongoing, irrespective of date. Upcoming sessions query is limited to four. Recent activity limits five entries and LEFT JOINs users so removed actors show “System”. The moderation preview groups unresolved reports, excludes deleted posts and limits three; report count is not total community activity. `entities()` generates the CRUD shortcut destinations.

### Moderation queue

HTML `status` defaults to `reported`, allowlists `reported|visible|hidden|all`; invalid input falls back to reported. `reported` uses EXISTS pending report, `all` retains only nondeleted posts. Pagination clamps `list_page` to 1..100000 and shows 20 posts. Report reasons/reporters are private to admins. Every decision asks hidden/visible plus nonempty reason, posts `moderate_post` and `post_id`, then returns to moderation. A previous reason may be shown. Deleted tombstones are absent and cannot be restored. The API uses omitted/empty status for all; sending literal `all` to the API is invalid — HTML and API filter encoding differs.

### Audit history

Latest 100 entries show formatted time, actor, action and escaped details. The JSON audit endpoint paginates a larger history. This is a database activity log, not cryptographically tamper-proof evidence; there is no retention UI or export in this branch.

### Cross-stage effect

An admin can view a club without approved membership, can delete another author's post/comment, but cannot edit another author's post. Moderation occurs here; ordinary feed stays in the Stage 02 guide. Keep that distinction when documenting “admin access”.

## Real annotated excerpts

Copied moderation filter initialization:

```php
$filter=(string)($_GET['status']??'reported'); if(!in_array($filter,['reported','visible','hidden','all'],true))$filter='reported'; $where='p.status<>"deleted"'; if($filter==='reported')$where.=' AND EXISTS(SELECT 1 FROM club_reports r WHERE r.post_id=p.id AND r.status="pending")'; elseif($filter!=='all')$where.=' AND p.status="'.$filter.'"';
```

Read left to right: choose a default; replace invalid values; always exclude tombstones; add pending-report existence or a fixed status. The interpolated status is safe only because the exact allowlist precedes interpolation. Do not remove that check when adding filters.

Copied decision fields:

```html
<input type="hidden" name="action" value="moderate_post"><input type="hidden" name="post_id" value="<?=$post['id']?>">
```

These bind the rendered form to its backend action and record. They are still attacker-controlled inputs at the server.

## Input → transformation → output and connections

GET `/?page=moderation&status=reported` → `public/index.php` requires admin → portal query obtains posts/reasons → form. Submit → JS `PATCH /api/v1/admin/community/posts/<id>` or native POST `moderate_post` → `moderate_post()` locks the post → status+reason, reports resolved, audit → queue refresh. Details in the [moderation service guide](app__community_php__admin_sections.md).

## Why this implementation

Sharing service functions prevents native and REST actions from maintaining separate validation rules. PHP views render reads directly, so an HTML report is not necessarily fetched from its JSON report endpoint. Fixed entity/field allowlists make generated forms practical without accepting arbitrary SQL identifiers. The trade-off is shared-file coordination: changing one descriptor affects forms, API fields and persistence together.

## Security, correctness and accessibility

Private report reasons must never be added to public post payloads or roster fields. Render with `e()`. Keep select labels, headings, table headers and meaningful empty states. Admin route guards occur before these queries. A page screenshot of the queue may reveal reporters; use synthetic identities and redact reasons if sensitive.

## Common mistakes and failure diagnosis

| Symptom | Check |
|---|---|
| Queue empty after Hide | Pending reports were resolved; use Hidden filter |
| API rejects status `all` | HTML label maps to omitted/empty API status, not literal `all` |
| “Active” count seems high | Query counts status != completed, not time window |
| Deleted actor shows System | LEFT JOIN actor is NULL; historical row remains |
| Page misses older audit row | HTML limit 100; use paginated API as admin |
| Restoring a deleted post fails404 | Tombstone excluded by service intentionally |

## Run and verify safely

From the repository root, after [local setup](../../Assemble/README.md):

```bash
php -l app/portal-views.php
php bin/doctor.php
```

Use synthetic disposable records for UI inspection. Read [test isolation rules](../06-testing-and-debugging.md) before running integration suites. The syntax check does not prove authorization or business behavior works.

## Debugging lesson

Place a debugger breakpoint before the relevant validation and after its SQL statement, then inspect control flow and synthetic record IDs. Check response status in browser Network. Record the action name, expected status and opaque API request ID; never log passwords, session cookies, CSRF tokens, authenticator/recovery codes or real student rows. Do not enable public stack traces.

## Guided experiment

In a disposable setup, let a synthetic approved member report a synthetic post. Predict which filters contain it before Hide and afterwards. Hide with a neutral synthetic reason: expected unresolved queue clears, Hidden contains the post. Restore with another reason: Visible contains it, pending report stays resolved. Save synthetic screenshots and two corresponding audit actions. Restore visibility to undo only the reversible lab state; report resolution/audit history remain truthful records and should not be erased.

## Challenge ladder

- **Understand:** Draw the caller→service→table chain before opening the answer.
- **Modify:** Propose one narrow improvement to this admin section, predict success and failure behavior, and agree the shared-file boundary before editing.
- **Engineer:** Explain a forged request or concurrent edit; propose a focused regression check and ask Stage 04 to review it. Save only evidence you actually produced.

## Knowledge check

1. Why can a pending report disappear while the post remains in the database?
2. Is “Active tournaments” equal to tournaments happening today?
3. Why does changing the HTML filter to arbitrary SQL introduce risk?

<details><summary>Answer key — compare after explaining aloud</summary>

1. Moderation resolves reports and changes post visibility; neither operation requires deleting the post.
2. No; it counts status other than completed.
3. The current query relies on an allowlist before interpolation. Bind values or maintain a fixed allowlist rather than accepting user SQL.

</details>

## Contribution boundaries

Stage 03 owns the operational requirement, not exclusive edit rights. Name the specific branch/function in the [contribution contract](../07-contribution-contract.md), ask the canonical owner to review and run affected native/REST/community tests. Preserve existing field names, CSRF, role checks, escaping, transactions and foreign-key behavior unless a separately reviewed change explicitly addresses them. Never use a production delete to demonstrate learning.

## Current status and limitations

This guide was checked against the baseline source. No runtime change was made here. Manual experiments and proposed improvements are **PLANNED** until a human records execution. Historical test results in `docs/validation.md` are not fresh results for this guide. There is no production deployment claim. Consult the [current register](../../Assemble/16-evidence-and-artifact-register.md) for checks actually run by this task.

## Glossary

EXISTS: true when a subquery has any matching row. LEFT JOIN: preserve left rows even when an actor is missing. Tombstone: retained deleted record identity with erased content. Pagination: bounded chunks of a result set.

## One-minute explanation to personalize

“I can distinguish dashboard summary limits from full record views and explain how a moderation decision resolves private reports while retaining the post and its audit trail.” Explain what you actually studied or changed; do not claim you originally implemented this baseline.
