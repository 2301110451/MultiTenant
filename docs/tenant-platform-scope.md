# Tenant platform — scope and expectations

This document states what the codebase can and cannot honestly claim. It is meant for operators, stakeholders, and developers maintaining this project.

## “100% of all tenant modules”

No repository can prove that **every** product requirement is implemented, tested, and free of edge cases. This app includes many tenant-facing areas (for example reservations, facilities, reports, users, roles, settings, support, updates, and appearance), but **“100% complete”** would require a written specification, QA sign-off, and ongoing maintenance—not something inferred from code alone.

## Customization

The system supports **real** customization: tenant-level settings (including colors and layout-related appearance), and per-user display preferences. It is **not** unlimited white-labeling of every email template, PDF layout, and field label unless those features are built explicitly.

## “Version control” vs central System Versions

**Central → System Versions** stores **application release metadata** (version labels, notes, migration batch references). It is **not** a substitute for Git, CI/CD, or deployment pipelines. Use your normal source control and release process for code; use System Versions for an operator-visible changelog if you want one.

## Roles: Tenant Admin / Staff vs legacy names

Provisioning and the data model use **tenant admin** plus **optional staff**. Older **secretary / captain** naming was migrated in the database; legacy string values may still appear in enums or migrations for backward compatibility. Residual wording in old docs or screenshots is **content debt**, not necessarily missing features.

## Optional second account at provisioning

Tenant creation and public apply flows support **one required tenant admin** and **optional staff** credentials (`staff_email` / `staff_password`). If your product policy is “only one initial user,” that is a business choice—the code still allows an optional staff account when those fields are filled.

## Google OAuth and multi-host development

Production-grade OAuth needs **exact redirect URIs** registered in Google Cloud, **HTTPS** in production, and a **consistent host** between login and callback (mixed `127.0.0.1` vs `*.localhost` can break sessions). See `.env.example` under Google settings. Remaining issues are usually **configuration and operations**, not a single line of application code.

## What is largely in place (high level)

- Tenant RBAC (roles, permissions, middleware, policies).
- Tenant user management.
- Support tickets and update announcements (central + tenant flows where implemented).
- System version records in central admin (release metadata, not Git).
- Tenant-admin-centric provisioning with legacy role names handled in migrations.

## Next step for a gap analysis

Define a **one-page must-have module checklist**, then map each item to **route + controller + view** and mark **done / partial / missing**. That produces an actionable backlog; the codebase alone does not certify completeness.
