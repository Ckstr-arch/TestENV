# `docs/screenshots/admin-dark.png` · Administrator overview · dark

## 1 · File card

| Field | Inspected value |
|---|---|
| Source | [docs/screenshots/admin-dark.png](../../docs/screenshots/admin-dark.png) |
| Primary owner / reviewers | Stage 01 / affected Stage 02 or Stage 03 and team integrator |
| Type / dimensions / mode | PNG; 1440×1292 px; RGB |
| Approximate size | 147, 050 bytes |
| Baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |

## 2 · Why it exists

A real rendered development capture preserves a visual example for reviewers and learners. It is evidence of appearance at capture time, not executable code or proof of a live deployment.

## 3 · Prerequisites

Know pixels, viewport versus image dimensions, and [CSS cropping/theme](public__assets__app_css.md). For screenshots, distinguish a capture from an automated assertion or live deployment.

## 4 · Dependencies / usage

Linked from existing UI documentation and report guidance; not loaded as a runtime page asset. Dependencies: Rendered PHP views, layout, CSS, JS, development database and browser viewport.

## 5 · Complete visual/file tour

Grouped admin sidebar, pending-membership/booking/session/tournament metric cards, upcoming sessions, audit activity, moderation empty state and management shortcuts. The dark theme uses navy surfaces and lime accents. Inspect the admin role label and grouped destinations rather than assuming student navigation is reused. There are no hidden source-code functions to enumerate in this binary asset.

## 6 · Real excerpt or binary interpretation

Binary PNG pixels, not source text. No code excerpt applies. The inspected image shows Grouped admin sidebar, pending-membership/booking/session/tournament metric cards, upcoming sessions, audit activity, moderation empty state and management shortcuts. The dark theme uses navy surfaces and lime accents.

## 7 · Input → transformation → output

Input: actual development app state and viewport. Transformation: browser layout/paint and screenshot encoding. Output: static PNG evidence; state is not refreshed when the database changes.

## 8 · Connections

[Layout](app__layout_php.md), [CSS](public__assets__app_css.md), [existing UI record](../../docs/ui-and-community.md) and [report guidance](../../Assemble/13-final-report-and-screenshots.md). The relevant route/path is `/?page=admin`.

## 9 · Why this format / trade-offs

PNG preserves sharp text and UI borders but costs more bytes than some lossy formats; keep the original as visual evidence and compress only after checking legibility.

## 10 · Rights, privacy and accessibility

Existing application capture containing synthetic demo identities. Underlying supplied logo/photo usage rights still require team confirmation before public publication. No known authentication key, recovery code or password is visible in the inspected screenshot set. Captures still need a human privacy review before submission. Supply meaningful Markdown descriptions and never rely on a screenshot alone to explain a workflow.

## 11 · Common mistakes / diagnosis

- Broken image: check exact case/path and HTTP status, not secret-bearing full configuration.
- Distorted/cropped content: compare intrinsic dimensions with CSS cover/contain and container sizing.
- Stale evidence: record actual release/route/theme/viewport rather than relabeling an old image as new.
- Private data leak: use synthetic accounts and review browser chrome, menus and page text before sharing.

## 12 · How to inspect / verify

Use the [capture protocol](../diagrams/evidence-capture.md). Open the route with a real ID from your disposable database; `<approved-club-id>` above is a placeholder, not a runnable URL. This guide's image metadata and visual description were inspected; no new capture is claimed.

## 13 · Debugging lesson

Inspect Network image request and computed object-fit/background-size. For screenshots compare the actual DOM/text with source/template before blaming rendering. Record only public asset paths and synthetic state, never session or private-profile details.

## 14 · Small guided experiment

Open the corresponding local page with a disposable account, match viewport/theme, and explain one difference without changing data to imitate this image. Evidence: one labeled observation and predicted/actual result. Revert only temporary DevTools changes by reloading; do not overwrite the original evidence asset.

## 15 · Challenge ladder

Beginner: identify dimensions/format and the component using it. Intermediate: explain crop/scale/theme differences with an observation. Advanced: propose a rights-cleared optimization or accurate capture refresh with a peer review and size/quality/privacy comparison. Hint: smaller file size alone is not sufficient evidence of quality or permission.

## 16 · Knowledge check

1. Does viewing this file prove the current server works?
2. Why distinguish intrinsic pixels from viewport CSS pixels?
3. What must be confirmed before publishing supplied imagery or refreshed evidence?

<details><summary>Answers</summary>

No; a static file is not a fresh runtime test. CSS may scale/crop an image and device pixel ratios differ. Confirm rights, synthetic/private-data safety, accurate route/theme/viewport and actual evidence provenance.

</details>

## 17 · Contribution boundaries

Do not replace or retouch baseline screenshots to imply a feature exists. Coordinate asset/path changes with Stage 01 and affected views. Runtime asset edits require visual/mobile/theme checks; capture-only updates require privacy/provenance review.

## 18 · Status / limitations

VERIFIED BY INSPECTION: format, dimensions and visible subject. NOT RUN: new capture, optimization benchmark or licensing verification. Rights remain UNVERIFIED where noted. Historic image content is not a current hosted URL or test result.

## 19 · Glossary

Intrinsic size: image's stored pixel dimensions. Viewport: layout area in CSS pixels. Crop: clip part of image while filling a box. Provenance: where an artifact came from. Lossy: compression that changes pixel details. Alt: alternative text conveying image meaning.

## 20 · One-minute explanation to personalize

“I can explain where this artifact appears, what it proves, what it cannot prove, and how to inspect its dimensions, privacy and rights safely.” Add your own actual work/evidence after completing the exercise; no authorship is assigned by this guide.
