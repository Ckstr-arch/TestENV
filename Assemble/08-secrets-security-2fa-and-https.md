# 🔐 Secrets, authenticator 2FA and HTTPS

A password is verified against an Argon2id hash. An authenticator's shared secret must be available to compute codes, so it is encrypted with libsodium secretbox and private APP_KEY. Recovery codes are hashed and single-use. These have different storage needs; do not call encryption “password hashing”.

## Secret lifecycle

| Item | Private location | Rotation/recovery |
|---|---|---|
| DB password | host config / ignored local env | Coordinate actual DB user change and app binding; editing env alone insufficient |
| APP_KEY | host secret store; local Docker private persistent var/app.key | Retain for existing encrypted users; loss breaks TOTP decryption |
| authenticator seed | encrypted users row; user's authenticator | Enroll by password+code; never share screenshots |
| recovery codes | user private storage; hashes in DB | Display once; consume once; never report/log |
| session/CSRF | HttpOnly cookie / private session | Rotate at auth; CSRF in forms/header, never public evidence |

Fresh production gets a new key and fresh DB. Existing production keeps its key. Back up key independently under strong access controls, with release/DB restore mapping but no secret values in evidence. Blindly replacing APP_KEY leaves encrypted TOTP unreadable; a reviewed re-encryption or secure re-enrollment plan is a separate change. Never “fix” failure by disabling checks or exposing secrets.

## Authentication flow

Login validates credentials, then either establishes the full session or creates a five-minute pending challenge. Pending login is not authenticated portal access. TOTP uses SHA1, six digits,30-second steps and ±one step allowance; the stored last accepted counter blocks replay. A clock-synchronized fresh code matters. Recovery code atomically consumed once can complete verification. Enrollment requires current password and code; disabling requires password+second factor.

New student: register/sign in → profile Account security → set up authenticator → add unique manual key privately in authenticator app → confirm password+current code → save the eight recovery codes privately. No email/SMS is sent. Do not copy the disposable demo athlete key to new users.

## Browser and server boundary

Production APP_URL must begin HTTPS; bootstrap then sets Secure/HttpOnly/SameSite=Lax and HSTS. Host must really terminate TLS correctly. Same-origin REST uses JSON and X-CSRF-Token plus cookie; Origin must match exact scheme/host/port when present. Native forms check hidden csrf and return419 on failure; REST CSRF returns403. SameSite is defense in depth, not replacement for CSRF.

PDO prepared statements defend values; SQL identifiers must come from code allowlists. HTML `e()` escaping protects text/attributes at render time; stored plain-text community content is not trusted HTML. Roles/ownership are checked server-side even for forged requests. CSP limits same-origin assets; no external QR service receives secrets. localStorage stores theme only, never auth tokens/passwords.

## Known gaps and diagnostics

No email verification/password reset, mandatory admin 2FA, global session revocation or institutional SSO. Single-node file sessions and database-backed basic rate limiting need production review/edge protection. Database DSN does not currently expose verified remote TLS options; choose a supported private connection or reviewed extension. Audit retention/session cleanup are operations decisions. See [legacy security](../docs/security.md).

For TOTP errors check clock/account/fresh step privately, then encrypted key availability; do not log seed/code. For expiredCSRF reload form/get fresh auth/session with same cookie. For permissions inspect service ownership, not a chmod777 shortcut. For production errors correlate opaque requestID with private logs; never enable public stack traces.
