# Timetable Module

## Purpose

Class timetable builder, teacher timetable view, relief teacher allocation, conflict detection.

## User roles involved

- Admin — manage all (`manage-timetable`)
- Teachers / Students — view own (`view-timetable`)

## DB tables used

- `timetables` (model `TimetableEntry`): academic year, class, day (Mon–Fri), period (1–8), subject, teacher
- `relief_teacher_assignments`: timetable entry, relief teacher, date, reason, assigned_by

## Routes

| Method | Path | Name | Notes |
|---|---|---|---|
| GET/POST/PUT/DELETE | `/admin/timetables*` | `admin.timetables.*` | Class grid builder |
| GET/POST/DELETE | `/admin/relief-assignments*` | `admin.relief-assignments.*` | Manual relief |
| GET | `/teacher/timetable` | `teacher.timetable` | Own teaching slots |
| GET | `/student/timetable` | `student.timetable` | Current class grid |

## Key business rules

- A class cannot have two lessons in the same day/period.
- A teacher cannot be scheduled in two classes in the same day/period.
- Subject must be linked to the class and apply to its grade.
- Relief assignment is manual; date must match the entry weekday; relief teacher ≠ original; relief teacher cannot already be busy that period/date.
- Period count assumed **8**; school days **Monday–Friday**.

## Edge cases

- Updating a slot ignores itself when checking conflicts.
- Students without a current class receive 403 on timetable view.
- Teachers without a profile receive 403 on timetable view.

## Status

Done (Phase 4).
