# 05 · Labs: predict, observe, explain

All source changes below are **learner proposals**, not precompleted contributions. Prerequisites: [setup](00-start-here.md), DevTools and a disposable local account for the network lab. Save evidence without credentials, cookies, CSRF tokens or real identities.

## Lab 1 · Homepage anatomy (Level 1)

**Objective: ** connect real PHP with emitted DOM. Sources: `app/views.php`, `app/layout.php`, CSS. Read the [anatomy lesson](diagrams/homepage-anatomy.md). In 5–10 minutes identify the hero, h1, hero-photo, img, stats-strip, content, grid and article.card in Elements. Predict which exist if no clubs are stored; do not clear a database to test this. Read the conditional empty-state branch instead.

**Expected: ** semantic structure survives without CSS; dynamic text is escaped; no production `home.html` exists. **Questions: ** Does `span` make a heading? Is the plus sign a database field? **Evidence: ** annotated DOM tree and your answers. **Revert: ** no source change; reload any DevTools edits.

## Lab 2 · Breakpoint clinic (Levels 1–2)

**Objective: ** learn layout algorithms safely. Source: [isolated exercise](exercises/layout-clinic.html). Open it directly from disk; it has no API, secrets or database. Predict the number of cards at 900/600/390 px. Inspect computed display, gap and track widths. Switch its Flex/Grid control. Long labels show how minimum sizes affect shrinkage.

Then inspect the real app at 1440/1050/801/800/500/390. Record drawer visibility, header search, card columns and overflow. At 801 the desktop shell remains; at 800 the drawer rules apply. **Expected: ** no document-wide horizontal overflow, while tables may scroll inside `.table-wrap`.

**Questions: ** Why can `1fr` still overflow? Why does `min-width:0` matter? **Evidence: ** width/columns/overflow table plus a screenshot at 390. **Safety: ** do not change shared code yet. **Revert: ** reload DevTools; undo only your own exercise edits if made.

## Lab 3 · Cascade and theme investigation (Level 2)

**Objective: ** prove a winning rule. Sources: CSS and theme-init. Select `.metric small`; compare mobile rule to final unscoped rule. Predict winning font size before opening Computed. Temporarily change a theme token in DevTools. **Expected: ** all token consumers update but hardcoded brand/photo colors may stay intentional.

**Questions: ** Does later always win over more specific? What if localStorage is blocked? **Evidence: ** property, selector, specificity, source-order explanation. **Revert: ** reload; restore your preferred theme through the toggle, not by deleting all browser storage.

## Lab 4 · Keyboard and form network (Level 2)

**Objective: ** verify controls as a keyboard user, then trace a real action. Sources: JS/layout and student membership walkthrough. Use a synthetic local account, press Tab from the address bar, activate skip link, open/close profile and drawer, loop Tab/Shift+Tab at drawer boundaries and press Escape. Toggle theme and reload. Inspect the Network request metadata for a disposable membership without copying token/cookie values.

**Expected: ** visible focus, current page label, role-appropriate nav, JSON POST plus cookie/CSRF, actionable error text for an ineligible duplicate. **Evidence: ** redacted checklist and state/route notes. **Safety: ** browser input constraints are not authorization. **Revert: ** cancel only your own synthetic membership if appropriate; never clear other accounts or tables. Reload temporary DOM edits.

## Lab 5 · Long text and failure triage (Level 3)

**Objective: ** engineer a reviewed accessibility/responsiveness improvement. Put a long synthetic heading/paragraph into DevTools only; compare wrapping in card, post body and profile dropdown. Simulate offline mode **before navigation/read** rather than replaying a write. Predict how the submit catch differs from a JSON 422 response.

Choose one demonstrated issue, write a hypothesis, agree exact shared sections with reviewers and manually implement a small patch only after a human owns the task. **Expected: ** a minimal diff, observed before/after, keyboard/theme/mobile regression notes and honest limitations. **Questions: ** Why no automatic write retries? Which change requires Stage 04 review? **Evidence: ** explanation, redacted screenshots, actual tests and reviewer. **Revert: ** compare your patch with your saved pre-edit version, remove only your own lines; preserve all other work. Do not use repository reset/clean.

<details><summary>Separate hints / answer direction</summary>

Lab 1: counts come from SQL and plus is literal. Lab 2: grid items have automatic minimum content sizes; `minmax(0,1fr)` permits narrower tracks, and wrapping must still be handled. Lab 3: the trailing `.metric small` has equal specificity to earlier `.metric small` and wins by order; a more-specific selector can win earlier. Lab 4: hiding a button is not server permission. Lab 5: a network failure says nothing definitive about whether a write committed; fetch current state before a human retries.

</details>
