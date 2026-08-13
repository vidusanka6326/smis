# Teacher Module

## Purpose

Teacher profiles, assignment types (class / subject / PT-PD), assigned classes/subjects view, class-teacher student management for own class.

## User roles involved

- Admin — full manage (`manage-teachers`)
- Teacher — view/update own profile; class teachers manage students in own class

## DB tables used

- `teachers` (SoftDeletes; `user_id`, `employee_no`, `phone`)
- `teacher_class_subject_assignments` (`role_in_assignment`: class_teacher | subject_teacher | pt_pd_teacher)
- `classes.class_teacher_id` → `teachers.id`
- `users` (login + Spatie role `teacher`)

## Routes

| Method | Path | Name | Notes |
|---|---|---|---|
| resource | `/admin/teachers` | `admin.teachers.*` | Admin CRUD |
| GET/PUT | `/admin/teachers/{teacher}/assignments` | `admin.teachers.assignments.*` | Sync year assignments |
| GET | `/teacher/dashboard` | `teacher.dashboard` | Scoped KPIs, 6 charts, at-risk/rankings, lessons today |
| resource | `/teacher/students` | `teacher.students.*` | except show/destroy |

## Key business rules

- A teacher may hold multiple assignment types via pivot (not a single enum on the profile).
- Subject teacher assignments require a subject already linked to the class and applicable to the grade.
- Class teacher assignments set `classes.class_teacher_id` for that year.
- Class teachers may create/update students only in their homeroom / class_teacher assignment classes.
- Access to student data is scoped by class teacher assignment (subject-only teachers cannot create students).

## Edge cases

- Teacher user without a `teachers` row sees a warning on the dashboard.
- Non-admins receive 403 on `/admin/teachers*`.

## Status

Done for Phase 3 core. Attendance/timetable/marks scoping expands in later phases.
