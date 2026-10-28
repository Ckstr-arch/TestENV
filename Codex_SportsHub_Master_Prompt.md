# CODEX MASTER PROMPT — NSBM SportsHub: Learn, Build, Contribute, Assemble

> **Intended use:** Paste this entire prompt into Codex with the **root of `Ckstr-arch/TestENV` open**. If possible, provide the NSBM WAD assignment PDF as additional context. Codex is being asked to modify the repository **locally**, create a learning-first stage system, and produce verifiable results **without committing, pushing, or deploying on anyone's behalf**.
>
> **Repository:** https://github.com/Ckstr-arch/TestENV.git  
> **Selected assignment:** NSBM Green University, Faculty of Computing, Web and Mobile Application Development, Batch 26.1 IFSL, **Topic 3 — NSBM SportsHub: Sports Club Management System**.  
> **Goal:** Four individual, rigorously documented learning-and-contribution stages plus exactly one top-level folder named **`Assemble`** for integration, deployment, hosting, validation, report and demonstration.  
> **Philosophy:** *Teach us the code. Preserve what already works. Separate responsibility without falsifying authorship. We are students learning the project, not outsourcing our understanding.*

---

## 0. Your role and definition of success

Act simultaneously as a senior PHP/MySQL engineer, frontend mentor, database instructor, Git/GitHub collaboration lead, technical writer, quality engineer, security reviewer and patient undergraduate tutor.

We already have a functioning Codex-assisted prototype. **Do not rebuild it from scratch, rebrand it, simplify away implemented functions, or replace its architecture merely because a different architecture would be easier to explain.** Inspect the real repository first. Your job is to turn the existing source into four **well-separated responsibility/learning tracks** that our four contributors can study, implement changes in, test independently, and integrate safely, plus one deployment/assembly track.

A successful result must satisfy **all** of these simultaneously:

1. The current application remains operable; existing data and important behavior are preserved.
2. Four stage folders exist at the root, each assigned a distinct primary contribution domain, with a complete roadmap and real code walkthroughs.
3. A root-level folder named **`Assemble`** contains the complete integration, local setup, PHP/MySQL hosting, production release, rollback, verification, documentation, report and video guide.
4. **Every relevant existing source/config/test file** is included in a traceable inventory and has a deep explanatory guide in its primary stage or `Assemble`. Shared files must have a clear primary guide and cross-stage references.
5. The learning material explains the **actual code in this checkout**, not invented `home.html`/`style.css` examples that do not exist here.
6. Each contributor has clear bounded work, a separate branch/PR workflow, evidence of real learning and implementation, and an individual contribution record.
7. No automated fake authorship, fake commits, misleading contribution claims, or unauthorized GitHub writes occur.
8. All tests executed and findings reported honestly. Do not invent pass counts, successful deployments, screenshots, URLs, or credentials.
9. The assignment requirements are traceable to actual implementation, tests, report evidence and video demonstrations.
10. Documentation is attractive, navigable, **colorful but accessible**, GitHub-readable and technically correct.

**Do the substantial work, not just an outline.** Create actual Markdown documents, directories, tables, diagrams, code excerpts, checklists, exercises and examples. Avoid filler and repeated generic tutorials. If file sizes require multiple writing passes, continue until the full agreed scope is complete; do not silently stop after creating empty folder structures.

## 1. Source of truth, inspection sequence and explicit project facts

### 1.1 Read the real checkout FIRST

Before editing anything:

- Check `pwd`, `git status --short`, `git branch --show-current`, `git remote -v`, `git log -8 --format='%h %an <%ae> %s'`, and the tracked file tree. Do not alter uncommitted work.
- Read `README.md`, `docs/architecture.md`, `docs/ui-and-community.md`, `docs/api.md`, `docs/security.md`, `docs/local-setup.md`, `docs/project-report.md`, `docs/validation.md`.
- Examine `public/`, `app/`, `database/`, `bin/`, `tests/`, `docker/`, `Dockerfile`, `compose.yaml`, `.env.example`, `.env.docker.example`, `.gitignore`, `.dockerignore`.
- Inspect actual function declarations, PHP view branches, form fields, SQL tables and foreign keys, CSS selectors/media queries, JS event handlers, API endpoints, scripts and tests. Collect actual evidence; do not assume code from this prompt is exhaustive or unchanged.
- Read any newly provided assignment instructions if available. If the assignment PDF is not in your filesystem, use the mandatory facts embedded in §2 below. **Do not pretend you read a file you cannot access.**
- Create a concise baseline map and record the current commit SHA in the generated documentation.
- **Confirm the repository state before editing.** If it has uncommitted files, preserve them, work around them safely and report what was already modified. Never reset or overwrite unknown user changes.

### 1.2 Known prototype architecture (verify against checkout)

The baseline repository is **NSBM SportsHub, Topic 3**. It is not a plain static HTML project.

- `public/index.php` is the PHP website entrypoint/page router. It selects a `page`, handles traditional form POST, enforces authentication/roles, and renders the layout.
- `app/views.php` contains the publicly rendered homepage/hero, club browsing, schedules, registration/login and other page markup. **The homepage HTML is written within PHP views. There is no requirement to invent a `home.html` file.**
- `app/portal-views.php` contains student dashboard, club spaces/community, admin dashboard and moderation/audit views.
- `app/layout.php` and `app/ui.php` provide the shared document shell, sidebars, navigation, controls and UI helpers.
- `public/assets/app.css` owns the design system, responsive layouts and component styles.
- `public/assets/theme-init.js` initializes saved theme before CSS to avoid flashing the wrong theme.
- `public/assets/app.js` handles navigation drawer/focus, theme, form-to-REST enhancement, `fetch`, CSRF headers and response/UI handling.
- `public/router.php` routes the built-in PHP development server; `public/api.php` is the public JSON API entrypoint; `app/api.php` implements REST dispatch and request/response behavior.
- `app/bootstrap.php` owns configuration, PDO helpers, roles, session/CSRF helpers, validation utilities and Sri Lanka timezone behavior.
- `app/actions.php` owns mutation dispatch and shared form-oriented business actions; `app/models.php` validates/administers domain records; `app/community.php` owns club community behavior; `app/security.php` owns TOTP/2FA and associated protections.
- `database/schema.sql` and `database/migrations/20261017_club_communities.sql` define the initial relational database and additive community migration.
- `bin/` includes setup, migration, seed, demo users/community, admin creation, doctor, OpenAPI generator and cloud-specific helpers.
- `tests/` includes security, integration, REST, community and browser coverage.
- `compose.yaml`, `Dockerfile`, `docker/` provide local containerization; the current Compose database is MariaDB, a MySQL-compatible engine. The **assignment explicitly says MySQL is mandatory**; document that distinction and recommend a MySQL 8 production target or ask the lecturer for explicit approval if the final submitted DB is MariaDB. Do not silently replace a working local DB or assert that compatibility automatically satisfies the marking requirement.
- Current frontend identity uses navy/lime Performance Dark with a light theme, responsive portals and community spaces. Preserve it unless a specific defect requires an agreed correction.
- Current API is under `/api/v1`; API writes use session cookies and CSRF, and native PHP POST forms provide fallback.
- The current code is PHP server-rendered with HTML/CSS/vanilla JavaScript; do not add React/Next/Vue, Node build tooling or a Composer framework to make documentation look modern.

**IMPORTANT:** Existing files, routes and feature coverage may have changed. Use the live checkout as the final truth. Clearly mark every proposal not yet implemented as **PLANNED**, never as **EXISTING**.

### 1.3 Existing behavior that must not silently disappear

Protect student signup/signin, admin/student permissions, sports-club discovery, membership approval, schedules, tournaments/registrations, match results/standings, participation tracking, facility/equipment bookings and overlap prevention, admin CRUD, reports/audit trail, opt-in authenticator TOTP 2FA/recovery, club community posts/comments/reactions/reports/moderation, light/dark theme, mobile navigation, CSRF and REST/native form parity. Audit the exact list against current files rather than assuming every bullet is functional.

## 2. Assignment requirements: these are hard constraints

The NSBM Faculty of Computing WAD final project instructs groups to select **one** topic. We selected **Topic 3 — NSBM SportsHub: Sports Club Management System**. **Four stages are our internal contribution/learning organization, NOT four separate assignment topics.**

### 2.1 Required technology and general function

Use **HTML, CSS, JavaScript, PHP (mandatory) and MySQL (mandatory)**. Bootstrap may be used, but is not required. Show a proper relational database, PHP/MySQL integration, complete relevant CRUD, separate admin/student interfaces, secure login, responsive professional UI, validations, consistent navigation and applicable search/filter functionality.

Topic 3 student functionality: register/login; browse/join sports clubs; view training schedules and match fixtures; register for tournaments/matches; view results/standings; book facilities/equipment; track individual participation history.

Topic 3 admin functionality: login; manage sports clubs and membership approvals; schedule training/matches; create/manage tournaments/fixtures; record results/scores; manage facilities/equipment booking requests; generate participation/performance reports.

### 2.2 Deliverables and collaboration

- Report as PDF or Word: title, team leader/contact, member names and **true roles**, introduction/objectives/system description, real key screenshots of admin/student views, actual accessible repository URL, actual live application URL and demonstration video URL.
- Complete source code on GitHub; **commit history should reflect each member's contribution**.
- Public repository, or otherwise accessible to lecturer.
- **Real live working PHP/MySQL-compatible deployment** with links that the lecturer can open.
- One recorded demonstration showing login, admin panel, student panel, CRUD, database, main features and a **brief explanation by each member of their own contribution**.
- One leader/designated member submits the final report.
- The deadline in the document is tentative/**TO BE CONFIRMED**; do not invent a date.
- Work must be original, genuinely collaborative and properly documented.

### 2.3 Evaluation rubric: maintain visible traceability

| Criterion | Weight |
|---|---:|
| UI design and frontend | 20% |
| Backend and database integration | 20% |
| Admin panel functionality | 15% |
| Student/user panel functionality | 15% |
| Report, screenshots, GitHub and hosting links | 15% |
| Video demonstration and teamwork | 15% |

Produce an `Assemble/assignment-traceability.md` table with **requirement → actual file(s) → user-visible route → test evidence → owner stage → video moment → completion state**. Fill only things you can verify; use TODO for real-host/report items awaiting humans.

## 3. Non-negotiable guardrails

### 3.1 Academic integrity and authorship — HIGHEST priority

- **Never impersonate a student in commits, PRs or documentation.** No fabricated team names, GitHub handles, dates, test results, contribution counts or code authorship.
- Do not set `git config user.name`, `user.email`, `GIT_AUTHOR_NAME`, `GIT_COMMITTER_NAME`, or `Co-authored-by` to pretend someone wrote code they did not write.
- Codex can generate **reference material, scaffolding and suggested changes**, but generated code and documentation are **not proof a student independently wrote, understood or tested them**. State this explicitly in the contribution guide and report template.
- A teammate's contribution record must reflect their **real reviewed implementation, debugging, tests, explanations, documentation or design work**; do not distribute pre-existing prototype authorship retroactively.
- Existing Codex-assisted code is a **baseline**. Mark it as baseline, not as newly authored stage code.
- Do not prepare final presentation scripts falsely claiming four students each independently developed pre-existing modules. Write honest evidence fields and explain how to cite assistance in line with course policy.
- Do not make four cosmetic commits to manipulate activity/contribution graphs.
- Team member names, handles and assignments remain `Member A/B/C/D` placeholders until entered by humans. If the team size is not four, explain how to redistribute stages without inventing people.

### 3.2 Git/GitHub operations — explicit permission required

**This task authorizes local documentation/source changes only. It does NOT authorize a push, commit, pull request creation, deployment, merge or branch-protection change.**

- Do **not** run `git add`, `git commit`, `git push`, `git merge`, `git rebase`, `git reset --hard`, `git clean -fd`, `git push --force`, `git branch -D`, `git tag -f`, or write to the remote.
- Do not change remote URLs, default branch or Git identity, and do not create GitHub issues/PRs unless a human separately asks.
- You may run read-only Git commands. If you genuinely need a new local working branch, describe it and request permission instead of silently switching.
- Never overwrite uncommitted changes or undo another contributor's work.
- Do not silently squash, rebase, amend or rewrite existing team commits. Explain how different GitHub merge strategies affect preservation of individual commit metadata; for this course, favor a **merge strategy that keeps actual individual commits visible** and verify GitHub attribution after merging.
- For future human actions, specify authentic personal Git account, owned feature branch, appropriately scoped commits, genuine PR review, passing tests and protected `main` where available. Attribution is evidence, not a grading guarantee.
- If Git remote authentication is unavailable, produce manual instructions. Never request tokens in Markdown, terminal arguments or screenshots.

### 3.3 System/data/security safety

- Never drop/reset the real `sportshub` database. Integration tests may be destructive to **`sportshub_test` only**; verify the database name before running them. If separation cannot be proven, skip the destructive tests and explain why.
- Never run `docker compose down -v`, `docker volume rm`, `docker system prune --volumes` or equivalent destructive commands. An existing database, local container and encryption key must survive updates.
- Never commit `.env`, `.env.docker`, keys, passwords, API tokens, recovery codes, private exports, SQL dumps containing identities, local session folders or real student records.
- Never print private secrets in a report or log. Demo credentials from legacy docs are **development-only**, not for live hosting; recommend removal/rotation and a fresh production database.
- Keep a private `APP_KEY` stable for users already enrolled in 2FA. Explain securely backing it up and the consequences of losing/rotating it improperly.
- Preserve PDO prepared statements, parameter validation, HTML escaping, CSRF, role/ownership checks, session rules, rate limits, secure 2FA handling, and database transactions/invariants.
- Do not expose `app/`, `database/`, `bin/`, `tests/`, `.env`, `var/` or hidden environment files to public HTTP. Web document root should resolve to **`public/`**.
- Never assume free hosts support PHP extensions, private file locations, CLI, migrations, HTTPS, database features or persistent storage. Verify each host against actual requirements; mark unknown capability **UNVERIFIED**.
- Respect rights to use images, fonts and brand marks. The repository's supplied `nsbm.png` and `track.jpg` must have usage rights confirmed before public deployment. No unapproved external image embeds.

### 3.4 Engineering scope

- Do not fork the application into four diverging runtime copies, create four duplicate production databases or install incompatible frameworks.
- Do not convert the PHP-rendered homepage to a made-up static `home.html` merely to teach HTML. Explain `<section>`, `<article>`, `<h1>`, forms, cards etc. **inside the real `app/views.php`** with representative excerpts, DOM diagrams and the emitted HTML. A tiny **nonproduction exercise** named `home.html` is fine if clearly labeled as an exercise, never the actual production homepage.
- Keep **one canonical runtime** in existing `app/`, `public/`, `database/`, `bin/`, etc. Four new root stage directories are contributor/learning packages with accurate source maps, not a second deployable application.
- Any code improvements must be limited, justified, tested, backward-compatible and disclosed separately. Favor **documentation + small demonstrable exercises** over an unnecessary sweeping refactor.
- Avoid adding paid services, unrelated cloud providers, dependencies or unnecessary large assets.
- If the observed implementation differs from this prompt, preserve reality and document discrepancies rather than modifying reality to match examples.

## 4. Exact top-level outcome: four stage folders + `Assemble`

Keep current application directories intact. Create these five **new top-level folders** (names must match):

```text
TestENV/
├── Stage-01-Frontend-UX/
├── Stage-02-Student-Experience/
├── Stage-03-Admin-Operations/
├── Stage-04-Backend-Database-Security/
├── Assemble/
├── app/                         # existing canonical PHP source
├── public/                      # existing canonical document root
├── database/                    # existing canonical schema and migrations
├── bin/                         # existing utilities
├── tests/                       # existing test suites
├── docker/                      # existing Apache/container helpers
├── docs/                        # preserve and link existing original docs
├── compose.yaml                 # existing Compose root, preserve
├── Dockerfile                   # existing container build, preserve
└── README.md                    # add a concise guided navigation section
```

**Do not rename existing runtime files just to force ownership.** There is one running application. Stages are learning tracks and genuine contribution boundaries. A stage's `files/` directory contains **per-file explanation `.md` files linking to real source**, not shadow copies of production PHP/CSS/JS files. For appropriate hands-on teaching, use `exercises/` with self-contained demo/snippet exercises and clearly distinguish them from deployed source.

### 4.1 Required folder template for EACH stage

Each stage gets at least the following (expand where warranted; **no empty placeholders disguised as completed docs**):

```text
Stage-0X-<Name>/
├── README.md                     # beautiful start page, outcomes, time-independent milestones
├── 00-start-here.md              # prerequisites, system tour, study order, setup
├── 01-architecture.md            # exact code/HTTP/database interactions & diagrams
├── 02-source-inventory.md        # exhaustive relevant file map and canonical links
├── 03-learning-roadmap.md        # lessons: beginner → intermediate → advanced
├── 04-guided-walkthroughs.md     # full user journeys traced across layers
├── 05-labs-and-exercises.md      # hands-on exercises, hints, expected output
├── 06-testing-and-debugging.md   # runnable tests, breakpoints, safe failure cases
├── 07-contribution-contract.md   # files/functions owned, shared edit policy, PR checks
├── 08-knowledge-check.md         # short and difficult questions + separated answers
├── 09-completion-evidence.md     # unfilled human evidence/commit/PR/demo record
├── files/                        # one accurate source-specific guide per relevant file
│   ├── <path__to__real__file>.md
│   └── ...
├── diagrams/                     # Mermaid .md if separate diagram pages help
└── exercises/                    # optional SAFE isolated learner exercises
    └── README.md
```

Adapt file slugs consistently, e.g. `files/public__assets__app_css.md` explains `/public/assets/app.css`. Include source backlinks and semantic headings. Binary files (photos/images) get **asset usage, format, provenance/rights and optimization docs**, not invented line-by-line code documentation. Generated `openapi.json` gets a contract/how-to-regenerate guide, not a manual recreation of hundreds of generated lines.

### 4.2 The `Assemble` folder MUST contain

```text
Assemble/
├── README.md
├── 00-integration-overview.md
├── 01-repository-assembly-map.md
├── 02-git-github-team-workflow.md
├── 03-local-development-macos.md
├── 04-local-development-windows-linux.md
├── 05-docker-compose-and-data-safety.md
├── 06-php-mysql-deployment-options.md
├── 07-production-deployment-runbook.md
├── 08-secrets-security-2fa-and-https.md
├── 09-database-migration-backup-restore.md
├── 10-ci-tests-and-quality-gates.md
├── 11-host-troubleshooting-and-rollback.md
├── 12-assignment-traceability.md
├── 13-final-report-and-screenshots.md
├── 14-video-demo-and-team-speaking-plan.md
├── 15-release-checklist.md
├── 16-evidence-and-artifact-register.md
├── 17-glossary-and-faq.md
├── files/                      # source/config/deployment file-specific guides
├── diagrams/                   # browser→PHP→DB, Git→PR→test→deploy, schema etc.
└── templates/                  # honest PR, release notes, checklists, report tables
```

Also create a **single** concise `LEARNING_MAP.md` at repository root if useful, and a root README navigation section with links to the five new folders. Do not erase legacy documentation; cross-link it and state which guide is canonical for what.

## 5. The file-by-file teaching standard (MANDATORY for EVERY relevant file)

A file guide is not a paraphrased list of filenames. Explain enough that a first-year software engineering student can open the actual source and follow the logic independently. For each source file, include **all applicable** sections:

1. **File card:** canonical relative path, stage owner, secondary reviewers, language, approximate size, role, last inspected baseline commit, actual link.
2. **Why it exists:** role in the application and consequences if missing.
3. **Prerequisites:** concepts needed, with links to simple explanations.
4. **Imports/dependencies:** included/required files, PHP variables, env keys, SQL tables, JS selectors, HTML forms, API calls, assets.
5. **Detailed tour in source order:** functions, branches, templates, data structures, selectors, media queries or commands; cover **every meaningful section**, not the first 20 lines only.
6. **Real annotated excerpts:** use short accurate excerpts **copied from the actual file** and explain syntax line-by-line. Do not invent code as if it is present. On huge files, split into many meaningful sections and explain each function/selectors group.
7. **Input → transformation → output:** what enters, what happens, what's rendered/written/returned.
8. **Connections:** clickable relative links to the callers/callees, request paths, related schema tables, JS callbacks and tests. Show exact call chain.
9. **Why this implementation?** Trade-offs, why relevant API/property is used, alternatives and why they are not selected here.
10. **Security, correctness and accessibility:** trust boundaries, validation, auth, escaping, CSRF, keyboard behavior, focus, ARIA, contrast where relevant.
11. **Common mistakes / failure diagnosis:** 3–8 realistic bugs, symptoms, tools, and safe fixes.
12. **How to run it:** minimal, accurate setup and non-destructive verification commands (if execution makes sense).
13. **Debugging lesson:** breakpoint/logging strategy **without logging passwords, tokens, recovery codes, cookies or personal data**.
14. **Small guided experiment:** what to change safely, what to predict, how to test, and how to restore without touching real data.
15. **Challenge ladder:** beginner, intermediate, advanced. Provide hints and expected observable outcomes; don't automatically solve the human's contribution task.
16. **Knowledge check:** at least three reasoning questions, including one code-flow or failure-case question, with answer key distinctly separated.
17. **Contribution boundaries:** safe edit areas, shared-review requirements, required tests and what must not be changed.
18. **Current status & limitations:** VERIFIED BY INSPECTION / EXECUTED & PASSED / NOT RUN / PLANNED, backed by honest evidence.
19. **Glossary:** unfamiliar terms expanded in plain English.
20. **One-minute explanation:** the way a student could explain this file at a viva/video, **to be personalized after actual work**, not a false claim of authorship.

For long source files like `app/views.php`, `app/portal-views.php`, `app/api.php`, `app/actions.php`, `public/assets/app.css` and `public/assets/app.js`, use expandable subsections or subsidiary chapters where useful. **All major actual functions and page branches must be accounted for.** Provide a coverage index listing names/sections and guide anchors; explicitly flag any uncovered region.

**Teach progressively:** first the mental model, then real excerpt, then how browser/server interprets it, then how the next file consumes it, then a safe practice task. Assume knowledge of basic programming but do not assume the reader already understands CSS positioning, SQL transactions, PHP sessions, HTTP, or Git workflows.

## 6. Stage 01 — Frontend, UI/UX and web fundamentals

**Primary track:** Understand the visible site from real emitted HTML → CSS → JavaScript DOM → responsive behavior → PHP template integration. This contributor has first responsibility for presentation/accessibility improvements, not for inventing a second static frontend.

### 6.1 Source files and sections to cover

Primary guides for:

- `public/assets/app.css` — **FULL structural walkthrough:** roots, tokens, dark/light themes, typography, nav/sidebar, desktop/mobile layouts, cards, grids, forms, tables, states, buttons, animation/transitions, focus, print and media queries.
- `public/assets/app.js` — DOM selection, optional chaining, event listeners, navigation drawer, focus handling, keyboard support, theme control, POST form enhancement, `FormData`, `fetch`, JSON, CSRF header/response, error rendering, redirect and any remaining functions.
- `public/assets/theme-init.js` — script timing, localStorage theme only, flash prevention and storage failures.
- `app/layout.php` — PHP renders `<head>`, stylesheet/scripts, navigation, menus, student/admin shell, responsive semantics, security-related output.
- `app/ui.php` — inspect and teach actual helper functions including `icon`, `initials`, `club_url`, `community_form`, `metric` if still present.
- `public/assets/nsbm.png`, `public/assets/track.jpg` — usage paths, alt text, rights checks, image sizing and optimization, no accidental publication of unlicensed assets.
- `docs/screenshots/*` — actual UI evidence and how to refresh screenshots accurately, excluding secrets; explain that binary files have no executable logic.

Cross-referenced guides, not duplicated ownership, for `app/views.php` homepage markup, `app/portal-views.php` dashboard markup, `public/index.php`, `public/router.php` and REST submit flow.

### 6.2 Extremely detailed HTML lessons rooted in the home page

Use the **actual home branch in `app/views.php`**, likely including the hero, `stats-strip`, `hero-copy`, `hero-photo`, `content`, club cards and `feature-banner` (verify names). Explain:

- What `<html>`, `<head>`, `<body>` are and which real file produces them.
- Semantic `<header>`, `<nav>`, `<main>`, `<section>`, `<article>`, `<h1>`–`<h3>`, `<p>`, `<span>`, `<div>`, `<a>`, `<img>`; meaning vs visual appearance.
- How `.class`, `#id`, data attributes, form name/action/hidden fields, buttons, links, ARIA and alt text work.
- DOM hierarchy with a Mermaid/ASCII tree; map at least 8 real elements to their exact CSS selectors and rendered result.
- How PHP-generated lists (`foreach`), database values, output escaping (`e()`), conditional empty states and hardcoded marketing copy coexist in the rendered HTML.
- An end-to-end example: source PHP snippet → emitted HTML structure → matching CSS rules → the element on screen → JavaScript interaction if applicable.
- Show that dynamic counts are database-derived; no fabricated counters or fake animation metrics.

### 6.3 CSS masterclass: make it exceptionally thorough

Explain **with accurate before/after visual descriptions and executable mini exercises**:

- Cascade, specificity, inheritance, source order, shorthand, box model, content/padding/border/margin, margin collapse and `box-sizing`.
- CSS custom properties (`--variable`), `var()`, design tokens and exact dark/light root selectors from this app.
- Selector families: element, `.class`, `#id`, descendant, child `>`, pseudo-classes, pseudo-elements, attribute selectors; show how specificity resolves a real conflict.
- `display: block`, `inline`, `inline-block`, `none`, `flex`, `grid`; what happens to width, children and flow.
- Flexbox: axes, `flex-direction`, `justify-content`, `align-items`, `gap`, `flex-wrap`, sizing and shrink/grow; trace a real navbar.
- Grid: tracks, `grid-template-columns`, `repeat`, `minmax`, `fr`, `auto-fit`, `auto-fill`, gap and responsive card grid; trace a real dashboard.
- Positioning: `static`, `relative`, `absolute`, `fixed`, `sticky`; containing blocks, stacking contexts, `z-index`, overflow and why a mobile drawer covers content correctly.
- Sizing: pixels, `%`, `rem`, `em`, `vw`, `vh`, `dvh`, `max-width`, `min-width`, `clamp()`, responsive type and touch targets.
- Colors/contrast, gradients if actually used, borders/shadows, `object-fit`, `aspect-ratio`, image cropping and the navy/lime palette.
- Media queries/breakpoints and behavior at 390 px/mobile, tablet and desktop; browser devtools responsive testing, avoiding horizontal overflow.
- `transition`, `transform`, `animation`/`@keyframes` **only if the source uses them**, and `prefers-reduced-motion`.
- `:focus-visible`, accessible states, disabled/hidden behavior, print styles and why keyboard access matters.
- A real computed-style walkthrough for `.hero`, `.hero-photo`, `.card`, `.sidebar`, `.theme-toggle` and a form control, adjusting actual selectors to the checkout.
- Add an annotated **CSS property glossary** grouped by function with real examples, effect, common mistake, and location in source.

Do not write “Flexbox aligns things” and move on. Explain the layout algorithm conceptually and connect it to a **specific existing component**.

### 6.4 JavaScript fundamentals grounded in `app.js`

Teach variables/const, arrays/objects, functions, arrow functions, events, optional chaining, `querySelector` vs `querySelectorAll`, `classList`, DOM attributes, event `preventDefault`, `FormData`, `JSON.stringify`, async/await, Fetch, HTTP method, credentials, headers, status/error handling, theme storage, focus traps and user interaction. Map a real click to a real handler and final UI state.

Clarify that **frontend validation is for usability; server validation is mandatory and authoritative**. Explain exactly why the API uses a CSRF token and why passwords must never go in `localStorage`.

### 6.5 Stage 01 learning deliverables

- A source-mapped **homepage anatomy diagram** and CSS selector-to-element table.
- An interactive-by-instructions responsive breakpoint lab with predictions and observations; safe sandbox examples may use tiny isolated HTML/CSS/JS files.
- Color-contrast and keyboard/focus test checklist.
- A genuine proposed small UI improvement task for a human member, **not pre-credited**.
- A walkthrough of *one complete DOM → API* mutation without claiming the UI alone authorizes it.
- Stage tests: desktop/mobile page flows, theme persistence, CSS changes, no overflow, forms and keyboard navigation; record what actually ran.

## 7. Stage 02 — Student experience, authentication UI and community journeys

**Primary track:** Follow a student from registration through clubs, schedules, tournaments, facilities, participation and community using the actual PHP-rendered views and shared backend contracts. This is **student-facing workflows**, not reimplementation of Stage 04's security primitives.

### 7.1 Files and sections to cover

Primary student-focused walkthroughs:

- `app/views.php`: homepage as contextual reference, **login/register/2FA input UI**, club discovery/search/filter, public schedules, tournaments/results, facilities, student-facing forms, participation and navigation destinations. Cover every applicable page branch in a source index.
- `app/portal-views.php`: student dashboard, `my_clubs`, club overview, membership status/gating, community feed, members/schedule, student-facing content; explicitly separate from admin branches owned in Stage 03.
- `app/community.php`: approved-membership access logic, posting, editing, deleting, commenting, reacting and reporting; Stage 03 owns moderation UI and reviews its impact.
- `app/actions.php`: **student action mappings and the native form path** (shared file; Stage 04 maintains the canonical technical API/action guide).
- `tests/browser.py` and appropriate student cases in `tests/integration.py`, `tests/api.py`, `tests/community.py` (test ownership shared; cite exact tests).

Cross-reference `public/assets/app.js`, `app/bootstrap.php`, `app/security.php`, `app/api.php`, `database/schema.sql` rather than copying their canonical descriptions.

### 7.2 Required journeys, each step-by-step

1. Registration form → input names → JS/native POST → backend validation → password hashing/role assignment → SQL write → resulting page; distinguish UI from server enforcement.
2. Login → session creation/rotation → optional 2FA challenge → permission to enter dashboard. Do not expose real secrets or suggest insecure bypass.
3. Discover a club → query/filter → student application → admin review → approved membership → members-only content.
4. Club community: viewing/gating → post → comment → react → report; explain ownership and moderation effects.
5. View training/matches → apply registration rules → record/view participation; separate tournament registrations from event registration where the app does.
6. Tournament registration → deadlines/capacity → results/standings.
7. Booking facility/equipment → requested interval → collision/capacity logic → admin decision → cancellation/status.
8. Mobile version of at least two journeys.

For **each** journey include UI route, real form fields, HTTP path/method, PHP functions, SQL tables, success state, failure state, test coverage and which stage is responsible for changing it.

### 7.3 Student curriculum

Explain forms/GET/POST, cookies, sessions, 303 redirects, HTML escaping, role-limited pages, one-to-many/many-to-many relationships, lifecycle states (`pending/approved/rejected`), safe handling of user-generated text, pagination, authorization vs authentication and why client-displayed controls do not equal access control.

At least one exercises chapter must require a learner to predict an HTTP/SQL change from reading the existing files and then verify it on a **disposable local account**. Provide safe rollback instructions and a quick oral demonstration guide.

## 8. Stage 03 — Admin operations, domain rules, reports and moderation

**Primary track:** Administration of sports operations and the real CRUD flows, including approval, scheduling, results, bookings, moderation and reporting. Admin changes must be reviewed for backend authorization, not assumed safe because only admins see a UI button.

### 8.1 Files and sections to cover

- `app/portal-views.php` admin dashboard, management menus, moderation queue and audit views (cover every current admin branch).
- `app/views.php` actual admin-management page branches, approvals, reports and CRUD forms (inspect and cross-link, do not invent pages).
- `app/models.php` actual allowlisted entities, validation rules, admin CRUD, transactional behavior and SQL write boundaries (shared with Stage 04).
- `app/actions.php` admin actions and approval/rejection, record-save/delete, scores and attendance (shared canonical guide with Stage 04).
- `app/community.php` admin hide/restore, report review and reasons (shared with Stage 02).
- Tests in `tests/integration.py`, `tests/api.py`, `tests/community.py` that exercise admin operations.
- `database/schema.sql` for relationship impact and cascade effects (canonical SQL guide in Stage 04).

### 8.2 Required admin walkthroughs

- Create, read, update, delete for clubs, tournaments, training/match events, facilities/resources as actually implemented. Build **CRUD matrices** containing route, view, action, SQL table, validation and test.
- Approve/reject membership with limits and ownership rules.
- Manage tournament registrations, deadlines, fixtures, scores and displayed standings.
- Record attendance and participation report generation, distinguishing SQL aggregates from inferred statistics.
- Evaluate booking requests/overlap rejection and race conditions.
- Moderate reported community content, supply reasons, hide/restore, and keep audit records.
- Search/filter, status transitions, confirmation handling and deletion consequences, particularly foreign-key cascades.
- Demonstrate that a non-admin **cannot** access admin endpoints even by manually sending HTTP requests.

### 8.3 Concepts to teach

HTTP 401 vs 403 vs 404 vs 422 vs 409 (where relevant), RBAC, safe admin dashboard design, server-side validation, transactions, `SELECT ... FOR UPDATE` if used, unique constraints, race conditions, idempotence, audit logs, pagination, SQL aggregation, reports, data retention, destructive vs reversible actions, and why “Delete” may cascade.

Every CRUD exercise must explain what rows change, how to verify in **disposable data**, and how to avoid ruining team data. Include a reviewer checklist for cross-stage changes and one practice failure triage per CRUD entity.

## 9. Stage 04 — PHP backend, MySQL, REST API, security and verification

**Primary track:** The deep server/data contract on which the other three stages depend. Teach the “why” of code, not merely the names of security technologies. This is a large stage; split its guide into ordered, achievable modules and explicitly ask human teammates to share review load.

### 9.1 Primary file map (verify completeness)

- `app/bootstrap.php` — configuration, PHP process boot, environment loading, PDO, prepared query helpers, sessions, timezone, auth/role helpers, redirects, CSRF, escaping, validation and errors.
- `app/security.php` — password/security-related helpers, 2FA/TOTP computation, encryption key management, enrollment/replay/recovery, throttling as actually implemented.
- `app/api.php`, `public/api.php`, `public/router.php` — public entrypoints, REST route matching, status codes, JSON parsing, size/field restrictions, cookies/CSRF, output contract and failure behavior.
- `app/actions.php`, `app/models.php`, `app/community.php` — full mutation/service boundaries and server-authoritative business rules; Stage 02/03 each document their domain-specific usage.
- `public/index.php` — PHP page dispatch, role-gating and native forms/exception handling; shared with frontend.
- `database/schema.sql` and every `database/migrations/*.sql` — tables, columns, constraints, indexes, relationships, migrations and preservation strategy.
- `bin/setup.php`, `bin/migrate.php`, `bin/seed.php`, `bin/create-admin.php`, `bin/demo-users.php`, `bin/demo-community.php`, `bin/doctor.php`, `bin/build-openapi.py`, `bin/cloud-start.sh`, `bin/container-init.php` — practical purpose, dev vs production safety, inputs/outputs, dependencies.
- `tests/security.php`, `tests/integration.py`, `tests/api.py`, `tests/community.py`, `tests/browser.py`, `tests/fixtures.php` — exact scope, prerequisites, destructive/non-destructive classification, expected evidence and limitations.
- `public/assets/openapi.json` — generated contract and generation/check procedure.
- `.env.example`, `.env.docker.example`, `.gitignore`, `.dockerignore`, `.gitattributes` — configuration/privacy and portability (deployment-specific canonical guides may reside in `Assemble/files/` with cross-links).
- `Dockerfile`, `compose.yaml`, `docker/apache.conf`, `docker/entrypoint.sh`, `docker/init-test.sql` — read for architecture; **deployment/operations primary guides live in `Assemble/files/`**.

Make a **complete tracked-file coverage audit**. Do not ignore a script just because it is short or “internal”. If a file cannot be assigned to an instructional stage, assign a primary guide in `Assemble/files/`, then link back to the relevant stage.

### 9.2 PHP and HTTP lessons

Explain `<?php`, `declare(strict_types=1)`, variable and array syntax, functions/type declarations, `require` vs `require_once`, scope, return values, `throw`, `try/catch`, exceptions, request superglobals, sessions, cookies, response headers, status codes, redirect semantics and separation of controller/service/view.

Trace both supported mutation paths with actual code:

```text
Path A: Browser native <form method="post"> → public/index.php
       → app/actions.php → validation/authorization → PDO/SQL
       → flash/303 redirect → server-rendered page

Path B: JavaScript enhanced form → fetch('/api/v1/...')
       → public/api.php → app/api.php → shared services/actions
       → PDO/SQL → JSON response → frontend update/redirect
```

Validate the exact call graph first. Give real examples from join-club and create/update-admin flows. Explain why progressive enhancement exists and what breaks when JavaScript is disabled.

### 9.3 MySQL and database lessons

For **every real table**, generate a readable data dictionary: field/type/nullability/default, primary/foreign/unique/index constraints, relationships, owner, affected screens, sample **synthetic** row and CRUD actions. Use Mermaid ER diagram that matches actual schema, including community migration tables. Teach `CREATE TABLE`, `INSERT`, `SELECT`, `UPDATE`, `DELETE`, `JOIN`, `WHERE`, `ORDER BY`, `GROUP BY`, indexes, SQL injection, prepared statements, atomicity, transactions, foreign keys, cascade rules, normalization, cardinality, migrations and schema version history.

Add a guided **request → query → row change → response** example for join club, tournament enrollment and booking overlap. Explain `InnoDB`, row locks, unique constraints and time-zone handling. Make queries runnable **only against a designated disposable study database**; label destructive SQL loudly and never offer `DROP DATABASE sportshub` as a casual reset.

### 9.4 API and auth lessons

- `GET /auth/session`, cookie sessions/CSRF bootstrap, POST/PUT/PATCH/DELETE, content types, JSON shape, error schema and status codes as actually implemented.
- Explain `fetch` credential behavior, Origin checks, safe status handling, why same-origin matters and why CORS wildcard is inappropriate here.
- How `bin/build-openapi.py` generates `public/assets/openapi.json`, and how to test one endpoint using browser devtools and a client preserving cookies safely.
- Password hashing/Argon2id; why not plaintext or reversible password storage.
- 2FA RFC-style concepts: secret, time step, one-time code, encryption of secrets, replay checks, recovery-code hashing/single-use, and what a private `APP_KEY` does.
- Session fixation mitigations, CSRF vs XSS vs SQL injection vs authorization bypass, rate limiting, logging boundaries and server/client validation.
- Document actual limits and remaining security gaps from `docs/security.md`; don't claim “production secure” or certify the app.

### 9.5 Stage 04 verification

Explain and safely run applicable commands when the environment supports them:

```bash
php --version
php -m
php -l public/index.php
php -l public/api.php
php bin/doctor.php
php tests/security.php
# Below are destructive ONLY to the isolated test database; inspect config first.
python3 tests/integration.py
python3 tests/api.py
python3 tests/community.py
# Browser suite optional and dependency-specific.
python3 tests/browser.py
```

`README.md` currently documents runtime PHP 8.2+, PDO MySQL, mbstring, sodium and Argon2id; MySQL 8+ or compatible MariaDB for local work; **verify exact current requirements**. Record each command as EXECUTED/PASS, EXECUTED/FAIL or SKIPPED with factual reason. Never copy historic test numbers into a new execution report as if freshly verified.

## 10. Ownership contract, shared-file change policy and individual GitHub contributions

Create a contributor matrix like this and refine from the actual source:

| Stage | Human contributor | Primary responsibility | Expected concrete contribution evidence | Required secondary review |
|---|---|---|---|---|
| Stage 01 | Member A (TBD) | UI, semantic HTML, CSS, responsive/JS experience | Actual changes to UI or accessibility + tests + explanation | Stage 02 or 04 when shared forms/API touched |
| Stage 02 | Member B (TBD) | Student flow, clubs, community, registrations | Actual student-related code/test/doc changes | Stage 04 for auth/API/SQL boundary |
| Stage 03 | Member C (TBD) | Admin CRUD, approvals, fixtures, reports/moderation | Actual admin-related code/test/doc changes | Stage 04 for security/transaction boundary |
| Stage 04 | Member D (TBD) | DB, PHP/API/security, migrations and test infrastructure | Actual server/data/test changes and safety proof | Stage 02/03 for behavior; all for schema changes |
| Assemble | Team jointly | Integration, hosting, report/video, verification | Verifiable deployment steps, screenshots, real working live URL, members' explanations | All four |

Explain that `app/views.php`, `app/portal-views.php`, `app/actions.php`, `app/models.php`, `app/community.php`, `public/assets/app.js` and `database/schema.sql` may affect more than one stage. **Ownership is by feature/section and code review, not by pretending each file is unique to one person.** Build an explicit shared-file contract: before editing, name functions/branches, notify other owner, use separate PRs, preserve current behavior, run relevant tests, resolve conflicts together and update docs.

### 10.1 Required actual Git/GitHub workflow guide

Document with **real examples but NEVER execute writes automatically**:

```bash
# Each teammate, using their own authenticated GitHub account:
git clone https://github.com/Ckstr-arch/TestENV.git
cd TestENV
git status
git switch main
git pull --ff-only origin main

# Example ONLY: choose a meaningful per-contributor feature name.
git switch -c feat/stage-01-responsive-card-accessibility

# After personally changing, understanding and testing code:
git status --short
git diff --check
git diff
# Stage ONLY the actual files you worked on, not blindly all files.
git add <specific-paths-you-actually-changed>
git commit -m "feat(ui): improve mobile club card keyboard navigation"
git push -u origin feat/stage-01-responsive-card-accessibility
# Then OPEN A PULL REQUEST via GitHub UI; get a human review.
```

Teach `git clone`, `fetch`, `pull --ff-only`, `switch`, `branch`, `status`, `diff`, `add`, `commit`, `push`, `log`, `show`, PR, review, merge and conflicts in simple terms. Teach how `git config --local user.name` and `user.email` should accurately match a student's **own** identity and verified GitHub account email, including GitHub's private noreply option; do **not** assign identities for them.

Require each future human PR to include:

- Student's own username (human supplies); task/issue link if one exists.
- Changed source paths/functions and **why** the change was made.
- Manual steps to reproduce and tests **actually run** with results.
- Before/after screenshots for UI, no secrets or fake numbers.
- DB migration safety notes if relevant; no live resets.
- Explanation in the student's own words of a central code excerpt.
- Review acknowledgment for shared code; honest help/AI-assistance disclosure following module policy.

**No one is credited solely because their name appears in a table or because Codex creates a folder for them.** The existing prototype must remain attributed as existing baseline. Show how to review git logs and PRs, but do not promise GitHub contribution charts or assessments will automatically assign credit. Explain that commit authorship metadata can be lost/misrepresented by careless squash/merge choices and must be checked.

### 10.2 Branch management and release discipline

Recommend one member branch per feature/issue, not a month-long shared branch. Use PRs into `main`, require at least one peer review if repository settings allow, prefer passing checks, avoid direct pushes to `main`. **No fake approvals or self-declared reviews.** Integration conflicts go through code review and a designated integrator, retaining genuine commit metadata. Explain that Codex **must stop before any remote-changing action**.

Provide `Assemble/templates/pull-request.md`, `Assemble/templates/contribution-record.md`, `Assemble/templates/review-checklist.md`, `Assemble/templates/release-notes.md`. Leave human identity and activity fields empty.

## 11. The full `Assemble` guide — integration and web deployment

### 11.1 Explain the physical assembly correctly

Create a diagram and table showing how existing production root files are assembled:

```text
Browser
  ├─ HTML/CSS/JS ⇄ public/assets/*
  ├─ rendered pages → public/index.php → app/layout.php + app/views.php / portal-views.php
  └─ REST writes → public/api.php → app/api.php / app/actions.php / app/models.php / app/community.php
                                             ↓
                                    app/bootstrap.php / app/security.php
                                             ↓
                                           MySQL
```

Correct actual includes/calls after inspection. Distinguish **learning stage folders** (not served, not deployed, not included in PHP autoload) from canonical source directories. Do not instruct a team member to copy code from four stage folders into production on every run.

### 11.2 Local development: separate precise instructions

**macOS (special priority):** Docker Desktop path and Homebrew PHP + MariaDB/MySQL-native path, each with exact prerequisites; safe copy `.env.example` or run `php bin/setup.php`, generate/retain private key, create local DB, migrate, seed only dev, run doctor, start PHP server, view port, check health. Do not assume `localhost` vs `127.0.0.1` are interchangeable for the app's Origin check. Show port conflict diagnosis. Preserve the current user's other Docker projects by documenting Compose project name, port overrides and named volumes. Use modest resources; avoid installing Node/Composer needlessly.

**Windows:** compatible XAMPP/PHP or Docker Desktop; explain PHP extensions and supported WSL2 backend where Docker Desktop requires it; show PowerShell syntax and correct document root. **Linux:** compatible PHP packages, MySQL/MariaDB and Docker Compose, permissions and services. Do not give a dangerous blanket `chmod -R 777` workaround.

Distinguish local PHP `-S` server from production Apache/PHP-FPM. Document route rewriting and `public/router.php` vs `.htaccess` behavior. Include the actual health endpoint `/api/v1/health` and troubleshooting for DB/CSRF origins.

### 11.3 Docker: protect existing data and other projects

Based on real `compose.yaml`, document app/db services, private-loopback bind port, database health gate, named `db_data` and `app_data` volumes, and server state (database, sessions, key). Show `docker compose --env-file .env.docker config --quiet`, `up --build -d`, `ps`, `logs --tail`, `exec ... php bin/doctor.php`, a safe `stop`/`up` cycle, backups and restore. Document exactly how to keep the same Compose project name/override across upgrades, particularly on macOS.

**NEVER** suggest `down -v` for an upgrade. Explain why deleting a volume can erase users, bookings, session data and the 2FA encryption key. Document how to inspect volume names *without revealing secrets*. Avoid touching unrelated containers, image caches or services.

### 11.4 Compare possible PHP/MySQL hosting methods, without invented promises

Build a decision table for:

A. Verified shared hosting with PHP 8.2+, MySQL 8, required extensions, HTTPS, private files, persistent state, migrations and CLI/cron or documented alternatives.
B. A controlled Linux VPS (potential cost and maintenance) running Apache or Nginx/PHP-FPM + MySQL, with TLS/backups; explain responsibilities.
C. A compatible managed container host with persistent database/private volume and real PHP support, **only if actually supported**.
D. Free hosts **only after checking current provider limits**. If web access is unavailable, mark provider capability/pricing UNVERIFIED and provide a host capability questionnaire instead of asserting that InfinityFree, 000webhost, Render, Heroku or another provider definitely works. A free tier is not automatically production-suitable.

Document that **GitHub Pages and Cloudflare Pages static hosting alone cannot execute the required PHP/MySQL backend**. Likewise, the PHP development server and a Mac laptop running Docker are not reliable public course submission hosts on their own. No false “deployment successful” claim based on local localhost access.

### 11.5 Real production release procedure

In `Assemble/07-production-deployment-runbook.md`, produce a sequential practical checklist including:

0. Prerequisite/deployment feasibility check and host extension matrix. Confirm PHP 8.2+ (or current app minimum), PDO MySQL, `mbstring`, `sodium`, Argon2id, MySQL 8, database charset/collation, HTTPS, private writable session path, ability to run migrations safely, reliable logs/backups, webroot configuration.
1. Freeze/review release commit, confirm code/tests and verify **no tracked secrets**.
2. Create dedicated **fresh** production MySQL database/user(s), using least privilege and separate migration privileges where supported; do not copy demo users.
3. Generate and securely store an entirely new **production** `APP_KEY` (base64 of 32 random bytes), provision production settings privately, set real HTTPS `APP_URL`, safe logs, cookies, and `DEMO_MODE=0` or equivalent.
4. Deploy the entire PHP app **outside** public webroot, exposing **only** contents of `public/` as document root. For a fixed `public_html` host, document private/app dir mapping and required bootstrap path update with a tested example; never blindly upload `.env` into `public_html`.
5. Prepare writable **non-public** `var/sessions` with minimal correct permissions and durable storage. Ensure restarts do not erase required state.
6. Take verified backup/snapshot before applying any existing-production migrations; inspect applied schema history and run **only additive intended migrations**, using proper privileges. Do not reset production to test.
7. Create a real administrator via secure server-side CLI/private provisioning procedure, not web-accessible public setup script or URL parameters. Enroll administrator 2FA and store recovery codes privately.
8. Verify `/api/v1/health`, homepage assets, HTTPS, correct Host/Origin/CSRF behavior, registration/login/2FA, distinct roles, CRUD, approvals, score/report/booking and mobile flow using **synthetic test data**.
9. Check XSS/SQL injection/CSRF/auth protections operationally, error messages not leaking secrets, private paths inaccessible over HTTP, logging and rate-limit assumptions.
10. Capture real URL, screenshots and redacted test evidence; ensure lecturer access. Remove test accounts/data if appropriate.
11. Configure backups, restore rehearsal, uptime/error review, session cleanup/retention, rollback route and emergency key/data recovery.
12. Record release SHA, migration version, human approver and deployment date only when actually known.

Include safe diagnostics for HTTP 403/404/419/422/500/503, wrong `APP_URL`, missing PHP extensions, PHP route rewrites, SQL connection issues, JS asset 404s, static file path mismatches, permissions, unavailable sessions and unexpected 2FA failures. Never recommend exposing stack traces or removing security checks on production as a “fix”.

### 11.6 Tests, CI and quality gates

Propose simple GitHub Actions validation compatible with project requirements, but do not add a workflow that fails or leaks secrets merely for show. If creating a CI file, inspect dependencies, runner compatibility, database service setup and current tests; use disposable DB and synthetic credentials. Do not run destructive tests on a real DB. If CI cannot realistically run in this environment, provide a **reviewed proposed workflow** instead of claiming it passes. Document PHP syntax checks, test suites, basic security checks, DOM/mobile smoke and manual tests. A test matrix must distinguish unit/integration/browser/manual and local vs production.

### 11.7 Submission and demonstration evidence

Create a report preparation guide aligned to the real rubric and template, including: title/team leader contact, each real team member and role, problem/requirements, architecture (Mermaid ER + request flow), tech stack, tested functions, screenshots, honest limitations, code repository link, real hosted link and video URL. Do not fill unknown people or links with fake examples.

Create a timed **suggested** video outline that each member can customize *after* learning and contributing. Include student registration/login, admin/student UI, club approval, fixtures/tournaments, CRUD, bookings, reports/database proof, 2FA without showing secrets, and **each teammate explaining their own actual work**. Include demo-safe fake/synthetic identities and a rehearsal checklist.

## 12. Documentation visual design — colorful GitHub-compatible guides

We want guides that feel like a premium coding bootcamp, not a monochrome wall of text, and remain legible in GitHub dark/light mode.

Use **GitHub-Flavored Markdown** consistently:

- Navy + lime-inspired palette from current project for *illustrative Mermaid diagrams*, e.g. deep navy `#0B1220`, vibrant lime `#B8F34A`, light slate `#E8EFF5`, and semantic colors for source→process→database. Verify contrast; don't depend on color alone.
- Short tasteful emoji signposts (`🧠` Concept, `🔍` Source, `🧪` Try It, `⚠️` Safety, `✅` Verify, `🔗` Flow) with text labels.
- GitHub block admonitions: `> [!NOTE]`, `> [!TIP]`, `> [!IMPORTANT]`, `> [!WARNING]`, `> [!CAUTION]`. Use them for real distinctions, not every paragraph.
- Syntax-highlighted code fences `html`, `css`, `javascript`, `php`, `sql`, `bash`, `powershell`, `diff`, `mermaid`. `diff` may show added/removed lines, but **clearly label them illustrative** when not an actual repo diff.
- Mermaid flowcharts, sequence diagrams, ERDs, state diagrams and Git workflow, with accurate edges and accessible labels; use `classDef`/styles sparingly and ensure readability on GitHub.
- Progress checklists, learning-level indicators, collapsible `<details><summary>Answer / hint</summary>` if rendering supports it, comparison tables and table-of-contents links.
- Avoid arbitrary inline `<style>`, JavaScript inside Markdown, invisible remote tracking pixels, externally hosted CSS/JS, and HTML techniques that GitHub sanitizes or strips. For colors, prefer supported code highlighting and Mermaid; use optional SVG diagrams only if safe and self-contained.
- Favor real code/file links and diagrams over decorative stock images. Document screenshots as **real app captures only**.
- Maintain consistent terms, file slugs, relative links, admonition semantics, diagram colors, icon legend and section headings throughout all five folders.
- Explain concepts in plain natural English, with technically precise definitions. Avoid inflated marketing, generic AI filler and invented references.

A stage README should look like a learning dashboard: mission → outcome → source map → prerequisites → suggested learning order → labs → tests → contribution rules → evidence checklist, with working anchors and relative links. The reader should never wonder which guide to open next.

## 13. Detailed teaching artifacts and challenge design

Within each stage, provide a **progressive 3-level curriculum**:

- **Level 1 (Understand):** annotated example, vocabulary, trace an existing flow, 5–10 minute safe inspection task.
- **Level 2 (Modify):** small scoped improvement, hypothesize expected behavior, implement manually, run focused tests, compare before/after.
- **Level 3 (Engineer):** troubleshoot an edge case, reason about performance/security/accessibility, propose a reviewed implementation, explain trade-offs and demonstrate in a real PR.

For each practical lab include: objective, source files, prerequisites, setup, exact commands where appropriate, expected outcome, guiding questions, safety warnings, test/evidence to save and how to **revert only the learner's temporary changes**. Never auto-edit production to “answer” every human exercise. Provide answer keys separately in the learning docs; do not hide essential safety information behind them.

Build at least these cross-cutting capstones:

1. **Homepage anatomy:** real PHP template → output HTML DOM → cascade → responsive rendering.
2. **CSS breakpoint clinic:** desktop layout vs mobile drawer/cards with Grid/Flex/positioning.
3. **Student membership lifecycle:** browser form → API/native action → memberships table → admin approval → gated content.
4. **Admin CRUD lifecycle:** create tournament/fixture → validation → SQL → visible UI → edit/delete behavior.
5. **Booking collision concurrency:** why transactions/constraints/locking matter, safely reasoned using test data.
6. **Login + 2FA journey:** cookie/session/CSRF/roles, no secret leakage.
7. **Git feature contribution:** personal branch, genuine scoped patch, review and merge with preserved author metadata.
8. **Production release simulation:** private config, public document root, migration plan, health checks, backups and rollback, no live credentials.

Use UML-style sequence diagrams only when accurate; a detailed Mermaid `sequenceDiagram` for UI→API→SQL is encouraged.

## 14. Execution plan Codex must follow

### Phase A — Read-only audit and baseline inventory

- Collect Git state, current tree and existing docs.
- Generate a matrix of **all tracked paths**, categorized as runtime source, assets, config, DB, tests, utility, legacy docs, generated code or binary. Mark primary teaching guide location for **every path**.
- Inspect actual requirements, source logic and test safety.
- Note conflicts, missing docs and any ambiguous source ownership.
- **Do not alter runtime before the map is finished.**

### Phase B — Design the four stage tracks

- Choose precise responsibility boundaries based on existing actual functions/views.
- Make the shared-file review matrix and roadmap.
- Create four folder skeletons and `Assemble`, then fill them with substantive material. Directory-only scaffolding does not count as completion.

### Phase C — Produce Stage 01 and Stage 02 deeply

- Write real file guides first, then source maps, code paths, labs and exams that link to those guides.
- Give exceptional teaching detail for HTML/CSS/Grid/Flex/positioning/responsiveness/JS, rather than short definitions.
- Verify actual code snippets and links after writing.

### Phase D — Produce Stage 03 and Stage 04 deeply

- Admin CRUD, permissions, reports and moderation.
- Backend/REST/PHP/MySQL/transactions/2FA/security, utilities/tests and data dictionary.
- Ensure every meaningful major function and SQL table is covered.

### Phase E — Produce `Assemble`

- Integrate architecture, local dev for OSes, Docker safety, hosting choices, production instructions, CI/test review, release, rollback, report, video and original-submission guardrails.
- Explain exact deployment layout and why stage folders are learning resources, not alternate webroots.

### Phase F — Link audit and consistency pass

- Validate every Markdown link/relative path, diagram syntax where possible, command safety and stage ownership table.
- Check no `.env` secret examples are accidentally real, no screenshots show private credentials, and no false completed dates/URLs.
- Ensure the same file does not have contradictory “source of truth” explanations across folders.
- Check `README.md` now routes students clearly to the stages and Assemble.
- Produce a generated `Assemble/01-repository-assembly-map.md` inventory matching the actual tree and code.

### Phase G — Testing without destruction

- Run safe documentation link checks and PHP syntax checks, and applicable non-destructive tests.
- Inspect test database configuration **before** optional integration tests; only run if isolated and safe.
- If browser dependencies or PHP/MySQL unavailable, record SKIPPED with exact reason rather than pretending tests passed.
- Check `git diff --check`, `git status --short`, and summarize changed/added files.

### Phase H — Final local handoff; NO GITHUB WRITES

In your final response, show:

1. **Exactly what was created**, listing all five folders and important documents.
2. Baseline commit, branch and whether there were pre-existing local changes.
3. Every file covered and any documented gaps.
4. Key diagrams, lessons and exercises available per stage.
5. Test commands actually run and actual results; skipped reasons.
6. Any current risks, implementation mismatches, uncertain host/assignment compliance questions.
7. **The first three suggested human tasks** for each contributor, clearly not yet assigned/completed.
8. Exact **manual** next steps to create truthful individual branches/PRs and integrate.
9. A very explicit statement: **"I did not commit, push, merge, deploy or assign student authorship."** (Unless the user later explicitly authorizes a particular action in another turn.)

If a limit prevents finishing, report a precise progress and coverage matrix; do not assert completion. Prioritize thorough correctness of the real project over sheer document count. Continue with the remaining requested docs when execution capacity permits, rather than creating fake or empty content.

## 15. Final acceptance criteria: fail the job if any of these are missing

- [ ] `Stage-01-Frontend-UX/`, `Stage-02-Student-Experience/`, `Stage-03-Admin-Operations/`, `Stage-04-Backend-Database-Security/`, `Assemble/` exist and are linked from root README.
- [ ] Each stage includes all essential README/roadmap/architecture/inventory/labs/test/ownership/evidence guides listed above.
- [ ] Relevant source/config/test assets have accurate per-file guides; a coverage audit confirms no unaccounted important tracked file.
- [ ] `app/views.php` homepage is taught **as PHP producing HTML**, not incorrectly represented as an existing `home.html`.
- [ ] CSS guide thoroughly teaches `display`, Flexbox, Grid, all positioning models, breakpoints, theme variables, cascade, spacing, units, transitions and accessibility using actual components.
- [ ] JS guide traces real DOM listeners and `fetch`, cookies, CSRF and error handling.
- [ ] PHP/API guide traces both native form and REST mutation flows with actual source links.
- [ ] Database guide covers every existing table, primary/foreign keys, indexes, constraints, migration safety, relationships and real query lifecycles.
- [ ] Student and Admin workflows cover all mandatory Topic 3 functions and state transitions.
- [ ] Security docs cover password hashing, sessions, CSRF, authorization, XSS prevention, SQL injection defenses, 2FA and private-key handling accurately.
- [ ] `Assemble` explains genuine MySQL/PHP public deployment, local macOS/Windows/Linux, Docker data-preserving updates, tests, backups/rollback, report and video.
- [ ] Current MariaDB local container vs assignment's **mandatory MySQL** is explicitly identified, not hidden.
- [ ] Stage ownership and **genuine individual contribution PR workflow** are clearly specified. No team member or commit is fabricated.
- [ ] Colorful docs use GitHub-supported Markdown/Mermaid without sacrificing accessibility or truthfulness.
- [ ] No live/personal secrets, default demo credentials, unverified hosting claims, invented URLs, fabricated scores or false screenshots.
- [ ] No destructive database/container command was executed; no unauthorized GitHub or hosting write.
- [ ] Existing working features and private data were not intentionally damaged; regression status stated accurately.
- [ ] `git diff --check` and documentation link validation outcomes are reported.
- [ ] The final response includes created paths, factual tests, pending human tasks and the no-push/no-commit affirmation.

## 16. Important output-style instructions

Write like an excellent programming lecturer mentoring first-year undergraduates: explain **why** before **how**, define jargon the first time, connect abstract concepts to the specific SportsHub lines, use small runnable examples and safe experiments, and ask readers to predict outcomes. Be exceptionally detailed where students usually struggle (CSS, PHP form handling, REST/CSRF, sessions, SQL joins, database transactions, Git collaboration and hosting). Do not drown the documentation in shallow repeated paragraphs. Prefer depth and verified examples to volume for its own sake.

Do not ask unnecessary clarifying questions. You have explicit folder names, a selected assignment topic and a source checkout to inspect. Unknown names, hosts, credentials and real contributions must remain honestly unfilled template fields. Work safely with what is available.

**Start now with read-only repository inspection and the complete tracked-file coverage map. Then produce the five folders and the deep, real-source-based guides. Remember: four learning stages; one canonical application; one `Assemble`; genuine individual authorship; no autonomous commit/push/deploy.**
