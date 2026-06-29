# Laravel Rules

- Prefer the Laravel way before custom infrastructure.
- Use Artisan generators for new framework files when available.
- Keep controllers thin; move business logic to focused classes or services.
- Validate all request input with Form Requests or controller validation.
- Authorize actions explicitly before reading or mutating protected data.
- Prefer route model binding and named routes.
- Use Eloquent relationships and query scopes instead of raw SQL when practical.
- Keep database changes in migrations and seed predictable project data in seeders.
- Add or update tests for behavior that changes.
- Do not add dependencies unless the task requires them.

