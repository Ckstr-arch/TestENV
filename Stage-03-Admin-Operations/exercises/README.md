# Safe admin exercise workspace

[Lab instructions](../05-labs-and-exercises.md) · [Answer key](../08-knowledge-check.md)

This folder deliberately contains learning worksheets rather than a second deployable application. Do not copy PHP services, SQL credentials or production database rows here. Use the canonical runtime and a disposable study installation; run integration fixtures only after test-name isolation is proven.

## Worksheet: one decision

Copy this table into your private learning notes and fill with synthetic IDs only:

| Step | Prediction | Actual observation | Source symbol | Allowed/denied status | Evidence |
|---|---|---|---|---|---|
| Admin reads pending request | | | | | |
| Concurrent capacity changes | | | | | |
| Admin submits decision | | | | | |
| Locked reread | | | | | |
| Student refreshes club | | | | | |

## Worksheet: one patch

Before editing, name the exact function/branch and save a copy of only your intended original excerpt in private notes. Record the current diff so you can distinguish other people's changes. Predict a passing and failing case; implement manually after scope review; compare source and behavior; undo only your own temporary hunk by hand if the exercise is discarded. Stop for shared reconciliation if another contributor touched that region. No global reset or file restore is required.

Do not place real screenshots, request payloads, cookies, setup/recovery codes or `.env` files in these worksheets. Evidence for a committed human PR must be reviewed and redacted first; this task does not perform that commit.
