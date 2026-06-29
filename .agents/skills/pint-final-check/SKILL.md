---
name: pint-final-check
description: Run Pint as the final formatting gate after Laravel/PHP tasks. Use when finishing backend PHP changes that may have formatting drift and need an automatic style pass before handoff.
---

# Pint Final Check

## Final Gate

- Run from `backend/`.
- Use the project command: `vendor/bin/pint --dirty --format agent`.
- Re-run once after formatting changes until the command is clean.
- If the command is missing, report the missing dependency instead of guessing a result.

## When to Apply

- Run after any PHP file changes are complete.
- Run before handoff on formatting-sensitive work.
- Run before PHPCS and Larastan if the codebase needs a final rewrite pass.
