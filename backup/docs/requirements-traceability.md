# Topic 3 requirement traceability

**Interpretation:** EXISTING means implementation located in this package. A named test below means an assertion exists in the PHP suite, not that it was executed here; current outcomes are in [validation](validation.md). Human deliverables remain TODO until real evidence exists. UI routes are relative to the configured origin; REST routes start `/api/v1`.

| Requirement | Actual source | UI / API | Data / rule | Evidence to verify | State |
|---|---|---|---|---|---|
| HTML/CSS/JavaScript frontend | `app/layout.php`, `app/views.php`, `app/portal-views.php`, `public/assets/app.css`, `app.js` | All pages | Real PHP-emitted markup, responsive themes/navigation | PHP source audit; manual desktop/mobile and keyboard checklist | EXISTING; final manual device evidence TODO |
| Mandatory PHP backend | `public/index.php`, `public/api.php`, `app/actions.php`, `app/models.php` | Pages and `/api/v1/*` | Native forms and REST share PHP services | PHP lint/security/HTTP suite | EXISTING |
| Mandatory actual MySQL | `app/bootstrap.php`, `database/schema.sql` | Database-backed health and every persisted flow | Version guard rejects MariaDB/MySQL below 8; InnoDB relations | Doctor plus actual engine/version and migration/HTTP suite | EXISTING guard; current engine execution in validation |
| Separate admin/student interfaces | `public/index.php`, `app/layout.php`, `app/portal-views.php` | `?page=admin`, `?page=dashboard` | Server role guards, distinct sidebar destinations | Suite “Administrator rendered interface”; unauthorized admin requests | EXISTING |
| Register and secure login | `app/views.php`, `app/actions.php`, `app/security.php` | `?page=register/login/verify`, `/auth/*` | Student-only registration, Argon2id, session rotation and throttle | Suite registration, role rejection, login/2FA/rate-limit assertions | EXISTING |
| Browse/search/join clubs | `app/views.php`, `app/actions.php`, `app/models.php` | `?page=clubs`, `/clubs`, `POST /clubs/{id}/memberships` | Scoped search and pending relation; capacity/duplicate checks | Membership application/duplicate assertions; manual search filters | EXISTING |
| Manage clubs and approve members | `app/views.php`, `app/models.php`, `app/actions.php` | `?page=manage_clubs/approvals`, `/admin/clubs`, `/admin/memberships/{id}` | Admin allowlisted CRUD; pending-only approval with capacity/locks | CRUD loop and “Administrator approves membership” | EXISTING |
| Training sessions and match fixtures | `app/views.php`, `app/models.php` | `?page=schedule/manage_events`, `/events`, `/admin/events` | Club relation, optional tournament, time/type validation | All-entity CRUD and future-event attendance assertions | EXISTING |
| Student match/training registration | `app/actions.php`, `app/models.php` | Schedule Register; `POST /events/{id}/attendance` | Future event plus exact-club approved membership, unique participant | “Approved member registers for future training”; duplicate/ineligible checks where present | EXISTING |
| Create/manage tournaments | `app/models.php`, `app/views.php` | `?page=manage_tournaments`, `/admin/tournaments` | Open/ongoing/completed, ordered dates, deadline/capacity | Tournaments create/read/update/delete in PHP suite | EXISTING |
| Student tournament registration | `app/models.php`, `app/actions.php` | `?page=tournaments`, `POST /tournaments/{id}/registrations` | Individual registration, deadline/capacity/duplicate guard | “Individual tournament registration”; duplicate and own cancellation | EXISTING; automated bracket/team assignment not implemented |
| Record scores and show results/standings | `app/models.php`, `app/views.php`, `app/api.php` | Manage event scores; `?page=results`, `/results`, `/standings` | Paired scores only for match/opponent; generic win 3 / draw 1 / loss 0 | “Updated match scores visible”; “Scored win yields three standing points” | EXISTING; sport-specific tie-breaks TODO if requested |
| Facilities/equipment CRUD and booking requests | `app/models.php`, `app/views.php`, `app/actions.php` | `?page=facilities/manage_resources/approvals`, `/bookings`, `/admin/resources` | Single reservable resource, future interval ≤4h, parent lock and overlap checks | Resource CRUD, pending overlap, adjacency, duration, review and cancellation assertions | EXISTING |
| Individual participation history | `app/views.php`, `app/portal-views.php`, `app/api.php` | `?page=activity/dashboard`, `/me/memberships/registrations/attendance/bookings` | Session-scoped identity, registered versus attended, ownership/time checks | Own-history collection checks; attended history; cross-owner cancellation rejection | EXISTING |
| Participation/performance reports | `app/views.php`, `app/api.php`, `app/actions.php` | `?page=reports/results/audit`, `/admin/reports`, `/admin/audit-logs` | SQL aggregates, attendance after start, scores and audit | Report API/audit assertions and completed-session attendance | EXISTING; print-to-PDF is browser output, not a scheduled analytics service |
| Form validation and relevant CRUD | `app/bootstrap.php`, `app/models.php`, `app/actions.php`, `app/api.php` | Every mutation | Bounded fields, parameterized values, allowlisted entities/fields | All-entity CRUD, unexpected fields, sport length, score pair and booking limits | EXISTING |
| Professional responsive consistent navigation | `app/layout.php`, `app/ui.php`, `public/assets/*` | Dark/light toggle before profile, mobile drawer | Accessible labels, focus/keyboard and reduced-motion handling | Manual 390/768/1440 px and light/dark check; no new screenshot claim without capture | EXISTING; final manual evidence TODO |
| Innovative community option | `app/community.php`, `app/portal-views.php`, additive migration | `?page=club`, `?page=moderation`, club posts/member/report routes | Approved exact-club access, own edits, private reports, admin hide/restore | PHP suite community ownership/privacy/XSS/idempotence/moderation assertions | EXISTING optional extension |
| Innovative 2FA | `app/security.php`, `app/actions.php`, `app/views.php` | `?page=security/verify`, `/me/2fa*` | Encrypted secret, replay checks, hashed single-use recovery | Enrollment/encryption/pending access/replay/recovery/disable assertions | EXISTING opt-in extension |
| Complete source repository | This `backup/` package plus actual repository | Source link in final report | Genuine member history, lecturer access | Human verifies committed/published contents and access | TODO until actual publishing/access verified |
| Live working PHP/MySQL host | `docs/security-and-hosting.md` runbook | Actual HTTPS URL — human supplies | Real PHP/extensions/MySQL/private state/backups | Real host health and role/CRUD/2FA/booking checks | TODO; local health is insufficient |
| Report and real screenshots | `docs/report-and-video.md` | PDF/Word artifact | Real people/roles/title/objectives/system/screenshots/links | Human export, redaction and rubric review | TODO |
| Video and each member's explanation | `docs/report-and-video.md` | Actual accessible video URL — human supplies | Actual personal contribution and assistance disclosure | Rehearsal/recording/access check | TODO |
| Submission and deadline | Assignment manual | LMS/designated submission | Leader/designated member; deadline to confirm | Lecturer's current announcement | TODO |

## Rubric and practical evidence

| Criterion | Weight | Evidence to prepare |
|---|---:|---|
| UI design/frontend | 20% | Real student/admin dark/light and mobile captures, keyboard walk-through |
| Backend/database integration | 20% | Actual MySQL version/schema, safe query flow and executed tests |
| Admin functionality | 15% | CRUD, approvals, fixtures/scores, bookings and reports |
| Student functionality | 15% | Signup/login, membership, registration, booking/history |
| Report/screenshots/source/live links | 15% | Human-completed report and accessible real release links |
| Video/teamwork | 15% | Working demonstration and authentic member explanations/history |

The table does not allocate marks or certify acceptance. Confirm sport-specific interpretation with the lecturer. Known product limits and unexecuted platform/host checks must remain visible in the final report.
