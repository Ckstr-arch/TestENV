# ✅ Human release checklist

Unchecked means pending. This documentation task did not create a release.

## Before integration

- [ ] Real members chosen; personal identities and assistance policy confirmed.
- [ ] Stage patches scoped, understood and reviewed; baseline authorship not reassigned.
- [ ] Shared-function changes coordinated and actual tests saved.
- [ ] No private environment file, key, dump, session or credential in Git diffs/artifacts.
- [ ] Merge strategy preserves real individual commit metadata; human integration authorized.

## Before hosting

- [ ] Host capability matrix verified and current costs/limits confirmed.
- [ ] MySQL 8 tested, or explicit lecturer decision recorded for MariaDB.
- [ ] Supplied logo/photo usage rights confirmed.
- [ ] Fresh production database, least privilege, private config and new production key prepared.
- [ ] HTTPS with exact APP_URL, public-only document root and durable private sessions verified.
- [ ] Migration backup, isolated restore and rollback plan reviewed.
- [ ] Unique administrator created privately and 2FA enrolled; no demo identities imported.
- [ ] Separate authorization to deploy obtained.

## Before submission

- [ ] Actual HTTPS flows, negative cases and mobile tests passed and recorded.
- [ ] Actual release SHA, migration versions, approver, date and URL recorded.
- [ ] Real report/screenshots/GitHub/live/video links are accessible to the lecturer.
- [ ] Each member explains their own actual contribution; final report exported to PDF/Word.
- [ ] Lecturer confirms deadline; designated member submits.

Use [the detailed runbook](07-production-deployment-runbook.md), not just this checklist. Never check an item merely because a guide exists.
