# 🛠️ Troubleshooting and rollback

Diagnose the failed boundary before changing code. Record request ID/status and redacted context, inspect private logs, then reproduce on disposable data. Never disable CSRF/auth/TLS or publish stack traces as a fix.

| Symptom | Likely boundary | Safe investigation |
|---|---|---|
| REST HTTP 403 | Role, CSRF or Origin | Confirm role, fresh session token and exact APP_URL origin |
| API HTTP 404 | Rewrite/router or unknown resource | PHP development server needs public/router.php; Apache needs mod_rewrite/AllowOverride; verify route/method |
| Native HTTP 419 | Missing/stale csrf | Reload form and preserve its cookie; API uses 403 for this check |
| HTTP 422 | Validation/business rule | Read real field, membership, date, capacity and overlap requirements; correct the input |
| HTTP 409 | Uniqueness/state conflict | Fetch current state; do not blindly replay a mutation |
| HTTP 429 | Throttle | Wait for Retry-After: 900; keep protection enabled |
| HTTP 500 | Unexpected application failure | Correlate request ID with private error log; reproduce in isolation |
| HTTP 503 | Database/config/schema unavailable | Check DB health, host/port, account grants and extensions; run doctor without exposing secrets |
| Missing CSS/JS | Document root or asset path | Inspect asset 404s in Network; ensure public/assets is served under the public-only root |
| Login loop/session loss | Session directory or Secure-cookie mismatch | Check HTTP service ownership, HTTPS/production settings and persistent storage |
| TOTP rejection | Clock, replay, wrong account or lost APP_KEY | Use a fresh interval, synchronize clocks privately and verify matching key availability; do not log codes |
| Empty data after Docker update | Changed project/volume selection | Inspect label-scoped names and reuse original project/options; do not reset data |

## Safe release rollback decision

1. Identify the actual release SHA and applied migration versions, then stop further rollout. Preserve logs, data and key.
2. Determine whether old code works with the new schema. Adding community tables ordinarily does not require dropping old tables, but verify affected queries rather than assume compatibility.
3. A human approves switching to a known previous build with the same private configuration and durable volumes. Document any temporarily unavailable features.
4. If data/schema repair is needed, choose a reviewed corrective migration or verified restoration into a controlled target. Explain possible data loss since backup before approval.
5. Recheck doctor/health, roles/login/2FA and representative flows. Save actual outcomes in release notes.

There are no automatic down migrations. No destructive rollback or deployment was executed here. Restore rehearsals use new isolated destinations; production restoration needs explicit separate authorization.

## Stage exercise: release simulation

**Objective:** reason before production changes. On paper, choose a proposed UI patch and an additive database patch, then trace their diffs. List state and secret dependencies. **Prerequisites:** read source inventory and migration guide. **Expected outcome:** separate plans for code rollback and data recovery. **Evidence:** an annotated decision table with reviewer questions. **Guiding question:** why can changing APP_KEY not be fixed by rolling back CSS? **Safety:** no real credentials or production commands. **Undo:** remove only your own private simulation notes if unneeded. **Answer:** TOTP ciphertext depends on the matching key, not the UI release; preserve key and corresponding state.
