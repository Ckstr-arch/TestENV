# Security and deployment

## Implemented controls

Passwords use Argon2id rather than reversible storage. Account registration accepts 12–128 characters. Only a CLI provisioning command can create an administrator; registration always creates a student. Database-backed throttles restrict login, registration and second-factor attempts to eight per identity/IP/scope in 15 minutes. These are baseline controls; a distributed production deployment also needs edge rate limiting and monitoring.

Sessions use strict mode, HttpOnly and SameSite=Lax cookies, rotation after authentication and a 30-minute idle expiry. Production enables Secure cookies and HSTS, and requires an HTTPS APP_URL. Every mutation checks a random CSRF token. REST requests use X-CSRF-Token, JSON-only scalar fields, fixed route/field allowlists, a 64 KiB body limit, and a same-origin check when Origin is present. Cookies are never replaced with tokens in localStorage. Server-side checks enforce roles and ownership, and critical reservations use InnoDB row locks/transactions. Membership approvals enforce capacity; tournament registrations enforce capacity and deadlines. Pending or approved resource reservations block overlapping requests.

Authenticator enrollment requires the current password and a valid code. The 160-bit secret is encrypted with libsodium secretbox and a private 256-bit APP_KEY. RFC 6238 SHA-1, six digits, 30-second period, one-step drift is supported. Codes are rejected after their time step has been used. Pending authentication expires in five minutes and grants no portal access. Recovery codes are random, hashed, single-use and displayed once. Disabling 2FA requires the password and a second factor. Manual key enrollment avoids sending authenticator secrets to external QR services.

2FA is opt-in in this version. Administrators should enable it before handling real records. Lost authenticator access is recovered with a saved recovery code; there is no insecure security-question or email bypass. Keep APP_KEY in protected hosting configuration and back it up securely; losing it prevents decrypting enrolled authenticator secrets.

## Production deployment

1. Choose a PHP/MySQL-compatible host with PHP 8.2+, sodium, mbstring, PDO MySQL and Argon2id. Confirm these extensions before choosing a free hosting provider.
2. Set the document root to **`public/`**. Keep `app/`, `.env`, `database/`, `tests/`, `bin/` and `var/` outside the public root. Do not upload the checkout wholesale into a public directory. If the provider has a fixed `public_html`, place only `public/` contents there and adjust the bootstrap include path to a private application directory.
3. Provision an empty UTF-8 database; run schema setup with a migration user. Use a distinct least-privilege runtime database user. Keep database access private; remote databases require trusted TLS configuration through the host.
4. Supply private environment settings: APP_ENV=production, APP_URL=https://your-host, database settings and a generated APP_KEY. Set PHP `display_errors=Off`, `log_errors=On` and protect logs. Enable HTTPS, disable directory listings and use PHP-FPM/Apache rather than PHP's development server.
5. Ensure the web user can create/read/write `var/sessions` privately (0700). Persistent sessions must not be publicly served. For multiple nodes, replace file sessions with a shared supported session store.
6. Create an administrator using the CLI or a private host terminal. Never deploy default/shared passwords or public setup scripts. Enroll 2FA and save recovery codes.
7. Configure backups for MySQL and APP_KEY; verify restoration. Establish error/audit retention, session-file cleanup and login-attempt cleanup jobs. Restrict access to audit logs.
8. Verify student login, 2FA, approvals, CRUD and bookings on the actual HTTPS host. This workspace testing does not prove live hosting is configured.

## Remaining product decisions

This is a tested project implementation, not a certification of production security. Email ownership verification, password-reset email delivery, mandatory administrator 2FA, session revocation across devices, organization SSO, notifications and an independent security review remain extensions for a real institutional rollout. Email addresses are identifiers without delivery integration.

Tournaments register individual players; the sports office manages team assignment and fixtures. Standings aggregate club scores using the displayed points model; sport-specific rankings/tie-break rules and automated knockout brackets are not implemented. Resource records represent a single reservable unit; quantities of equipment should be represented as separate units or extended with inventory accounting.

Deleting parent records cascades linked participation where defined in the schema. The administrator UI requests confirmation. A production organization may prefer archival/soft deletion and mandatory retention rules. Booking history remains as cancelled records, while cancelling registrations/memberships removes those participation records.

## Disposable development accounts

The explicit development demo seeder creates known administrator/student test identities and a known authenticator key for the demo athlete. It refuses production and never overwrites existing identities. These are not safe production accounts. Use a new production database and provision a real administrator privately; do not publish a demo database snapshot containing known logins. Docker demo mode is opt-in through DEMO_MODE=1 and requires APP_ENV=development. A local Docker encryption key generated in var/app.key is private, persisted in app_data and absent from the source ZIP.
