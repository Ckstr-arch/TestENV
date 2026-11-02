# 🧩 Assemble — one application, honest evidence

**Mission:** join real contributions safely and prepare a verifiable PHP/MySQL submission. This guide does not claim a deployment has happened. Hosting, identities, PRs and final URLs remain human work.

## Learning order

1. [Integration overview](00-integration-overview.md) → [exhaustive source map](01-repository-assembly-map.md) → [Git/team workflow](02-git-github-team-workflow.md).
2. [macOS](03-local-development-macos.md) or [Windows/Linux](04-local-development-windows-linux.md), then [Docker and data safety](05-docker-compose-and-data-safety.md).
3. [Hosting choices](06-php-mysql-deployment-options.md) → [production runbook](07-production-deployment-runbook.md) → [secrets/2FA/HTTPS](08-secrets-security-2fa-and-https.md).
4. [Migrations/backups](09-database-migration-backup-restore.md) → [tests/CI gates](10-ci-tests-and-quality-gates.md) → [troubleshooting/rollback](11-host-troubleshooting-and-rollback.md).
5. [Assignment traceability](12-assignment-traceability.md) → [report/screenshots](13-final-report-and-screenshots.md) → [video plan](14-video-demo-and-team-speaking-plan.md).
6. [Release checklist](15-release-checklist.md) → [evidence register](16-evidence-and-artifact-register.md) → [glossary/FAQ](17-glossary-and-faq.md).

**Outcome:** you can explain which files run, which server serves them, what state survives restart, what each teammate actually changed and what evidence is still missing.

## Practical work

- Understand: trace [browser → PHP → SQL](diagrams/request-flow.md) without changing data.
- Modify: follow a local stage lab; create a proposed reviewable patch under your own identity.
- Engineer: rehearse a release against synthetic data, documenting backup, migration and rollback decisions before requesting deployment authorization.

## Evidence gates

- [ ] Read your stage and its real source; explain one flow without notes.
- [ ] Record actual patch/tests/reviewer; never assign old prototype authorship to a stage owner.
- [ ] Resolve mandatory MySQL vs local MariaDB with tested MySQL 8 or lecturer approval.
- [ ] Obtain host capability answers, image/brand usage permission and lecturer link access.
- [ ] Fill real report, video and release evidence only when obtained.

The [current validation record](16-evidence-and-artifact-register.md) separates this task from historical tests in [legacy validation](../docs/validation.md). [Templates](templates/review-checklist.md) are unfilled human tools.
