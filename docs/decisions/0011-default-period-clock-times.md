# ADR 0011 — Default period clock times on timetable UI

## Context

Timetable data stores period numbers (1–8) without wall-clock times. Dashboards and timetable screens need human-readable time ranges.

## Decision

Use `App\Services\Timetable\PeriodSchedule` defaults: day starts **07:30**, each period **40 minutes**, **20-minute break after period 4**. Displayed in the shared `x-timetable.grid` component; not stored in the database.

## Consequences

- Schools with different bell schedules must change `PeriodSchedule` (or later make it configurable) — until then this is an assumption logged in PROJECT_STATUS.
- No migration needed; UI-only enhancement.
