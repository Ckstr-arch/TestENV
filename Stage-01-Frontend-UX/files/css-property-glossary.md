# CSS property desk reference · real SportsHub examples

Use with the [masterclass](public__assets__app_css.md) and [complete rule index](css-rule-index.md). Example values below are taken from current declarations unless marked as a teaching alternative.

| Group | Property / real location | Effect | Common mistake |
|---|---|---|---|
| Foundation | `box-sizing:border-box` · `*` | Width includes padding/border | Adding padding to a fixed width twice |
| Foundation | `margin:0` · body | Removes browser body gap | Expecting child margins to disappear |
| Foundation | `padding:23px` · card | Space inside box | Using margin to paint background space |
| Flow | `display:flex` · header | One main-axis layout | Confusing main/cross axes |
| Flow | `flex-direction:column` · sidebar | Vertical main axis | Expecting justify-content horizontal |
| Flow | `flex:1` · search | Shares available main space | Ignoring min/max constraints |
| Flow | `flex-shrink:0` · header actions | Protects controls from shrinking | Applying to every child and forcing overflow |
| Flow | `flex-wrap:wrap` · hero-actions | Allows another row | Expecting default flex to wrap |
| Flow | `gap:20px` · grid | Gutter between tracks | Adding extra item margins |
| Grid | `display:grid` · dashboard-grid | Two-axis tracks | Setting columns on a non-grid element |
| Grid | `grid-template-columns:1.05fr 1fr` · dashboard | Unequal fluid columns | Treating fr as percentage |
| Grid | `repeat(3,minmax(0,1fr))` · grid.three | Three shrinkable equal tracks | Forgetting long text still needs wrap |
| Grid | `grid-column:1/-1` · community guidelines | Spans all grid tracks | Using against Flex parent |
| Sizing | `min-width:0` · card | Allows shrink below content minimum | Assuming width 100% fixes min-content overflow |
| Sizing | `max-width:1510px` · main | Bounds reading/layout width | Expecting it to force width 1510 |
| Sizing | `min-height:calc(100vh - 140px)` · main | Fills viewport allowance | Ignoring mobile browser chrome |
| Type | `font-size:clamp(28px,3vw,42px)` · h1 | Responsive size with bounds | Ignoring later headline overrides |
| Type | `line-height:1.6` · body | Unitless font-relative line box | Thinking it adds margin |
| Type | `letter-spacing:-.025em` · headings | Tightens glyph spacing | Overcompressing small text |
| Color | `color:var(--text)` · body | Theme foreground | Hardcoding dark ink on dark surface |
| Color | `background:var(--surface)` · card | Theme surface | Assuming background inherits |
| Color | `color-scheme:light` · light root | Native control palette | Treating it as full theme implementation |
| Image | `object-fit:contain` · brand img | Whole logo inside size | Using cover and clipping logo |
| Image | `object-fit:cover` · hero-photo img | Crop to fill without distortion | Expecting whole photo visible |
| Image | `object-position:65% 45%` · performance image | Chooses crop focal region | Editing dimensions to move focal point |
| Image | `filter:grayscale(1) brightness(.65)` · performance | Darkens/desaturates image | Applying filter to entire page |
| Surface | `border:1px solid var(--border)` · card | Visible edge inside border-box | Treating border as margin |
| Surface | `border-radius:10px` · card | Rounds painted box | Expecting children clipped without overflow |
| Surface | `box-shadow:var(--shadow)` · card | Adds non-layout depth | Using shadow to satisfy text contrast |
| Placement | `position:relative` · profile-menu | Anchors dropdown | Thinking relative removes flow |
| Placement | `position:absolute` · profile-dropdown | Overlay outside flow | Missing positioned ancestor |
| Placement | `position:fixed` · sidebar | Viewport-pinned navigation | Forgetting app-frame offset |
| Placement | `position:sticky;top:0` · header | Scroll sticking in context | Ancestor overflow changes behavior |
| Placement | `inset:0 auto 0 0` · sidebar | Top/bottom/left pinning | Misreading shorthand order |
| Placement | `z-index:100` · mobile sidebar | Layer within stacking context | Raising child index across contexts |
| Overflow | `overflow:auto` · table-wrap | Internal scrolling if needed | Hiding content with overflow: hidden |
| Overflow | `overflow-wrap:anywhere` · post-body | Breaks long strings | Breaking semantic labels unnecessarily |
| Overflow | `white-space:pre-wrap` · post-body | Preserves linebreaks and wraps | Trusting it to escape HTML |
| Overflow | `text-overflow:ellipsis` · profile-name strong | Clips long name with marker | Missing nowrap/overflow properties |
| State | `visibility:hidden` · mobile sidebar | Hides box presentation | Using translate alone for hidden menu |
| State | `display:none!important` · hidden | Removes hidden layout | Overriding global hidden semantics |
| State | `outline:3px solid var(--link)` · focus-visible | Visible keyboard location | Removing outline without replacement |
| State | `outline-offset:4px` · focus-visible | Separates ring from edge | Clipping it with overflow |
| State | `opacity:.6` · disabled button | Deemphasizes unavailable action | Assuming opacity disables click |
| State | `cursor:wait` · disabled button | Waiting cursor cue | Treating cursor as disabled behavior |
| Motion | `transform:translateX(-105%)` · mobile sidebar | Moves panel offscreen | Forgetting focus/visibility state |
| Motion | `transition:transform .2s ease` · mobile sidebar | Smooths movement | Ignoring reduced-motion preference |
| Print | `break-inside:avoid` · printed card | Avoids splitting card if possible | Assuming printer guarantees pagination |

Concept-only additions: static positioning is the default; rem/em/dvh/auto-fit/auto-fill/aspect-ratio are taught in the masterclass as alternatives and are **not falsely listed as current declarations**. No keyframe animation is present in this baseline.
