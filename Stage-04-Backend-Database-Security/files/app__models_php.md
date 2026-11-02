# Models: allowlisted records and transactional reservations

[Stage dashboard](../README.md) · [Real source](../../app/models.php) · [Source inventory](../02-source-inventory.md)

## 1. File card

| Item | Value |
|---|---|
| Canonical path | [app/models.php](../../app/models.php) |
| Primary owner | Stage 04; Member D **TBD**, no authorship assigned |
| Secondary reviewers | Stage 02 for student effects; Stage 03 for admin rules; Stage 01 for transport/UI effects |
| Language / size | PHP; 78 physical lines (many contain several statements) |
| Role | Describe editable domain entities, validate CRUD data and serialize capacity/review decisions. |
| Inspected baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |

## 2. Why it exists

The admin form builder and REST allowlist use one entity specification; reservations need parent locks and transactions that cannot be expressed by hiding a button.

## 3. Prerequisites

Read [PHP and HTTP foundations](../php-http-and-security.md), then the [request sequence](../01-architecture.md). Know variables, arrays, if/else, functions and exceptions before following a whole request. SQL prerequisites are in [SQL and transactions](../sql-and-transactions.md).

## 4. Dependencies

bootstrap validation/query/audit functions; clubs,tournaments,events,resources,memberships,registrations,attendance,bookings; InnoDB row locks and unique constraints.

The dependency is canonical source, not a PHP copy in these learning folders. See [the full map](../../Assemble/01-repository-assembly-map.md).

## 5. Source-order coverage tour

The index below accounts for every meaningful function/branch group in this file. Related operations share a row where their implementation is inseparable; read the rows in order. No intentional runtime region is omitted.

- [entities](#tour-1)
- [save_entity validation loop](#tour-2)
- [save_entity cross-field validation](#tour-3)
- [save_entity write and audit](#tour-4)
- [reserve memberships](#tour-5)
- [reserve registrations](#tour-6)
- [reserve attendance](#tour-7)
- [reserve commit/rollback](#tour-8)
- [review_request validation/read](#tour-9)
- [review_request parent checks](#tour-10)
- [review_request final lock/write](#tour-11)

<a id="tour-1"></a>

### entities

Four keys: clubs, tournaments, events, resources. Clubs need name/sport/description/coach/location/capacity; tournaments add start/end/deadline/status; events require club, optional tournament, training|match, location/time/opponent/paired scores; resources choose facility|equipment.

<a id="tour-2"></a>

### save_entity validation loop

Look up metadata or reject. Arrays call choice, datetimes call date_input, capacity/club use positive (capacity max10000), optional tournament becomes null, score blank becomes null or integer0..100000, opponent trimmed <=120, required text uses max5000 descriptions,120 names,100 other text.

<a id="tour-3"></a>

### save_entity cross-field validation

end must be strictly after start; registration_deadline may equal start but not exceed it (the message says before). Events require existing club and optional tournament. Either both scores are null or neither; a score needs kind=match and a nonempty opponent.

<a id="tour-4"></a>

### save_entity write and audit

For a nonzero ID require existing row, construct column placeholders from trusted metadata and UPDATE; otherwise INSERT. Values are bound, identifier strings are allowlisted. Return existing or lastInsertId. Save+audit have no enclosing transaction here; document that accurately.

<a id="tour-5"></a>

### reserve memberships

Lock clubs parent, reject missing club or existing non-rejected membership, count approved capacity, insert relationship or reset rejected status to pending using unique key.

<a id="tour-6"></a>

### reserve registrations

Lock tournament, require open status and deadline not already past, reject duplicate user+tournament, check capacity and insert. This is independent of per-event attendance and does not assign teams.

<a id="tour-7"></a>

### reserve attendance

Lock event, reject started session, require approved membership in the event club, reject duplicate user+event then insert attendance with schema default registered. No extra event capacity exists in this schema.

<a id="tour-8"></a>

### reserve commit/rollback

Audit inside transaction, then commit. Any Throwable rolls back if a transaction is active. This ensures failed capacity/reservation checks cannot leave a partial row.

<a id="tour-9"></a>

### review_request validation/read

Only memberships/bookings and approved/rejected status accepted. Begin transaction, fetch candidate and require pending. This initial fetch is not yet the final lock check.

<a id="tour-10"></a>

### review_request parent checks

Membership approval locks club and counts approved members; booking review locks resource and checks overlaps with other approved bookings. Both use the parent as the serialization point.

**INSPECTION RISK — not reproduced or tested in this task:** `review_request()` performs an initial nonlocking read before waiting on the parent lock. Under REPEATABLE READ, later nonlocking capacity/overlap reads may use an earlier snapshot. The final locked request reread protects the same request's pending status; parent-lock intent alone does not prove capacity safety across different requests. See [the isolation analysis and planned regression](../sql-and-transactions.md#isolation-sensitive-review-path-inspection-risk-not-reproduced).

<a id="tour-11"></a>

### review_request final lock/write

Reread request FOR UPDATE and require pending again, then update status, audit and commit. Another reviewer must not overwrite an already-final decision. Catch rollback prevents partial decision/audit.

## 6. Actual annotated excerpts

These excerpts are copied verbatim from the inspected source; their line numbers refer to the baseline, not a promise that future edits retain the same positions.

### Source lines 2–9

```php
function entities(): array {
 return [
 'clubs'=>['label'=>'Clubs','fields'=>['name'=>'text','sport'=>'text','description'=>'textarea','coach'=>'text','location'=>'text','capacity'=>'number']],
 'tournaments'=>['label'=>'Tournaments','fields'=>['name'=>'text','sport'=>'text','description'=>'textarea','location'=>'text','starts_at'=>'datetime-local','ends_at'=>'datetime-local','registration_deadline'=>'datetime-local','capacity'=>'number','status'=>['open','ongoing','completed']]],
 'events'=>['label'=>'Training & fixtures','fields'=>['title'=>'text','club_id'=>'clubs','tournament_id'=>'tournaments','kind'=>['training','match'],'location'=>'text','starts_at'=>'datetime-local','ends_at'=>'datetime-local','opponent'=>'optional','home_score'=>'score','away_score'=>'score']],
 'resources'=>['label'=>'Facilities & equipment','fields'=>['name'=>'text','kind'=>['facility','equipment'],'description'=>'textarea','location'=>'text']]
 ];
}
```

entities() is server-owned metadata. Its keys bound the SQL table names and its fields bound editable columns. Some field values are UI types; arrays enumerate allowed status/kind strings. Optional tournament linkage is distinct from a required club.

### Source lines 39–47

```php
 db()->beginTransaction();
 try {
  if ($table==='memberships') {
   $c=one('SELECT * FROM clubs WHERE id=? FOR UPDATE',[$id]); if (!$c) throw new DomainException('Club not found.');
   $existing=one('SELECT * FROM memberships WHERE club_id=? AND user_id=?',[$id,$uid]);
   if ($existing && $existing['status']!=='rejected') throw new DomainException('You already have a membership request.');
   if ((int)query("SELECT COUNT(*) FROM memberships WHERE club_id=? AND status='approved'",[$id])->fetchColumn()>=$c['capacity']) throw new DomainException('This club is full.');
   query("INSERT INTO memberships(user_id,club_id) VALUES(?,?) ON DUPLICATE KEY UPDATE status='pending'",[$uid,$id]);
  } elseif ($table==='registrations') {
```

reserve starts a transaction then locks the club row. A rejected membership may be resubmitted, whereas pending/approved rows block duplication. Count only approved members for capacity. ON DUPLICATE KEY UPDATE uses the unique user+club relationship to move rejected back to pending, without inserting another relationship.

## 7. Input → transformation → output

**Input:** Allowlisted entity/table name, posted record fields, record ID and session-derived user ID.

**Transformation:** Follow the coverage tour; validate at the boundary, enforce role/state rules, then write or project data.

**Output:** Inserted/updated domain records, reservation rows, reviewed statuses and audit events.

## 8. Connections and call chain

Native: [public/index.php](public__index_php.md) → [actions](app__actions_php.md) → [models](app__models_php.md) / [community](../../Stage-02-Student-Experience/files/app__community_php.md) → [bootstrap PDO](app__bootstrap_php.md). REST: [public/api.php](public__api_php.md) → [REST dispatcher](app__api_php.md) → those same services. [Security](app__security_php.md) participates in authentication; [schema](database__schema_sql.md) supplies persistent constraints. [Verification suites](../verification-suites.md) connect these paths to actual assertions.

## 9. Why this implementation

Parent-row locks are simple and work even when there are no child rows yet. They serialize operations per parent, which can reduce throughput for a hot club/resource; measure before proposing finer-grained locks.

## 10. Security, correctness and accessibility

The server is authoritative even when controls are hidden in HTML. Keep prepared values, trusted identifier allowlists, CSRF, role/ownership checks and transaction boundaries. Escape dynamic text at HTML output, not by damaging data before storage. Transport errors must remain understandable to the interface; Stage 01 reviews focus/announcements when error presentation changes. A diagram or explanation here is not a security certification. See [known product limits](../../docs/security.md).

## 11. Common failure diagnosis

- Binding a table name with ? does not work: keep entities and field names server-owned.
- Checking capacity without the parent lock permits two requests to claim the last place.
- Confusing registrations with attendance hides the separate tournament and session lifecycles.
- Reducing capacity below current approved membership is not explicitly prevented by save_entity: propose a reviewed policy change.
- Missing either score must fail even when one score is zero: compare null, not truthiness.

## 12. Run and verify safely

From repository root:

```bash
php -l app/models.php
```

This checks PHP syntax only; it does not prove HTTP, SQL, authorization or concurrency behavior. Follow [testing](../06-testing-and-debugging.md) for the appropriate functional suite. Do not execute the file as a standalone web application or point reset-capable tests at real data.

## 13. Debugging lesson

Set a breakpoint at the first relevant tour function, then step into its callee. Observe method, route, integer record ID, expected branch and transaction state. Do not inspect/share screenshots of password fields, session IDs, CSRF values, factor secrets, recovery codes or full account rows. If logging is needed, use a safe event label and request ID in protected logs; remove temporary diagnostics after the exercise.

## 14. Guided experiment

On paper, interleave two last-place registrations. Mark where the second request waits at SELECT tournament FOR UPDATE. Then compare with the booking collision test in the verification chapter; do not introduce real duplicate rows.

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

InnoDB: transactional MySQL engine; row lock: temporary exclusive claim; unique constraint: database duplicate prevention; idempotence: repeating an operation has the same end state.

## 20. One-minute explanation to personalize

Explain the file’s role, show one actual input check, follow one call to its next file, and demonstrate one failure case. Say “this existing baseline does…” before describing any real change you personally made. Add your own evidence and assistance disclosure; do not read this guide as a claim that you authored the prototype.
