# 06 · Verify allowed and denied behavior

[Labs](05-labs-and-exercises.md) → [Contribution contract](07-contribution-contract.md)

The weakest admin test is “the page loaded”. A useful check also attempts an invalid field, a forbidden role and a state change that should fail. Different failures need different diagnosis: an unavailable DB is not a reason to remove authorization.

## Isolation gate

The existing fixtures are intentionally destructive to **`sportshub_test` only**. Read [tests/fixtures.php](../tests/fixtures.php) and the runner environment assignments before executing any suite. Confirm effective DB_NAME is exactly `sportshub_test`, distinct from your development database, and never contains real work. Database credentials remain private; inspect names/status rather than printing `.env`. Tests use synthetic generated passwords. Never alter the fixture's name check to make a run convenient.

Run suites sequentially, because they reset the same test schema. Do not run a learner browser session against that schema concurrently. If you cannot establish isolation or the service is unavailable, mark **SKIPPED with reason** and continue source/documentation checks.

## Existing test map — inspected, not fresh pass claims

| Actual source test / assertion text | Operational protection | Command after isolation |
|---|---|---|
| integration: `Student cannot access admin`, `Student cannot submit admin mutation` | Native read/write role boundaries | `python3 tests/integration.py` |
| integration: Admin creates/updates/deletes club/tournament/equipment/fixture | Four native CRUD areas | same |
| integration: `Admin approves membership`, Booking approval | State transition and queues | same |
| integration: `Future attendance cannot be fabricated`, `Completed session attendance recorded` | Date gate and report facts | same |
| integration: `Recorded scores visible on results` | Event edit reaches public results | same |
| API: `Anonymous admin denied`, `Student admin read denied`, `Student admin write denied` |401/403 REST boundaries | `python3 tests/api.py` |
| API: entity payload loop: REST creates/replaces/deletes, Admin detail | Full four-entity JSON contract | same |
| API: `Unknown update fields rejected`, missing delete404 | Field allowlist/existence | same |
| API: admin GET route loop and membership/booking approval | Admin reads/report/lists and review | same |
| community: member queue/decision/report-details403 | Privacy and moderation role | `python3 tests/community.py` |
| community: Moderator hides/restores, hidden author edit, deleted restore404, audit count | Moderation state and audit | same |
| community: locked post deletion versus queued comment | Current-visibility invariant after lock wait | same |
| browser: admin club create/edit/delete; approval; hide/restore; admin pages390/1440 both themes | Rendered operations, REST map, responsive layouts | `python3 tests/browser.py` |

The API test's “Unknown update fields” case covers each entity. Tests do not automatically prove all maximum string lengths, MySQL 8 deployment, lecturer access, real devices, or every possible race. No standalone parallel booking-race result is claimed here.

## Non-destructive checks

From source root:

```bash
php -l app/views.php
php -l app/portal-views.php
php -l app/actions.php
php -l app/models.php
php -l app/community.php
git diff --check
```

`php bin/doctor.php` checks environment/schema without printing credentials. Follow [Stage 04 test guides](../Stage-04-Backend-Database-Security/06-testing-and-debugging.md) for runtime/tool prerequisites. Browser suite requires Playwright/Chromium; its absence means skipped, not pass. Windows/macOS native commands remain unexecuted on a Linux-only host unless separately evidenced.

## Manual admin regression checklist

- [ ] Guest and student cannot read/write admin routes; valid admin can.
- [ ] Four entity forms create, edit and planned delete only synthetic rows.
- [ ] Empty, invalid enum/date, paired-score and relationship cases reject correctly.
- [ ] Pending membership approves/rejects; full/stale review fails.
- [ ] Booking overlap and adjacent interval outcomes match the predicate.
- [ ] Future attendance rejects; started registration can be marked.
- [ ] Counts/rosters match synthetic SQL facts; print view is legible.
- [ ] Report reasons stay private; hide/restore/deletion distinctions hold.
- [ ] Keyboard/mobile390px and desktop, both themes, no clipped action buttons or overflow.
- [ ] Native forms still work with JS disabled; no false assumption that confirmation is a server gate.

## Debugging decision table

| Status/symptom | First evidence | Safe next step |
|---|---|---|
|401 API / native login redirect | Is authenticated session present? | Login normally; don't copy cookies into notes |
|403 | Role versus CSRF/Origin message | Use correct role and exact APP_URL; preserve checks |
|419 native | Form/session expired | Reload fresh form |
|404 | Correct entityID/post exists and not tombstone? | Reread synthetic record; don't disable404 |
|422 | Error message/field/date/state | Compare descriptor and model rules |
|409 | Duplicate report or state conflict | Read latest state before retrying |
|500/503 | Opaque requestID/service health | Private logs/doctor; don't show stack traces publicly |
| Page200 but wrong count | Exact SQL grouping/count unit | Compare synthetic rows, not assumed people count |
| Mutation request never sent | Browser Network/console, form action map | Check named fields and JS handler; try native fallback |
| SQL lock timeout | Competing transaction/service protocol | Inspect test transaction order with Stage 04; do not remove locks |

Log only operation names and synthetic IDs. A full request payload may include passwords/2FA/CSRF data; never dump it. Use breakpoints at `require_admin`, `save_entity`, parent lock and final review reread to see which decision failed.

## Fresh result policy

The [team evidence register](../Assemble/16-evidence-and-artifact-register.md) is canonical for checks actually run in this task. Historical counts in [legacy validation](../docs/validation.md) describe an earlier run. Human lab checks above are blank until personally executed; do not copy automated results into a student's contribution record as their work.
