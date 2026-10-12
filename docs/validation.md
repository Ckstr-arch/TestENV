# Validation record

Date: 12 October 2026.

| Check | Outcome |
|---|---|
| PHP syntax (all PHP sources) | Passed |
| JavaScript and shell syntax | Passed |
| RFC 6238/encryption/password/escaping security checks | 17 passed |
| Native PHP form + real MySQL integration | 71 passed |
| JSON REST API + real MySQL integration | 130 passed |
| Same security/form/API suites in Docker | All 218 passed |
| OpenAPI 3.1 specification validator | Passed, 61 operations |
| Chromium browser workflows | Passed |
| Public desktop/mobile layouts | Passed at 1440px and 390px, no horizontal overflow or JavaScript errors |
| Demo seed repeatability and production refusal | Passed |
| .env creation and preservation | Passed, existing private settings not overwritten |
| Fresh Docker database initialization | Passed after correcting init-SQL readability |
| Docker Apache rewrite, database and health checks | Passed |

Browser workflows include REST-backed account creation/login, rejected login feedback, membership application/approval, session/tournament registration, bookings, admin create/edit/delete, 2FA enrollment with a one-time recovery-code dialog, recovery login and authenticated 2FA removal. No authenticator secrets/recovery codes were included in screenshots.

Native cloud runtime: PHP 8.4.26 and MariaDB 11.8.6. Docker runtime: official PHP 8.4 Apache image and MariaDB 11.8 (the tested pulled MariaDB version was 11.8.9). Image versions are major/minor tags, not permanently locked digests; rebuilds can receive upstream security fixes.

Docker building in this restricted cloud required forwarding the existing proxy route and using the cloud's trusted CA bundle as a temporary BuildKit secret. TLS, repository signatures and package artifact verification remained enabled. The portable Dockerfile does not hardcode cloud proxy addresses or credentials.

Native Windows and macOS instructions are documented, but those operating systems were not available for execution here. The Docker application was tested on Linux; Docker Desktop instructions follow the same Compose workflow. No public hosting, GitHub push, environment publication, real email integration or independent security audit is claimed by these checks.

Test databases are deliberately disposable. Run test suites sequentially because they reset the same `sportshub_test` schema. The main development database is separate. See `local-setup.md` for commands and `security.md` for product/deployment limitations.
