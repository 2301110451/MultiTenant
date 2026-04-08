# Tenant app (logical module)

**Tenant (barangay) code lives here:**

- Routes: `routes/web.php` → `Route::prefix('tenant')->name('tenant.')`
- Controllers: `app/Http/Controllers/Tenant/`
- Views: `resources/views/tenant/`
- Models that use the dynamic `tenant` DB connection: `Facility`, `Equipment`, `Reservation`, `User`, etc.

Each barangay is identified **only by domain** (no URL slugs). The `IdentifyTenant` middleware resolves the host against the `domains` table and switches the `tenant` MySQL connection for that request.

Example hosts after seeding: `carmen.localhost`, `macasandig.localhost` (add them to your OS hosts file pointing to `127.0.0.1`).
