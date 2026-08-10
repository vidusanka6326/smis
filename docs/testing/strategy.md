# Testing Strategy

## Tools

- **Pest 5** with `pestphp/pest-plugin-laravel`
- Feature tests under `tests/Feature`
- Unit tests under `tests/Unit`
- Factories + Seeders for all fixtures (no ad-hoc DB rows)

## Per-module requirements

1. Unit tests for models, Form Requests, calculators.
2. Feature tests: happy path, validation failure, authorization failure (403).
3. Policy tests: every ability × every role (pass + fail).
4. At least one multi-step journey test per major flow (later phases).

## Coverage targets

| Area | Target |
|---|---|
| Overall line coverage | ≥ 80% |
| Policy ability methods | 100% (pass + fail each) |
| Grade / pass-fail / attendance % / GPA calculators | 100% branch coverage |

## CI

GitHub Actions workflow `.github/workflows/tests.yml` runs `composer setup` then `composer ci:check` (Pint check, PHPStan, Pest).

Coverage runs locally / on demand with:

```bash
php artisan test --coverage
```

Results are appended to [coverage-log.md](coverage-log.md).

## Phase 0

Scaffolding smoke test: `tests/Feature/PhaseZeroScaffoldingTest.php` asserts docs skeleton, Spatie config, and permission migration presence.
