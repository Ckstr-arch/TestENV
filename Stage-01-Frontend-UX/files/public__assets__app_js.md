# `public/assets/app.js` · source guide

## 1 · File card

| Field | Value |
|---|---|
| Canonical source | [public/assets/app.js](../../public/assets/app.js) |
| Primary guide owner | Stage 01; proposed Member A (TBD) |
| Secondary review | Stage 02/03 for affected views; Stage 04 for server/form contracts |
| Language / size | JavaScript; 270 lines at inspected baseline |
| Role | DOM interactions and progressive form-to-REST enhancement |
| Inspected baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |

## 2 · Why it exists

This script enhances native PHP-rendered pages. It controls drawer/profile/theme interactions and translates recognizable POST forms into JSON REST writes. Without it, native server forms remain available, while dynamic theme/menu/recovery-dialog behavior is unavailable or reduced. Do not replace native form semantics with mouse-only div controls.

## 3 · Prerequisites

A **DOM node** represents an element. A **listener** is a function called when an event happens. A **Promise** represents a future result; `await` suspends an async function until it resolves/rejects. JSON is a text interchange format, not a JavaScript executable expression. Read [the architecture](../01-architecture.md) and [CSS drawer behavior](public__assets__app_css.md).

## 4 · Dependencies

Loaded with `defer` by [layout.php](app__layout_php.md). Selectors depend on PHP-generated classes/IDs; forms depend on hidden action/CSRF/domain IDs. It uses native browser APIs, not jQuery or a package. API contract is defined by [app/api.php](../../app/api.php) and generated [OpenAPI](../../public/assets/openapi.json). UI helpers emit compatible forms. PHP uses a session cookie; JS sends same-origin credentials and never moves authentication into localStorage.

## 5 · Full source-order coverage index and walkthrough

Every major function and top-level listener is covered below; no uncovered function/listener region remains at baseline.

| Region | Guide subsection |
|---|---|
| strict mode, menu/navigation/scrim selectors | [DOM and variables](#dom-and-variables) |
| closeNavigation | [Drawer lifecycle](#drawer-lifecycle) |
| menu click, scrim click, document keydown | [Drawer lifecycle](#drawer-lifecycle) |
| matchMedia change, profile outside click | [Drawer lifecycle](#drawer-lifecycle) |
| themeToggle, updateThemeControl, click, storage | [Theme and print](#theme-and-print) |
| print-report click | [Theme and print](#theme-and-print) |
| endpoint action map | [Action routing](#action-routing) |
| showRecoveryCodes | [Recovery dialog](#recovery-dialog) |
| form enumeration/submit/normalization | [Submission pipeline](#submission-pipeline) |
| fetch, response, CSRF, error, redirect, catch/finally | [Response pipeline](#response-pipeline) |

### DOM and variables

`"use strict"` prevents certain silent language errors. `const` means a binding cannot be reassigned; the referenced object/DOM node can still change. `querySelector('.menu-toggle')` returns the first matching node or null. `getElementById('navigation')` uses the ID directly. `querySelectorAll` returns a collection; `.forEach` installs listeners on each form. Optional chaining `menu?.focus()` skips the call if menu is null. This matters because one script supports different page/role shapes.

Arrow functions capture callbacks compactly. Objects map action names to arrays `[method,path]`; template literals interpolate IDs into strings. Array destructuring `const [method,path] = route` extracts positions. Spread syntax `[...nodeList]` converts a DOM collection to an array for `.at(-1)` or iteration. These are language features, not security controls.

### Drawer lifecycle

`closeNavigation()` remembers whether the menu was open, sets aria-expanded=false, removes is-open/open and modal role/ARIA, removes body nav-open, hides scrim and returns focus only if previously open. The menu click toggles state; if the navigation is a sidebar, it also sets role=dialog/aria-modal, locks body scrolling, reveals scrim and focuses the first link. Public navigation receives open classes but not sidebar-modal handling.

The scrim calls the same close function. Document keydown handles Escape for both drawer and profile. If Tab occurs while navigation is modal, it gathers `a,button` and wraps focus between first/last for Shift+Tab/Tab. `event.preventDefault()` prevents the browser moving focus out at those boundaries. The current selector is simple: it does not generally filter every disabled/hidden descendant or make the background inert. This deserves real accessibility testing.

`matchMedia('(min-width: 801px)')` closes mobile state when width returns to desktop. This must agree with CSS max 800. Document click closes the profile if the target is outside `.profile-menu`; native details behavior handles its summary. `classList` changes CSS state; ARIA attributes separately describe interaction state. They must remain synchronized.

### Theme and print

`updateThemeControl()` reads root `dataset.theme`, sets the button's next-action label and string aria-pressed. The click flips root dark/light, tries to persist `sportshub-theme`, then updates the accessible control. A storage failure is caught so the current page can still change theme. A `storage` event synchronizes another tab's preference; it is not the same-tab click event. The print-report optional listener invokes `window.print()` and CSS print rules handle output.

Only presentation preference belongs in localStorage. Passwords, session tokens, authenticator keys and recovery codes must not be stored there. Script-readable storage is accessible to page scripts and vulnerable if an XSS defect occurs; it is not an authentication vault.

### Action routing

`endpoint(action,fields)` returns a method/path pair or undefined. It does not validate eligibility. Server checks remain authoritative. The current map covers all these actions:

| Action(s) | REST method/path contract |
|---|---|
| register, login, verify, logout | POST `/auth/register`, `/auth/login`, `/auth/2fa`, `/auth/logout` |
| setup_2fa, enable_2fa, disable_2fa | POST `/me/2fa/setup`, POST `/me/2fa`, DELETE `/me/2fa` |
| book | POST `/bookings` |
| join | POST `/clubs/{id}/memberships` |
| register_tournament | POST `/tournaments/{id}/registrations` |
| attend | POST `/events/{id}/attendance` |
| cancel | DELETE `/me/{entity}/{id}` |
| review | PATCH `/admin/{entity}/{id}` |
| attendance | PATCH `/admin/attendance/{id}` |
| save | POST `/admin/{entity}` when no numeric id; PUT `/admin/{entity}/{id}` otherwise |
| delete | DELETE `/admin/{entity}/{id}` |
| community_post, community_edit, community_delete | POST club posts; PATCH/DELETE specific post |
| community_comment, community_comment_delete | POST comments; DELETE specific comment |
| community_react, community_unreact | PUT/DELETE post reaction |
| community_report | POST post reports |
| moderate_post | PATCH `/admin/community/posts/{post_id}` |

All paths are prefixed with `/api/v1` at fetch time. The client table does not prove these routes accept arbitrary entity names. The server has its own fixed allowlists and permission checks. Adding a new action without mapping it leaves the native form path intact by design.

### Recovery dialog

`showRecoveryCodes(codes)` creates a native modal dialog and returns a Promise. It builds h2, p, div, code and button nodes with `createElement` and `textContent`, then appends them. It prevents cancel/Escape so users must explicitly acknowledge saving the once-displayed codes. The button closes/removes the dialog and resolves the promise once; the caller awaits it before redirecting. Codes stay in memory/DOM only and are never logged or stored. This function displays server-provided recovery material; it does not generate or verify codes.

### Submission pipeline

For each `form[method="post"]`, submit callback reads `Object.fromEntries(new FormData(form))`. FormData includes successful named fields; if future forms repeat names, this object conversion would collapse repeated keys. Current API expects scalar fields, not arbitrary nested structures.

The callback finds the route first. If none exists, it returns without preventing native submit. For a mapped action, it prevents native submit, asks optional data-confirm confirmation, and rejects a duplicate while data-busy is present. It copies method/path, keeps CSRF aside, then removes routing-only action, csrf, id, entity. Community route IDs are also removed from body. Admin attendance sets status=attended. Numeric fields capacity/club_id/tournament_id/resource_id/home_score/away_score are converted from strings; blank becomes null. Other scalar strings stay strings. Client conversion is convenience; PHP must still validate types/ranges/references.

Submit buttons are disabled and busy set while request runs. An `.api-alert` is reused or created at form start with role=alert, then hidden before the request. This avoids multiple stale error containers. Disabling the UI is not sufficient protection against duplicate requests from another tab or client; database constraints/transactions still matter.

### Response pipeline

`fetch` sends JSON Content-Type/Accept, the session cookie via same-origin credentials, and X-CSRF-Token. `await response.json()` parses the body. HTTP 422/403/401 do not automatically reject fetch; `response.ok` is checked explicitly. JSON parse/network failures go to catch.

If the response includes a fresh meta.csrf_token, all hidden CSRF inputs are refreshed, important after session/token rotation. Non-success sets textContent from the error message (or fallback), reveals alert and appends a sign-in anchor only for 401. It does not insert server error text as markup.

Success awaits recovery-code acknowledgment if present, then follows only an internal redirect starting `/?page=`; otherwise reloads. Catch warns that current state should be checked before retry, because a lost response can follow a committed write. Finally reenables buttons and deletes busy even after early return/error. There is no automatic mutation replay.

## 6 · Real annotated excerpts

```javascript
const fields = Object.fromEntries(new FormData(form));
const route = endpoint(fields.action, fields);
if (!route) return; // Existing native form behavior remains for any unmapped extension.
event.preventDefault();
```

Line 1 turns named values into an object; line 2 chooses a client route; line 3 deliberately preserves unknown native actions; line 4 suppresses native submission only after a mapping exists. Calling preventDefault before the route check would accidentally break fallback extensions.

```javascript
headers: {
  "Content-Type": "application/json",
  Accept: "application/json",
  "X-CSRF-Token": token,
},
body: JSON.stringify(fields),
```

Content-Type describes the body; Accept requests JSON; CSRF connects the mutation to the session. JSON.stringify serializes data, not arbitrary executable code. A valid-looking header value is not proof of permission: PHP compares the real token and checks Origin/roles/state.

## 7 · Input → transformation → output

Input: click, key, storage or submit event and DOM field values. Transformation: state updates or route/body normalization, asynchronous same-origin request, JSON parsing. Output: changed theme/navigation/focus, error alert, recovery dialog or internal navigation/reload. The server owns database changes.

## 8 · Connections

[Layout](app__layout_php.md) supplies nodes; [UI helpers](app__ui_php.md) supply community forms; [CSS](public__assets__app_css.md) renders states. [Stage 02 student walkthrough](../../Stage-02-Student-Experience/04-guided-walkthroughs.md) explains membership/community outcomes. [Stage 04 actions](../../Stage-04-Backend-Database-Security/files/app__actions_php.md) explains business dispatch; [browser tests](../../tests/browser.py) exercise actual DOM workflows.

## 9 · Trade-offs

One script supports progressive enhancement without a frontend framework. Full reloads after writes simplify authoritative rendering but cost navigation and lose some in-page state. A central action map is inspectable but must stay aligned with backend routes/forms. No SPA rewrite is necessary to improve usability.

## 10 · Security, correctness and accessibility

Server validation is mandatory because users can bypass JavaScript. Same-origin cookies do not replace CSRF; cookies are attached by the browser, while an unpredictable token proves authorized page context. Output text uses textContent. Recovery data remains ephemeral. Focus trapping, labels, live errors and role state require human tests, including reduced motion and keyboard-only access.

## 11 · Common mistakes

| Symptom | Likely cause / safe investigation |
|---|---|
| Form suddenly submits natively | Missing action map or case-sensitive selector mismatch |
| API 400/415 | Wrong object/Content-Type; compare contract without logging secrets |
| API 403 after hostname change | APP_URL Origin mismatch or stale CSRF/session |
| Spinner/buttons stay disabled | Promise/finally flow regression |
| Drawer traps desktop focus | CSS/JS breakpoints disagree or close state not cleared |
| Duplicate write after retry | Response lost after commit; inspect state instead |
| Theme label contradicts theme | updateThemeControl omitted after root change |

## 12 · Run and verify

```bash
node --check public/assets/app.js
```

This checks syntax only; optional Node does not become a runtime requirement. Use [manual/browser checks](../06-testing-and-debugging.md) for interactions. Browser tests reset only a verified disposable test database; inspect safety first.

## 13 · Debugging lesson

Set a breakpoint at endpoint mapping and inspect action/method/path only. At fetch response inspect status, not credential-bearing fields or headers. Do not console.log FormData, full response or recovery codes. Use synthetic accounts and redact screenshots.

## 14 · Guided experiment

Use DevTools to remove the theme button temporarily, then reload with a temporary local-only DOM inspection to reason about optional chaining. For a safer pure exercise, use [layout clinic](../exercises/layout-clinic.html). Predict missing-node behavior. Restore by reloading; don't delete production nodes in source.

## 15 · Challenge ladder

Beginner: trace theme click and resulting root/ARIA values. Intermediate: trace join normalization and native fallback; predict a 422 alert. Advanced: propose a visibility-aware focusable selector or improved network state UX with review and regression cases. Expected outcome is observed behavior plus a justified diff; do not claim the proposal is implemented.

## 16 · Knowledge check

1. Why doesn't HTTP 422 automatically throw in fetch?
2. Why capture route IDs before deleting body fields?
3. Why is recovery display awaited before redirect?

<details><summary>Answers</summary>

Fetch resolves when an HTTP response arrives; status must be checked. IDs build the URL but are not necessarily allowed JSON body fields. Redirecting before acknowledgment would lose once-displayed recovery material. None of these client choices replaces server authorization.

</details>

## 17 · Contribution boundaries

Any action map/payload/form change needs Stage 02/03 domain and Stage 04 contract review. Drawer/theme/CSS changes need Stage 01 browser evidence and affected-role review. Preserve native fallback and never remove CSRF/error checks to make a demo pass.

## 18 · Status and limitations

VERIFIED BY INSPECTION: all current functions, listeners and action entries accounted for. NOT RUN here: manual user testing and assistive-technology audit. Parent validation records executed checks centrally. No JS runtime edits made by this guide.

## 19 · Glossary

DOM: document object tree. Event: browser notification. Callback: function passed for later use. Promise: future result. Async/await: syntax for asynchronous control. CSRF: cross-site request forgery. JSON: structured text format. Idempotent: repeating an operation has the same intended state effect.

## 20 · One-minute explanation to personalize

“I traced a real form from named HTML fields to a same-origin JSON request, and explained what the server still must check. I also tested the drawer/theme interaction and can distinguish a network failure from a rejected response.” Replace with your own verified work and disclose assistance; the baseline is not your new authorship.
