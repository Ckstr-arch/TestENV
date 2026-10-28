# Stage 03 · Admin operations 🛡️

[Learning map](../LEARNING_MAP.md) · [Integration and release](../Assemble/README.md)

**Mission:** explain and safely improve how the sports office manages clubs, approvals, fixtures, results, resources, participation and community concerns. A button is a request; the backend decides whether it is allowed.

**Human contributor:** Member C (TBD). Existing prototype code is baseline. This generated learning package does not establish anyone's authorship or competence.

## Outcomes and study order

| Step | Open next | Observable outcome |
|---|---|---|
| 1 | [00 · Start here](00-start-here.md) | Separate admin permissions, pages and data from student experience |
| 2 | [01 · Architecture](01-architecture.md) | Trace both request paths and approval locks |
| 3 | [02 · Source inventory](02-source-inventory.md) | Find every admin branch and its canonical guide |
| 4 | [03 · Learning roadmap](03-learning-roadmap.md) | Move from reading to a bounded genuine contribution |
| 5 | [04 · Guided walkthroughs](04-guided-walkthroughs.md) | Explain complete CRUD, validation and reporting behavior |
| 6 | [05 · Labs and exercises](05-labs-and-exercises.md) | Predict, inspect, test and save synthetic evidence |
| 7 | [06 · Testing and debugging](06-testing-and-debugging.md) | Distinguish role, state, validation and infrastructure failures |
| 8 | [07 · Contribution contract](07-contribution-contract.md) | Agree shared-file changes and review responsibilities |
| 9 | [08 · Knowledge check](08-knowledge-check.md) | Defend design decisions without reading an answer |
| 10 | [09 · Completion evidence](09-completion-evidence.md) | Record only actual personal work, tests and review |

## Source guides

These are section guides; the primary whole-file guides remain with Stage 02/04.

- [Admin rendered forms, approvals and reports](files/app__views_php__admin_sections.md)
- [Admin dashboard, moderation and audit](files/app__portal-views_php__admin_sections.md)
- [Entity validation and approval locks](files/app__models_php__admin_sections.md)
- [Admin actions and transport parity](files/app__actions_php__admin_sections.md)
- [Moderation and deletion invariants](files/app__community_php__admin_sections.md)
- [Operational state and request diagrams](diagrams/admin-flows.md)

## Before a lab

Understand HTML form names, basic PHP arrays and SQL SELECT/UPDATE. Complete one [local setup](../Assemble/README.md); use synthetic disposable accounts and records. No Node/framework is needed. The current Docker DB is MariaDB; the assignment requires MySQL. Use [the hosting decision guide](../Assemble/06-php-mysql-deployment-options.md) for a verified MySQL 8 target or lecturer clarification.

> [!CAUTION]
> Never delete real clubs/resources to practice CRUD. Parent deletion cascades child data. Never run resets or container-volume deletion. Test runners may reset only a verified disposable `sportshub_test`.

## Suggested first three human tasks — unassigned, not completed

1. Explain the existing membership approval lock and record a paper trace of two reviewers competing for the last club slot.
2. Reproduce the future-attendance UI/backend mismatch on synthetic data and propose one narrow usability improvement, with Stage 02/04 review.
3. After agreement, personally implement the scoped improvement, run focused native/REST checks and prepare a truthful feature PR with an explanation of the actual patch.

## Evidence checklist

- [ ] I can show all four CRUD entities and explain row/cascade effects.
- [ ] I can explain401/403/404/409/422 with real source paths.
- [ ] I can distinguish session registration, attendance and tournament enrollment.
- [ ] I tested both an allowed and a denied request using synthetic data.
- [ ] I can explain hide/restore/deletion and private reports.
- [ ] My contribution record contains genuine work, review and assistance disclosure.

Current source inspection is complete for this stage; fresh execution results are recorded centrally in [Assemble's evidence register](../Assemble/16-evidence-and-artifact-register.md). Human learning checkboxes remain blank until actually earned.
