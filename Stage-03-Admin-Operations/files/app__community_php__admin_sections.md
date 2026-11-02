# Moderation, private reports and deletion invariants

[Stage dashboard](../README.md) · [Canonical whole-file guide](../../Stage-02-Student-Experience/files/app__community_php.md) · [Actual source](../../app/community.php)

## File card and teaching boundary

| Field | Value |
|---|---|
| Canonical path | `app/community.php` |
| Baseline inspected | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |
| Approximate size | 140 lines; several PHP lines contain whole templates |
| Language | PHP with embedded HTML/SQL where shown |
| Primary Stage 03 scope | Admin operations described below; Member C remains TBD |
| Secondary review | Stage 04 for authorization/SQL; Stage 02 for student effects; Stage 01 for markup |
| Status | VERIFIED BY INSPECTION; execution evidence belongs to the [team register](../../Assemble/16-evidence-and-artifact-register.md) |

This section guide does not duplicate ownership of the whole file. The canonical guide linked above covers its other sections. Existing code is a Codex-assisted baseline, not new student-authored work.

## Why this section exists

Club discussions need a private review route separate from member feeds. Admin moderation changes visibility and resolves reports without rewriting authors' messages. Removing content has stronger consequences than hiding it.

## Prerequisites

Read [architecture](../01-architecture.md), [CRUD walkthroughs](../04-guided-walkthroughs.md) and [terms](../../Assemble/17-glossary-and-faq.md). A request is one browser/server exchange; a transaction is a group of database changes that commits or rolls back together.

## Imports and dependencies

The native request starts at [public/index.php](../../public/index.php), loads [bootstrap](../../app/bootstrap.php) and [actions](../../app/actions.php), then renders [layout](../../app/layout.php) → [views](../../app/views.php) → [portal views](../../app/portal-views.php) when selected. REST starts at [public/api.php](../../public/api.php) → [API dispatch](../../app/api.php). Both use the same models/community services; database constraints live in [schema](../../database/schema.sql) and the [community migration](../../database/migrations/20261017_club_communities.sql).

## Tour and coverage index

| Function/section | Scope and source-order behavior |
|---|---|
| `club_access()` | Requires user; club404; admin bypasses approved-membership requirement |
| `community_post()` | Club/post match; deleted/hidden checks; optional parent lock; author read only without lock |
| `community_body`, `community_summary`, `community_posts` | Bounded plain text and visible-feed counts; canonical student guide |
| `community_action()` | READ COMMITTED transaction wrapper for member mutations |
| `perform_community_action()` edit/delete | Author-only editing even for admin; author/admin deletion; erase content and child rows |
| comment-delete | Author/admin deletion after parent visibility check |
| report | One report per user/post, private 500-char reason,409 duplicate |
| `moderate_post()` | Admin+positiveID+status+reason → lock nondeleted → update+resolve+audit+commit |
| `community_api()` member branches | Member payload allowlists; canonical student guide |
| `community_api()` admin report/list/decision branches | Private report pagination, status allowlist, moderation dispatch |

### Moderation transaction

Allowed status is visible or hidden; reason is required1..500 chars. A nondeleted post is locked directly, without joining users. Its status and moderation reason update; all reports for that post become resolved; audit stores postID+status (not private report text); transaction commits. A thrown error rolls it all back. A previously hidden author may edit their own text but editing does not change status. Only admin restore changes it to visible.

### Deletion

Author or admin deletion uses a retained post tombstone (`status=deleted`, empty body/reason) and explicitly deletes comments/reactions/reports. The post ID remains; moderation excludes deleted and cannot restore it. Admin cannot edit another person's post, even though admin can remove it. Comment removal is allowed only when its parent is a visible accessible post under the common lookup; do not promise the UI can browse/remove hidden comments independently.

### REST admin branches

GET `/admin/community?status=reported|visible|hidden` or omitted status returns paginated nondeleted posts. GET `/admin/community/posts/<id>/reports` reveals report details only after admin guard. PATCH `/admin/community/posts/<id>` accepts only status/reason and uses the shared moderation function. Page filters differ slightly: HTML `all` equals omitted API status. The dispatcher also requires admin for `admin/` before entering this function.

## Real annotated excerpts

Copied central transaction statements:

```php
  $post=one('SELECT * FROM club_posts WHERE id=? AND status<>"deleted" FOR UPDATE',[$id]);
  if (!$post) throw new RequestError(404,'Post not found.');
  query('UPDATE club_posts SET status=?,moderation_reason=? WHERE id=?',[$status,$reason,$id]);
  query('UPDATE club_reports SET status="resolved" WHERE post_id=?',[$id]);
  audit('community_moderation','Post '.$id.': '.$status); db()->commit();
```

1. Parent-row lock serializes a conflicting operation.
2. Missing or deleted post becomes404.
3. Bound values store the decision.
4. Reports resolve in the same transaction.
5. Audit records action+post+status; commit publishes the grouped result.

The member mutation wrapper's exact statement is:

```php
db()->exec('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
```

It applies before beginning that next transaction so a write waiting behind deletion/moderation sees current committed visibility. Preserve this behavior when discussing the regression test.

## Input → transformation → output and connections

Member report POST → `club_reports.pending`. Admin GET queue → reports/read-only post context. PATCH decision/native `moderate_post` → lock `club_posts` → visibility/reason + `club_reports.resolved` + `audit_logs` →200 JSON or303. A member GET feed then excludes hidden/deleted posts. [Moderation UI guide](app__portal-views_php__admin_sections.md) explains private rendering.

## Why this implementation

Sharing service functions prevents native and REST actions from maintaining separate validation rules. PHP views render reads directly, so an HTML report is not necessarily fetched from its JSON report endpoint. Fixed entity/field allowlists make generated forms practical without accepting arbitrary SQL identifiers. The trade-off is shared-file coordination: changing one descriptor affects forms, API fields and persistence together.

## Security, correctness and accessibility

Report details are not public post fields. Text stays plain text and output is escaped. A hidden post's author edit never restores visibility. Read committed plus a locked parent makes stale queued child writes reject after deletion; a raw child insert bypassing services would violate the protocol even if its foreign key accepts the tombstoneID. Review both DB constraint and service invariant. Admin2FA remains opt-in baseline; ensure actual administrators enroll before handling real records.

## Common mistakes and failure diagnosis

| Symptom | Reason / safe step |
|---|---|
| Author changed hidden text but feed empty | Expected; admin must review/restore |
| Admin edit another author's post403 | Editing remains author-only |
| Repeat report409 | Unique report contract, resolved reports also exist |
| Restore deleted404 | Deleted is a tombstone, not hidden |
| Report queue clears after Keep visible | All pending reports resolved by decision |
| Race test fails after query refactor | Preserve direct parent lock + READ COMMITTED; inspect current-visibility behavior |

## Run and verify safely

From the repository root, after [local setup](../../Assemble/README.md):

```bash
php -l app/community.php
php bin/doctor.php
```

Use synthetic disposable records for UI inspection. Read [test isolation rules](../06-testing-and-debugging.md) before running integration suites. The syntax check does not prove authorization or business behavior works.

## Debugging lesson

Place a debugger breakpoint before the relevant validation and after its SQL statement, then inspect control flow and synthetic record IDs. Check response status in browser Network. Record the action name, expected status and opaque API request ID; never log passwords, session cookies, CSRF tokens, authenticator/recovery codes or real student rows. Do not enable public stack traces.

## Guided experiment

On paper interleave two operations: transactionA locks a synthetic post and deletes it; transactionB tries to comment and waits. Predict B must reread after A commits and return404, leaving no comment. Read the lockcode block and assertions in `tests/community.py`; optional execution only against isolated `sportshub_test`. No live moderation or deletion is necessary for this exercise. If executing the suite, only fixture data is discarded by the suite, never the main database.

## Challenge ladder

- **Understand:** Draw the caller→service→table chain before opening the answer.
- **Modify:** Propose one narrow improvement to this admin section, predict success and failure behavior, and agree the shared-file boundary before editing.
- **Engineer:** Explain a forged request or concurrent edit; propose a focused regression check and ask Stage 04 to review it. Save only evidence you actually produced.

## Knowledge check

1. Why is hiding reversible while deletion is not?
2. Why must reports resolve in the same transaction as status change?
3. Why is a foreign key alone insufficient to forbid comments on a deleted tombstone?

<details><summary>Answer key — compare after explaining aloud</summary>

1. Hidden retains text/children; deleted erases content and child rows.
2. Otherwise a partial failure could leave a resolved report with no applied decision or the inverse.
3. The parent row still exists, satisfying referential integrity; service visibility checks enforce the stronger business invariant.

</details>

## Contribution boundaries

Stage 03 owns the operational requirement, not exclusive edit rights. Name the specific branch/function in the [contribution contract](../07-contribution-contract.md), ask the canonical owner to review and run affected native/REST/community tests. Preserve existing field names, CSRF, role checks, escaping, transactions and foreign-key behavior unless a separately reviewed change explicitly addresses them. Never use a production delete to demonstrate learning.

## Current status and limitations

This guide was checked against the baseline source. No runtime change was made here. Manual experiments and proposed improvements are **PLANNED** until a human records execution. Historical test results in `docs/validation.md` are not fresh results for this guide. There is no production deployment claim. Consult the [current register](../../Assemble/16-evidence-and-artifact-register.md) for checks actually run by this task.

## Glossary

Moderation: reviewed visibility decision. Referential integrity: a child references an existing parent. Isolation level: which committed data a transaction reads. Race condition: timing between operations changes a result unless coordination protects it.

## One-minute explanation to personalize

“I can distinguish hide/restore from irreversible content deletion and explain why moderation updates, report resolution and audit commit together under a post lock.” Explain what you actually studied or changed; do not claim you originally implemented this baseline.
