# 08 — Explain it before changing it

[Dashboard](README.md) · [Evidence record](09-completion-evidence.md)

Answer in your own words, cite a real function and predict an observable effect. Start with short answers, then use the harder cases to rehearse a viva. Scoring is a learner self-check, not fabricated course marks.

## Questions

1. What is PHP executing when app/views.php emits `<h1>`? Which file supplies the outer document shell?
2. How do `require` and `require_once` differ, and why does ROOT use `__DIR__`?
3. Why does query() bind values but entities() allowlist table/column identifiers?
4. A user has pending_user but no user_id. Can they read the dashboard or call an admin API? Trace the checks.
5. Why does password success rotate the session and CSRF? What must app.js do with response metadata?
6. Native form csrf is correct; JSON request header is absent. Is the hidden field alone enough for API authentication or CSRF?
7. Why must a TOTP value remain a string? Where is replay prevention persisted?
8. Why can passwords be hashed while TOTP secrets require encryption? What happens if APP_KEY is lost?
9. What prevents two different recovery-code requests from consuming the same stored code simultaneously?
10. Two students claim the last tournament place. Give a valid lock interleaving and locate the unique constraint backstop.
11. Why do adjacent booking intervals not overlap under starts_at<end AND ends_at>start?
12. Is membership cancellation the same SQL operation as booking cancellation? Explain history and gating effects.
13. Does tournament registration automatically create an attendance row for every fixture?
14. A tournament registration_deadline equals starts_at. Does actual code accept it despite the “before” error wording?
15. If audit fails after save_entity UPDATE, must UPDATE be rolled back? Identify the transaction boundary.
16. A logged-in student forges PUT admin/clubs/1. Name both layers that prevent the write.
17. Does HTTP200 from health prove MySQL8 assignment compliance or public HTTPS hosting?
18. Why can you not copy the historical291 test count as today’s execution result?
19. Which private paths must never be served? Why is the development router not a production hosting service?
20. What is truthful evidence of Member D’s contribution to the existing Codex-assisted prototype?

<details><summary>Separated answer key — open after writing your answers</summary>

1. PHP evaluates template conditions/loops and emits HTML bytes; app/layout.php creates shell, views contains page markup.
2. require executes each time; require_once avoids duplicate definitions. __DIR__ is stable relative to source, unlike process working directory.
3. Placeholders represent SQL values, not identifiers. Trusted metadata bounds identifiers; both mechanisms are required.
4. No. require_user checks user_id→account. Pending challenge permits only verification, not authenticated roles.
5. It prevents fixation and stale pre-login session trust. The frontend adopts current CSRF response metadata for later writes.
6. No. API requires its X-CSRF-Token and preserves cookie session; a body field is not the expected header and may be rejected as unknown.
7. Leading zeros are meaningful; totp_last_counter on users rejects accepted counters, including same-step replay.
8. Password verification compares one-way hashes; the server must recompute TOTP with a secret. Loss of matching APP_KEY prevents decryption of enrolled secrets.
9. verify_second_factor locks the user inside a transaction, checks and deletes the matching recovery row before commit.
10. First locks tournament and inserts/commits; second waits then observes capacity full. user+tournament uniqueness prevents duplicate relationships but alone does not enforce aggregate capacity.
11. Strict comparisons are false when one start exactly equals the other end. Intersecting interior time is rejected.
12. Membership row is deleted, revoking future club-access checks. Booking retains cancelled row and removes it from blocking statuses.
13. No. registrations is separate from attendance; an approved club membership is needed for event attendance registration.
14. Accepted by current `>` check. A stricter deadline policy would be a proposed reviewed change.
15. No universal guarantee: simple save+audit has no enclosing transaction here; inspect exact scope. Reservation mutations do have explicit transactions.
16. API require_admin before dispatch; perform_action require_admin before save/delete; no write based on visible UI alone.
17. No. It executes SELECT1 and reports local service health; engine/version, external HTTPS and host validation are separate.
18. Historical evidence belongs to its recorded run; today’s commands may be skipped/fail/differ. Record actual outcomes.
19. app,database,bin,tests,var,.env and learning docs remain private; only public is webroot. PHP -S is a local development server, not a reliable production deployment.
20. Real personal reviewed changes/debugging/tests/design/docs, own explanation, truthful help disclosure and genuine account/PR history. A guide folder is not authorship proof.

</details>
