# 🔍 Community service: authorization, state and transactions

## 1 · File card

| Field | Value |
|---|---|
| Canonical source | [app/community.php](../../app/community.php) |
| Language / size | PHP · 140 physical lines · 10,439 bytes; many physical lines contain several template statements |
| Primary guide / contributor boundary | Stage 02 · Member B (TBD); existing baseline authorship is not reassigned |
| Secondary reviewers | Stage 01 for presentation/accessibility; Stage 03 for admin/moderation branches; Stage 04 for API/data/security |
| Inspected baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |
| Role | Implements private club community reads/writes and moderation shared by HTML fallback and REST. |

## 2 · Why this file exists

Implements private club community reads/writes and moderation shared by HTML fallback and REST. Removing it would break its callers rather than produce a simpler independent frontend. The canonical application has one implementation; this guide is a map, not a replacement source file.

## 3 · Prerequisites

Read [student vocabulary](../00-start-here.md), [two transports](../01-architecture.md) and [all student journeys](../04-guided-walkthroughs.md). A PHP variable starts `$`; arrays store values by keys such as `$post['body']`; `if/elseif` chooses a branch; `foreach` repeats markup. `<?=...?>` emits an expression; leaving PHP mode emits literal HTML. Those operations happen on the server, before the browser constructs its DOM.

## 4 · Inputs and dependencies

Loaded through actions.php require_once; uses bootstrap require_user/admin, PDO query helpers, RequestError/text/positive/choice/audit; security throttle; actions action_result; API paginated/json_response/api_fields/api_filter/api_pagination. Tables: clubs/users/memberships, events summary; club_posts/comments/reactions/reports; audit_logs and login_attempts throttle. API method/path allowlist lives in app/api.php; views call summary/posts after appropriate gate. Environment DB settings/APP_URL/session and private APP_KEY are indirect shared dependencies, not values to print.

## 5 · Detailed source-order tour and coverage index

**Coverage navigation:**

- [club_access(clubId, optional user)](#club_accessclubid-optional-user)
- [community_post(clubId, postId, allowHidden=false, lock=false)](#community_postclubid-postid-allowhiddenfalse-lockfalse)
- [community_body(field=body, max=5000)](#community_bodyfieldbody-max5000)
- [community_summary(clubId)](#community_summaryclubid)
- [community_posts(clubId, uid, limit=20, offset=0)](#community_postsclubid-uid-limit20-offset0)
- [community_action(action)](#community_actionaction)
- [perform_community_action · common prelude](#perform_community_action--common-prelude)
- [Edit/delete branches](#editdelete-branches)
- [Comment branches](#comment-branches)
- [Reaction branches](#reaction-branches)
- [Report branch and unknown action](#report-branch-and-unknown-action)
- [moderate_post · shared Stage 03 feature](#moderate_post--shared-stage-03-feature)
- [community_api · member route recognition](#community_api--member-route-recognition)
- [community_api · GET branches](#community_api--get-branches)
- [community_api · mutation mapping](#community_api--mutation-mapping)
- [community_api · admin report collection](#community_api--admin-report-collection)
- [community_api · moderation queue](#community_api--moderation-queue)
- [community_api · moderation write and fallback](#community_api--moderation-write-and-fallback)

### club_access(clubId, optional user)

Resolve `require_user()` when no account passed, load exact club or404, then allow admin or an approved membership matching both club/user. A missing, pending, rejected or other-club relation is403. Return club row for trusted service context. This check recurs per request, so stale UI does not preserve rights after leaving.

### community_post(clubId, postId, allowHidden=false, lock=false)

Read post by both IDs; optionally append FOR UPDATE. Reject missing/deleted, and reject nonvisible unless explicitly allowed. Nonlocking reads add author name/faculty using a separate query; locking reads intentionally do not JOIN the users row. This keeps parent lock behavior focused. Hidden access is used narrowly for author edit/delete; public reads never return hidden/deleted bodies.

### community_body(field=body, max=5000)

Wrap common `text()` validation: trim, required, mb_strlen bound. Comments pass2000 and report reason500. This validator returns ordinary text; output still needs HTML escaping later. No Markdown/HTML sanitizer or rich editor is implemented.

### community_summary(clubId)

Three subqueries count approved memberships, visible posts and future events. Counts are actual records, not fabricated community statistics. It does not independently call club_access; callers must enforce the intended context. Club HTML overview can show summary without private post/roster bodies.

### community_posts(clubId, uid, limit=20, offset=0)

SELECT visible posts joined to author, per-post reaction/comment totals and current-user reacted EXISTS flag, ordered descending post ID, with bound limit/offset. This helper itself assumes access checked by caller. Fields exclude moderation reasons. HTML uses10/page; API supplies validated pagination. A future optimization must preserve visibility and current-user scoping.

### community_action(action)

Set transaction isolation READ COMMITTED before beginning. Perform mutation, commit and return its action result; any Throwable rolls back active transaction then rethrows. The isolation plus parent row lock ensures a dependent write sees committed moderation/deletion after waiting. Do not change this to an old repeatable-read snapshot or a joined lock casually; tests include a real deletion/comment interleaving.

### perform_community_action · common prelude

Require user; positive club ID; `club_access`; build internal community redirect. New post uses no post ID; all other actions require positive post ID. User ID is taken from session, never a client field. Post creation validates/throttles, inserts body and audits, returning created ID. Other actions call `community_post(...,allowHidden for edit/delete,lock=true)` before dependent changes.

### Edit/delete branches

Edit only own post; admin may delete others but cannot edit their words. Edit validates/throttles body, UPDATEs only body and leaves hidden status unchanged. Delete erases body, marks deleted, clears moderation reason and deletes dependent comments/reactions/reports within the same transaction. Both audit and return ID. This is irreversible tombstone behavior, not hide/restore.

### Comment branches

Add validates body max 2000 and throttles, then inserts under the locked visible parent. Delete requires positive comment ID belonging to that parent, then author/admin permission before DELETE and audit. Missing child404; wrong owner403. User cannot change a different post's comment by swapping ID alone.

### Reaction branches

React uses INSERT IGNORE for the unique post/user pair; unreact deletes only own pair. Return reacted boolean and authoritative count after mutation. Repetition is idempotent (same desired state), not an instruction to create another reaction row.

### Report branch and unknown action

Validate required reason max 500 and throttle. Existing post/user report yields409; otherwise insert report and return private-submission message. No public report detail is added to feed response. Unknown action raises422; wrapper rolls back. Reports are not a user-side hide button.

### moderate_post · shared Stage 03 feature

Require admin, positive post ID, allowlisted visible/hidden target and required reason. Begin transaction, lock a nondeleted post or404, update status/reason, resolve pending reports for post, audit and commit. Errors roll back. Deleted cannot be restored. Unlike author delete, hide retains body/children for review. Stage 03 owns policy/UI; Stage 04 reviews transaction correctness.

### community_api · member route recognition

Match clubs/{club}/(community|members|posts), optional post and comments/reaction/reports/child IDs. Upstream `api_dispatch` first restricts valid path/method combinations, auth where applicable, mutation CSRF/Origin and JSON. Do not treat this permissive shared regex alone as the complete REST allowlist. After matching require user and club access.

### community_api · GET branches

Community summary returns club+counts; members selects approved name/faculty/joined_at with API pagination. Single post reads only visible content and returns explicit safe field subset. Post comments validate parent visibility before pagination. Feed computes total visible posts then returns posts with page/per_page/total/pages metadata. Contact/auth and moderation reason fields are intentionally absent from member outputs.

### community_api · mutation mapping

Choose action from method/subresource: comments DELETE/remove vs add, reaction DELETE/remove vs add, reports→report, post DELETE→delete, post PATCH→edit, collection→create. Allow only body or reason as applicable; route-derived club/post/comment IDs overwrite client payload fields. Call transaction wrapper, choose201 for create/comment/report else200, set Location only on post create, return data/message with community redirect metadata. Routing IDs are not authorization shortcuts.

### community_api · admin report collection

GET admin/community/posts/{post}/reports requires admin and existing nondeleted post, then paginates reason/status/time/reporter. This is intentionally different from member-safe feed. A hidden post may still have admin report detail; a tombstone does not.

### community_api · moderation queue

Require admin. Filter accepts empty/visible/hidden/reported; invalid422. Omitted means all nondeleted, while reported requires unresolved reports. Queue includes pending-report count and admin fields; parameterize status. The HTML all filter maps conceptually to omitted REST status, not literal all.

### community_api · moderation write and fallback

PATCH admin/community/posts/{post} allows status/reason, fixes post ID from URL and calls moderate_post, returning moderation redirect. When no route matched, return false for the main dispatcher to continue. JSON response helpers exit on handled routes. Every declared function and meaningful branch is covered above.


## 6 · Real excerpts, read one statement at a time

```php
 if ($u['role']!=='admin' && !one('SELECT id FROM memberships WHERE club_id=? AND user_id=? AND status="approved"',[$clubId,$u['id']])) throw new RequestError(403,'An approved club membership is required for this community.');
```
`!==` compares value and type; `&&` means both sides must be true; `!one(...)` means no matching row. Thus a non-admin without the exact approved relation fails. Two placeholders bind club/user values separately, preventing a caller from reusing approval for another club. Throwing stops normal flow and lets the transport format the error.

```php
 db()->exec('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
 db()->beginTransaction();
 try {
  $result=perform_community_action($action); db()->commit(); return $result;
 } catch (Throwable $ex) { if (db()->inTransaction()) db()->rollBack(); throw $ex; }
```
Set isolation before BEGIN for this transaction. The service result is returned only after commit. Catching Throwable includes validation and database failures; rollback makes partial changes invisible and rethrow preserves the proper error path. Isolation is not a substitute for locks: `community_post(...,lock=true)` serializes dependent edits/comments against the same post.


## 7 · Input → transformation → output

Session account + validated route/form IDs + bounded plain text → exact-club authorization → parent/post lock + transaction → insert/update/delete/summary → action result/native redirect or safe JSON. Moderation changes post visibility and report status atomically; author deletion also removes dependent content. Read helpers return arrays and rely on callers to apply their documented access boundary.

## 8 · Follow the connections

The shared entry chain is [public/index.php](../../public/index.php) → [layout](../../app/layout.php) → [views](../../app/views.php) → [portal views](../../app/portal-views.php). Browser mutations use [app.js](../../public/assets/app.js) → [public/api.php](../../public/api.php) → [API dispatch](../../app/api.php) → [actions](../../app/actions.php) → [models](../../app/models.php) or [community](../../app/community.php). Database relations are defined in [schema](../../database/schema.sql) and [community migration](../../database/migrations/20261017_club_communities.sql). [Source inventory](../02-source-inventory.md) links every cross-stage technical guide.

## 9 · Why this implementation?

One shared service prevents JS/native drift. Parent locks protect dependent changes, while unique pairs handle duplicate reactions/reports. Tombstones avoid silently reusing a deleted post ID and distinguish deletion from reversible hide. Plain text plus escaping keeps the rendering contract small. Trade-offs: feed subqueries and per-post HTML comment reads need measurement at scale; no attachments, notifications or rich-text behavior should be claimed.

## 10 · Security, correctness and accessibility

Recheck approved exact-club membership for each request. Preserve ownership, method/field allowlists and server-fixed IDs. READ COMMITTED with parent lock is a correctness choice tested against deletion races, not a performance tweak. Throttle writes; never reveal private report reasons/contact/auth fields to members. Markup escaping occurs in the templates, so raw validated text remains untrusted output. Hiding content is not sanitizing it. All frontend controls need labels and correct action IDs.

## 11 · Common mistakes and diagnosis

| Mistake | Symptom | Safe diagnosis |
|---|---|---|
| Check membership only at render | Stale tab can write after leaving | Verify club_access is called in service on every request |
| Permit admin edit | Another person's words are rewritten | Preserve own-only edit and separate admin delete/moderation |
| Update status during author edit | Hidden content becomes public | UPDATE body only; verify hidden-edit test |
| Remove parent lock | Comment survives deletion race | Study concurrency test rather than retry arbitrary errors |
| Return p.* to members | Moderation reason leaks | Preserve safe field subset and feed SELECT |
| Treat delete as hide | UI promises impossible restore | Explain erased body/children and tombstone |
| Put route IDs in allowed JSON | Unexpected-field failure or mass assignment | Fix IDs from URL and strip them in JS payload |

## 12 · Run it through the application

Do not request `app/...php` over HTTP or run a template as a standalone CLI page: its required request context is absent. Complete [local setup](../../Assemble/README.md), use document root `public/`, and run these **suggested checks, not a claim of execution here** from repository root:

```bash
php -l app/community.php
php bin/doctor.php
php -S localhost:8080 -t public public/router.php
```

Choose that origin only when `APP_URL` matches. On an existing instance use its existing port/project; do not start another conflicting server. Read [test isolation](../06-testing-and-debugging.md) before optional database suites.

## 13 · Debug without recording private state

Place a debugger breakpoint at the relevant branch/function and inspect integer target IDs, branch decisions, status and synthetic row counts. Never dump `$_POST`, `$_SESSION`, full user rows, cookies, keys, passwords, recovery codes or private report text. Network evidence should contain method/path/status rather than secret-bearing headers. A generic503 is a reason to inspect protected server logs, not to expose stack traces publicly.

## 14 · Small guided experiment

Use an approved disposable member to add one harmless plain-text post and one reaction. Press reaction again after removing it; inspect the authoritative count, then explain why repeated PUT aims for one state while toggle UI chooses PUT or DELETE. Delete only your own lab post afterward and observe it cannot be restored. Do not test on shared real discussion. For stale rights, follow labD with no source edits.

## 15 · Challenge ladder

- **Understand:** trace a visible action to its exact form fields and service/table; explain the current success and failure response before touching code.
- **Modify:** after reproducing a real usability issue, improve one student-facing message/empty state in the relevant view. Predict the result, preserve fields/checks and compare native/REST plus mobile.
- **Engineer:** reason about a changed permission or capacity between render and submit. Propose a focused regression assertion and a reviewed patch only if it exposes a real gap. Consult Stage 04 before changing a transaction or API contract.

Use [full lab setup/cleanup](../05-labs-and-exercises.md). Hints are separated in the stage answer key; these challenges have not been performed or credited to a student.

## 16 · Knowledge check

1. Why does community_post need both clubId and postId?
2. Why can an author edit hidden text without making it visible?
3. What could fail if a dependent comment uses an old snapshot after waiting for a delete lock?

<details>
<summary>Answer key · read after predicting</summary>

1. The post must belong to the authorized club; a globally valid post ID alone must not bridge another club's boundary.
2. allowHidden permits the ownership check/edit path, but SQL updates only body; moderation remains admin-only.
3. It could act on previously visible state after deletion committed; current committed reads and the parent lock prevent a stale dependent write.

</details>

## 17 · Contribution boundaries

Name the function/page branch before editing and coordinate [shared-file review](../07-contribution-contract.md). Preserve the existing navy/lime and light themes, keyboard controls, data-derived counts, native form fallback, session identity, escaping, CSRF and authorization. Stage 03 reviews all admin/moderation changes; Stage 04 reviews SQL/API/locking/security. No work is assigned or credited merely because this file has a primary guide here.

## 18 · Current status and limitations

**VERIFIED BY INSPECTION:** the branch/function coverage and excerpts above describe the inspected baseline. **NOT RUN in this individual guide:** runtime/browser/database commands; use the central [execution register](../../Assemble/16-evidence-and-artifact-register.md) for actual current outcomes. **PLANNED:** learner exercises and future PRs. No public host or independent security certification is claimed. All major source sections are indexed above; deeper shared security/database primitives belong to the linked Stage 04 guides, not an alternate implementation here.

## 19 · Glossary

**Idempotent:** repeating a desired-state operation does not multiply its effect. **Row lock:** serialize conflicting writes on a record. **READ COMMITTED:** each read sees committed state appropriate to that statement. **Atomic:** all transaction changes commit together or roll back. **Tombstone:** retained deleted identity with erased content. **Ownership:** permission tied to the authenticated creator.

## 20 · One-minute explanation to personalize

Start with club_access, distinguish user identity from submitted IDs, and follow one comment through validation, parent lock, SQL and response. Explain how a hidden post differs from a deleted tombstone, and why author edit cannot bypass moderation. Point out that the existing implementation is a baseline you studied, not automatically your own authorship.

This is a study prompt, not a first-person authorship claim. Replace it with your own explanation after understanding, implementing/reviewing and testing real work. Record the evidence in [your unfilled record](../09-completion-evidence.md).
