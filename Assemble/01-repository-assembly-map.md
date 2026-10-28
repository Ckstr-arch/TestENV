# 🔍 Repository audit and assembly map

## Confirmed baseline before edits

- Working directory: `/workspace/TestENV`; branch `main`; initial `git status --short` empty.
- Local/runtime baseline: `f81eab54ebe7179e1763f364d6b8d4f564c36541`.
- Fetched remote specification commit: `096258d302367aba198a83147c5f985fa74477b7`; compared tree differed only by `Codex_SportsHub_Master_Prompt.md` (743 lines).
- No merge/branch switch was performed. The exact remote prompt was copied into the local tree for review/navigation. It is untracked locally until a human decides how to integrate the newer remote commit and these docs.
- Remote retained: `https://github.com/Ckstr-arch/TestENV.git`; no Git identity/settings changed. Existing author metadata was inspected, not reassigned.
- The source inventory was collected **before documentation edits**. No runtime source/config/test/schema was changed. Private existing `.env`, `.env.docker`, var/ and outside-checkout toolchain were preserved and excluded from teaching inventories.
- The supplied four-page assignment PDF was read through pdftotext. Topic 3, PHP/MySQL mandatory, six rubric weights, true team contributions and tentative unconfirmed deadline match the prompt. Attachment is external to Git; do not pretend its local path is a hosted submission artifact.

## Canonical ownership, not authorship

There are **63 baseline tracked paths + 1 remote specification = 64 primary guide entries**. Stage 03 owns admin **sections/features** across shared files, so it has section guides and secondary review rather than artificially unique production files. The JSON [baseline manifest](baseline-source-manifest.json) records path/category/owner/guide, line counts and SHA256 for preservation checks. It contains no private file hashes or values.

| Path | Category | Primary guide owner | Guide |
|---|---|---|---|
| [.dockerignore](../.dockerignore) | configuration/deployment | Assemble | [Primary explanation](../Assemble/files/_dockerignore.md) |
| [.env.docker.example](../.env.docker.example) | configuration/deployment | Assemble | [Primary explanation](../Assemble/files/_env_docker_example.md) |
| [.env.example](../.env.example) | configuration/deployment | Assemble | [Primary explanation](../Assemble/files/_env_example.md) |
| [.gitattributes](../.gitattributes) | configuration/deployment | Assemble | [Primary explanation](../Assemble/files/_gitattributes.md) |
| [.gitignore](../.gitignore) | configuration/deployment | Assemble | [Primary explanation](../Assemble/files/_gitignore.md) |
| [Dockerfile](../Dockerfile) | configuration/deployment | Assemble | [Primary explanation](../Assemble/files/Dockerfile.md) |
| [README.md](../README.md) | legacy documentation/specification | Assemble | [Primary explanation](../Assemble/files/README_md.md) |
| [app/actions.php](../app/actions.php) | runtime source | Stage-04-Backend-Database-Security | [Primary explanation](../Stage-04-Backend-Database-Security/files/app__actions_php.md) |
| [app/api.php](../app/api.php) | runtime source | Stage-04-Backend-Database-Security | [Primary explanation](../Stage-04-Backend-Database-Security/files/app__api_php.md) |
| [app/bootstrap.php](../app/bootstrap.php) | runtime source | Stage-04-Backend-Database-Security | [Primary explanation](../Stage-04-Backend-Database-Security/files/app__bootstrap_php.md) |
| [app/community.php](../app/community.php) | runtime source | Stage-02-Student-Experience | [Primary explanation](../Stage-02-Student-Experience/files/app__community_php.md) |
| [app/layout.php](../app/layout.php) | runtime source | Stage-01-Frontend-UX | [Primary explanation](../Stage-01-Frontend-UX/files/app__layout_php.md) |
| [app/models.php](../app/models.php) | runtime source | Stage-04-Backend-Database-Security | [Primary explanation](../Stage-04-Backend-Database-Security/files/app__models_php.md) |
| [app/portal-views.php](../app/portal-views.php) | runtime source | Stage-02-Student-Experience | [Primary explanation](../Stage-02-Student-Experience/files/app__portal-views_php.md) |
| [app/security.php](../app/security.php) | runtime source | Stage-04-Backend-Database-Security | [Primary explanation](../Stage-04-Backend-Database-Security/files/app__security_php.md) |
| [app/ui.php](../app/ui.php) | runtime source | Stage-01-Frontend-UX | [Primary explanation](../Stage-01-Frontend-UX/files/app__ui_php.md) |
| [app/views.php](../app/views.php) | runtime source | Stage-02-Student-Experience | [Primary explanation](../Stage-02-Student-Experience/files/app__views_php.md) |
| [bin/build-openapi.py](../bin/build-openapi.py) | utility | Stage-04-Backend-Database-Security | [Primary explanation](../Stage-04-Backend-Database-Security/files/bin__build-openapi_py.md) |
| [bin/cloud-start.sh](../bin/cloud-start.sh) | utility | Stage-04-Backend-Database-Security | [Primary explanation](../Stage-04-Backend-Database-Security/files/bin__cloud-start_sh.md) |
| [bin/container-init.php](../bin/container-init.php) | utility | Stage-04-Backend-Database-Security | [Primary explanation](../Stage-04-Backend-Database-Security/files/bin__container-init_php.md) |
| [bin/create-admin.php](../bin/create-admin.php) | utility | Stage-04-Backend-Database-Security | [Primary explanation](../Stage-04-Backend-Database-Security/files/bin__create-admin_php.md) |
| [bin/demo-community.php](../bin/demo-community.php) | utility | Stage-04-Backend-Database-Security | [Primary explanation](../Stage-04-Backend-Database-Security/files/bin__demo-community_php.md) |
| [bin/demo-users.php](../bin/demo-users.php) | utility | Stage-04-Backend-Database-Security | [Primary explanation](../Stage-04-Backend-Database-Security/files/bin__demo-users_php.md) |
| [bin/doctor.php](../bin/doctor.php) | utility | Stage-04-Backend-Database-Security | [Primary explanation](../Stage-04-Backend-Database-Security/files/bin__doctor_php.md) |
| [bin/migrate.php](../bin/migrate.php) | utility | Stage-04-Backend-Database-Security | [Primary explanation](../Stage-04-Backend-Database-Security/files/bin__migrate_php.md) |
| [bin/seed.php](../bin/seed.php) | utility | Stage-04-Backend-Database-Security | [Primary explanation](../Stage-04-Backend-Database-Security/files/bin__seed_php.md) |
| [bin/setup.php](../bin/setup.php) | utility | Stage-04-Backend-Database-Security | [Primary explanation](../Stage-04-Backend-Database-Security/files/bin__setup_php.md) |
| [compose.yaml](../compose.yaml) | configuration/deployment | Assemble | [Primary explanation](../Assemble/files/compose_yaml.md) |
| [database/migrations/20261017_club_communities.sql](../database/migrations/20261017_club_communities.sql) | database | Stage-04-Backend-Database-Security | [Primary explanation](../Stage-04-Backend-Database-Security/files/database__migrations__20261017_club_communities_sql.md) |
| [database/schema.sql](../database/schema.sql) | database | Stage-04-Backend-Database-Security | [Primary explanation](../Stage-04-Backend-Database-Security/files/database__schema_sql.md) |
| [docker/apache.conf](../docker/apache.conf) | configuration/deployment | Assemble | [Primary explanation](../Assemble/files/docker__apache_conf.md) |
| [docker/entrypoint.sh](../docker/entrypoint.sh) | configuration/deployment | Assemble | [Primary explanation](../Assemble/files/docker__entrypoint_sh.md) |
| [docker/init-test.sql](../docker/init-test.sql) | configuration/deployment | Assemble | [Primary explanation](../Assemble/files/docker__init-test_sql.md) |
| [docs/api.md](../docs/api.md) | legacy documentation/specification | Assemble | [Primary explanation](../Assemble/files/docs__api_md.md) |
| [docs/architecture.md](../docs/architecture.md) | legacy documentation/specification | Assemble | [Primary explanation](../Assemble/files/docs__architecture_md.md) |
| [docs/local-setup.md](../docs/local-setup.md) | legacy documentation/specification | Assemble | [Primary explanation](../Assemble/files/docs__local-setup_md.md) |
| [docs/project-report.md](../docs/project-report.md) | legacy documentation/specification | Assemble | [Primary explanation](../Assemble/files/docs__project-report_md.md) |
| [docs/screenshots/admin-dark.png](../docs/screenshots/admin-dark.png) | binary asset/evidence | Stage-01-Frontend-UX | [Primary explanation](../Stage-01-Frontend-UX/files/docs__screenshots__admin-dark_png.md) |
| [docs/screenshots/admin-light.png](../docs/screenshots/admin-light.png) | binary asset/evidence | Stage-01-Frontend-UX | [Primary explanation](../Stage-01-Frontend-UX/files/docs__screenshots__admin-light_png.md) |
| [docs/screenshots/community-dark.png](../docs/screenshots/community-dark.png) | binary asset/evidence | Stage-01-Frontend-UX | [Primary explanation](../Stage-01-Frontend-UX/files/docs__screenshots__community-dark_png.md) |
| [docs/screenshots/community-light.png](../docs/screenshots/community-light.png) | binary asset/evidence | Stage-01-Frontend-UX | [Primary explanation](../Stage-01-Frontend-UX/files/docs__screenshots__community-light_png.md) |
| [docs/screenshots/community-mobile.png](../docs/screenshots/community-mobile.png) | binary asset/evidence | Stage-01-Frontend-UX | [Primary explanation](../Stage-01-Frontend-UX/files/docs__screenshots__community-mobile_png.md) |
| [docs/screenshots/student-dark.png](../docs/screenshots/student-dark.png) | binary asset/evidence | Stage-01-Frontend-UX | [Primary explanation](../Stage-01-Frontend-UX/files/docs__screenshots__student-dark_png.md) |
| [docs/screenshots/student-light.png](../docs/screenshots/student-light.png) | binary asset/evidence | Stage-01-Frontend-UX | [Primary explanation](../Stage-01-Frontend-UX/files/docs__screenshots__student-light_png.md) |
| [docs/security.md](../docs/security.md) | legacy documentation/specification | Assemble | [Primary explanation](../Assemble/files/docs__security_md.md) |
| [docs/ui-and-community.md](../docs/ui-and-community.md) | legacy documentation/specification | Assemble | [Primary explanation](../Assemble/files/docs__ui-and-community_md.md) |
| [docs/validation.md](../docs/validation.md) | legacy documentation/specification | Assemble | [Primary explanation](../Assemble/files/docs__validation_md.md) |
| [public/.htaccess](../public/.htaccess) | configuration/deployment | Assemble | [Primary explanation](../Assemble/files/public___htaccess.md) |
| [public/api.php](../public/api.php) | runtime source | Stage-04-Backend-Database-Security | [Primary explanation](../Stage-04-Backend-Database-Security/files/public__api_php.md) |
| [public/assets/app.css](../public/assets/app.css) | runtime source | Stage-01-Frontend-UX | [Primary explanation](../Stage-01-Frontend-UX/files/public__assets__app_css.md) |
| [public/assets/app.js](../public/assets/app.js) | runtime source | Stage-01-Frontend-UX | [Primary explanation](../Stage-01-Frontend-UX/files/public__assets__app_js.md) |
| [public/assets/nsbm.png](../public/assets/nsbm.png) | binary asset/evidence | Stage-01-Frontend-UX | [Primary explanation](../Stage-01-Frontend-UX/files/public__assets__nsbm_png.md) |
| [public/assets/openapi.json](../public/assets/openapi.json) | generated API contract | Stage-04-Backend-Database-Security | [Primary explanation](../Stage-04-Backend-Database-Security/files/public__assets__openapi_json.md) |
| [public/assets/theme-init.js](../public/assets/theme-init.js) | runtime source | Stage-01-Frontend-UX | [Primary explanation](../Stage-01-Frontend-UX/files/public__assets__theme-init_js.md) |
| [public/assets/track.jpg](../public/assets/track.jpg) | binary asset/evidence | Stage-01-Frontend-UX | [Primary explanation](../Stage-01-Frontend-UX/files/public__assets__track_jpg.md) |
| [public/index.php](../public/index.php) | runtime source | Stage-04-Backend-Database-Security | [Primary explanation](../Stage-04-Backend-Database-Security/files/public__index_php.md) |
| [public/router.php](../public/router.php) | runtime source | Stage-04-Backend-Database-Security | [Primary explanation](../Stage-04-Backend-Database-Security/files/public__router_php.md) |
| [tests/api.py](../tests/api.py) | verification | Stage-04-Backend-Database-Security | [Primary explanation](../Stage-04-Backend-Database-Security/files/tests__api_py.md) |
| [tests/browser.py](../tests/browser.py) | verification | Stage-04-Backend-Database-Security | [Primary explanation](../Stage-04-Backend-Database-Security/files/tests__browser_py.md) |
| [tests/community.py](../tests/community.py) | verification | Stage-04-Backend-Database-Security | [Primary explanation](../Stage-04-Backend-Database-Security/files/tests__community_py.md) |
| [tests/fixtures.php](../tests/fixtures.php) | verification | Stage-04-Backend-Database-Security | [Primary explanation](../Stage-04-Backend-Database-Security/files/tests__fixtures_php.md) |
| [tests/integration.py](../tests/integration.py) | verification | Stage-04-Backend-Database-Security | [Primary explanation](../Stage-04-Backend-Database-Security/files/tests__integration_py.md) |
| [tests/security.php](../tests/security.php) | verification | Stage-04-Backend-Database-Security | [Primary explanation](../Stage-04-Backend-Database-Security/files/tests__security_php.md) |
| [Codex_SportsHub_Master_Prompt.md](../Codex_SportsHub_Master_Prompt.md) | legacy documentation/specification | Assemble | [Primary explanation](../Assemble/files/Codex_SportsHub_Master_Prompt_md.md) |

## Coverage and documentation growth

All64 baseline/spec paths have a primary source-specific explanation. New Markdown learning files explain themselves through stage dashboards/inventories; they do not recursively require one guide per guide. The [checked-in validator](checks/check_learning_docs.py) dynamically lists/validates all learning Markdown links and required templates and compares every baseline hash except the intentionally extended root README. Its [final results](16-evidence-and-artifact-register.md) distinguish file coverage from teaching quality and browser/runtime regression.

Canonical perfile links are shared across stages; section guides deepen feature ownership. Long PHP files use dense lines, so coverage is indexed by actual functions/branches, not just apparent line counts. Generated OpenAPI is explained via its generator rather than manual copying; binary assets via provenance/usage/evidence, not imaginary executable logic.

## Risks discovered by inspection

- Compose MariaDB 11.8 is not proof of assignment MySQL 8 compliance; no engine replacement executed.
- Opt-in 2FA, no email verification/reset/SSO, single-node file sessions, generic standings and individual tournament entries remain baseline limits.
- CurrentPDO DSN has no explicitremote DB TLS options; remotehosting may need a reviewed extension.
- Simpleadmin save/delete plus audit are not wrapped in one transaction; existing behavior is documented, not silently refactored.
- API pagination and HTML lists differ; students must describe actual cap/limit behavior rather than promise infinitefullUIrosters.
- Images/brandmarks need humanrights confirmation beforepublichosting.
- No livehost/video/report/teamnames were supplied. These remainTODO.
