# 🔗 Membership to private community

```mermaid
sequenceDiagram
    actor S as Student
    participant V as views.php + app.js
    participant A as api.php
    participant P as actions.php / models.php
    participant D as Database
    actor M as Administrator
    S->>V: Apply to join (club ID)
    V->>A: POST /api/v1/clubs/ID/memberships + cookie/CSRF
    A->>P: perform_action(join) → reserve(memberships)
    P->>D: BEGIN; lock club; check capacity/duplicate; INSERT pending
    D-->>P: COMMIT
    A-->>V: 201 + redirect metadata
    M->>A: PATCH /api/v1/admin/memberships/ID (approved)
    A->>P: require_admin; review_request
    P->>D: Lock parent and request; recheck; UPDATE approved; COMMIT
    S->>A: GET /api/v1/clubs/ID/posts
    A->>D: club_access checks approved membership
    D-->>A: Visible posts only
    A-->>S: JSON feed
```

Native forms reach the same actions through `public/index.php → handle_action()` and then a 303 redirect, rather than using this JSON transport. See [architecture](../01-architecture.md). All labels describe inspected code; no student is credited with building the baseline.
