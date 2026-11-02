# 🧪 Backend exercise workspace

[Full lab instructions](../05-labs-and-exercises.md) · [Separated answer key](answer-key.md)

This directory contains worksheets, not production PHP or an alternate database. Do not put private `.env`, passwords, session exports, TOTP keys or SQL dumps here. Run application commands from repository root after following the isolation instructions.

## Request worksheet

Copy this table to a private local note and fill safe names/status only:

| Step | Actual file/function | Trusted data | Untrusted input | Check | Observable result |
|---|---|---|---|---|---|
| Transport entry | | | | | |
| Auth/CSRF | | | | | |
| Action/service | | | | | |
| Lock/query/write | | | | | |
| Response/render | | | | | |

## Booking interleaving worksheet

Use synthetic symbolic IDs, no SQL mutation:

| Moment | Request A | Request B | Invariant |
|---|---|---|---|
| 1 | Locks resource row | Arrives, waits at same lock | One writer examines slot |
| 2 | Checks overlap, inserts | Still waits | Pending row is inside transaction |
| 3 | Commits | Lock released; begins own check | New committed reservation visible |
| 4 | Responds | Rejects overlap, rolls back | No double reservation |

Now write a second table without the lock. Which order allows both overlap SELECTs to return empty? Explain why a unique booking ID does not prevent that state. Preserve real locking code; this is a reasoning exercise.

Temporary human changes must be undone manually only in the lines that person changed, after reviewing their diff. Never reset/clean the repository to clear a worksheet.
