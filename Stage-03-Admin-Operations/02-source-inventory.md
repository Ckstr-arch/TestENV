# 02 · Source inventory and shared ownership

[Architecture](01-architecture.md) → [Roadmap](03-learning-roadmap.md)

All links describe baseline `f81eab5`. Stage 03 has **section ownership**, not a unique runtime directory. The exhaustive root inventory is [Assemble's map](../Assemble/01-repository-assembly-map.md). Every admin branch/function below has a guide; no shadow PHP application is created.

| Actual path / section | Stage 03 explanation | Canonical whole-file guide | Reviewer |
|---|---|---|---|
| `app/views.php`: manage_clubs/manage_tournaments/manage_events/manage_resources, approvals, reports, public results | [Views admin sections](files/app__views_php__admin_sections.md) | [Stage 02 views](../Stage-02-Student-Experience/files/app__views_php.md) | 02+04;01 markup |
| `app/portal-views.php`: admin, moderation, audit; admin post/comment controls | [Portal admin sections](files/app__portal-views_php__admin_sections.md) | [Stage 02 portal](../Stage-02-Student-Experience/files/app__portal-views_php.md) | 02+04 |
| `app/models.php`: entities/save_entity/review_request; reserve impacts | [Models admin sections](files/app__models_php__admin_sections.md) | [Stage 04 models](../Stage-04-Backend-Database-Security/files/app__models_php.md) | 04 |
| `app/actions.php`: handle_action, save/delete/review/attendance; early moderation | [Actions admin sections](files/app__actions_php__admin_sections.md) | [Stage 04 actions](../Stage-04-Backend-Database-Security/files/app__actions_php.md) | 04+02 |
| `app/community.php`: admin access/deletion, moderate_post, admin API branches | [Community admin sections](files/app__community_php__admin_sections.md) | [Stage 02 community](../Stage-02-Student-Experience/files/app__community_php.md) | 02+04 |
| `app/api.php`: admin CRUD/review/attendance/report/audit routes | [CRUD route matrix](04-guided-walkthroughs.md#crud-route-matrix) | [Stage 04 API](../Stage-04-Backend-Database-Security/files/app__api_php.md) | 04 |
| `public/index.php`: admin page guard | [Request architecture](01-architecture.md) | [Stage 04 page router](../Stage-04-Backend-Database-Security/files/public__index_php.md) | 04 |
| `app/layout.php`: grouped admin sidebar | [Operational navigation](04-guided-walkthroughs.md#admin-navigation-and-page-coverage) | [Stage 01 layout](../Stage-01-Frontend-UX/files/app__layout_php.md) | 01 |
| `public/assets/app.js`: generic save/delete/review/attendance/moderation maps | [Transport parity](files/app__actions_php__admin_sections.md) | [Stage 01 JS](../Stage-01-Frontend-UX/files/public__assets__app_js.md) | 01+04 |
| `database/schema.sql`: core constraints/cascades | [Deletion matrix](04-guided-walkthroughs.md#delete-impact-matrix) | [Stage 04 schema](../Stage-04-Backend-Database-Security/files/database__schema_sql.md) | all |
| `database/migrations/20261017_club_communities.sql` | [Moderation invariant](files/app__community_php__admin_sections.md) | [Stage 04 migration](../Stage-04-Backend-Database-Security/files/database__migrations__20261017_club_communities_sql.md) | all |
| `tests/integration.py`: native CRUD, denied admin, approvals, scores, attendance | [Test map](06-testing-and-debugging.md) | [Stage 04 native tests](../Stage-04-Backend-Database-Security/files/tests__integration_py.md) | 04+03 |
| `tests/api.py`: REST CRUD four entities, read/write denial, report/list reads | [Test map](06-testing-and-debugging.md) | [Stage 04 API tests](../Stage-04-Backend-Database-Security/files/tests__api_py.md) | 04+03 |
| `tests/community.py`: report privacy, forged moderation, hide/restore/deleted race | [Test map](06-testing-and-debugging.md) | [Stage 04 community tests](../Stage-04-Backend-Database-Security/files/tests__community_py.md) | 02+03+04 |
| `tests/browser.py`: admin CRUD, approval, moderation, mobile themes | [Test map](06-testing-and-debugging.md) | [Stage 04 browser tests](../Stage-04-Backend-Database-Security/files/tests__browser_py.md) | 01+02+03+04 |
| `docs/security.md`, `docs/api.md`, `docs/validation.md` | Known limits and historic evidence; not new execution | [Original docs](../docs/security.md) | all |

## Coverage interpretation

Page selectors are `/?page=...`, not folders of standalone HTML files. Score recording is event editing, not a missing `scores.php`. Fixtures are match-kind events with optional tournament linkage. The report view includes tournament rosters, not an admin interface that assigns team brackets. Stage 03 guide coverage does not prove runtime regression tests were executed; consult the [current test register](../Assemble/16-evidence-and-artifact-register.md).
