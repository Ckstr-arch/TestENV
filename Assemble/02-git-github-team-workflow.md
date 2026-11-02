# 🔗 Genuine Git/GitHub collaboration

Git stores snapshots and author metadata; GitHub hosts repositories, reviews and PRs. A branch is a movable reference to commits. A pull request proposes integrating a branch; opening it does not prove correctness. These commands are **manual future human instructions, not executed by Codex**.

> [!IMPORTANT]
> The prototype and these generated guides are assisted baseline/reference material. They are not proof any student independently wrote, understood or tested them. Record genuine learning, debugging, design, reviewed code and documentation. Follow the lecturer's AI-assistance policy. Do not retroactively distribute existing authorship.

## Fresh personal checkout

Each person uses their own authenticated account with actual collaborator access. Do not share tokens or configure another person's identity.

```bash
git clone https://github.com/Ckstr-arch/TestENV.git
cd TestENV
git status --short --branch
git switch main
git pull --ff-only origin main
git config --local --get user.name
git config --local --get user.email
```

Inspect results privately. If identity is wrong, that human configures their own actual name and verified email (or their GitHub-provided noreply address) locally; never copy a friend's identity. Attribution depends on verified metadata, repository settings and GitHub rules; it is not a grading guarantee.

`fetch` updates downloaded objects/references without integrating. `pull --ff-only` fetches and only advances a non-diverged branch; if it fails, inspect `git log --oneline --graph --all` and coordinate rather than reset work. This task used fetch only; local `HEAD` remains the earlier runtime commit. The remote master-prompt file was read and materialized locally, not merged.

## One meaningful feature at a time

Example for a human after choosing a real issue:

```bash
git switch -c feat/stage-01-card-keyboard-review
# Read source, make your own scoped change, run relevant checks.
git status --short
git diff --check
git diff -- public/assets/app.css
# Only after review of your own diff:
git add public/assets/app.css
git commit -m "fix(ui): describe the actual keyboard improvement"
git push -u origin feat/stage-01-card-keyboard-review
```

Do not blindly stage the entire cloud handoff: generated guides span all tracks. A team must first review and integrate the learning package under honest assistance attribution, then distribute real implementation tasks. Never turn four folders into four fake contribution commits.

Open a PR in GitHub UI with the [PR template](templates/pull-request.md). Include owned function names, reproduction, actual tests, safe screenshots, migration impact, own explanation and assistance disclosure. A real peer applies the [review checklist](templates/review-checklist.md). Do not fabricate approval. Keep each branch short-lived and scoped.

## Merge policy and conflicts

Prefer a reviewed **merge commit** when preserving each student's existing commits is important. Squash creates one combined commit and can obscure individual commit authors; rebase rewrites commit IDs and may complicate evidence. Never silently rewrite teammates' history. Settings and final merge are human-authorized actions. After integration, inspect `git log --format=fuller`, `git show` and GitHub attribution/PR records. Save actual links in contribution records.

When two people edit `app/views.php`, compare changes by page branch. Coordinate intended behavior, resolve the conflict in a working copy, rerun both affected suites and have both reviewers inspect. Do not accept “ours/theirs” for an entire shared file without reading the other feature. Do not use destructive resets or force pushes as routine conflict fixes.

## Suggested protection, not applied

Require peer review and passing relevant checks on `main`, disallow force pushes where supported, and give lecturer repository access. No branch settings, issues or PRs were created here. Keep current local changes intact; a human should export/review this working tree before switching branches. Do not run `git pull` on it blindly.
