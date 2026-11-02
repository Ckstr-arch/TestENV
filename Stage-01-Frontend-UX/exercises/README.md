# Isolated exercises · no application copy

Open [layout-clinic.html](layout-clinic.html) directly in a browser. It uses local inline teaching CSS/JS only; it does not include production PHP, call APIs, read storage or touch a database. Inline styles/scripts here are intentionally outside the real application's CSP/webroot. This is a nonproduction sandbox, not a new homepage.

Before pressing Switch, predict how Grid auto-fit versus wrapping Flex will size the three synthetic cards at 900/600/390 px. Inspect computed widths, padding and gap. Move the absolute-child badge in DevTools and identify its containing block. Use Tab/Enter to operate the button and verify its accessible state/text change.

Save a prediction/observation table with viewport, layout, columns/rows and overflow. The expected outcome is responsive wrapping without document-wide horizontal overflow; the exact track widths depend on viewport and outer padding. Resize just above/below a wrap threshold to explain it.

Restore temporary DevTools changes by reloading. If you edited this exercise file, compare with your saved pre-edit copy and remove only your own experimental lines. Never reset or clean the whole repository. Complete answers and safety guidance are in [the labs](../05-labs-and-exercises.md).

## Checked in this cloud session

Chromium rendered this HTML through Playwright `set_content` at 900/600/390 px in both layouts. All six cases passed overflow/state checks and keyboard Enter toggling, with no JS errors. The managed cloud browser blocks direct file URLs; an ordinary local browser may allow them. This result does not claim a production CSP/deployment check or the app's full browser suite.
