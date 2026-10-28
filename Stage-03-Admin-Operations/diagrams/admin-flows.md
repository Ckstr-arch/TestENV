# Operational state diagrams

[Stage dashboard](../README.md) · [Request/transaction sequence](../01-architecture.md)

These diagrams describe the inspected baseline, not proposals. Node labels carry the meaning; color is decorative.

## Membership

```mermaid
stateDiagram-v2
 [*] --> pending: Student applies when capacity allows
 pending --> approved: Admin review under club lock
 pending --> rejected: Admin review
 rejected --> pending: Student reapplies
 pending --> [*]: Student cancels row
 approved --> [*]: Student leaves row
 rejected --> [*]: Student cancels row
```

An approved request is not reviewed again through the current pending-only review function. Joining a full club rejects rather than queues an unlimited waitlist.

## Booking

```mermaid
stateDiagram-v2
 [*] --> pending: Future valid nonoverlapping request
 pending --> approved: Admin approves after resource lock
 pending --> rejected: Admin rejects
 pending --> cancelled: Owner cancels before start
 approved --> cancelled: Owner cancels before start
```

Rejected/cancelled histories remain. No completed booking enum exists; a past approved booking remains approved in storage. Pending and approved block interval creation. Touching interval endpoints do not collide.

## Community content

```mermaid
stateDiagram-v2
 [*] --> visible: Approved member or admin posts
 visible --> hidden: Admin reason and report resolution
 hidden --> visible: Admin reason and report resolution
 visible --> visible: Author edit
 hidden --> hidden: Author edit
 visible --> deleted: Author or admin removes
 hidden --> deleted: Author or admin removes
```

Deleted is terminal for content: body/reason erased, child rows removed, tombstone retained. Reports independently move pending→resolved on moderation; restoring does not reopen them.

## Parent deletion graph

```mermaid
flowchart LR
 C[Delete club] -->|CASCADE| M[Memberships]
 C -->|CASCADE| E[Events]
 E -->|CASCADE| A[Attendance]
 C -->|CASCADE| P[Posts]
 P -->|CASCADE| CH[Comments reactions reports]
 T[Delete tournament] -->|CASCADE| R[Registrations]
 T -->|SET NULL link only| E
 F[Delete resource] -->|CASCADE| B[Bookings]
 classDef source fill:#0B1220,color:#E8EFF5,stroke:#B8F34A
 classDef rows fill:#E8EFF5,color:#0B1220,stroke:#0B1220
 class C,T,F source
 class M,E,A,P,CH,R,B rows
```

The arrow from tournament to Events means clear `tournament_id`, not delete the event. Foreign keys enforce these effects even if a UI hides a delete control. Read [cascade guidance](../04-guided-walkthroughs.md#delete-impact-matrix) before any synthetic delete.
