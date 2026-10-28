# 04 · Trace what the browser actually does

## Journey A · Homepage to a rendered card

1. `GET /?page=home` reaches `public/index.php`; it accepts the allowlisted page and resolves current user/flash.
2. `layout.php` creates the document head/body and includes `views.php` inside `main#main`.
3. The home branch reads four database counts and the next future match. It also selects the first three clubs by ID. It does not fetch these cards in JavaScript.
4. A PHP `foreach` emits an `<article class="card club-card">` for each row. `e()` escapes names/descriptions/location before they enter HTML.
5. `.grid.three` allocates three equal tracks at desktop; medium/mobile overrides reduce columns. `.card` provides surface, padding, border and `min-width:0`.
6. The browser builds a DOM without any PHP syntax. A card link navigates to the clubs route; the homepage has no pretend club-specific API handler.

Continue with the [DOM/source map](diagrams/homepage-anatomy.md). The counts are SQL-derived. The visible `+` is literal marketing punctuation, not animated/fabricated data.

## Journey B · Computed styles, not guesses

Open Styles and Computed, select each node and record the winning declaration plus source line. Temporarily disable only one declaration, observe, then reload.

| Element | Desktop cause | What to inspect at 390 px |
|---|---|---|
| `.hero` | Grid `1.1fr 1fr`, 35 px gap | At 800 px becomes one column; below 500 headline override applies |
| `.hero-photo` | Relative container, clipped rounded box; absolute cover image | Minimum height becomes 280px; cover crops rather than stretches |
| `.card` | Border-box, 23 px padding, min-width 0 | 18 px padding at 500; long text can still need wrapping |
| `.sidebar` | Fixed left, 238 px variable width, z-index 60 | At 800 hidden translated drawer, width 250, z-index 100; `.is-open` reveals |
| `.theme-toggle` | Flex row, pill border | Child icon cells 23px at 500; evaluate whole button hit area |
| `input` inside `.header-search` | Generic width 100/padding then specific border 0 transparent override | Search hidden at 1050; don't blame missing DOM |

The `:root` light selector changes variables rather than applying a filter to the whole page. A hardcoded text/background color may therefore need a purposeful theme exception.

## Journey C · Apply to a club, DOM → API → state

Use only a disposable local account. `action_button('join', $id, ...)` emits a native POST form with hidden `action=join`, `id` and `csrf`. Its button is a submit control because omitted `type` defaults to submit in a form.

`app.js` reads `Object.fromEntries(new FormData(form))`; `endpoint()` maps this action to `POST /api/v1/clubs/{id}/memberships`. It calls `preventDefault()` only for a recognized route. IDs used in the URL and action/CSRF metadata are removed from the JSON object. The CSRF value moves into the request header; the cookie travels via `credentials:'same-origin'`.

The server entry/API/action services authenticate, validate and insert a pending membership; details belong to [Stage 02](../Stage-02-Student-Experience/04-guided-walkthroughs.md) and [Stage 04](../Stage-04-Backend-Database-Security/04-guided-walkthroughs.md). A control being visible never grants authorization.

On success JS refreshes CSRF hidden fields, follows only a `/?page=` internal redirect or reloads. On non-2xx it writes safe text into a role=alert region; a 401 adds a sign-in link. On a lost/unparseable response it warns to check activity before retrying because the server could already have committed. It never automatically retries a mutation.

Disable JavaScript and repeat with a different disposable case: native POST goes through `handle_action()` and a 303 redirect. Do not submit a duplicate blindly; predict duplicate/state errors first.

## Journey D · Mobile drawer and focus

At≤800 px sign in locally. Menu click sets aria-expanded=true and both open classes. For a sidebar only it adds dialog/aria-modal, blocks body scroll, reveals scrim and focuses the first link. Tab wraps first↔last; Escape/scrim closes and returns focus to the toggle. Resizing past 800 closes modal state. Public nav uses the simpler `.open` presentation and does not receive sidebar dialog behavior.

Observe limitations: focus wrapping uses `a,button`, not a generalized visibility-aware focusable selector, and modal background is not made inert. These are review topics, not reasons to claim a comprehensive accessibility audit passed.
