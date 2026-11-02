# 02 — Canonical backend source inventory

[Dashboard](README.md) · [All tracked files](../Assemble/01-repository-assembly-map.md)

Baseline inspected: `f81eab54ebe7179e1763f364d6b8d4f564c36541`. The 27 primary guides below explain original source, not copies. The stage guide is primary for technical server/data behavior; student/admin behavior still requires domain peer review.

| Actual source | Primary guide | Category |
|---|---|---|
| [app/actions.php](../app/actions.php) | [Read guide](files/app__actions_php.md) | runtime source |
| [app/api.php](../app/api.php) | [Read guide](files/app__api_php.md) | runtime source |
| [app/bootstrap.php](../app/bootstrap.php) | [Read guide](files/app__bootstrap_php.md) | runtime source |
| [app/models.php](../app/models.php) | [Read guide](files/app__models_php.md) | runtime source |
| [app/security.php](../app/security.php) | [Read guide](files/app__security_php.md) | runtime source |
| [bin/build-openapi.py](../bin/build-openapi.py) | [Read guide](files/bin__build-openapi_py.md) | utility |
| [bin/cloud-start.sh](../bin/cloud-start.sh) | [Read guide](files/bin__cloud-start_sh.md) | utility |
| [bin/container-init.php](../bin/container-init.php) | [Read guide](files/bin__container-init_php.md) | utility |
| [bin/create-admin.php](../bin/create-admin.php) | [Read guide](files/bin__create-admin_php.md) | utility |
| [bin/demo-community.php](../bin/demo-community.php) | [Read guide](files/bin__demo-community_php.md) | utility |
| [bin/demo-users.php](../bin/demo-users.php) | [Read guide](files/bin__demo-users_php.md) | utility |
| [bin/doctor.php](../bin/doctor.php) | [Read guide](files/bin__doctor_php.md) | utility |
| [bin/migrate.php](../bin/migrate.php) | [Read guide](files/bin__migrate_php.md) | utility |
| [bin/seed.php](../bin/seed.php) | [Read guide](files/bin__seed_php.md) | utility |
| [bin/setup.php](../bin/setup.php) | [Read guide](files/bin__setup_php.md) | utility |
| [database/migrations/20261017_club_communities.sql](../database/migrations/20261017_club_communities.sql) | [Read guide](files/database__migrations__20261017_club_communities_sql.md) | database |
| [database/schema.sql](../database/schema.sql) | [Read guide](files/database__schema_sql.md) | database |
| [public/api.php](../public/api.php) | [Read guide](files/public__api_php.md) | runtime source |
| [public/assets/openapi.json](../public/assets/openapi.json) | [Read guide](files/public__assets__openapi_json.md) | generated API contract |
| [public/index.php](../public/index.php) | [Read guide](files/public__index_php.md) | runtime source |
| [public/router.php](../public/router.php) | [Read guide](files/public__router_php.md) | runtime source |
| [tests/api.py](../tests/api.py) | [Read guide](files/tests__api_py.md) | verification |
| [tests/browser.py](../tests/browser.py) | [Read guide](files/tests__browser_py.md) | verification |
| [tests/community.py](../tests/community.py) | [Read guide](files/tests__community_py.md) | verification |
| [tests/fixtures.php](../tests/fixtures.php) | [Read guide](files/tests__fixtures_php.md) | verification |
| [tests/integration.py](../tests/integration.py) | [Read guide](files/tests__integration_py.md) | verification |
| [tests/security.php](../tests/security.php) | [Read guide](files/tests__security_php.md) | verification |

## Shared primary guides

- [Community services](../Stage-02-Student-Experience/files/app__community_php.md): Stage 02 primary; Stage 04 reviews permission, SQL and race implications.
- [Public/student/admin view branches](../Stage-02-Student-Experience/files/app__views_php.md) and [portal branches](../Stage-02-Student-Experience/files/app__portal-views_php.md): Stage 02 file guide, Stage 03 admin section ownership, Stage 01 presentation review.
- [Browser DOM/fetch](../Stage-01-Frontend-UX/files/public__assets__app_js.md): Stage 01 primary; Stage 04 reviews JSON/CSRF contract changes.
- [Configuration/deployment](../Assemble/01-repository-assembly-map.md): Assemble primary; Stage 04 checks DB/secret/runtime requirements.

## Semantic coverage index

| Region | Guide |
|---|---|
| Bootstrap helpers and HTTP session initialization | [Source-order tour](files/app__bootstrap_php.md#5-source-order-coverage-tour) |
| Base32/TOTP/key/secretbox/throttle/session completion | [Source-order tour](files/app__security_php.md#5-source-order-coverage-tour) |
| Every shared mutation action | [Action tour](files/app__actions_php.md#5-source-order-coverage-tour) |
| Entity metadata, CRUD and three reservation paths, review | [Model tour](files/app__models_php.md#5-source-order-coverage-tour) |
| Every core route group, middleware and response helper | [API tour](files/app__api_php.md#5-source-order-coverage-tour) |
| Every SQL table including schema_migrations | [Data dictionary](data-dictionary.md) |
| All setup/migration/dev/test utilities | [Per-file guides above](02-source-inventory.md), [suite overview](verification-suites.md) |

No known backend path is intentionally uncovered. Guide coverage is inspection evidence; feature execution evidence lives separately in the current Assemble register. Generated OpenAPI is documented by its generator/contract rather than by 13,000 manually paraphrased lines.
