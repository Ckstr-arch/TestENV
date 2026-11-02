# Actions: one business boundary, two transports

[Stage dashboard](../README.md) · [Real source](../../app/actions.php) · [Source inventory](../02-source-inventory.md)

## 1. File card

| Item | Value |
|---|---|
| Canonical path | [app/actions.php](../../app/actions.php) |
| Primary owner | Stage 04; Member D **TBD**, no authorship assigned |
| Secondary reviewers | Stage 02 for student effects; Stage 03 for admin rules; Stage 01 for transport/UI effects |
| Language / size | PHP; 104 physical lines (many contain several statements) |
| Role | Dispatch domain mutations shared by native HTML POST and versioned REST endpoints. |
| Inspected baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |

## 2. Why it exists

A rule such as club eligibility must not exist in two independently changing implementations. Native forms call the same action logic as the REST adapter, so turning JavaScript off preserves server validation.

## 3. Prerequisites

Read [PHP and HTTP foundations](../php-http-and-security.md), then the [request sequence](../01-architecture.md). Know variables, arrays, if/else, functions and exceptions before following a whole request. SQL prerequisites are in [SQL and transactions](../sql-and-transactions.md).

## 4. Dependencies

models.php and community.php are require_once dependencies; bootstrap/security helpers; all domain tables; API sets $_POST from an allowlisted JSON body.

The dependency is canonical source, not a PHP copy in these learning folders. See [the full map](../../Assemble/01-repository-assembly-map.md).

## 5. Source-order coverage tour

The index below accounts for every meaningful function/branch group in this file. Related operations share a row where their implementation is inseparable; read the rows in order. No intentional runtime region is omitted.

- [Imports and action_result](#tour-1)
- [handle_action](#tour-2)
- [perform_action delegation](#tour-3)
- [register](#tour-4)
- [login](#tour-5)
- [verify](#tour-6)
- [require_user / logout](#tour-7)
- [setup_2fa](#tour-8)
- [enable_2fa](#tour-9)
- [disable_2fa](#tour-10)
- [join / register_tournament / attend](#tour-11)
- [book](#tour-12)
- [cancel](#tour-13)
- [require_admin / save](#tour-14)
- [delete](#tour-15)
- [review](#tour-16)
- [attendance / unknown action](#tour-17)

<a id="tour-1"></a>

### Imports and action_result

Require model/community services once. action_result contains redirect, message and data; both transports interpret this shape. A function declared later can be called once the include has completed.

<a id="tour-2"></a>

### handle_action

Native POST checks csrf, extracts action, calls perform_action, optionally flashes and sends go() 303. A browser refresh follows the GET rather than resubmitting the original write.

<a id="tour-3"></a>

### perform_action delegation

community_* goes to community_action; moderate_post goes to its own service, which performs its own access checks. These branches precede generic require_user intentionally; the delegated code is responsible for membership/admin gates.

<a id="tour-4"></a>

### register

Require name/email/student_id/faculty; normalize email; validate email and alphanumeric/hyphen student ID, password 12–128 bytes; throttle; check duplicate identifiers; insert Argon2id hash. No role field is accepted: schema default is student. Return created ID and login route.

<a id="tour-5"></a>

### login

Normalize email, throttle and fetch account. password_verify uses a dummy hash for missing accounts, but this is not a claim of identical timing across Argon2id/bcrypt. Clear successful login bucket; rehash password if needed. If TOTP enrolled, rotate into pending_user/pending_until only; otherwise finish_login.

<a id="tour-6"></a>

### verify

Reject/clear expired or absent five-minute pending challenge, fetch pending account, throttle 2FA and call verify_second_factor. Only success reaches finish_login and grants user_id.

<a id="tour-7"></a>

### require_user / logout

All remaining generic actions require a current user. logout audits, clears state and rotates ID; the next page receives a new session rather than retaining authenticated state.

<a id="tour-8"></a>

### setup_2fa

Reject already-enrolled accounts; generate 20 random bytes encoded as base32 and retain for ten minutes in session. This action prepares enrollment; it does not activate the factor.

<a id="tour-9"></a>

### enable_2fa

Throttle, verify current password and session setup lifetime/code, reject already-enrolled state. Within a transaction save encrypted secret+used counter, replace old recovery hashes with eight random codes hashed with Argon2id and audit. Return codes once, clear setup state. API removes the session copy so JSON is the only display.

<a id="tour-10"></a>

### disable_2fa

Throttle; require both current password and valid factor. Clear secret/counter, remove recovery rows, audit. These statements are not wrapped in one enclosing action transaction; do not teach them as atomic with verification.

<a id="tour-11"></a>

### join / register_tournament / attend

Map fixed actions to memberships/registrations/attendance, validate positive ID, pass session user ID to reserve. The table is selected by code, never arbitrary client input.

<a id="tour-12"></a>

### book

Read future start/end, duration <= four hours and purpose <=255 characters; begin transaction; lock resource; reject pending/approved overlap; insert booking, audit and commit. No equipment inventory quantity accounting exists: one resource row is one reservable unit.

<a id="tour-13"></a>

### cancel

Allowlist four participation tables, fetch only id AND session user_id. Booking cancellation retains history as cancelled and requires a future pending/approved booking. Membership/registration/attendance cancellation deletes rows, with event/tournament timing and attended-state restrictions. Ownership is repeated in write WHERE clauses.

<a id="tour-14"></a>

### require_admin / save

Only after shared student/security actions is the admin gate crossed. save selects an entity from entities() keys and calls save_entity; returns manage_entity and saved ID.

<a id="tour-15"></a>

### delete

Allowlist entity, positive ID, DELETE by ID and audit. Foreign-key cascades may erase linked data. Browser confirmation is a usability guard; server administrator enforcement is the authorization guard.

<a id="tour-16"></a>

### review

Validate memberships/bookings, ID and approved/rejected status, then transactional review_request. Re-reviewing no-longer-pending requests is rejected by the model.

<a id="tour-17"></a>

### attendance / unknown action

Join attendance to event, require event started, update status=attended and audit. No arbitrary attendance status from the client is accepted. Unknown actions throw DomainException and become transport validation errors.

## 6. Actual annotated excerpts

These excerpts are copied verbatim from the inspected source; their line numbers refer to the baseline, not a promise that future edits retain the same positions.

### Source lines 4–12

```php
function action_result(string $redirect, string $message='', array $data=[]): array {
 return ['redirect'=>$redirect,'message'=>$message,'data'=>$data];
}
function handle_action(): never {
 check_csrf();
 $result=perform_action((string)($_POST['action']??''));
 if ($result['message']!=='') flash($result['message']);
 go($result['redirect']);
}
```

action_result returns data, rather than immediately outputting transport-specific HTML or JSON. handle_action is the native wrapper: check form CSRF, dispatch by action, store a flash message and redirect. The REST wrapper checks header CSRF earlier and calls perform_action directly.

### Source lines 61–72

```php
  query('UPDATE users SET totp_secret=NULL,totp_last_counter=NULL WHERE id=?',[$u['id']]); query('DELETE FROM recovery_codes WHERE user_id=?',[$u['id']]); audit('2fa_disabled','Authenticator removed'); return action_result('security','2FA disabled.');
 }
 if (in_array($action,['join','register_tournament','attend'],true)) { reserve(['join'=>'memberships','register_tournament'=>'registrations','attend'=>'attendance'][$action],positive('id'),(int)$u['id']); return action_result('dashboard','Your request has been saved.'); }
 if ($action==='book') {
  $resource=positive('resource_id'); $start=date_input('starts_at'); $end=date_input('ends_at'); $purpose=text('purpose',255);
  if (strtotime($start)<=time() || $end<=$start || strtotime($end)-strtotime($start)>4*3600) throw new DomainException('Choose a future booking of up to four hours.');
  db()->beginTransaction();
  try {
   if (!one('SELECT id FROM resources WHERE id=? FOR UPDATE',[$resource])) throw new DomainException('Resource not found.');
   if (one("SELECT id FROM bookings WHERE resource_id=? AND status IN ('pending','approved') AND starts_at<? AND ends_at>?",[$resource,$end,$start])) throw new DomainException('This time slot is unavailable.');
   query('INSERT INTO bookings(user_id,resource_id,starts_at,ends_at,purpose) VALUES(?,?,?,?,?)',[$u['id'],$resource,$start,$end,$purpose]); audit('booking_created','Resource '.$resource); db()->commit();
  } catch (Throwable $e) { if (db()->inTransaction()) db()->rollBack(); throw $e; }
```

The booking action validates a positive resource and exact datetimes before beginning a transaction. Future/up-to-four-hour constraints are server checks. Locking the resources row serializes contenders. The strict overlap comparison rejects intersecting pending or approved intervals, then insert+audit+commit happen together; exceptions roll back.

## 7. Input → transformation → output

**Input:** An allowlisted action name, $_POST values adapted from form or JSON, authenticated session and CSRF checked by the transport.

**Transformation:** Follow the coverage tour; validate at the boundary, enforce role/state rules, then write or project data.

**Output:** A structured redirect/message/data result, SQL writes, session changes and audit records.

## 8. Connections and call chain

Native: [public/index.php](public__index_php.md) → [actions](app__actions_php.md) → [models](app__models_php.md) / [community](../../Stage-02-Student-Experience/files/app__community_php.md) → [bootstrap PDO](app__bootstrap_php.md). REST: [public/api.php](public__api_php.md) → [REST dispatcher](app__api_php.md) → those same services. [Security](app__security_php.md) participates in authentication; [schema](database__schema_sql.md) supplies persistent constraints. [Verification suites](../verification-suites.md) connect these paths to actual assertions.

## 9. Why this implementation

Using $_POST as a shared adapter keeps the small prototype simple and ensures parity. An explicit typed command object could reduce coupling later; broad service refactoring is not part of these learning folders.

## 10. Security, correctness and accessibility

The server is authoritative even when controls are hidden in HTML. Keep prepared values, trusted identifier allowlists, CSRF, role/ownership checks and transaction boundaries. Escape dynamic text at HTML output, not by damaging data before storage. Transport errors must remain understandable to the interface; Stage 01 reviews focus/announcements when error presentation changes. A diagram or explanation here is not a security certification. See [known product limits](../../docs/security.md).

## 11. Common failure diagnosis

- Calling perform_action directly from a new public route without transport CSRF bypasses the intended boundary.
- Trusting user_id from a form permits ownership forgery: this file uses require_user id instead.
- Adding a role field to public registration would enable escalation: keep schema-controlled student default.
- Double-click retry after a timeout may duplicate a committed operation: fetch state before retrying.
- Treating all audit calls as transactional obscures partial-write risks in simple actions.

## 12. Run and verify safely

From repository root:

```bash
php -l app/actions.php
```

This checks PHP syntax only; it does not prove HTTP, SQL, authorization or concurrency behavior. Follow [testing](../06-testing-and-debugging.md) for the appropriate functional suite. Do not execute the file as a standalone web application or point reset-capable tests at real data.

## 13. Debugging lesson

Set a breakpoint at the first relevant tour function, then step into its callee. Observe method, route, integer record ID, expected branch and transaction state. Do not inspect/share screenshots of password fields, session IDs, CSRF values, factor secrets, recovery codes or full account rows. If logging is needed, use a safe event label and request ID in protected logs; remove temporary diagnostics after the exercise.

## 14. Guided experiment

Disable JavaScript in a disposable local browser session and submit a club application. Predict POST to /?page=clubs then 303 to dashboard; compare the enhanced path in the guided walkthrough. Cancel only the synthetic request you created.

Save a prediction and observed status/state, then explain any mismatch. Use no personal records. If you edit a study file, keep a before-copy outside public/, review only your diff and manually undo only your added lines. Never reset somebody else’s changes.

## 15. Challenge ladder

1. **Understand:** point to the first trust boundary in this file and predict one rejected input. Expected evidence: named branch and status/result.
2. **Modify:** propose one clearer validation error or regression assertion for this specific boundary. First write expected before/after behavior; implement manually only after a scoped review. Expected evidence: a focused diff and actual test output.
3. **Engineer:** analyze two concurrent requests, an expired session or an unavailable dependency at this boundary. Identify the preserved invariant and a remaining limit; propose a test without production data. Hint: distinguish transport checks from service/database rules.

## 16. Knowledge check

1. Which caller reaches this file for native forms and which for REST?
2. Why can a valid-looking browser form still fail on the server?
3. If an exception occurs after a SQL write, can you assume the write was rolled back?

<details><summary>Answer key — read after predicting</summary>

1. index.php→handle_action and public/api.php→api_dispatch are the entry chains; the coverage tour identifies the applicable branch.
2. The server rechecks session, role, ownership, fields, timing and persistent state. A UI cannot authorize an operation.
3. Only a write inside a live transaction that is rolled back is undone. Some simple writes in this baseline are autocommitted; inspect the exact transaction scope rather than claiming universal atomicity.

</details>

## 17. Contribution boundary

Member D is a suggested reviewer/learning track, not a retroactive author. Name the exact function/branch in a human-owned feature PR. Stage 02/03 review changed behavior; all stages review schema contracts; Stage 01 reviews form/API mapping effects. Run the focused tests, record actual results and disclose help according to course policy. Do not change authentication, encryption, field allowlists or destructive semantics merely to simplify a lesson. [Shared contract](../07-contribution-contract.md).

## 18. Status and limits

**VERIFIED BY INSPECTION:** source excerpts and coverage are based on the recorded commit. **Execution:** consult the current [evidence register](../../Assemble/16-evidence-and-artifact-register.md); historic results in legacy docs are not this run’s results. **PLANNED:** learner improvements and exercises have not been credited as completed human work. MySQL 8 deployment must be verified separately from the existing MariaDB local setup.

## 19. Glossary

Dispatch: choose a handler; progressive enhancement: native behavior plus optional JS; 303: redirect with a new GET; invariant: rule that must remain true.

## 20. One-minute explanation to personalize

Explain the file’s role, show one actual input check, follow one call to its next file, and demonstrate one failure case. Say “this existing baseline does…” before describing any real change you personally made. Add your own evidence and assistance disclosure; do not read this guide as a claim that you authored the prototype.
