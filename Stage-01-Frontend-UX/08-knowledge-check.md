# 08 · Explain causes, not definitions alone

Answer before expanding the key.

1. Which file emits `<head>` and which emits the actual homepage hero? Why is `home.html` not the production entrypoint?
2. The `.grid.three` container is 900px wide with 20px gaps. Approximately how much space remains per track before card borders/padding? What does border-box change?
3. Why can `display:none` remove a link from keyboard navigation while a transform alone may leave it focusable?
4. Why does a child `z-index:9999` not necessarily appear above a different stacking context?
5. At 390px, why can `.metric small` still be 10px despite an earlier 8 px mobile rule?
6. Why does the theme script precede CSS while app.js is deferred?
7. Why is a visible Apply button insufficient proof that its user may join?
8. A fetch mutation times out. Should JS immediately resend it? Explain both possible server outcomes.
9. How does optional chaining help a script shared by public and portal pages?
10. How does `textContent` differ from inserting an API error using `innerHTML`?
11. A field's name changes in PHP but endpoint mapping is unchanged. Predict a failure path.
12. A logo has empty alt beside the words SportsHub. Is that necessarily inaccessible?

<details><summary>Answer key — read after your attempt</summary>

1. layout.php emits the document shell; views.php home branch emits the hero; index.php is the controller.
2. Two gaps use 40px; remaining 860/3≈286.67 px. Border-box includes padding/borders in the declared width.
3. display: none removes layout/accessibility presentation; transformed content still exists. Actual drawer also changes visibility and focus/modal state.
4. Descendant z-index is bounded by its stacking context; inspect sticky header/fixed sidebar ancestors.
5. Equal specificity and later source order win. Media-query membership does not increase selector specificity.
6. Saved theme must apply before paint; deferred main script needs parsed nodes and should not block parsing.
7. Server authenticates, checks roles/capacity/state, CSRF and database invariants.
8. The request may have failed before commit or only the response may have been lost; inspect current state before retry.
9. Missing optional nodes return null; chaining skips calls rather than throwing.
10. textContent inserts text; innerHTML parses markup and can enable injection if input is untrusted.
11. Missing id produces a wrong route/validation error; native/enhanced behavior can diverge.
12. Empty alt avoids repeating adjacent descriptive text for the same link. Meaningful standalone imagery still needs appropriate alternative text.

</details>

## Viva challenge

Use a real source excerpt of your choice. Explain input → DOM/property/event → visible effect → server boundary → test evidence. Describe what you personally changed or learned and what was already present. Do not read an invented authorship script.
