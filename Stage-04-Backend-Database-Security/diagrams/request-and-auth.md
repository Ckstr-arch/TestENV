# 🔗 Request and authentication diagrams

[Architecture](../01-architecture.md) · [Walkthroughs](../04-guided-walkthroughs.md)

## Enhanced membership write

```mermaid
sequenceDiagram
  actor Student
  participant DOM as Rendered form + app.js
  participant API as public/api.php + app/api.php
  participant Action as perform_action
  participant Model as reserve
  participant DB as InnoDB database
  Student->>DOM: Submit join for a club
  DOM->>API: POST /api/v1/clubs/{id}/memberships, {}, cookie + CSRF
  API->>API: Route, user, CSRF, Origin, JSON allowlist
  API->>Action: join with fixed id
  Action->>Model: memberships, club id, session user id
  Model->>DB: BEGIN + SELECT club FOR UPDATE
  DB-->>Model: Locked parent
  Model->>DB: Check duplicate and approved capacity
  alt Invalid state
    Model->>DB: ROLLBACK
    Model-->>API: DomainException
    API-->>DOM: 422 error envelope
  else Valid
    Model->>DB: INSERT or reset rejected to pending + audit
    Model->>DB: COMMIT
    Model-->>API: Success
    API-->>DOM: 201 membership + redirect metadata
    DOM->>DOM: Show message / navigate to rendered dashboard
  end
```

Native path differs only in transport: index→handle_action checks hidden csrf, reaches the same perform_action/reserve, then303 and server-rendered GET. Do not draw REST as the only path.

## Login state machine

```mermaid
stateDiagram-v2
  [*] --> Anonymous
  Anonymous --> Anonymous: Invalid password / throttled
  Anonymous --> Authenticated: Password valid, no 2FA / finish_login
  Anonymous --> PendingFactor: Password valid, enrolled / rotate session
  PendingFactor --> PendingFactor: Invalid or replayed code
  PendingFactor --> Anonymous: Five-minute challenge expires / sign in again
  PendingFactor --> Authenticated: Fresh TOTP or single-use recovery / finish_login
  Authenticated --> Anonymous: Logout or idle expiry / rotate session
```

Authenticated account/security settings may prepare a ten-minute setup secret, then activate enrollment only after current password+valid TOTP. Setup and pending login are distinct states. No diagram implies a bypass or an automatic recovery email service.
