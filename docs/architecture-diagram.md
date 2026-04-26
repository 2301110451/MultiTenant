# BRGY Reservation Architecture Diagram

This diagram shows the central + tenant request flow and database isolation model.

```mermaid
flowchart TD
    A[Client Browser] --> B[Laravel App Entry]
    B --> C{Host/Domain Check}

    C -->|central.localhost / localhost| D[Central Context]
    C -->|tenant-domain.localhost| E[IdentifyTenant Middleware]

    D --> F[EnsureCentralHost + auth:web]
    F --> G[Central Modules<br/>Tenants, Plans, Updates, Support]
    G --> H[(Central DB: brgy_reservation)]

    E --> I{Domain exists in domains table?}
    I -->|No| J[Abort 404 Unknown Tenant]
    I -->|Yes| K[Load Tenant Record]
    K --> L{Tenant status active?}
    L -->|No| M[Tenant Suspended View]
    L -->|Yes| N[configureTenantConnection()]

    N --> O[Tenant Context Bound]
    O --> P[EnsureTenantHost + auth:tenant]
    P --> Q[Tenant Modules<br/>Dashboard, Users, Roles,<br/>Facilities, Reservations, Reports]
    Q --> R[(Tenant DB: tenant_*)]

    S[Central Admin Creates Tenant] --> T[Provisioning Service]
    T --> U[Create tenant_* database]
    T --> V[Run tenant migrations]
    T --> W[Create Tenant + Domain records]
    W --> H
    U --> R
```

## Notes

- Central data is stored in `brgy_reservation`.
- Each tenant has its own isolated `tenant_*` database.
- `IdentifyTenant` resolves request host to tenant domain and switches tenant DB at runtime.
- Central and tenant access are separated by host middleware and auth guards.
