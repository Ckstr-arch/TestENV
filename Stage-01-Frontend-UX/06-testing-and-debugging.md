# 06 · Test what a user can observe

No runtime behavior was changed by this stage package. **Inspection is not an executed browser test.** Read the current root handoff in [quality gates](../Assemble/10-ci-tests-and-quality-gates.md) for commands actually run this session. Existing [October 17 validation](../docs/validation.md) is historic evidence.

## Focused commands from repository root

```bash
node --check public/assets/app.js
node --check public/assets/theme-init.js
php -l app/layout.php
php -l app/ui.php
git diff --check
```

Node is optional syntax tooling, not an application build dependency. Syntax checks cannot prove a menu opens, an API accepts a form or contrast is adequate.

The optional `python3 tests/browser.py` suite requires Playwright/Chromium, configured PHP/database and isolated `sportshub_test`. **Do not run it before reading its fixture configuration and Stage 04's test safety guide.** It resets the disposable test schema; no real student records belong there. Run it sequentially with other database suites. Do not treat its historic pass as your own execution.

## Manual observation matrix (record PASS/FAIL/NOT RUN)

| Case | Expected observation | Result |
|---|---|---|
| Public home 1440 and 390 | Meaningful headings/cards, no document overflow | NOT RUN by learner |
| Student/admin nav | Correct links; protected access still enforced server-side | NOT RUN by learner |
| Theme twice + reload | Dark/light token changes; same saved choice | NOT RUN by learner |
| Theme in second tab | `storage` event updates control and root | NOT RUN by learner |
| Storage unavailable | Toggle works this page; no uncaught failure | NOT RUN by learner |
| Drawer at 800, 801; Escape/scrim | Correct visibility, scroll state, focus return | NOT RUN by learner |
| Tab/Shift+Tab | Visible focus; drawer boundary wraps | NOT RUN by learner |
| Native vs REST form | Same domain result/error for synthetic account | NOT RUN by learner |
| Long post/name | Wraps without hiding actions or overflow | NOT RUN by learner |
| Reduced motion + print | Drawer motion removed; report controls omitted in print | NOT RUN by learner |

## Contrast and accessibility procedure

Check computed foreground/background in both themes using a contrast checker; record ratios for body, muted, text links, buttons and focus. Aim WCAG AA 4.5:1 normal text and 3:1 large text; interface boundaries/focus need appropriate non-text contrast. Passing one pair does not certify the whole interface. Include hover/disabled/badge/photo-overlay cases and check that state is conveyed with text as well as color.

Use browser Accessibility tree for control names, landmarks and aria-current. Empty logo alt is appropriate because adjacent brand text names the link; the meaningful homepage track photo has descriptive alt. CSS background images are decorative. Confirm enlarged text and 200% zoom as well as narrow viewport.

## Debugging ladder

1. **Missing node: ** check page branch/current role, then emitted HTML. CSS cannot display HTML that PHP did not render.
2. **Node present but invisible: ** inspect hidden attribute, display, visibility, transform, media queries and ancestor overflow.
3. **Wrong color: ** inspect root data-theme, token definition, winning rule and hardcoded overrides.
4. **Click does nothing: ** check selector matches, deferred script loaded, event listener and console errors. Do not log FormData or API bodies containing credentials.
5. **Request fails: ** inspect method/path/status; preserve headers privately, never screenshots of tokens/cookies.403 suggests CSRF/Origin/role, 422 validation; network catch may mean a committed write with lost response.
6. **Mobile focus stuck: ** inspect aria-modal, body nav-open and width transition; resize past 800 should close state.

Safe breakpoints: click-handler first line, endpoint mapping and response status branch. Use synthetic values and avoid expanding sensitive locals. Record only route/method/status, selector, viewport and reproduction steps.

## Executed stage-package checks · 28 October 2026

- `node --check public/assets/app.js` and `node --check public/assets/theme-init.js`: passed.
- `/workspace/toolchain/bin/php -l app/layout.php` and `... -l app/ui.php`: passed.
- Standalone exercise Chromium check: **six** viewport/layout combinations (900/600/390 px × Grid/Flex) passed state/text, Enter-key toggle and no-horizontal-overflow assertions; no JavaScript errors.

The initial Chromium sandbox launch was blocked by process permissions. Running the local browser outside that sandbox succeeded, then managed browser policy blocked `file:` navigation. The final harness loaded the HTML directly with Playwright `set_content` and reset the page between cases; this verifies the exercise, not file-URL hosting or the SportsHub application. A harness reset issue was corrected before the passing run. No database/API/Git write occurred. Human contrast/assistive-device testing remains NOT RUN. Root handoff records whole-project checks separately.
