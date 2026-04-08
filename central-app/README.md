# Central app (logical module)

This repository is a **single Laravel monolith** with strict separation of concerns.

**Central (super admin) code lives here:**

- Routes: `routes/web.php` → `Route::prefix('central')->name('central.')`
- Controllers: `app/Http/Controllers/Central/`
- Views: `resources/views/central/`

The central UI is served only when the request host is listed in `CENTRAL_DOMAINS` (see `config/tenancy.php`). Use a dedicated hostname such as `central.localhost` in your hosts file and open `http://central.localhost:8000`.
