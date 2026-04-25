# BRGY Reservation - Multi-Tenant SaaS Platform

BRGY Reservation is a live, Laravel-based **Software as a Service (SaaS)** platform built for barangay operations. It provides one centralized application where multiple barangays (tenants) can securely manage reservations, users, and operations with strict tenant isolation.

---

## 1. 🚀 SYSTEM OVERVIEW

BRGY Reservation addresses fragmented and manual barangay workflows by delivering a centralized digital platform.

- **SaaS model**: one managed platform serves many barangays.
- **Core problem solved**: disconnected reservation/admin processes and inconsistent governance.
- **Why SaaS was used**:
  - **Scalability**: onboard new tenants without duplicating systems.
  - **Accessibility**: browser-based access from any authorized device.
  - **Centralized control**: unified updates, monitoring, auditing, and operations.

---

## 2. 🏢 MULTI-TENANCY ARCHITECTURE (CRITICAL)

### What multi-tenancy means in this system
Multi-tenancy means one codebase serves multiple organizations (barangays), where each barangay is a tenant with isolated operational data.

### How tenants are separated
This system follows a **central + tenant database isolation model**:

- **Central context/database**
  - stores global/platform-level records (tenant registry, domains, plans, global updates, central users, deployment control data).
- **Tenant context/database**
  - stores tenant-level records (tenant users, reservations, facilities, tenant settings, tenant audit logs).
- **Dynamic tenant switching**
  - tenant is identified by host/domain, then tenant database connection is configured at runtime.

### Isolation, configuration, and security boundaries

- Requests are classified as central or tenant by host.
- Unknown tenant hosts are blocked.
- Suspended tenants are blocked from normal operations.
- Tenant-specific settings (appearance/features/permissions) are applied inside tenant context.
- Access control boundaries combine:
  - host-context middleware
  - guard separation
  - role/permission checks
  - policy enforcement

This layered model prevents tenants from accessing each other's data through normal application flow.

---

## 3. 👥 TARGET USERS

- **System Admin (Super Admin)**
  - manages the entire platform: tenants, global updates, version monitoring, support workflows, governance.
- **Tenant Owners / Tenant Admins (clients)**
  - manage tenant users, permissions, reservations, facilities, settings, and tenant operations.
- **End Users**
  - use tenant modules based on granted permissions (for example: creating and tracking reservations).

---

## 4. ✨ KEY FEATURES

- Multi-tenant architecture with tenant-aware request routing
- Tenant data isolation with runtime tenant database connection
- Authentication and authorization:
  - separate guards (`web`, `tenant`)
  - role/permission and policy checks
- Reservation and facility management workflows
- Tenant announcements and support modules
- Global update/version tracking
- Deployment candidate pipeline (detect -> review -> validate -> controlled deploy state)
- Audit trail support for traceability and accountability

---

## 5. ⚙️ SYSTEM WORKFLOW

1. **Tenant registration/creation**
   - central admin provisions tenant profile, domain, and related setup.
2. **User login**
   - request enters through central host or tenant host.
3. **Tenant identification**
   - middleware maps host -> tenant domain -> active tenant context.
4. **Tenant-scoped data processing**
   - tenant database connection is configured before tenant business logic executes.
5. **API and service interactions**
   - integrations are used based on workflow needs (auth protection, update ingestion, notifications, etc.).
6. **Response to user**
   - role-based UI and data are returned according to current context and permissions.

---

## 6. 🧠 HOW MULTI-TENANCY WORKS (VERY IMPORTANT FOR DEFENSE)

- **Tenant identification**
  - incoming request host is checked.
  - central hosts are recognized from tenancy config.
  - non-central hosts are matched against tenant domain records.

- **Request scoping**
  - once tenant is resolved, tenant context is bound for the request lifecycle.
  - tenant DB connection is set dynamically.

- **Query filtering/isolation**
  - tenant models execute against tenant connection.
  - central models execute against central connection.
  - this creates hard contextual separation for data operations.

- **Middleware and logic used**
  - `IdentifyTenant`
  - `EnsureCentralHost`
  - `EnsureTenantHost`
  - permission and feature access middleware
  - policy checks on protected actions

---

## 7. 🛠️ TECHNOLOGIES USED

- **Backend**: Laravel 13, PHP 8.3+
- **Frontend**: Blade templates, Tailwind CSS, Alpine.js, Vite, Axios
- **Database**: MySQL (central) + tenant DB connection switching
- **Auth system**: Laravel session guards (`web` for central, `tenant` for tenant users)
- **Authorization**: RBAC + middleware + policy/gate enforcement
- **Queues/Scheduling**: Laravel queue workers and scheduler
- **Testing and quality**: Pest, Laravel test tooling, Laravel Pint
- **Hosting-ready setup**: environment-based configuration, migration scripts, CI workflow support

---

## 8. 🔗 API INTEGRATIONS (CRITICAL SECTION)

### Internal APIs / endpoints

- **GitHub webhook ingestion**
  - Endpoint: `POST /api/deployments/webhook/github`
  - Purpose: receives GitHub events for update detection and candidate creation.
- **Facility availability API**
  - Endpoint: `GET /api/availability/{facility}`
  - Purpose: reservation-side availability checks.

### External services and APIs

- **GitHub API**
  - Purpose: fetch commit/release/tag update data and support release-related workflows.
  - Where used: deployment/global update detection and version-linked operations.
- **Google reCAPTCHA**
  - Purpose: bot mitigation in authentication flows.
  - Where used: login/register security surfaces.
- **Google OAuth (via Socialite)**
  - Purpose: Google-based authentication flow for tenant users.
  - Where used: tenant authentication flow.
- **SMTP Mail Service**
  - Purpose: transactional and system notifications.
  - Where used: account, update, and workflow notifications.
- **Stripe (configured integration path)**
  - Purpose: billing/subscription-related integration support.

### API security

- API secrets/tokens are environment-driven (`.env`) and loaded via config.
- No sensitive keys should be hardcoded.
- Recommended production practice: secure secret storage, key rotation, and least-privilege access.

---

## 9. 👤 USER ROLES & PERMISSIONS

- **Super Admin**
  - full central control across tenants and global modules.
- **Tenant Admin**
  - full management rights within a single tenant scope.
- **Users**
  - limited to permissions assigned by tenant role policy.

Access control is enforced through:
- auth guard separation (`web` and `tenant`)
- permission middleware
- policy/gate authorization checks

---

## 10. 📦 INSTALLATION GUIDE

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run dev
php artisan serve
```

Recommended background processes:

```bash
php artisan queue:work --queue=default
php artisan schedule:work
```

Optional quality and validation commands:

```bash
php artisan test
php vendor/bin/pint
php vendor/bin/pint --test
```

---

## 11. ▶️ USAGE GUIDE

- Access central panel using configured central domain/host (for local: usually `localhost` central app URL).
- Access tenant panel using registered tenant domain/subdomain.
- Tenant admins manage users, roles, reservations, facilities, and settings within their tenant boundary.
- Super admin manages multi-tenant operations, updates, and monitoring from central context.

---

## 12. ⚠️ IMPORTANT NOTES

- Tenant isolation is the most critical architectural requirement.
- Always protect secrets in `.env`; never commit real keys.
- Keep middleware and policy checks active on protected routes.
- Use HTTPS and secure cookie/session settings in production.
- Deployment controls are safety-first (dry-run/explicit production gate patterns).
- Some advanced deployment actions may be intentionally restricted to avoid unsafe live operations.

---

## 13. 👨‍💻 DEVELOPER

ONLY DEVELOPER:
JOSHUA PHILIP M. CAGAANAN

---

## 🧠 TECHNICAL EXPLANATION (FOR DEFENSE)

### What is SaaS?
SaaS (Software as a Service) is a model where software is centrally hosted and delivered via web access to multiple clients without per-client codebase installation.

### What is Multi-Tenancy?
Multi-tenancy is an architecture where one application serves many clients (tenants) while preserving strict data and operational isolation for each tenant.

### Why this architecture was chosen
- Centralized governance and maintenance
- Faster onboarding of new barangays
- Strong separation of tenant data with shared platform efficiency

### Advantages
- **Scalability**: new tenants can be added with minimal platform duplication.
- **Cost-efficiency**: shared infrastructure and operations.
- **Maintainability**: one core platform to update, secure, and monitor.

### Challenges
- **Data security**
- **Tenant isolation correctness**

### How the system solves these challenges
- Host-based tenant identification and context enforcement
- Runtime tenant DB connection switching
- Separate auth guards and role-based authorization
- Middleware + policy layers on sensitive actions
- Audit-friendly workflows and controlled update/deployment mechanisms

---

## Project References

- `docs/pricing-integration.md`
- `docs/tenant-platform-scope.md`
