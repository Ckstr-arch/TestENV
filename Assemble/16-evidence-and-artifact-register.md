# ✅ Current task evidence and unfinished human work

User-facing date: **28 October 2026, Asia/Colombo**. Baseline local `main`: `f81eab54ebe7179e1763f364d6b8d4f564c36541`. Remote specification read at `096258d302367aba198a83147c5f985fa74477b7`. Initial working tree was clean. No merge was performed; the fetched master prompt was materialized locally as an untracked document.

## Commands actually executed in this task

| Command/check | Result | Scope and limits |
|---|---|---|
| Git status/branch/remotes/log/tree; read-only fetch and remote tree comparison | PASS | 63 local tracked paths; remote adds only the master prompt. No GitHub writes. |
| pdftotext on supplied assignment PDF | READ | All four pages, Topic 3, stack, rubric and deliverables inspected. |
| `/workspace/toolchain/bin/php --version`, `-m` | PASS | Retained PHP 8.4.26, PDO MySQL, mbstring, sodium available; doctor confirms Argon2id. |
| PHP `-l` over all baseline tracked PHP files | PASS — 23 files | Syntax, not end-to-end behavior. |
| `node --check public/assets/app.js` and `theme-init.js` | PASS | JavaScript parsing. |
| `bash -n bin/cloud-start.sh`, `sh -n docker/entrypoint.sh` | PASS | Shell parsing. |
| `/workspace/toolchain/bin/php tests/security.php` | PASS — 17 checks | RFC TOTP vectors/replay/drift, secret encryption/tampering, Argon2 long-password behavior, escaping; no SQL resets. |
| `bash bin/cloud-start.sh` followed by doctor | PASS — startup readiness + 20 doctor checks | Local retained database/application only. No migration/seed executed by this script. |
| `python3 Assemble/checks/read_only_smoke.py` against the running local server | PASS — 17 checks | Eight public pages/CSP, health, two unauthenticated API gates, six private-file paths. No login or domain mutations. |
| OpenAPI validation via `PYTHONPATH=/workspace/toolchain/openapi` and `openapi_spec_validator.validate` | PASS — 77 operations | Contract schema validity, not proof of every endpoint behavior. |
| Standalone Stage 01 exercise: `python3 /workspace/test_frontend_clinic.py` | PASS — 6 Chromium cases | 900/600/390px × Grid/Flex; Enter-key toggle, aria-pressed, no overflow or page errors. Playwright `set_content`; not application browser regression. |
| Fixed-public_html layout release-copy check in temporary directory | PASS | Two patched entrypoints lint; six private includes resolve; bootstrap ROOT resolves to private directory. No canonical source edits; full Apache/host HTTP behavior NOT RUN. |
| `python3 Assemble/checks/check_learning_docs.py` | PASS — 158 Markdown files, 2,003 local links, 64 primary guides | Required templates, repository paths/anchors and baseline hashes checked; no private configuration values read. |
| `git diff --check` and new-document whitespace scan | PASS | Untracked learning files scanned separately; only intentional Markdown two-space hard breaks retained. |

## Startup and diagnostic history

The first doctor check failed because the retained DB was stopped. Starting it inside the restricted sandbox failed to bind loopback sockets. A reviewed local-start exception allowed the existing services to start; readiness and doctor then passed. A later standalone HTTP attempt found its server no longer reachable; startup and checks were rerun within one tool invocation and passed.

The standalone exercise initially hit sandbox browser-launch and managed file-URL restrictions. Its final reviewed harness loaded HTML using Playwright `set_content` and reset the page between cases; all six cases passed. No application database fixture was used.

The first private-path smoke assertion required HTTP404. PHP's development server instead returned ordinary SportsHub homepage HTML for an unknown `/.env` path. The check was corrected to distinguish homepage fallback from private-file content. All six paths returned either the ordinary homepage fallback or 403/404, **not private file contents**. Strict negative-path404 behavior on a chosen production server remains a hosting check; no security control was removed.

## Intentionally not executed

| Check/action | State and reason |
|---|---|
| Native form, REST, community and browser reset suites | SKIPPED this task. Their fixture guard and runner DB_NAME override were inspected; they drop/rebuild `sportshub_test` tables. This documentation-only change uses nondestructive checks and preserves all existing DB state. Historical counts in legacy validation are not current runs. |
| New full browser/mobile regression and screenshots | NOT RUN/captured. Existing seven real captures are historical and unchanged; syntax/read-only smoke are narrower evidence. |
| Fresh Docker build/upgrade or platform-native Windows/macOS setup | NOT RUN. Existing implementation is unchanged; no claim those environments were retested now. |
| MySQL 8 compatibility | NOT RUN. Retained local engine is MariaDB 11.8.6; assignment requires MySQL. |
| Production host, HTTPS, remote DB TLS, fixed-public_html full HTTP test | UNVERIFIED. No host chosen or deployment authorized. |
| Backup/restore, live migration, production release/rollback | NOT EXECUTED. Runbooks are planned human procedures, not results. |
| GitHub Actions | PLANNED design only; no workflow installed/executed. |
| Full Mermaid render validation and external link availability | Scope recorded in final audit below; local link checker does not test remote URLs. |
| Student names, actual contribution PRs, live/video links, final report | TODO humans. No invented identity, authorship or completed artifact. |

## Known risks and boundaries

- Mandatory MySQL versus Compose MariaDB must be resolved by tested MySQL 8 deployment or explicit lecturer approval; no data engine conversion happened.
- `review_request()` reads a request before taking the parent lock. Under repeatable-read isolation, later nonlocking capacity/overlap reads may use an earlier snapshot. This is an **inspection risk**, not a reproduced booking/approval race result. Existing sequential cases and the separate community deletion race do not prove this invariant for all concurrent approvals. A disposable concurrent regression is a proposed human task.
- Simple administrative save/delete and audit are not one atomic transaction. HTML lists have different caps/pagination than API collections. These behaviors are explained rather than silently rewritten.
- Optional 2FA, absent email verification/reset/SSO, single-node sessions, generic standings and individual tournament entries remain baseline limitations. Production security is not certified.
- Asset/brand usage rights, lecturer access, true team assignments, deadline and current host capabilities remain human confirmations.
- Private `.env`, `.env.docker`, session/key files, database records and unrelated Docker projects were not intentionally modified. Web smoke can create ordinary local session files; no domain rows were changed by these checks.

## Artifact register — actual versus pending

| Artifact | State | Location/evidence |
|---|---|---|
| Learning reference package | Local working-tree documents | Four stage directories + Assemble; root navigation/map |
| Baseline all-file map | Generated from inspected tracked tree | [Assembly map](01-repository-assembly-map.md), [JSON manifest](baseline-source-manifest.json) |
| Current local audit | Reproducible read-only checker | [Validator](checks/check_learning_docs.py), [HTTP smoke](checks/read_only_smoke.py) |
| Existing app captures | Historical actual app images, unchanged | [Legacy UI guide](../docs/ui-and-community.md) |
| Actual human contribution evidence | TODO | [Blank record](templates/contribution-record.md) |
| Release SHA/approval/live URL/migrations | TODO | [Release notes](templates/release-notes.md) |
| Report/PDF/Word/video | TODO | [Report guide](13-final-report-and-screenshots.md), [video plan](14-video-demo-and-team-speaking-plan.md) |

## Original local handoff audit — before branch publication

- Five folders contain **160 files**, including **156 Markdown documents**, one standalone HTML exercise, two read-only Python helpers and one source manifest. Root README plus LEARNING_MAP bring the learning/navigation Markdown audit to 158; the original master prompt is preserved separately.
- **64/64 baseline/specification paths** have primary guides: Stage 01 fourteen, Stage 02 three, Stage 04 twenty-seven, Assemble twenty. Stage 03 additionally has five section guides for shared admin behavior; no artificial file ownership or authorship is implied.
- **2,003 local links** passed path/anchor checks. All required stage and Assemble documents exist. No baseline path lacks a guide.
- **20 Mermaid blocks** have balanced Markdown fences. No local Mermaid renderer/parser was available; full rendered syntax/layout and external URLs remain unverified. This is a documented validation limit, not a rendered-diagram pass.
- Independent review matched 49 quoted source excerpts across Stage 01/02/04; all 20 Assemble per-file excerpts were also matched to their canonical files. Review findings corrected two navigation anchors and the new-post locking wording; concurrent-approval concerns remain explicitly inspection-only.
- Baseline preservation passed for all 62 pre-existing tracked paths other than the intentionally extended README, plus the byte-identical remote master prompt. No runtime source, schema, config, tests, legacy guides or assets changed.
- `git diff --check` passed. A separate scan of untracked teaching files found no unexpected trailing whitespace. Known legacy demo password/key strings were absent from new teaching material; original development-only legacy docs remain unchanged.
- Final branch is still `main` at the original local SHA. Working-tree changes are README navigation plus the five folders, LEARNING_MAP and materialized master prompt. All remain uncommitted. No GitHub issue/PR/settings/write, merge, deployment, test-DB reset or volume removal occurred.

## First human handoff

Read [the integration overview](00-integration-overview.md) for each contributor's first three proposed tasks and the [manual branch/PR workflow](02-git-github-team-workflow.md) before any Git writes. Review this package and its assistance attribution first. Use a clean personal checkout for future contribution branches; do not pull or switch over uncommitted work blindly. Do not copy private environment/session/data files into a contribution.

Remaining actual work: human task assignment and genuine reviewed contributions; isolated MySQL 8 compatibility/concurrency testing; full app/browser and rendered-diagram review; chosen host capability and image rights confirmation; separately authorized real deployment/restore rehearsal; real report/screenshots/video/lecturer-access/deadline evidence.

At the original local handoff, no commit, push, merge, deployment or student-authorship assignment had occurred.

## Subsequently authorized branch publication — 28 October 2026

The user subsequently requested: “Push the changes into new branch”. That explicitly authorizes a documentation commit and push to `docs/sportshub-learning-stages`. The branch was created from the fetched remote commit `096258d302367aba198a83147c5f985fa74477b7`, which only adds the byte-identical master prompt above the audited runtime baseline. Local and remote `main` are not being updated by this publication.

Before staging, the learning checker was adapted to recognize the new teaching documents and four learning artifacts once tracked. It still rejects unaccounted runtime/config/test paths and verifies baseline hashes. Publication checks cover the staged content and preservation; the earlier runtime outcomes remain dated results, not fresh reruns. Standard Markdown hard breaks in added guides may be represented with `<br>` to keep Git whitespace checks clean without changing their rendered meaning.

The commit describes Codex-assisted learning material; no Git identity is changed, and no student authorship or independent understanding is assigned. No PR, merge, deployment, database reset or volume deletion is authorized by this push request. Exact publication outcome and commit link are reported in the chat after remote verification, rather than preclaimed in this document.
