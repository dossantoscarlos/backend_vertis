---
name: larastan-final-check
description: Run Larastan as the final static-analysis gate after Laravel/PHP tasks. Use when finishing backend PHP changes, refactors, or reviews that may affect types, query safety, relationships, or API contracts.
---

# Larastan Final Check

## Final Gate

- Run from `backend/`.
- Prefer the repository Larastan config when present: `vendor/bin/phpstan analyse --configuration=phpstan.neon` or `phpstan.neon.dist`.
- If no project config exists, run the narrowest useful analysis scope for the changed PHP files.
- Fix reported type, return, nullability, or Eloquent issues, then rerun once.
- If `vendor/bin/phpstan` is missing, report the missing dependency instead of guessing a result.

## When to Apply

- Run after any PHP file changes are complete.
- Run before handoff on code that touches models, requests, queries, or controllers.
- Run after Pint and PHPCS so the analysis reflects the final code state.
