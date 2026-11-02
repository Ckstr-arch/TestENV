# 03 · Understand → Modify → Engineer

A milestone is demonstrated understanding, not hours elapsed. Write a prediction before each observation; explaining a wrong prediction is valuable debugging evidence.

| Level | Lessons in order | Proof you can produce |
|---|---|---|
| 1 · Understand | DOM/semantic HTML → PHP output escaping → CSS cascade/box model → Flex/Grid → real script events | Annotate eight homepage nodes and trace one join form without editing |
| 2 · Modify | DevTools token/layout experiments → local sandbox → responsive and keyboard checks → small scoped source proposal | Before/after screenshots, computed styles, own diff and test record |
| 3 · Engineer | Focus/visibility edge cases → long strings/empty state → API failure handling → accessibility trade-offs | Reproduce a defect, explain cause, propose a reviewed fix and show regression evidence |

## Lessons and checkpoints

1. **Meaning before pixels.** Find header/nav/main/section/article and explain which provides a landmark. Open [homepage anatomy](diagrams/homepage-anatomy.md).
2. **PHP does not travel to the browser.** Explain `foreach` club cards and `e()`; source data can be untrusted even when it is in MySQL.
3. **Cascade and sizing.** Explain why `.metric small` at the end of CSS can override an earlier mobile font rule. Calculate card outer width under border-box.
4. **Layout algorithms.** Predict navbar shrinking with Flex and card track widths with Grid. Explain why `minmax(0, 1fr)` resists long-content overflow.
5. **Placement.** Explain sidebar fixed, header sticky, photo absolute within relative parent, and profile dropdown stacking context.
6. **Behavior.** Trace menu click → ARIA/classes → CSS → focus. Trace theme → root token change, then reload.
7. **Network.** Trace FormData → endpoint → JSON/cookie/CSRF → error or redirect. Compare native fallback.
8. **Evidence.** Run the relevant checks yourself, label what you did not run, and ask a peer to review shared contracts.

## Proposed human improvement

PLANNED, not implemented: assess whether the theme control's compact touch target at 390 px is comfortable with a coarse pointer; propose a minimal spacing/target improvement if evidence supports it. Do not assume changing icon size alone changes the interactive hit area. Review actual bounding rectangles and keyboard focus, preserve ordering before profile and both themes.

If there are fewer than four people, redistribute review and learning modules honestly. A folder is a responsibility area, not an invented teammate.
