---
name: phpcs-final-check
description: Run PHPCS as the final code-style gate after Laravel/PHP tasks. Use when finishing any backend PHP change, refactor, or review that may introduce style violations and needs a PSR-12/PHPCS pass before handoff.
---

# Phpcs Final Check

## Final Gate

- Run from `backend/`.
- Prefer the repository PHPCS config when present: `vendor/bin/phpcs --standard=phpcs.xml` or `phpcs.xml.dist`.
- If no project config exists, use the project standard explicitly: `vendor/bin/phpcs --standard=PSR12 app config database routes tests`.
- Fix violations, rerun once, and stop when the output is clean.
- If `vendor/bin/phpcs` is missing, report the missing dependency instead of guessing a result.

## When to Apply

- Run after any PHP file changes are complete.
- Run before handing off a backend task.
- Run after Pint if code formatting changed during the task.
