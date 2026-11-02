# ✅ Assignment → real implementation → evidence

Assignment PDF inspected **28 October 2026**; Topic 3 only. Four tracks are not four assignment topics. “Implemented” below means source was inspected; only the current tests/HTTP smoke explicitly listed were executed now. Historical test counts stay historical. Suggested video times are a plan, not recorded footage.

| Requirement | Actual files | User-visible route | Test evidence | Owner stage | Suggested video moment | Completion state |
|---|---|---|---|---|---|---|
| Secure student/admin login | app/views.php; app/actions.php; app/security.php | /?page=login; /?page=verify | tests/security.php: 17 current checks; integration/API/browser cases inspected | 02/04 | 1:40; 8:00 | Implemented; primitive security checks current, end-to-end NOT RERUN |
| Browse/search/filter clubs and join | app/views.php; app/models.php | /?page=clubs; /?page=my_clubs | Integration membership-application and REST membership cases inspected | 02 | 1:40 | Implemented by inspection; mutation flows NOT RERUN |
| Separate student/admin interfaces | app/layout.php; app/portal-views.php; public/index.php | /?page=dashboard; /?page=admin | Native/REST role-denial cases inspected; current unauthenticated gates smoke | 01/03/04 | 0:40; 4:10 | Implemented; read-only gate smoke current |
| Club CRUD and membership approvals | app/views.php; app/actions.php; app/models.php | /?page=manage_clubs; /?page=approvals | Integration/API CRUD and approval cases inspected | 03/04 | 3:00; 4:10 | Implemented by inspection |
| Training/matches and event registration | app/views.php; app/models.php; app/actions.php | /?page=schedule; /?page=manage_events | Approved-member registration and future-attendance denial cases inspected | 02/03/04 | 3:00 | Implemented; per-event registration is separate |
| Tournaments/fixtures and registrations | app/views.php; app/models.php | /?page=tournaments; /?page=manage_tournaments | REST tournament-registration and CRUD cases inspected | 02/03 | 3:00; 4:10 | Implemented for individuals; no automatic brackets |
| Record results and view standings | app/views.php; app/api.php; app/models.php | /?page=results; /?page=manage_events | Filtered results/standings and score cases inspected | 02/03/04 | 4:10; 6:10 | Implemented generic 3/1/0 points model |
| Book facilities/equipment and review collisions | app/views.php; app/actions.php; app/models.php | /?page=facilities; /?page=approvals | REST overlap rejection inspected; community race covers a different invariant | 02/03/04 | 5:20 | Implemented for single-unit resources; review-race inspection risk documented |
| Participation history/reports/attendance | app/views.php; app/api.php | /?page=activity; /?page=reports | Attendance and admin-report cases inspected | 02/03 | 6:10 | Implemented aggregates, not sport-specific analytics |
| Relational MySQL and PHP integration | app/bootstrap.php; database/schema.sql; migrations | API health; admin reports | Current doctor connectivity/schema checks; MySQL 8 NOT RUN | 04/Assemble | 7:00 | Local MariaDB verified; mandatory MySQL compliance TODO |
| Responsive professional HTML/CSS/JS | app/layout.php; public/assets/app.css; public/assets/app.js | All pages, dark/light and mobile | Browser suite inspected; no fresh screenshots | 01 | 0:40 | Existing design preserved; browser NOT RERUN |
| Server validation, search and navigation | app/bootstrap.php; app/models.php; app/views.php; app/layout.php | Club/schedule/tournament pages and forms | Invalid-field/Origin/role cases inspected; current public read-only smoke | 01/02/04 | 0:40; 7:00 | Implemented by inspection |
| Extra: community and moderation | app/community.php; app/portal-views.php | /?page=club&club_id=id&tab=community; /?page=moderation | Historical 73 community checks; not a current run | 02/03/04 | 1:40 | Implemented; approved-member privacy |
| Extra: authenticator 2FA/recovery | app/security.php; app/actions.php; app/views.php | /?page=security; /?page=verify | Current public-vector/replay/encryption checks; browser lifecycle NOT RERUN | 02/04 | 8:00 | Implemented opt-in; no secret captures |
| Source access and genuine GitHub history | Git repository; contribution templates | https://github.com/Ckstr-arch/TestENV | Read-only remote access verified; identities/lecturer access require humans | All | 8:40 | TODO genuine human contributions/PR review |
| Live working PHP/MySQL link | Assemble/07-production-deployment-runbook.md | TODO actual HTTPS URL | No deployment executed | Assemble | 9:30 | TODO |
| PDF/Word report and real screenshots | docs/project-report.md; Assemble/13-final-report-and-screenshots.md | TODO report artifact | Seven historical captures preserved; final capture/export pending | All | 9:30 | TODO human final report |
| One video; each member explains real contribution | Assemble/14-video-demo-and-team-speaking-plan.md | TODO video URL | No recording performed | All | All; 8:40 | TODO |
| Leader submission, deadline and original work | Assemble/15-release-checklist.md; contribution records | LMS, not supplied | Deadline TO BE CONFIRMED; no student authorship assigned | All | 9:30 | TODO lecturer deadline and real team identities |

## Rubric

UI 20%, backend/database 20%, admin 15%, student 15%, report/screenshots/GitHub/live links 15%, video/teamwork 15%. No grade or passing mark is claimed. [Current tests](16-evidence-and-artifact-register.md), [report](13-final-report-and-screenshots.md) and [video](14-video-demo-and-team-speaking-plan.md) complete the evidence chain.

## Acceptance caveats

Mandatory MySQL versus local MariaDB needs explicit resolution. Provider examples in the assignment are not verified current host capabilities. Humans must fill true roles, names, contributions and lecturer-access checks. Native OS instructions do not prove those commands ran on those systems. Final submission and live verification remain pending.
