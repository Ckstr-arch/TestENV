# 02 · Source inventory

Baseline `f81eab54ebe7179e1763f364d6b8d4f564c36541`. All paths below remain canonical outside this teaching folder. File guides use the same slug as the complete [assembly inventory](../Assemble/01-repository-assembly-map.md).

| Source | Primary explanation | Role |
|---|---|---|
| [app/layout.php](../app/layout.php) | [Guide](files/app__layout_php.md) | runtime source |
| [app/ui.php](../app/ui.php) | [Guide](files/app__ui_php.md) | runtime source |
| [docs/screenshots/admin-dark.png](../docs/screenshots/admin-dark.png) | [Guide](files/docs__screenshots__admin-dark_png.md) | binary asset/evidence |
| [docs/screenshots/admin-light.png](../docs/screenshots/admin-light.png) | [Guide](files/docs__screenshots__admin-light_png.md) | binary asset/evidence |
| [docs/screenshots/community-dark.png](../docs/screenshots/community-dark.png) | [Guide](files/docs__screenshots__community-dark_png.md) | binary asset/evidence |
| [docs/screenshots/community-light.png](../docs/screenshots/community-light.png) | [Guide](files/docs__screenshots__community-light_png.md) | binary asset/evidence |
| [docs/screenshots/community-mobile.png](../docs/screenshots/community-mobile.png) | [Guide](files/docs__screenshots__community-mobile_png.md) | binary asset/evidence |
| [docs/screenshots/student-dark.png](../docs/screenshots/student-dark.png) | [Guide](files/docs__screenshots__student-dark_png.md) | binary asset/evidence |
| [docs/screenshots/student-light.png](../docs/screenshots/student-light.png) | [Guide](files/docs__screenshots__student-light_png.md) | binary asset/evidence |
| [public/assets/app.css](../public/assets/app.css) | [Guide](files/public__assets__app_css.md) | runtime source |
| [public/assets/app.js](../public/assets/app.js) | [Guide](files/public__assets__app_js.md) | runtime source |
| [public/assets/nsbm.png](../public/assets/nsbm.png) | [Guide](files/public__assets__nsbm_png.md) | binary asset/evidence |
| [public/assets/theme-init.js](../public/assets/theme-init.js) | [Guide](files/public__assets__theme-init_js.md) | runtime source |
| [public/assets/track.jpg](../public/assets/track.jpg) | [Guide](files/public__assets__track_jpg.md) | binary asset/evidence |

## Shared source: explain together, do not duplicate ownership

| Shared source | Canonical primary guide | Stage 01 responsibility |
|---|---|---|
| `app/views.php` | [Stage 02 guide](../Stage-02-Student-Experience/files/app__views_php.md) | Semantics/classes in home, auth, cards and forms |
| `app/portal-views.php` | [Stage 02 guide](../Stage-02-Student-Experience/files/app__portal-views_php.md) | Dashboard/community markup and admin visual review |
| `app/community.php` | [Stage 02 guide](../Stage-02-Student-Experience/files/app__community_php.md) | Accessible controls and safe text display |
| `app/actions.php` | [Stage 04 guide](../Stage-04-Backend-Database-Security/files/app__actions_php.md) | Keep form/action field contract intact |
| `public/index.php` | [Stage 04 guide](../Stage-04-Backend-Database-Security/files/public__index_php.md) | Shell inputs and protected-page behavior |
| `public/router.php` | [Stage 04 guide](../Stage-04-Backend-Database-Security/files/public__router_php.md) | Correct asset/API URLs during local work |
| `tests/browser.py` | [Stage 04 guide](../Stage-04-Backend-Database-Security/files/tests__browser_py.md) | Human-readable frontend regression evidence |

Coverage: all 14 primary frontend/asset paths have guides. CSS has a complete source-order rule index; JS has a complete function/listener index. Binary assets have format/provenance guides instead of fictional code walkthroughs.
