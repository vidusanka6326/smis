# Student Module

## Purpose

Student CRUD, guardian info, enrollment history, categorization (grade/class/subject/gender), read-only student dashboard.

## User roles involved

Admin (full), Class Teacher (own class, limited fields), Student (own read-only).

## DB tables used

_Placeholder — `students`, `student_enrollments` (Phase 3)._

## Routes

_Placeholder — Phase 3._

## Key business rules

- Grades 1–13; streams for Grades 12–13.
- Gender values: G / B (per spec).
- Creating a student uses a DB transaction (profile + enrollment + guardian as applicable).

## Edge cases

_TBD in Phase 3._

## Status

Not Started (Phase 3). Empty student dashboard shell shipped in Phase 1 (`/student/dashboard`).
