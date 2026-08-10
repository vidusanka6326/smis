# Teacher Module

## Purpose

Teacher profiles, dashboards by assignment type, assigned classes/subjects view.

## User roles involved

Admin (manage), Teacher (own profile / assignments).

## DB tables used

_Placeholder — `teachers`, `teacher_class_subject_assignments` (Phase 3)._

## Routes

_Placeholder — Phase 3._

## Key business rules

- A teacher may hold multiple assignment types (class + subject + PT/PD) via pivot, not a single enum.
- Access to student/attendance/marks data is scoped by assignment.

## Edge cases

_TBD in Phase 3._

## Status

Not Started (Phase 3). Empty teacher dashboard shell shipped in Phase 1 (`/teacher/dashboard`).
