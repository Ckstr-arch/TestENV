# `public/assets/app.css` · source guide

## 1 · File card

| Field | Value |
|---|---|
| Canonical source | [public/assets/app.css](../../public/assets/app.css) |
| Primary guide owner | Stage 01; proposed Member A (TBD) |
| Secondary review | Stage 02/03 for affected views; Stage 04 for server/form contracts |
| Language / size | CSS; 2285 lines at inspected baseline |
| Role | Design tokens, layout, components, themes, responsiveness and accessibility |
| Inspected baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |

## 2 · Why this file exists

HTML supplies meaning, but this file determines the navy/lime visual system and how the same markup fits desktop/mobile/print. If it fails to load, native forms and links remain HTML, but the intended hierarchy, drawer visibility, focus styling and theme presentation are lost. CSS is not an authorization layer.

## 3 · Prerequisites

Start with the [homepage DOM](../diagrams/homepage-anatomy.md). A selector identifies elements; a declaration is `property:value`; a rule combines selectors with declarations. The **cascade** chooses a winner when several declarations apply. Layout runs after styles are resolved.

## 4 · Dependencies and consumers

Loaded by [layout.php](app__layout_php.md) after [theme-init.js](public__assets__theme-init_js.md). Classes come from [views.php](../../app/views.php), [portal views](../../app/portal-views.php) and [UI helpers](app__ui_php.md). JS updates `data-theme`, `.open`, `.is-open`, `.nav-open`, hidden and disabled attributes. `track.jpg` is used in background images and HTML. There is no CSS build step or external font download; Inter is a preference followed by system fallbacks.

## 5 · Detailed tour in source order

The complete [rule coverage index](css-rule-index.md) lists every selector block, scope and line. Use it when a component seems to be styled twice. The following groups explain their purpose; all meaningful regions are accounted for.

| Source-order region | Responsibility and interpretation |
|---|---|
| Roots → global typography/focus | Dark defaults, light override, box sizing, body/headings/links, hidden, sr-only and skip link |
| Brand → sidebar/nav | Flex brand/logo, fixed sidebar, scrollable nav, active pseudo-element and footer |
| App-frame → workspace/header/profile | Space for sidebar, sticky header, search, theme pill, native details/profile dropdown, public nav |
| Buttons → content/layout | Variants and states, heading/section hierarchy, grid/card/stack/dashboard containers |
| Performance hero → metrics/sessions | Absolute photo/overlay, relative copy, metric grid, session rows, date boxes, member clubs |
| Admin shortcuts → forms | Dashboard shortcuts, audit presentation, label/input/select/textarea, filters, badges and definition lists |
| Cards → schedules/results/tables | Card footers, grid fixture rows, score chips, scrolling table wrapper, activity and empty states |
| Alerts → security/admin layouts | Semantic colors, recovery grid/dialog, private setup-key wrapping, manage layout, reviews and buttons |
| Club banner → community/moderation | Tabs, feed/aside, composer, post author/body/actions, comments, gating, pagination and report reasons |
| Auth → public homepage | Auth two-column story/form, hero/photo/captions, SQL-count strip and next-fixture banner |
| Media queries → final overrides | Wide-screen, 1250, 1050, 800, 500, reduced-motion, print, then late profile/audit/type overrides |

### Cascade, specificity and source order

Specificity compares selector weights after origin/importance. Element selectors (`input`) are less specific than a class plus element (`.header-search input`). ID selectors (`#main`, taught here as a concept) outweigh class selectors, but this file mostly uses classes/attributes. Descendant `.hero h1` matches an h1 inside hero; child `.hero-photo > img` would require a direct child (the actual photo rule uses a descendant). `[data-theme="light"]` selects an attribute value; `:hover`/`:focus-visible` select states; `:after` creates a decorative pseudo-element.

The actual generic `input,select,textarea` rule gives padding and background; the more-specific `.header-search input` overrides border, padding, margin, background despite appearing earlier. Among equal-specificity `.metric small` rules the **last** one wins. The source ends with `.metric small {font-size:10px}` and `.session-item p {font-size:11px}` after responsive rules. Therefore the earlier 8 px metric-label declarations are not necessarily computed 8px. Media queries filter applicability; they do not add specificity.

Inherited properties such as color/font can flow to descendants; padding, borders and display do not. A shorthand like `margin: 3px 0` sets vertical and horizontal pairs. Overwriting a shorthand can reset individual longhands unexpectedly. `!important` appears for hidden/reduced-motion/print and some audit overrides; adding more to fix a local layout often masks the real cascade issue.

### Box model and normal flow

Actual `* {box-sizing:border-box}` means declared width includes content, padding, border. Margin sits outside. A 230px-wide card with 20px padding and 1 px borders has 188px content width. Under content-box it would occupy 272px plus margins. Adjacent vertical margins between normal blocks can collapse; Flex/Grid gaps do not collapse. The global h/p margin reset only removes top margin, so check computed bottom spacing.

`display:block` takes a row in normal flow; width/height apply. Inline text (`span`, `a` by default) participates in text lines and does not behave like a full block box. `.inline` uses inline-block, which keeps inline participation while accepting box dimensions. `[hidden] {display:none!important}` removes layout presentation, not merely opacity. `.sr-only` visually clips an absolute 1px box so its label can remain available to assistive technology; it is not equivalent to display: none.

### Flexbox algorithm: real workspace header

The header is `display:flex` with center alignment and gaps. Flex lays out direct children along its main axis (row by default); `align-items` works on the perpendicular cross axis. `.header-search {flex:1;max-width:400px}` can use available space up to its maximum. `.header-actions {margin-left:auto;flex-shrink:0}` pushes actions to the far side and protects them from shrinking. Identity has a minimum width. If the total minimum widths exceed the header width, children overflow: reducing only a gap cannot always solve it. The 1050 px rule hides search; the 800 px layout removes profile text and introduces drawer control.

In `.stack` or `.sidebar`, `flex-direction:column` makes the main axis vertical. `justify-content` now distributes vertically; `align-items` acts horizontally. `gap` spaces siblings without margins. `.hero-actions {flex-wrap:wrap}` permits link buttons to flow onto a second line. Flex growth/shrink distributes positive/negative free space; an automatic minimum content size can still stop a child shrinking, so components such as composer form use `min-width:0`.

### Grid algorithm: cards and dashboards

Actual rule:

```css
.grid.three {
  grid-template-columns: repeat(3, minmax(0, 1fr));
}
```

First subtract gaps, then divide available space into three fractional tracks. `fr` shares remaining space; `minmax(0,1fr)` allows the minimum to become 0 rather than insisting on long unbroken content width. `.card {min-width:0}` also allows the grid item to fit; its text still needs suitable wrapping. `.dashboard-grid` deliberately uses 1.05fr/1fr; `.metrics-grid` uses four equal tracks, two below 1050. `.community-layout` uses a flexible feed and an aside with a minimum 260px, then collapses to one column below 1050.

`auto-fit` and `auto-fill` are **teaching alternatives, not current source values**: `repeat(auto-fit,minmax(220px,1fr))` collapses empty tracks and stretches existing items; auto-fill retains possible empty tracks. Both are useful in the isolated sandbox, but replacing current breakpoints would be a real reviewed design change.

### Positioning and stacking: why the drawer covers content

`static` is normal placement; inset/z-index do not position a static box as an overlay. `relative` keeps its place and establishes a containing block for absolutely positioned descendants. `.hero-photo` is relative, so its absolute image and captions use that box. An `absolute` element leaves normal flow; it will not give its parent height, which is why `.hero-photo` has minimum height. `.profile-menu` is relative and `.profile-dropdown` is absolute at `top:calc(100% + 16px);right:0`.

`.sidebar` is fixed to the viewport edge; `.portal .app-frame` offsets normal content so it is not underneath. `.workspace-header` is sticky at top 0; it stays in flow and sticks within its scrolling context. Overflow ancestors can change sticky behavior. At 800 the sidebar has z-index 100, scrim 90, while header has z-index 40. The body scroll is locked by `.nav-open`. CSS transform creates a stacking context. A dropdown's z-index 80 inside header's context cannot outrank an unrelated higher sidebar context merely by growing its own number.

The mobile closed drawer combines `transform:translateX(-105%)` and `visibility:hidden`; moving a visible box offscreen alone would leave problematic focus behavior. `.sidebar.is-open` restores transform and visibility. JS must also manage focus and ARIA; CSS alone cannot trap or return focus.

### Units, images and responsive type

Pixels are CSS pixels, not hardware pixels. `%` resolves against an appropriate containing dimension; `vw` is viewport width/100, `vh` viewport height/100. `rem` is root font size and `em` depends on the local font context; these are concepts useful for proposed accessibility work, while current app mostly uses px/vw/vh. `dvh` accounts for dynamic browser chrome but is not a current declaration here. `h1` uses `clamp(28px,3vw,42px)`; homepage uses a different clamp then explicit breakpoint font sizes. The clamp chooses a preferred responsive size within minimum/maximum limits.

`object-fit:cover` preserves image ratio while cropping to fill; contain preserves the whole image with possible empty space. Logo uses contain. Hero uses cover, grayscale/brightness or saturation filters and gradient overlays. `aspect-ratio` is a useful teaching alternative but is not used in this baseline. Rounded corners plus overflow: hidden clip the photo and overlay. Shadows add depth; they do not increase layout size. Alpha hexadecimal colors such as `#0008` add transparency.

### Theme tokens, contrast and motion

`:root` provides dark token defaults; `:root[data-theme="light"]` overrides values such as `--bg`, `--text`, `--accent`, `--accent-ink`. `var(--surface)` resolves per current root. Light accent is dark green so white button labels can remain readable; copying the dark lime/white pairing blindly would reduce contrast. Gradients are actually used in hero overlays, club banner and auth story. Photo captions intentionally use controlled light text over darkened imagery.

Only the mobile sidebar has a transition (`transform .2s ease,visibility .2s`). Other transforms include rotating the member chevron. There is no actual `@keyframes` animation curriculum to invent. Reduced-motion disables transitions and scroll behavior; print hides controls/navigation, removes portal offset and shadows, uses light surfaces and makes table overflow visible.

### Exact breakpoint behaviors

| Query | Main changes |
|---|---|
| min 1700 | Larger main padding and performance hero height |
| max 1250 | Sidebar 216px, header/search/metric sizing, community feed ratio and compact public header |
| max 1050 | Search hidden, three-column cards become two, metrics two, dashboard/community one, schedule two columns, admin shortcuts two |
| max 800 | Sidebar drawer 250px, no portal left margin, public menu dropdown, profile text hidden, auth/hero/manage/two-grid stacked |
| max 500 | Compact header/cards, three-grid one, community-aside one, filters vertical, club banner stacked, stats two, recovery grid one |
| prefers-reduced-motion | Transitions disabled |
| print | No nav/buttons, white surfaces, report-friendly tables |

Test boundaries as well as representative sizes: 801/800 and 501/500 expose off-by-one assumptions. MatchMedia in JS uses min 801 to close mobile modal state when returning to desktop.

## 6 · Real annotated excerpts

```css
:root[data-theme="light"] {
  color-scheme: light;
  --bg: #f1f5f9;
```

The first line matches the root only when its attribute is light. `color-scheme` tells browser-native controls which built-in palette fits. The custom property stores a value for consumers; it does not paint anything until a property uses `var(--bg)`.

```css
.sidebar.is-open {
  transform: translateX(0);
  visibility: visible;
}
```

This actual block lives inside max 800 scope. Both classes must be on the same element; `.sidebar .is-open` would mean a descendant and change its meaning. JS toggles the class; a zero transform resets offscreen positioning. Visibility returns presentation after closed state hid it.

## 7 · Input → transformation → output

Input: DOM, viewport, root theme, interaction attributes/classes and user media preferences. Transformation: cascade → computed values → layout → paint/compositing. Output: positioned boxes, readable state cues, focus ring and responsive/print presentation. No SQL row is changed by a CSS declaration.

## 8 · Connections

[Layout](app__layout_php.md) owns matching classes; [JS](public__assets__app_js.md) owns state classes; [homepage](../diagrams/homepage-anatomy.md) maps 12 elements; [browser tests](../../tests/browser.py) exercise real viewports. The [property glossary](css-property-glossary.md) explains real values and failure patterns. The rule index links back to this tour rather than claiming every rule is a separate function.

## 9 · Why this implementation?

One token-driven stylesheet keeps both roles/themes consistent without framework dependencies. Explicit breakpoints make the intended sidebar transition easy to teach. Trade-off: a long shared stylesheet can accumulate late overrides and regress unrelated views. Review groups and computed styles before adding a duplicate selector. No rewrite is required to learn the current architecture.

## 10 · Security, correctness and accessibility

CSS visibility is not access control. Preserve `:focus-visible`, hidden semantics, readable contrast, reduced motion and print behavior. Do not hide API error text or use color alone for statuses. Decorative image rights still need confirmation before public hosting. Compact controls and small text require human usability review; this guide is not certification.

## 11 · Common mistakes and safe diagnosis

| Symptom | Cause to test | Safe investigation |
|---|---|---|
| Unexpected text size | Equal-specificity late override | Computed → winning source line |
| Card makes page wide | Long minimum content or missing wrapping | Inspect grid min-width and text overflow |
| Dropdown behind drawer | Stacking-context boundary | Inspect positioned/transform ancestors |
| Blank hero wrapper | Absolute children give no height | Check parent min-height, not image URL only |
| Wrong light colors | Hardcoded color or wrong root attribute | Inspect token consumer and light exception |
| Menu invisible but focusable | Transform without visibility/focus logic | Compare closed/open rules and JS |
| Print dark or clipped | Missing print token/overflow override | Print preview with table page |

## 12 · Run/verify safely

Use the existing configured server; open homepage, club, student/admin pages at 1440/390 and both themes. See [test procedure](../06-testing-and-debugging.md). No app build or package install is needed to load CSS.

## 13 · Debugging lesson

Inspect computed styles before editing source. Disable a declaration in DevTools; record selector, winning value, scope and effect. Do not log private text or capture profile emails in evidence. Reload undoes temporary DevTools styles.

## 14 · Guided experiment

In the [sandbox](../exercises/layout-clinic.html), change minmax minimum and switch Grid/Flex. Predict wrapping at 390. Then inspect an actual card without copying exercise CSS into production. Save observations; reload to revert.

## 15 · Challenge ladder

Beginner: trace three token consumers. Intermediate: explain a real specificity conflict and propose a scoped fix. Advanced: reproduce long-content/focus/zoom edge behavior and design a regression check. Expected evidence is your explanation and observed result, not a fabricated PR. Hints: inspect min-width, stacking context and last matching declaration.

## 16 · Knowledge check

1. Why doesn't a media query increase specificity?
2. Why does an absolute photo need a sized relative parent?
3. What can break if max 800 rules and min 801 JS no longer agree?

<details><summary>Answers</summary>

1. It controls whether declarations participate; selector weights are unchanged. 2. Absolute children leave normal flow; the parent otherwise may collapse. 3. A drawer could retain modal/focus/scroll state while desktop CSS makes it permanent, or close at the wrong width.

</details>

## 17 · Contribution boundaries

Stage 01 owns presentation. Shared PHP class changes need Stage 02/03 review; JS/modal contract changes need JS tests; form/API changes need Stage 04. Preserve all runtime capabilities, not only screenshot appearance.

## 18 · Status and limitations

VERIFIED BY INSPECTION: all source-order groups and selector blocks indexed. NOT RUN by this guide: manual contrast/usability audit and new screenshot refresh. Current executed checks are reported centrally. No stylesheet was modified for these lessons.

## 19 · Glossary

Cascade: rule-selection process. Specificity: selector weight. Containing block: reference box for placement/sizing. Stacking context: group painted together. Breakpoint: conditional width threshold. Token: named design value. Reflow: layout recalculation. Progressive enhancement: usable baseline with optional behavior.

## 20 · One-minute explanation to personalize

“I inspected how this stylesheet turns the existing PHP markup into both themes and responsive layouts. I can show the token, selector and breakpoint that control one component, and explain what I personally tested or improved.” Replace this with your own example after doing the work; do not claim baseline authorship.
