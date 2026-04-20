# Pricing Integration Guide

## 1) System Architecture Overview

The pricing model is integrated as a modular extension around existing tenant flows:

- `config/pricing.php` defines canonical tier defaults and feature flags.
- `app/Support/Pricing.php` resolves plan capabilities (config defaults + DB overrides).
- `app/Services/ReservationService.php` enforces monthly reservation limits safely.
- `app/Http/Middleware/EnsureTenantPlanFeature.php` gates premium/standard-only routes.
- Tenant reporting and reservation controllers consume `Pricing` so behavior remains centralized and maintainable.

No existing tables or core workflows were removed. New behavior is additive and backward compatible.

## 2) Database Schema

### Existing Core Tables Used

- Central DB: `plans`, `subscriptions`, `tenants`
- Tenant DB: `reservations`, `payments`, `facilities`, `damage_reports`

### New Migration Scripts

- `database/migrations/2026_04_16_000100_sync_pricing_plan_matrix.php`
  - Upserts the Basic/Standard/Premium pricing matrix to `plans`.
  - Keeps existing subscriptions intact.
- `database/migrations/tenant/2026_04_16_010000_add_payment_option_to_reservations_table.php`
  - Adds nullable `payment_option` to tenant `reservations`.
  - Supports premium integrated payment preference capture.

## 3) API / Route Endpoint Definitions

### Existing (preserved)

- `GET /api/availability/{facility}`: Facility busy ranges for calendar use.
- `GET /tenant/reports/download`: PDF export (existing behavior preserved).

### New

- `GET /tenant/reports/download/csv`
  - Middleware: `tenant.permission:reports.view`, `tenant.plan:export_reports_csv`
- `GET /tenant/reports/download/excel`
  - Middleware: `tenant.permission:reports.view`, `tenant.plan:export_reports_excel`

### Updated Route Guards

- Damage and penalty routes now include plan gating:
  - `tenant.plan:damage_penalty_tracking`

## 4) Pricing Plan Enforcement Logic

- Feature and limits are evaluated via `Pricing::allows()` and `Pricing::monthlyReservationLimit()`.
- Monthly reservation count is enforced in `ReservationService::assertWithinPlanLimits()`.
- Premium-only exports are enforced through middleware plus controller checks.
- Reservation creation stores `payment_option` only when `integrated_payments` is available for the tenant plan.

## 5) Implementation Notes / Code Snippets

- Central capability resolution:
  - `app/Support/Pricing.php`
- Plan model integration:
  - `app/Models/Plan.php::allows()`
- Limit enforcement:
  - `app/Services/ReservationService.php`
- Feature middleware:
  - `app/Http/Middleware/EnsureTenantPlanFeature.php`
- Premium report exports:
  - `app/Http/Controllers/Tenant/ReportController.php`

## 6) Deployment Guidelines

1. Configure env:
   - `PRICING_ENFORCEMENT_ENABLED=true`
   - `PRICING_PAYMENTS_PROVIDER=stripe` (or preferred)
2. Run central migrations:
   - `php artisan migrate`
3. Run tenant migrations per tenant DB:
   - `php artisan tenants:migrate` (or existing tenant migration command flow in your project)
4. Seed / sync plans:
   - `php artisan db:seed --class=PlansSeeder`
5. Cache refresh:
   - `php artisan optimize:clear`
   - `php artisan config:cache` (for production)

## 7) Testing Strategy

- Unit tests:
  - `Pricing` feature resolution across `basic`, `standard`, `premium`.
  - Monthly reservation limit enforcement.
- Feature tests:
  - CSV/Excel export access denied for non-premium plans.
  - Damage route denied for plans without `damage_penalty_tracking`.
  - Reservation stores `payment_option` only when premium feature is enabled.
- Regression tests:
  - Existing reservation creation, approval, and PDF download still work.
  - Existing report page access for allowed plans still works.

