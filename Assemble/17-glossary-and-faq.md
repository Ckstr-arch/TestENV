# 📚 Glossary and FAQ

| Term | Plain meaning in SportsHub |
|---|---|
| Baseline | Existing assisted prototype, not new individual work |
| DOM | Browser’s element tree produced from PHP-rendered HTML |
| Cascade/specificity | Rules for choosing which CSS declarations win |
| Flex/Grid | Layout along a main axis / two-dimensional tracks |
| Origin | Scheme + hostname + port; must match exact APP_URL |
| Session | Private server state identified by a browser cookie |
| CSRF | Forged mutation using a victim’s cookie; token/origin checks protect writes |
| XSS | Untrusted text becoming executable HTML/JS; escaping and CSP help prevent it |
| Authentication | Proving which account is signing in |
| Authorization/RBAC | Deciding which role or owner may perform an operation |
| PDO/prepared statement | PHP database interface; binds values separately from SQL syntax |
| Foreign key | Constraint requiring a parent row and defining deletion behavior |
| Unique constraint | Database rule preventing duplicates in a specified column set |
| Transaction | Group of changes that commit together or roll back |
| Row lock | A concurrent writer waits on a selected parent row |
| Migration | Versioned, reviewed schema change; not a data reset |
| TOTP | Time-based code computed from a shared secret and counter |
| APP_KEY | Private 32-byte key encrypting authenticator secrets |
| Hash/encryption | One-way password verification / reversible protection with a secret key |
| Image/container/volume | Packaged filesystem / running process / persistent state |
| Document root | Directory the web server exposes; SportsHub uses public/ |
| Branch/PR | Reference to Git commits / proposed reviewed integration |
| Deployment | Actually running a reviewed release on the selected host |
| Rollback | Reviewed return to safe release/state; not an automatic database reset |
| Evidence | Real observed result, not a guide or checked box alone |

## Why not four source copies?

Copies would diverge and duplicate bug fixes. Stages point to the one canonical source and coordinate shared edits.

## Is MariaDB automatically MySQL for grading?

No. It is a compatible local development engine, but the assignment says MySQL is mandatory. Test MySQL 8 or obtain lecturer approval; do not rename an engine to hide the distinction.

## Can I host on GitHub Pages?

Static Pages alone cannot execute PHP/PDO/MySQL. Select a verified PHP/MySQL host with HTTPS. A local Docker URL is not public submission hosting.

## Why do source edits not appear after Docker restart?

Code is copied into the image. Rebuild the SportsHub app using the same project/options; keep database and key volumes.

## Does a stage folder prove my contribution?

No. Read, implement, debug, test and explain real work, then record actual commits/reviews. Templates deliberately contain no assigned names or completed-work claims.

## Is the code production-certified?

No. Implemented controls and tests help, but opt-in 2FA, email/reset gaps, host configuration, retention and sport-specific logic remain review items. Read the [security limits](../docs/security.md) and [production runbook](07-production-deployment-runbook.md).
