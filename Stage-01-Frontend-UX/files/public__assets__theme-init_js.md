# `public/assets/theme-init.js` · source guide

## 1 · File card

| Field | Value |
|---|---|
| Canonical source | [public/assets/theme-init.js](../../public/assets/theme-init.js) |
| Primary guide owner | Stage 01; proposed Member A (TBD) |
| Secondary review | Stage 02/03 for affected views; Stage 04 for server/form contracts |
| Language / size | JavaScript; 8 lines at inspected baseline |
| Role | Early saved-theme initialization |
| Inspected baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |

## 2 · Why it exists

A deferred theme update can briefly paint the dark default before switching to a saved light preference. This tiny synchronous script runs before CSS and sets the correct root attribute early. Without it, persisted theme can flash on each navigation.

## 3 · Prerequisites

Understand DOM root, attributes and localStorage. See [main JS guide](public__assets__app_js.md) for events and [CSS tokens](public__assets__app_css.md) for consumers.

## 4 · Dependencies

[layout.php](app__layout_php.md) emits `<html data-theme="dark">` and loads this script before the stylesheet. Storage key is exactly `sportshub-theme`, shared with main JS. No database or environment key is read.

## 5 · Complete source-order tour

The comment states paint timing. An immediately invoked arrow function scopes variables. `let theme="dark"` provides a default. A try block reads localStorage; only the exact string light is accepted. Catch tolerates unavailable storage. Last line assigns `document.documentElement.dataset.theme`. The wrapper runs immediately. These eight lines are the complete meaningful behavior.

## 6 · Real annotated excerpt

```javascript
let theme = "dark";
try {
  if (localStorage.getItem("sportshub-theme") === "light") theme = "light";
} catch {}
document.documentElement.dataset.theme = theme;
```

`let` permits reassignment unlike const. Strict equality accepts only the expected string. An empty catch deliberately falls back rather than preventing rendering. `documentElement` is `<html>`; dataset.theme maps to data-theme. No user preference is sent to PHP.

## 7 · Input → transformation → output

Input: stored string or storage error. Transform: allowlisted light check/default dark. Output: root attribute consumed by CSS before paint.

## 8 · Connections

[CSS root tokens](public__assets__app_css.md), [main JS updateThemeControl](public__assets__app_js.md#theme-and-print), [layout ordering](app__layout_php.md).

## 9 · Trade-off

A tiny synchronous request blocks parsing briefly but avoids the wrong initial theme. Keeping it local satisfies the app's script-src self policy and avoids inline code. No new framework/dependency is warranted.

## 10 · Security/accessibility

Only theme preference is stored; no credentials. Invalid storage values become dark. Current theme is not automatically chosen from prefers-color-scheme; dark is the product default. Human preference/contrast still need testing.

## 11 · Common mistakes

Loading after CSS causes flash; using a different storage key loses persistence; assuming storage always works can throw; accepting arbitrary strings can desynchronize CSS/control. Check Network order, root attributes and exact key name without dumping all storage.

## 12 · Verify

```bash
node --check public/assets/theme-init.js
```

Then use the actual toggle, choose light, reload and observe root data-theme. Optional Node syntax checking cannot prove paint timing.

## 13 · Debugging

Set a breakpoint after initialization and inspect only theme/root attribute. Storage may be blocked in restricted browser contexts; verify fallback without logging unrelated storage.

## 14 · Safe experiment

In DevTools change only the theme preference through the UI, reload, observe. Restore your preferred theme via the toggle. Do not clear all site data because that also affects session state.

## 15 · Challenge ladder

Beginner: predict value when storage is absent. Intermediate: explain why only light is accepted. Advanced: propose a reviewed system-preference enhancement with clear user override semantics and no flash. These are exercises, not implemented changes.

## 16 · Knowledge check

1. Why must this run before CSS?
2. What happens when getItem throws?
3. Does data-theme authenticate a user?

<details><summary>Answers</summary>

It avoids painting the wrong palette. Catch retains dark. No: it is client-controlled presentation only.

</details>

## 17 · Contribution boundaries

Coordinate storage key/accepted values with main JS and CSS; test both themes, reload and blocked storage. Do not add auth material or external scripts.

## 18 · Status

VERIFIED BY INSPECTION, complete 8-line tour. Browser paint timing/storage scenarios NOT RUN by this guide. No source change.

## 19 · Glossary

IIFE: immediately invoked function expression. Flash: brief unwanted initial presentation. Dataset: DOM interface to data attributes. Fallback: behavior used when preferred input is unavailable.

## 20 · Personalize after work

“I can show why this small early script chooses the saved palette before the stylesheet and how it remains safe if storage is unavailable.” Explain your actual test rather than claiming authorship.
