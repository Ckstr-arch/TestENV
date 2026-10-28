# 02 · Student source inventory

Baseline: `f81eab54ebe7179e1763f364d6b8d4f564c36541`. Full repository coverage lives in the [assembly map](../Assemble/01-repository-assembly-map.md). The following three are canonical technical guides here; shared responsibilities do not transfer old code authorship.

| Actual source | Primary explanation | Review boundary |
|---|---|---|
| [app/views.php](../app/views.php) | [All helpers and page branches](files/app__views_php.md) | Stage 02 student templates; Stage 03 admin sections; Stage 01 layout styling |
| [app/portal-views.php](../app/portal-views.php) | [All portal branches](files/app__portal-views_php.md) | Stage 02 dashboard/club; Stage 03 admin/moderation/audit |
| [app/community.php](../app/community.php) | [All functions](files/app__community_php.md) | Stage 02 member semantics; Stage 03 moderation; Stage 04 transactions/API |

## Required cross-stage sources

| File | Read this part | Canonical guide |
|---|---|---|
| public/index.php | page allowlist, native errors, require_user/admin | [Entrypoint](../Stage-04-Backend-Database-Security/files/public__index_php.md) |
| public/assets/app.js | action-to-method/URL map, submit, redirects | [Browser enhancement](../Stage-01-Frontend-UX/files/public__assets__app_js.md) |
| app/actions.php | register/login/verify, enrollment, book, cancel, 2FA actions | [Action service](../Stage-04-Backend-Database-Security/files/app__actions_php.md) |
| app/models.php | reserve and review_request | [Domain rules](../Stage-04-Backend-Database-Security/files/app__models_php.md) |
| app/api.php | routes, CSRF/Origin, payload allowlists | [REST](../Stage-04-Backend-Database-Security/files/app__api_php.md) |
| app/bootstrap.php | escaping, PDO, session, CSRF, validation | [Bootstrap](../Stage-04-Backend-Database-Security/files/app__bootstrap_php.md) |
| app/security.php | login completion and second factor | [Security](../Stage-04-Backend-Database-Security/files/app__security_php.md) |
| database/schema.sql | users/memberships/events/attendance/registrations/bookings | [Schema](../Stage-04-Backend-Database-Security/files/database__schema_sql.md) |
| community migration | posts/comments/reactions/reports | [Migration](../Stage-04-Backend-Database-Security/files/database__migrations__20261017_club_communities_sql.md) |
| tests/integration.py | native POST registration, enrollments, booking, approvals | [Native tests](../Stage-04-Backend-Database-Security/files/tests__integration_py.md) |
| tests/api.py | JSON registrations, roles, ownership, state errors | [REST tests](../Stage-04-Backend-Database-Security/files/tests__api_py.md) |
| tests/community.py | private feed, author/member/admin, moderation/deletion races | [Community tests](../Stage-04-Backend-Database-Security/files/tests__community_py.md) |
| tests/browser.py | actual DOM, fetch routes, both themes/mobile | [Browser tests](../Stage-04-Backend-Database-Security/files/tests__browser_py.md) |

For admin functionality within the same files, start at [Stage 03](../Stage-03-Admin-Operations/README.md). Do not maintain a second description of password cryptography or the database dictionary in this stage.
