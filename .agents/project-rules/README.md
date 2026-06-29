# Project Rules

This directory contains the project rules that every agent must follow when working in this repository.

## Files

- `laravel.md` — application and framework rules
- `phpcs.md` — PHP_CodeSniffer formatting and style rules
- `larastan.md` — static analysis rules
- `psr.md` — shared PSR conventions

## Priority

When rules overlap, follow the most specific rule first:

1. file-specific rule in this directory
2. repository instructions
3. general framework conventions

## Final PHP Gate

- Before finalizing any Laravel/PHP backend task, run the quality gate in this order: Pint, PHPCS, Larastan.
- Use `vendor/bin/pint --dirty --format agent` first so formatting changes settle before style and analysis checks.
- Run PHPCS next and Larastan last.
- If PHPCS or Larastan are unavailable in the environment, report that explicitly instead of fabricating a passing result.
