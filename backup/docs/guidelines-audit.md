# Guidelines audit · Topic 3 native package

The four-page **WAD Batch 26.1 IFSL Project Instructions** was read in full for this task. Topic 3 remains the selected assignment. Four learning tracks in the parent repository are an internal teaching organization; they are not four assignment topics, and they are not required to run this package.

## What the manual actually requires

The objective and technology requirements specify HTML, CSS, JavaScript, **PHP mandatory** and **MySQL mandatory**, with integrated frontend/backend and relevant CRUD. Bootstrap or Material UI are permitted but optional. Separate admin/student interfaces, secure login, responsive UI, form validation, navigation and applicable search/filter are required.

Topic 3 specifically requires club membership and approvals, training/match scheduling, tournament/fixture administration, scores/results/standings, facility/equipment bookings, and individual participation plus administrative reports. The manual also encourages additional creative features. REST, optional authenticator 2FA, themes and member communities are compatible extensions rather than substitutions for those functions.

The report must include real title/team identities/roles/contact, introduction/objectives/system description, actual screenshots and accessible source/live/video links. Source history should reflect each member's genuine contribution. The app must actually be hosted and accessible to the lecturer. Each member must briefly explain their own contribution in the recorded demonstration. A leader/designated member submits the report. The tentative deadline is **TO BE CONFIRMED**.

## Audit findings and response

| Area | Existing baseline | This package / remaining evidence |
|---|---|---|
| Frontend/backend languages | PHP templates, HTML/CSS/vanilla JS | Preserved; no framework replacement |
| Database | PDO MySQL-compatible SQL; local container used MariaDB | Actual MySQL 8+ required and checked; MariaDB rejected in this edition |
| Core Topic 3 operations | Existing clubs/enrollments/schedules/tournaments/scores/bookings/reports | Preserved, mapped in [traceability](requirements-traceability.md) |
| REST | PHP JSON dispatcher and shared services | Preserved; REST is an interface style within PHP/HTTP |
| Security | Prepared statements, validation, CSRF/session/roles, TOTP and recovery | Preserved; read [limits and hosting](security-and-hosting.md) |
| UI | Approved dark/light design, distinct role navigation and community pages | Preserved; no rebranding or static mockup substitution |
| Operational dependencies | Parent repository had container/build/Python test helpers | Excluded from this standalone native distribution; pure PHP tests provided |
| State separation | Original development database/key/session state | Fresh `sportshub_backup` database, private key and separate session namespace/location |
| Production submission | No real deployment established by local prototype | TODO: verified host, HTTPS, actual MySQL, lecturer access, report/video |
| Team evidence | Codex-assisted baseline and supplied frontend inspiration | Retain attribution; record genuine new individual work and assistance |

A tooling distinction matters: the PDF mandates the application stack but does not explicitly ban every external development tool. It would be inaccurate to declare that an old Python test runner or container helper was automatically a language violation. Their removal here meets the requested stricter **package boundary**. The actual engine mismatch worth resolving is the difference between MariaDB and the manual's mandatory MySQL.

## Package boundary and preservation

`backup/` is the native application root; `backup/public/` is the only web document root. It includes its own `app/`, `database/`, `bin/`, `tests/`, private configuration template and documentation. It does not depend on parent files. Its ordinary database is `sportshub_backup`; optional resetting tests must target only `sportshub_backup_test` after explicit consent. Do not connect it to the original `sportshub` database.

One protective parent-file exception is recorded: `/backup/` was added to the original root `.dockerignore`. The legacy Dockerfile copies its build context; excluding this package prevents its ignored private `.env` or runtime state from accidentally entering that old build. No container manifest is shipped inside this native package. Original runtime/schema behavior is otherwise preserved outside the package.

This edition starts from the existing UI and shared service architecture. It does not automatically copy identities, booking records, private sessions or enrolled authenticator secrets. Creating a new random key is appropriate for this **fresh** separate dataset; replacing the original application's key would break its existing 2FA and is outside this task.

The preserved baseline commit is `f81eab54ebe7179e1763f364d6b8d4f564c36541`; the repository base for this package task is `372d26a`. The final package commit belongs in the [validation record](validation.md) after it exists. Do not invent a SHA, an executed test count or a deployment date.

## Assignment decisions still requiring people

- Verify current MySQL/host capabilities and run the documented functional checks on the final release.
- Confirm rights to use the supplied NSBM logo and athletics photograph before public hosting.
- Confirm whether generic club standings and individual tournament enrollment satisfy the intended demonstration for chosen sports; automated brackets, sport-specific tie-breaks and team-management workflows are not implemented.
- Fill real names, contact details, roles and contribution evidence under module assistance/originality policy.
- Produce actual report/PDF or Word export, safe release screenshots, public/lecturer-accessible source, working live URL and accessible video.
- Confirm the lecturer's actual deadline; the manual supplies no final date.

[Requirement table](requirements-traceability.md) · [Native setup](local-setup.md) · [Validation](validation.md)
