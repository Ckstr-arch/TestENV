# 🧠 SQL and transaction clinic

[Stage dashboard](README.md) · [17-table dictionary](data-dictionary.md) · [Models guide](files/app__models_php.md) · [Actions guide](files/app__actions_php.md) · [Community service guide](../Stage-02-Student-Experience/files/app__community_php.md)

A database stores related facts. PHP asks questions, enforces who may act, and changes rows. To understand a SportsHub feature, always predict **which row changes, which rule permits it, and which response makes the change visible** before clicking its button.

All examples here are learning material for a designated disposable study database. They are not imports for the working `sportshub` database. No SQL in this chapter was executed as part of authoring it. See [current evidence](../Assemble/16-evidence-and-artifact-register.md).

## 1. Reading rows before changing them

A `SELECT` computes a result without changing stored rows. `WHERE` filters candidates; `ORDER BY` sorts output; `LIMIT` bounds it. A database row has no inherent display order.

```sql
-- Read-only; use a synthetic, designated disposable study database.
SELECT DATABASE();
SELECT id, name, sport, capacity
FROM clubs
WHERE sport = 'Tennis'
ORDER BY name, id
LIMIT 20;
```

Predict before running: does a club with a different sport appear because its name contains “Tennis”? No. The equality filter reads only `sport`; application search uses its own parameterized `LIKE` rules. Zero matches yields an empty set, not a missing-table error.

A SQL **join** follows a relationship. A membership stores IDs, not repeated user/club names. The following is an illustrative read-only explanation, not claimed to be an exact source query:

```sql
SELECT m.id, c.name AS club, m.status
FROM memberships AS m
JOIN clubs AS c ON c.id = m.club_id
WHERE m.user_id = 101
ORDER BY m.id DESC;
```

`m` and `c` are aliases, short names within this query. `ON` states how rows correspond. `WHERE m.user_id=101` selects a synthetic learner; it is **not** an authorization system. Actual PHP derives user IDs from the authenticated session, rather than trusting a browser-supplied user ID. An inner join omits rows without a parent; foreign keys normally prevent such orphans.

### Aggregates are computations, not stored counters

```sql
SELECT c.id, c.name, COUNT(m.id) AS approved_members
FROM clubs AS c
LEFT JOIN memberships AS m
  ON m.club_id = c.id AND m.status = 'approved'
GROUP BY c.id, c.name
ORDER BY c.name;
```

This illustrative query teaches why a left join keeps clubs with zero approved members. `COUNT(m.id)` ignores NULL from a missing match. `COUNT(*)` would count the synthetic left-side row and incorrectly show1 for an empty club. Putting `m.status='approved'` in `WHERE` instead of `ON` removes zero-member clubs and defeats the intended left join. Compare these outcomes on paper first.

The real dashboard/report SQL is in [portal views](../app/portal-views.php), [views](../app/views.php), and [API](../app/api.php). Counts describe actual registrations/approved memberships; registered participation is different from attended participation. A query joining multiple one-to-many relations can multiply rows: two memberships × three events produces six joined rows. Use appropriate pre-aggregation or `COUNT(DISTINCT...)` after reasoning about the required meaning.

## 2. Prepared statements and allowed identifiers

Actual helper, copied from [bootstrap](../app/bootstrap.php):

```php
function query(string $sql, array $args = []): PDOStatement { $s = db()->prepare($sql); $s->execute($args); return $s; }
```

1. `$sql` contains SQL syntax and placeholder positions.
2. `prepare` parses that syntax through native PDO MySQL prepared statements.
3. `execute($args)` binds values separately. A quote in a search string remains data.
4. The returned statement can fetch rows or report affected rows.

A placeholder represents a **value**, not a table name or an arbitrary SQL fragment. `SELECT * FROM ?` is not an allowed way to choose a table. This project derives administrative table/field identifiers from fixed `entities()` definitions and other explicit allowlists. Never replace the allowlist with `$_POST['entity']` interpolation merely because values remain prepared.

PHP output escaping answers a different question: `e()` prevents text from becoming HTML. Prepared SQL prevents data from changing SQL syntax. Neither one establishes permission to edit someone else's post; ownership checks do that.

## 3. INSERT, UPDATE, DELETE and defaults

- `INSERT` creates a row. Omitted columns can receive declared defaults: a membership starts pending; public account role starts student.
- `UPDATE ... WHERE id=?` changes existing rows. Without the intended `WHERE`, a broad accidental update can affect every row.
- `DELETE` removes rows. Parent deletion can cascade through foreign keys. It is not automatically reversible after commit.
- A unique constraint rejects duplicate pairs even if two requests race past a UI button or a preliminary lookup.

For practice, prefer the application's disposable-account workflow. The following is an **illustrative transaction exercise**, not an operation performed here. Use only a separate study database with a synthetic club whose ID you have just verified; never paste it into a shared/production client:

```sql
SELECT DATABASE();
START TRANSACTION;
SELECT id, name, capacity FROM clubs WHERE id = 201 FOR UPDATE;
-- Predict the effect before you type this synthetic-only update:
UPDATE clubs SET capacity = capacity + 1 WHERE id = 201;
SELECT id, capacity FROM clubs WHERE id = 201;
ROLLBACK;
```

Expected: your transaction sees its own update; rollback restores that temporary uncommitted capacity. If ID201 does not exist, no row changes. Record only synthetic output. This rollback applies to this transaction's DML; it does not undo other people's committed work or MySQL DDL. Do not introduce `COMMIT` in a practice step without deciding to retain its rows.

## 4. Join-club request → query → row change → response

```mermaid
sequenceDiagram
    actor Student
    participant Form as PHP club form
    participant JS as app.js (optional)
    participant HTTP as API or index.php
    participant Action as perform_action / reserve
    participant DB as InnoDB
    Student->>Form: Apply to join
    alt JavaScript enhancement
        Form->>JS: submit action=join, id, csrf
        JS->>HTTP: POST /api/v1/clubs/{id}/memberships + JSON / CSRF
    else Native fallback
        Form->>HTTP: POST /?page=clubs with form fields
    end
    HTTP->>Action: authenticated user + validated club ID
    Action->>DB: BEGIN; SELECT club FOR UPDATE
    DB-->>Action: club row or missing
    Action->>DB: Read existing membership / approved capacity
    Action->>DB: INSERT pair; rejected duplicate can become pending
    Action->>DB: audit; COMMIT
    Action-->>HTTP: action result
    HTTP-->>Student: JSON201 + redirect metadata OR flash +303 → dashboard
```

Read [reserve](../app/models.php): it locks the club row, rejects an existing pending/approved request, checks approved capacity, then uses an upsert to permit a rejected request to become pending. The stored user ID comes from the session. A unique user/club pair prevents duplicate relationships. Admin approval is a separate transaction and state transition; pending membership does not grant community or event-registration access.

**Failure prediction:** requesting a full club must produce a business-rule failure without an inserted pending row. A repeated request while pending must fail. Reapplying after rejection changes that existing pair's status, not necessarily its ID. Verify the actual response distinction between REST errors and native form errors in their entrypoints.

## 5. Tournament enrollment is a different relation

`reserve('registrations', tournamentId, uid)` locks the tournament, requires status open and an unexpired registration deadline, rejects an existing pair, checks capacity, inserts a registration and audits it before commit.

| Step | Database observation | Visible consequence |
|---|---|---|
| Public listing | tournaments status/dates plus current roster count | Register control and availability context |
| POST enrollment | Parent tournament lock; duplicate/deadline/capacity checks | Success or422 rule failure |
| Commit | One registrations(user,tournament) row | Own activity entry / roster count |
| Fixture registration | Separate events/attendance relation | Tournament enrollment alone does not create it |
| Cancellation | Own registration and tournament start checked | Eligible future enrollment row removed |

This is an individual roster. It is not automatic team allocation, a bracket generator or sport-specific seeding. Explain these limits honestly in a viva.

## 6. Booking collision: why the parent row lock matters

Actual overlap query from [actions](../app/actions.php):

```php
if (one("SELECT id FROM bookings WHERE resource_id=? AND status IN ('pending','approved') AND starts_at<? AND ends_at>?",[$resource,$end,$start])) throw new DomainException('This time slot is unavailable.');
```

Treat intervals as half-open: `[start,end)`. Existing `[16:00,17:00)` conflicts with new `[16:30,17:30)` because existing start <new end **and** existing end >new start. New `[17:00,18:00)` is adjacent, so the strict second inequality fails and it is allowed. Rejected/cancelled bookings do not block the slot; pending bookings do block it.

A unique key on resource/start would not prevent all overlaps: two different starts can still share time. The interval index narrows candidates but is not an exclusion constraint. The service first locks the **resources parent row**, so concurrent requests for that same unit serialize even when there is no existing booking row to lock.

```mermaid
sequenceDiagram
    participant A as Request A
    participant DB as InnoDB resource row
    participant B as Request B
    A->>DB: BEGIN; SELECT resource FOR UPDATE
    DB-->>A: lock acquired
    B->>DB: BEGIN; SELECT same resource FOR UPDATE
    Note over B,DB: B waits for A's transaction
    A->>DB: Read overlapping pending/approved rows
    A->>DB: INSERT pending booking; COMMIT
    DB-->>B: lock acquired after A releases
    B->>DB: Check current conflicting booking
    DB-->>B: Conflict if interval overlaps
    B->>DB: ROLLBACK on business exception
```

**Scope of evidence:** the native/API suites exercise sequential overlap rejection. This diagram explains the intended parent-lock design; it is not a claim that a concurrent booking load test ran. The community suite does contain a real two-connection deletion/comment race, which is a different invariant.

### Isolation-sensitive review path: INSPECTION RISK, not reproduced

`review_request()` begins its transaction with a nonlocking request read **before** locking the club/resource parent, then performs capacity/overlap reads. Under REPEATABLE READ, that earlier read may establish a snapshot before waiting for another reviewer. The later nonlocking count can then be stale. Do not assert a concurrency guarantee solely because `FOR UPDATE` appears somewhere in the function. This is an **INSPECTION RISK**, not a demonstrated current failure. A reviewed disposable regression scenario with two simultaneous membership approvals at the last available slot is **PLANNED**, not run by this guide.

Potential fixes require deliberate isolation/locking/read-order design and tests; do not silently alter the baseline. Stage 03 should review behavior, Stage 04 should review database semantics. MySQL 8 and the configured MariaDB engine must both be considered before claiming identical behavior.

## 7. Community deletion race and current committed visibility

[Community service](../app/community.php) sets transaction isolation READ COMMITTED for community writes and locks the parent post using a simple post-table query. If a comment waits behind a committed deletion, it must inspect the post's new deleted state and refuse rather than append a child. The [community suite](files/tests__community_py.md) uses a separate PHP connection to hold the parent lock and a concurrent HTTP comment to exercise this case.

A locking join can lock more rows than expected and can interact with engine visibility/locking behavior. The baseline deliberately uses a simple parent-row locking lookup, fetching display author data separately when not locking. Preserve that distinction when refactoring.

## 8. Atomicity, migrations and rollback

A transaction's **atomicity** means its successful data writes commit together, or its uncommitted writes roll back on failure. A catch must check `inTransaction()` before rolling back after a failure. A row lock lasts until transaction completion, not until a PHP variable goes out of scope.

Schema migration DDL can implicitly commit. The migration runner therefore uses retry-safe statements plus a filename ledger and an advisory migration lock. That advisory lock serializes migrations, not ordinary bookings. Applied SQL must not be edited to “upgrade” existing data; a new migration needs review and backup/restore rehearsal. [Assemble backup/restore](../Assemble/09-database-migration-backup-restore.md) governs operational recovery.

## 9. Three-level capstone and safe evidence

| Level | Task | Expected evidence |
|---|---|---|
| 1 — Understand | Draw the row relationship for membership, tournament enrollment and attendance. Read a real query for each. | Three call chains; identify why these are distinct join tables. |
| 2 — Modify | In a separate approved study database, run the read-only join and count queries above; change a filter, predict the result. | Synthetic result comparison, including a zero-member club. No real identities. |
| 3 — Engineer | Design a two-connection last-slot membership approval test and compare isolation/read order. Implement only after agreed review. | Initial rows, lock sequence, expected single approval, actual outcome, proposed fix/limitations; all currently **PLANNED**. |

**Prerequisites:** understand `SELECT`, primary/foreign keys, transactions, isolated test DB configuration. **Setup:** follow the Stage 04 start guide and prove the selected study DB is disposable. **Safety:** no shared DB resets or volume deletion; never display password hashes or TOTP fields. **Revert:** read-only tasks require no cleanup; rollback your own uncommitted study DML only, and remove only personally created sandbox files. If you committed synthetic app actions, use normal UI cancellation/removal only for your own disposable records after checking cascades.

<details><summary>Answer key / oral reasoning</summary>

A membership is a student's relationship to a club; a registration enrolls a user in a tournament; attendance enrolls/records a user in a particular event. A unique pair prevents duplicate identity relations but cannot enforce all capacity rules. A parent lock serializes a scope, while a transaction snapshot determines which nonlocking reads are visible. SQL index presence, lock presence and business correctness are different facts that must be connected by a tested sequence.

</details>
