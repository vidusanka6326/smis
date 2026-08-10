# ADR 0007 — Grade letter and pass/fail defaults

## Context

Phase 6 requires grade-letter and pass/fail engines with full branch coverage. Product had not finalized thresholds.

## Decision

1. **Pass/fail:** `marks_obtained >= pass_mark` (configured per `exam_subjects`; seeder default 40/100).
2. **Grade letters** from percentage of max marks:
   - A ≥ 75
   - B ≥ 65
   - C ≥ 55
   - S ≥ 40
   - F otherwise
3. Class teachers may enter marks for **all subjects** in their own class; subject teachers only their assigned subject; PT/PD cannot enter marks.
4. Results are locked after `published_at` is set; admin can unpublish to reopen editing.

## Consequences

- Calculators live in `App\Services\Examination\*`.
- Changing thresholds later is localized to those services + unit tests.
- Product owner can override defaults without schema changes (except if letter bands become per-exam configurable later).
