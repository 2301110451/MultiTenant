# BRGY Reservation - Multi-Tenant Platform

BRGY Reservation is a Laravel-based multi-tenant reservation and administration platform designed for barangays.  
It provides a central admin context and tenant portal context in one codebase.

## Core Features

- Multi-tenant architecture (central + tenant contexts)
- Role-based access control (RBAC) for tenant users
- Facility and equipment reservation management
- Tenant user and role management
- Reports, announcements, support tickets, and update feeds
- Global updates and system version tracking
- Deployment candidate monitoring and approval workflow

## Tech Stack

- Laravel (PHP 8.3+)
- Blade + Tailwind CSS
- MySQL
- Queue support (database/redis)
- GitHub integration for releases and update detection

## Local Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run dev
php artisan serve
```

## Useful Commands

```bash
# run tests
php artisan test

# run queue worker
php artisan queue:work --queue=default

# run scheduler worker
php artisan schedule:work

# code style
php vendor/bin/pint
```

## Deployment Candidate Monitoring

To monitor GitHub additions (new files/features/versions) in the system UI:

- Open `Central Admin -> Deployments`
- Route: `central/global-updates/candidates`
- Check:
  - Latest Added from GitHub
  - Deployment Candidates table
  - Deployment Runs table

## Project Docs

- Pricing integration and rollout guide: `docs/pricing-integration.md`
- Scope and limitations: `docs/tenant-platform-scope.md`

## Developer

- JOSHUA PHILIP M. CAGAANAN
