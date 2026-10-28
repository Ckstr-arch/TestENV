# 08 · Explain before opening the answer

1. Why does registration send no role, even though the database stores one?
2. What is the difference between `club_id` in Apply and membership ID in an approval URL?
3. A student has club A approval. Why does club B's feed still return403?
4. Why does a password-valid 2FA challenge not create portal access immediately?
5. Does registering for a tournament register attendance for its fixtures?
6. What happens if a student edits an already hidden post?
7. Why does pressing React twice not produce two reactions, while reporting twice yields409?
8. A booking ends exactly when another starts. Does the strict predicate call that an overlap?
9. Why must pending bookings block overlaps before approval?
10. Why is `e($post['body'])` necessary even though the post has passed validation?
11. Native success returns303, REST success returns JSON. Where do they share logic?
12. If a network error occurs after submission, why is automatic retry unsafe?
13. A student removes their membership in one tab then submits a stale composer in another. What controls the result?
14. Which dashboard session metric proves a student attended? Explain the misleading assumption.
15. What can you honestly claim in a video after only reading this reference material?

<details>
<summary>Answer key · open after reasoning</summary>

1. Only server-controlled provisioning may create admin accounts. Schema default creates student; REST rejects unexpected fields.
2. Club ID identifies the thing joined; membership ID identifies the user's application relation. Confusing them can affect a different request.
3. `club_access()` requires an approved membership for the exact supplied club, not any approval.
4. Pending user state has five-minute expiry and no authenticated `user_id`; second-factor verification calls `finish_login()`.
5. No. `registrations` and `attendance` relate to different parent tables and actions.
6. Body changes, status stays hidden. Administrator moderation can restore; author edit cannot.
7. Unique pair plus insert-ignore makes reaction idempotent. Report branch explicitly rejects an existing user and post report.
8. No: strict `<` and `>` allow adjacent intervals. Draw boundaries before changing operators.
9. A pending request already reserves the conflict window; approval must not create contention the request step ignored.
10. Validation bounds/accepts text; escaping prevents its characters becoming executable markup. They solve different problems.
11. `perform_action()` and its domain services handle both; transport-specific CSRF/parsing/redirects differ.
12. SQL might have committed before the response was lost. Read current state before deciding to repeat a command.
13. The next server-side `club_access()` call, not the stale DOM. Previously seen data can remain in browser memory.
14. Dashboard Session registrations counts attendance rows, including registered status. Only attended status establishes recorded attendance.
15. Say what you studied and can explain. Do not claim authorship/implementation/testing you have not personally performed.

</details>

## Lab hints and answers

<details>
<summary>Lab A/B: membership and two transports</summary>

Read the action map and `reserve()` before clicking. The native form includes CSRF/action/id; enhanced JSON removes transport/routing fields because the club ID is fixed by the URL. Member identity comes from session. Pending→approved is an admin action; both transports share services. Compare wire responses rather than expecting identical status codes.

</details>

<details>
<summary>Lab C/D: change scope and stale authorization</summary>

An empty-state improvement should preserve data/permission behavior and be evaluated against a reproduced usability issue. Keep your patch small. A stale composer cannot retain permission after membership removal; expect a server refusal, then reload to see a friendly gate. Do not catch that refusal and insert text locally as if it were saved.

</details>

<details>
<summary>Lab E/F: overlap and secure mobile demonstration</summary>

Adjacent intervals have no positive-duration intersection, so B is allowed and C refused. Different resources are separate locks. A pending booking already blocks. Mobile changes no endpoints or schema rules. 2FA evidence should say whether a step worked and why, while its values remain private.

</details>
