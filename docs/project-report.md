# NSBM SportsHub — Project report template

Module: Web and Mobile Application Development · Batch 26.1 IFSL\
Selected topic: 3 — Sports Club Management System

## Team

Team leader: [Name]\
Contact details: [Email / phone]

| Member | Role | Actual contribution and GitHub commits |
|---|---|---|
| [Name] | [Role] | [Work completed] |

Record each person's real work. Do not invent commits or contributions. Keep your friends' original frontend contributions credited.

## Introduction and objectives

SportsHub brings club membership, campus training, tournaments and resource reservations into one accessible web application. It helps students discover sports and track participation, while the sports office maintains schedules, approves requests and monitors club activity.

Objectives: deliver the mandated HTML/CSS/JavaScript/PHP/MySQL stack; separate student/admin workflows; implement database-backed CRUD; provide responsive navigation and server validation; add authenticator 2FA for stronger login security and a documented JSON REST API for frontend actions.

## System description

Adapt the implementation overview and ER diagram in `architecture.md`. Include the security controls and explain the difference between development validation and a live deployed system.

| Manual topic 3 requirement | Implemented location |
|---|---|
| Register/login | Register, Sign in, Account security |
| Manage clubs and membership approvals | Admin → Clubs; Approvals |
| Training and matches | Schedules; Admin → Training & fixtures |
| Tournaments and fixtures | Tournaments; Admin tournament/session CRUD |
| Results and scores | Results; fixture score fields |
| Facility/equipment bookings | Facilities; Approvals |
| Participation/performance reports | Reports; Results standings |
| Browse/join clubs | Clubs with search/sport filters |
| Register tournaments/matches | Tournament and session registration |
| Participation history | My activity |

## Screenshots

Insert your own screenshots of: homepage, club search, registration/login, 2FA enrollment, student dashboard, schedule, tournament registration, booking, administrator CRUD, approvals, recorded results, and reports. Do not include private keys, recovery codes, real student details or credentials in screenshots.

## Validation

Workspace testing: 17 security checks, 71 HTTP/MySQL checks, 130 REST API checks, Chromium desktop/mobile public-page checks. Add your real-host validation results and screenshots after deployment. Explain any limitations from `security.md` honestly.

## Required links

- GitHub source: [Actual accessible repository URL]
- Live hosted application: [Actual PHP/MySQL hosting URL]
- Demonstration video: [Actual unlisted YouTube or accessible video URL]

## Demonstration sequence

1. Introduce the problem and each member's contribution.
2. Register and sign in as a student; demonstrate authenticator 2FA (without revealing secrets).
3. Apply to a club; approve the request from the administrator portal.
4. Register for training and a tournament; show the student activity history.
5. Demonstrate create/edit/delete for a club and a fixture; record match scores.
6. Request and approve a facility booking; show overlapping slot rejection.
7. Record attendance and show reports/standings.
8. Explain the relational database and secure form handling.

## Submission checklist

Complete the report with actual names and links; export to PDF/Word. Verify the lecturer can access the source repository, live application and video. Team leader/designated member submits via LMS. The supplied manual leaves the tentative deadline unconfirmed; check the lecturer's current announcement.
