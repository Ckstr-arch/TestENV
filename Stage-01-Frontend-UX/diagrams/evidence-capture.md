# Capture real evidence safely

A screenshot shows a rendered state, not an authorization proof or production release. The seven existing captures were visually inspected as development examples at baseline; their dates/counts are historic.

1. Start a configured local instance without deleting existing volumes or data.
2. Use synthetic accounts and content. Obtain a real disposable club ID through the UI; do not assume IDs are stable.
3. Choose the actual role route: student `/?page=dashboard`, admin `/?page=admin`, or selected club community.
4. Set viewport 1440px desktop or 390px mobile; use a full-page capture. Set theme through the actual toggle and wait until document/data-theme and control agree.
5. At mobile, wait for the drawer's closed transition/visibility, not just a class removal. Capture open drawer separately if teaching it.
6. Keep profile menu closed and avoid Account security/setup/recovery screens. Remove browser chrome that exposes personal tabs/bookmarks if present, without altering app content.
7. Inspect every pixel for credentials, keys, one-time codes, emails, student IDs or real names. Redact by recapturing a safe state where possible; label any necessary redaction honestly.
8. Save actual date, commit, route, theme, viewport, synthetic fixture/context, capturer and assertion in [artifact register](../../Assemble/16-evidence-and-artifact-register.md). Unknown fields remain TODO.
9. Explain the page in text for readers who cannot see the image. Do not pass a screenshot as proof of keyboard or API tests.

| Existing artifact | Intended visual comparison |
|---|---|
| student-dark / student-light | Same student dashboard hierarchy in both themes |
| admin-dark / admin-light | Admin role shell and operation summaries |
| community-dark / community-light | Approved-club feed and aside in both themes |
| community-mobile |390 px light feed with closed drawer and stacked content |

A refresh is PLANNED human work until actually captured. Never fabricate a screenshot, hosted URL, student contribution or pass count.
