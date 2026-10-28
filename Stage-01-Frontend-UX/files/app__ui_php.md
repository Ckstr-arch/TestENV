# `app/ui.php` · source guide

## 1 · File card

| Field | Value |
|---|---|
| Canonical source | [app/ui.php](../../app/ui.php) |
| Primary guide owner | Stage 01; proposed Member A (TBD) |
| Secondary review | Stage 02/03 for affected views; Stage 04 for server/form contracts |
| Language / size | PHP / static SVG strings; 31 lines at inspected baseline |
| Role | Small shared rendering helpers |
| Inspected baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |

## 2 · Why it exists

Icons, initials, club links, community form openings and metric cards repeat across portal views. Central helpers keep their structure consistent. Without them, callers would fail with undefined functions or duplicate subtly different HTML. This file does not replace business authorization.

## 3 · Prerequisites

Understand typed function arguments/returns, arrays, string concatenation and escaping. PHP `void` means the helper prints output rather than returning a usable value. See [backend PHP](../../Stage-04-Backend-Database-Security/README.md) and [homepage semantics](../diagrams/homepage-anatomy.md).

## 4 · Dependencies

Loaded once by [layout.php](app__layout_php.md); callers are [views](../../app/views.php) and [portal views](../../app/portal-views.php). `e()` and `csrf_field()` come from bootstrap. Initials require mbstring. Classes are styled by app.css and community forms enhanced by app.js. There are no direct SQL queries here.

## 5 · Every function in source order

### icon(string $name): string

An allowlisted paths array maps home, clubs, calendar, trophy, booking, chart, shield, arrow, sun, moon, menu, search, chat, heart, box, chevron, clock, pin to static SVG fragments. Return wraps a selected fragment in one SVG with consistent viewBox, stroke, currentColor, line properties and aria-hidden=true. Unknown names fall back to home. Because fragments are internal static strings, they are deliberately returned as markup, not escaped text. Never let a user-provided SVG string enter this array path.

`currentColor` inherits the surrounding color, so tokens style icons. `aria-hidden` avoids duplicate announcements when adjacent text/control label already names the action. A standalone icon button still needs its own accessible name in the caller.

### initials(string $name): string

Trim name, split on whitespace using Unicode regex, take the first character of the first two parts with mb_substr, then uppercase with mb_strtoupper. Default missing parts to empty. Unicode functions handle multibyte characters more appropriately than byte-based substr. It returns text; callers escape before emitting it. It does not create a unique user identity or avatar URL.

### club_url(int $id, string $tab='community'): string

Builds internal `/?page=club&club_id=...&tab=...`. ID is typed integer; tab is rawurlencode'd for a query value. Caller still e() escapes the returned URL when writing an HTML attribute. Encoding for a URL and escaping for HTML are distinct contexts. Default community does not bypass membership gating.

### community_form(string $action, int $clubId, int $postId=0, array $extra=[]): void

Prints an opening POST form with community-form class, CSRF and hidden action/club_id/post_id. Extra fields are escaped in both names and values. It intentionally does **not** print a closing form: callers insert body/button controls then close it. Call sites must avoid nested forms and provide appropriate labels. Hidden IDs come from the form but the server rereads ownership and club access.

### metric(string $label, int $value, string $glyph, string $url): void

Prints a linked metric card with icon, value, label, arrow. Value is integer; label/URL are escaped; icon glyph uses static allowlist. Values come from caller SQL aggregates, not a frontend random animation. It does not itself calculate or validate business statistics.

## 6 · Actual annotated excerpt

```php
function club_url(int $id,string $tab='community'): string { return '/?page=club&club_id='.$id.'&tab='.rawurlencode($tab); }
```

`int` restricts the parameter type at the PHP boundary; string concatenation uses dot. Default argument chooses the usual tab. rawurlencode prevents query separators in tab from becoming extra parameters. Return type declares a URL string; it is still escaped when inserted into href.

```php
foreach ($extra as $key=>$value) echo '<input type="hidden" name="'.e($key).'" value="'.e($value).'">';
```

The loop turns each extra key/value into a hidden input. Escaping both prevents quotes/markup from breaking attributes. The server must not assume these hidden values are trustworthy.

## 7 · Input → transformation → output

Input: trusted glyph names/IDs and display text/values supplied by callers. Transform: allowlist SVG, Unicode initials, URL encoding, HTML escaping and consistent markup. Output: return strings for icon/initials/url; emitted markup for form/metric. No database write occurs here.

## 8 · Connections

Layout calls icon and initials. Portal views call club_url, community_form, metric and other UI primitives. Main JS recognizes forms' action/IDs; backend community services enforce their real permissions. [CSS](public__assets__app_css.md) styles icon, avatar, community-form and metric classes.

## 9 · Why helpers?

They reduce repeated markup while keeping PHP views straightforward. Returning strings supports embedding; emitting void helpers supports templating. Mixing those conventions can confuse learners, so check signature/call sites. A component framework is unnecessary for five small helpers.

## 10 · Security/accessibility

Do not replace escaped labels/URLs with raw interpolation. SVG fragments must remain trusted static markup. CSRF field is required but does not replace server role/ownership checks. Icons are decorative; actions need text/name. Initials are a visual fallback, not guaranteed unique identification.

## 11 · Common mistakes

| Symptom | Cause / safe investigation |
|---|---|
| Home icon appears unexpectedly | Unknown glyph key; inspect allowlist spelling |
| Text not printed | Caller ignored returned string instead of echo |
| Broken/nested form | Caller failed to close community_form or opened it inside another form |
| Garbled initials | Missing mbstring or byte-based replacement |
| URL query broken | Removed rawurlencode or failed HTML escaping at call site |
| Fake dashboard counts | Caller substituted static numbers; preserve real aggregate source |

## 12 · Verify

```bash
php -l app/ui.php
```

Use actual configured pages to inspect output; calling a metric helper in isolation requires bootstrap escaping support. Do not require bootstrap with real credentials merely to create a screenshot.

## 13 · Debugging

Search callers with `rg 'community_form|metric|club_url' app`. Inspect emitted form nesting and labels in Elements. Record glyph/key/element shape, not private values or CSRF fields.

## 14 · Safe experiment

In DevTools change a displayed avatar's text temporarily to two synthetic initials, then inspect typography at 390. Predict that the account identity does not change. Reload restores original output. For source exercises use your own saved snippet, not a production identity edit.

## 15 · Challenge ladder

Beginner: identify return versus emitted-output helpers. Intermediate: trace a community form opening to its closing tag and REST payload. Advanced: propose a reviewed helper that maintains escaping and accessible names without changing business contracts. Hint: source signatures show who owns output.

## 16 · Knowledge check

1. Why should rawurlencode not replace e() at an href call site?
2. Why is icon markup not escaped while names are?
3. Can changing hidden club_id grant another club's access?

<details><summary>Answers</summary>

URL encoding protects query-value syntax; HTML escaping protects attribute syntax. Icons are controlled static markup; names can be untrusted text. Hidden IDs are editable inputs; backend membership/ownership checks must reject unauthorized access.

</details>

## 17 · Contribution boundaries

Stage 01 owns output consistency; Stage 02/03 review affected call sites and Stage 04 reviews new form/security fields. Preserve function contracts and never add SQL mutations to a rendering convenience helper.

## 18 · Status

VERIFIED BY INSPECTION: all 5 functions and 18 icon keys. Runtime/Unicode edge tests NOT RUN by this guide; current central validation may cover callers. No helper was changed.

## 19 · Glossary

SVG: scalable vector markup. Unicode: character standard beyond single-byte text. Allowlist: explicit accepted values. Void: function returns no value. Escaping: context-safe text encoding. URL encoding: representing reserved characters safely in a URL value.

## 20 · One-minute explanation

“I can trace how these helpers produce consistent safe display markup, which ones return versus print, and why server permissions remain elsewhere.” Personalize with your actual inspected call chain and work; do not claim prior code as new contribution.
