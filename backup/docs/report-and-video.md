# Honest report, demonstration and teamwork

The manual requires a PDF/Word report, complete accessible source, an actual live PHP/MySQL application and an accessible demonstration video with each member explaining their own contribution. This package supplies implementation and guidance; it does not fabricate those human deliverables.

## Report preparation template

| Required field | Actual entry |
|---|---|
| Title | NSBM SportsHub — Sports Club Management System |
| Selected topic/module | Topic 3; Web and Mobile Application Development, Batch 26.1 IFSL |
| Team leader/contact | TODO — human supplies |
| Member names/roles | TODO — actual people and accepted roles |
| Repository link | `https://github.com/Ckstr-arch/TestENV` — human verifies package commit published and lecturer access |
| Working hosted application | TODO — real tested HTTPS URL |
| Demonstration video | TODO — real accessible video URL |
| Release commit/date | TODO — actual released version, not an invented date |
| Confirmed deadline | TODO — lecturer announcement; PDF says TO BE CONFIRMED |

Write an introduction describing the student/admin sports coordination problem and concrete objectives. Explain actual source/data/request architecture using [architecture](architecture.md), then the [requirement table](requirements-traceability.md). State genuine MySQL engine/version and PHP/extensions tested. Describe concrete CRUD and student/admin examples instead of repeating technology names.

Include security mechanisms and their limits: opt-in 2FA, no email verification/reset or institutional SSO, generic club standings/individual tournament enrollment, single-unit equipment, destructive parent-record cascades and no automatic sport brackets. Distinguish the tested local package from final-host validation.

## Authorship and contribution record

The initial working application is a **Codex-assisted baseline** with inspiration/assets supplied earlier by teammates. Reading a generated guide, receiving a stage label or having a name in a table does not establish authorship of old modules. Do not claim four students independently developed the existing prototype. Disclose assistance following the course's originality/AI policy and ask the lecturer if its permitted scope is unclear.

| Real member/handle | Actual task and changed paths | Genuine commits/PR/review | Tests/explanation personally performed | Assistance disclosure |
|---|---|---|---|---|
| TODO | TODO | TODO | TODO | TODO |

Record real implementation, debugging, design, review, test and documentation work. Each person uses their own truthful Git identity and scoped feature branch, studies the patch and opens a genuine review. Prefer integration that keeps actual commits visible; verify attribution afterward. Do not manufacture cosmetic commits or reset another person's identity/work to manipulate contribution graphs.

This task's eventual commit records the package work; it does not automatically count as each member's personal contribution. The repository must actually contain the final package and be accessible before claiming source delivery. Publishing/merging/deploying are separate human-authorized actions.

## Screenshot evidence

Capture the actual final release with synthetic identities. Suggested set: homepage, filtered clubs, student dashboard, pending/approved membership, community feed, schedule, tournament enrollment, own history, facility booking/rejection, admin CRUD, approvals, scored results and reports. Include dark/light and mobile where useful.

Never capture visible passwords, `.env`, setup keys, recovery codes, cookie/token headers, raw private logs or real student contact records. Demonstrate 2FA through safe enabled/pending states and pause/redact before sensitive enrollment output. Existing parent screenshots are historical local evidence; they are not new captures of this package or the hosted release.

For every figure record its route, release SHA, environment, synthetic scenario and what it proves. A picture of a form does not prove its write persisted or access control held. Link test/row-state evidence without exporting identity-bearing SQL.

## Suggested video plan · customize after genuine work

This timing is a rehearsal aid, not a required duration or claim a recording exists.

| Suggested time | Demonstration | Person/evidence |
|---|---|---|
|0:00–0:40 | Topic/problem, actual stack and existing assisted baseline | Leader introduces real roles |
|0:40–1:40 | Student registration/login, separate portal, safe 2FA explanation | Member with actual relevant work |
|1:40–2:40 | Club search → pending request → admin approval → private club space | Show persisted state and role distinction |
|2:40–3:40 | Training/match fixture and independent tournament registration/history | Explain event versus tournament relation |
|3:40–5:00 | Admin create/read/update/delete a synthetic club/resource/fixture, paired scores/results | Show real CRUD and server validation |
|5:00–6:00 | Facility booking, overlap refusal, approval and own cancellation | Explain strict interval collision and retained history |
|6:00–7:00 | Attendance after session start, reports/standings, synthetic schema/relations | Demonstrate actual MySQL without secrets |
|7:00–8:00 | Community ownership/private report/admin hide/restore and themes/mobile | State these are additional features |
|8:00 onward | Each member explains their own actual patch/debug/test/design work and limitations | Show authentic evidence, not scripted false authorship |
|Closing | Real source/live/video access, tested scope and remaining limits | Use only verified URLs |

Prepare synthetic future dates, open capacity, two private browser contexts and expendable records. Avoid deleting a valued club whose foreign-key cascades remove participation. Select a past synthetic event for attendance; do not fabricate future attendance to make the video flow. Do not demonstrate every feature through one uncontrolled administrator session while claiming student permissions.

## Rehearsal and submission gate

- [ ] Final actual PHP/MySQL host/HTTPS works from a second authorized device/network.
- [ ] Safe synthetic student/admin identities work; no shared fixed production logins.
- [ ] CRUD writes, role refusals, membership, enrollment, booking/history/results/reports are verified.
- [ ] Setup/recovery/token/private data never appear in the recording or screenshots.
- [ ] Each real member can explain their own contribution and a central source excerpt.
- [ ] Failed/skipped tests and product limits are reported honestly using [validation](validation.md).
- [ ] Report has actual people, screenshots and accessible source/live/video links.
- [ ] Genuine contribution history and imagery/branding rights reviewed.
- [ ] PDF/Word exported and readable; video uploaded/access tested by a permitted viewer.
- [ ] Deadline confirmed, then leader/designated member submits once through required platform.

The assessment weights are UI 20%, backend/database20%, admin 15%, student 15%, report/screenshots/source/hosting 15%, video/teamwork 15%. These guides do not promise marks or replace lecturer interpretation.
