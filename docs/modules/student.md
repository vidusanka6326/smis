# Student Module

## Purpose

Student CRUD, guardian info, enrollment history, categorization (grade/class/subject/gender), read-only student dashboard.

## User roles involved

- Admin — full manage (`manage-students`)
- Class Teacher — create/update students in own class (limited fields)
- Student — view own profile/dashboard only

## DB tables used

- `students` (SoftDeletes; admission, DOB, gender G/B, guardian fields, `current_class_id`)
- `student_enrollments` (unique per student + academic year; status active/completed/transferred/withdrawn)
- `users` (login + Spatie role `student`)
- Academic tables for filters (`grades`, `classes`, `subjects`)

## Routes

| Method | Path | Name | Notes |
|---|---|---|---|
| resource | `/admin/students` | `admin.students.*` | Filters on index |
| resource | `/teacher/students` | `teacher.students.*` | Scoped; limited update |
| GET | `/student/dashboard` | `student.dashboard` | Read-only |

## Key business rules

- Creating a student uses a DB transaction: user + profile + active enrollment.
- Gender values: `G` / `B`.
- Admin index filters: search, gender, grade, class, subject (via class subjects).
- Class teacher create requires `createInClass` for the selected class.
- Class teacher update cannot change password/status/class (limited Form Request).

## Edge cases

- Enrollment class must belong to the selected academic year.
- Soft-deleting a student also soft-deletes the linked user (admin destroy).

## Status

Done for Phase 3 core. Attendance/results surfaces arrive later.
