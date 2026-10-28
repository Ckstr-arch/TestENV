# 08 · Explain the decisions

[Contract](07-contribution-contract.md) → [Evidence](09-completion-evidence.md)

Answer before opening the key. Source links are allowed during study; the final oral explanation should use your own understanding.

## Understand

1. Name the four entity keys in `entities()`. Why isn't `users` one of them?
2. Where are scores written? Is there a separate score-recording action?
3. What does membershipID identify that clubID does not?
4. Why can the admin sidebar never be the only access-control mechanism?
5. What two tables distinguish tournament enrollment from session participation?

## Apply

6. Native form reports419 and REST reports403 for expired CSRF. Is that contradictory?
7. An admin sets home_score 0 and leaves away_score empty. Predict output and row change.
8. A pending request was shown ten minutes ago; another reviewer approved it. What happens to a second approval?
9. One student registers for three sessions and attends two. What do the current report registration/attended counts represent?
10. Deleting a tournament versus deleting a club: compare effects on fixtures and attendance.
11. A formPUT includes only `home_score`. Why can that fail even though the field is valid?
12. A synthetic author edits a hidden post. Does that restore it? Can an admin edit another author's text?

## Engineer

13. Two simultaneous booking requests check that a slot is empty. Explain failure without the resource lock, and why locking a nonexistent booking row is not the chosen strategy.
14. Why does `review_request` read the same request before and after a parent lock?
15. Can a post foreign key prevent comments on a tombstone? Explain the extra invariant.
16. A network error occurs after an admin Create. Why is an automatic retry unsafe?
17. What evidence would justify changing reports to show “unique athletes”? Which current SQL count must not simply be relabeled?
18. Why are save/delete audit atomicity and string-width mismatches relevant even if happy-path tests pass?
19. Describe one real PR you could make without claiming you wrote the whole admin module.
20. How would you demonstrate2FA without disclosing a secret, recovery code or real account data?

<details><summary>Answer key — reasoning, not a memorized presentation script</summary>

1. clubs,tournaments,events,resources. Admin accounts are privately provisioned; public/admin entity CRUD is not arbitrary user-role management.
2. Save on an events row with paired scores; no separate scores action.
3. A specific student's request row and status. ClubID identifies the parent that coordinates capacity.
4. Anyone can craft HTTP requests; page/API/service guards enforce the stored role.
5. registrations versus attendance; attendance may later become attended.
6. No, different transports have different status contracts. Both reject before mutation.
7.422 pairing error, no saved score change.0 itself is valid; missing partner is the problem.
8. Locked reread sees not pending and rejects422; no second transition commits.
9. Three registration rows and two attended rows, not three/two distinct athletes across all contexts.
10. Tournament removes registrations and SET NULLs fixture tournament link; club cascades events then attendance and community/memberships.
11. PUT uses full entity validation, requiring other fields too. It is not a score-onlyPATCH.
12. Hidden remains hidden. Admin may delete, not edit another author's post.
13. Both requests may observe empty then insert overlapping rows. Locking existing resource parent serializes even with zero bookings; all writers need the same protocol.
14. First read finds context; waiting on parent can make its status stale. Final row lock+reread validates current state.
15. No; tombstone parent still exists. Visibility check under lock disallows new child writes after deletion.
16. The write may have committed before the response was lost. Reread current synthetic state; do not blindly duplicate.
17. Define a distinct-user measure, add SQL/test data proving repeated sessions count once, and label it honestly. Current attendance row count must retain its meaning.
18. Happy paths don't test partial audit failure or all maximum lengths. Document risks and propose narrow regressions rather than claiming certification.
19. Example: improve future-attendance explanation with shared review, focused tests, synthetic screenshots and own code explanation. Credit only actual patch/review.
20. Use a disposable synthetic account, hide setup/recovery screens and token/cookie values; show enrollment status and successful challenged login without capturing secrets.

</details>

## Teach-back rubric

A strong answer identifies actual source, describes allowed and denied cases, names the row(s) affected and admits limits. A weak answer only names a technology (“transactions”, “secure”, “industry standard”) without tracing what it protects. Ask a real peer to challenge one answer and record how you corrected it; do not fabricate examiner scores.
