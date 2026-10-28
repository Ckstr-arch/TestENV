# `app/layout.php` · source guide

## 1 · File card

| Field | Value |
|---|---|
| Canonical source | [app/layout.php](../../app/layout.php) |
| Primary guide owner | Stage 01; proposed Member A (TBD) |
| Secondary review | Stage 02/03 for affected views; Stage 04 for server/form contracts |
| Language / size | PHP emitting HTML; 35 lines at inspected baseline |
| Role | Document shell, role navigation, theme/profile controls and shared main/footer |
| Inspected baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |

## 2 · Why it exists

Every route needs one consistent document skeleton and navigation. This template wraps whichever view the controller selected. Duplicating it across pages would let themes, role links, skip targets and security controls drift. Without it, content could still be generated but would lack a coherent document/head and shared UI.

## 3 · Prerequisites

Read [homepage semantics](../diagrams/homepage-anatomy.md), PHP alternate template syntax and [Stage 04 controller guide](../../Stage-04-Backend-Database-Security/files/public__index_php.md). `<?=...?>` echoes an expression; `if: ... endif` and `foreach: ... endforeach` are template-friendly PHP control forms. HTML remains literal output between PHP regions.

## 4 · Inputs and dependencies

Controller supplies `$current`, `$page`, `$notice`, `$error`, `$clubView` after role/page checks. Bootstrap supplies `e()`, `csrf_field()`, date/timezone. First line requires [ui.php](app__ui_php.md) once. Assets are root-relative `/assets/...`. View inclusion is fixed `__DIR__.'/views.php'`, not a user-provided filename. Theme and behavior depend on exact attributes/selectors.

## 5 · Complete source-order section coverage

| Section | What to inspect |
|---|---|
| require_once and `$isAdmin` | Loads helper functions; derives role from current server user |
| `$groups` ternary arrays | Admin grouped areas vs student workspace; page key, label, icon pair |
| doctype/html/head | Language, default theme, charset, viewport, description, color-scheme, title, script/style order |
| body/skip link | portal/public-site class; keyboard route to main |
| authenticated aside | Brand, role label, group loop, current link, footer and scrim |
| app-frame/header role branches | Portal identity/search vs public brand/nav; shared menu toggle |
| header actions | Theme first, then profile details or public signin/join |
| main alerts/view | status/alert regions and fixed views include |
| footer | Brand, mission and actual year/SriLanka time note |

No meaningful branch in this 35-line compact template is omitted. Lines are long because multiple elements are written inline; expand mentally by nesting rather than assuming line count means complexity is low.

### Role-specific groups

Admin groups are Workspace, Community, Competitions, Resources, Insights. They link overview, clubs, approvals, moderation, events, tournaments, results, resources, reports, audit. Student groups link dashboard, my_clubs, clubs, schedule, tournaments, facilities, activity, results. `$page==='club'` marks My clubs active. The role is not chosen from request fields; controller/bootstrapped session resolves it.

The sidebar nav loops groups then items, destructuring each `[label,glyph]`. Active links receive class active and aria-current=page. Navigation labels use e(). `icon()` returns trusted static SVG. Sidebar bottom explores public home while retaining the authenticated shell if user is signed in.

### Head and progressive enhancement

The head includes standard UTF-8/viewport metadata and a dynamic escaped title based on allowlisted page. Theme-init loads synchronously before CSS; main JS is deferred. The default root dark attribute works if storage is missing. Script/style URLs are same-origin and compatible with CSP. The body class selects portal offset or public styling.

### Portal/public header

Portal menu controls navigation by ID. Identity text includes the actual club name when viewing a valid club. Header search is a native GET form with page=clubs and q; it does not search all entities. The label is visually hidden but associated through for/global-search ID.

Public header has brand and six navigation links. Public menu reuses navigation ID, so only one such nav exists per branch. Shared header-actions places theme toggle immediately before profile for authenticated users; public visitors get signin/join links instead. Theme button has accessible next-action label, pressed state and hidden descriptive text; JS keeps these synchronized.

### Profile, alerts and content

Native details/summary provides an operable profile menu. Initials/name/email are escaped. Role labels are fixed strings. Account security and participation are links; signout is a POST form with CSRF, not a GET link. The main has id=main and tabindex=-1 so skip navigation can target it. Notice uses role=status; error uses role=alert. Views are required after the wrapper opens main, then footer closes the document.

## 6 · Actual annotated excerpts

```php
$isAdmin=$current && $current['role']==='admin';
```

Logical AND prevents indexing a missing user. Strict equality compares role exactly. This determines presentation only; controller/API still enforce authorization separately.

```html
<a class="skip-link" href="#main">Skip to content</a>
```

The fragment targets main's unique ID. CSS hides the link above the page until focus, allowing keyboard users to bypass repeated navigation.

```php
<form method="post"><?=csrf_field()?><input type="hidden" name="action" value="logout"><button class="text-button">Sign out</button></form>
```

This exact inner form requests a mutation safely through POST and includes the per-session token. The hidden action selects dispatch; it is not proof of permission. app.js recognizes logout and uses REST; with JS unavailable, native handling remains.

## 7 · Input → transformation → output

Input: authenticated user, page, validated club context and flash/error messages. Transform: choose role arrays/branches, escape dynamic text, render fixed include. Output: full HTML document with landmarks, asset references, forms and content.

## 8 · Connections

[public/index.php](../../public/index.php) resolves inputs → layout → [views.php](../../app/views.php) → optional [portal views](../../app/portal-views.php). [UI helpers](app__ui_php.md) emit icons/initials/forms; [CSS](public__assets__app_css.md) and [JS](public__assets__app_js.md) consume the shell contract. [Browser suite](../../tests/browser.py) checks roles/themes/menu flows.

## 9 · Trade-offs

One template keeps role shells consistent and avoids frontend build tooling. Inline PHP/HTML is compact but difficult to scan; diagrams and source-order maps aid learning without a risky rewrite. A native details profile works with basic browser behavior, while outside-click/Escape enhancement is optional JS.

## 10 · Security, correctness and accessibility

Keep escaping around names, emails, club context and messages. Never use query-controlled include paths. Hidden controls are not authorization. Preserve CSRF signout, aria-current, control names, skip link and label associations. Decorative logo alt is empty because adjacent text names the same link. Do not photograph a real profile email in public evidence.

## 11 · Common mistakes

| Symptom | Diagnosis / safe fix |
|---|---|
| Menu button toggles nothing | ID/aria-controls/JS selector mismatch |
| Light theme flashes dark | Theme-init reordered after CSS/deferred |
| Student sees admin links | Wrong `$current`/role branching; inspect session logic rather than just hiding CSS |
| Search appears global | Label/copy changed without backend support; preserve honest club scope |
| Keyboard skips content poorly | Missing main ID/tabindex or skip focus styling |
| Signout becomes link | GET mutation loses intended POST/CSRF path |

## 12 · Run

```bash
php -l app/layout.php
```

Then load configured local public/student/admin routes using disposable accounts. Syntax alone cannot validate roles, focus or theme placement; use [manual checks](../06-testing-and-debugging.md).

## 13 · Debugging lesson

Inspect emitted DOM to confirm branch before CSS. Log only page key and boolean role result if necessary locally, never user records/session/cookies. Use view source for response HTML and Elements for JS-updated attributes.

## 14 · Guided experiment

In DevTools move focus to skip link and activate it. Compare activeElement/main outline. Then inspect header-actions child ordering in both themes. Reload restores any temporary DOM change. Do not change role logic merely to view admin controls.

## 15 · Challenge ladder

Beginner: draw landmark tree. Intermediate: trace a search submission and logout fallback. Advanced: propose a reviewed accessible profile/drawer improvement with both role/theme viewport evidence. Hint: native details semantics and modal focus are different interactions.

## 16 · Knowledge check

1. Why escape a name already stored in the database?
2. Why is signout a form rather than a GET link?
3. What breaks if navigation's ID changes in only this file?

<details><summary>Answers</summary>

Stored data can contain untrusted text; escaping prevents markup interpretation. Signout mutates session state and uses CSRF-protected POST. JS/aria-controls/fragment relationships can break when IDs diverge.

</details>

## 17 · Contribution boundaries

Stage 01 owns shell presentation. Stage 02/03 review changed page destinations/role journeys; Stage 04 reviews authorization/session/form contracts. Keep canonical controller gating and native functionality.

## 18 · Status and limitations

VERIFIED BY INSPECTION: all role/head/sidebar/header/profile/main/footer regions. Fresh browser/assistive testing is reported centrally or remains NOT RUN. No template source changes were made by these docs.

## 19 · Glossary

Landmark: semantic page region. Progressive enhancement: optional JS over a usable baseline. Escaping: encoding text for an output context. CSRF: forged authenticated cross-site request. Deferred script: script executed after document parsing.

## 20 · Personal explanation

“I can explain how the same layout renders public/student/admin navigation, why theme initialization comes first, and how protected logout and keyboard landmarks work.” Personalize after real inspection/testing; do not claim the baseline template as new personal code.
