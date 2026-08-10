# ADR 0006 — Attendance percentage formula

## Context

Monthly attendance reports need a single percentage rule. Product had not specified whether late or excused days affect the rate.

## Decision

- **Present** and **Late** count as attended.
- **Absent** counts against attendance.
- **Excused** is excluded from both numerator and denominator.
- Empty / all-excused sets yield `0.0%`.

Implemented in `App\Services\Attendance\AttendancePercentageCalculator` with full branch unit tests.

## Consequences

- Class and student monthly summaries share the same calculator.
- Changing the formula later is localized to one service + its unit tests.
