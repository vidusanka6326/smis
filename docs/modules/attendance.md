# Attendance Module

## Purpose

Capture student and teacher attendance; monthly summaries; present/absent/late/excused.

## User roles involved

- Admin — manage all sessions and teacher attendance (`manage-attendance`)
- Class teacher — class-level and subject sessions in own class
- Subject teacher — assigned subject sessions (enabled by default)
- PT/PD — assigned class/subject sessions
- Student — own history + monthly % (`view-attendance`)

## DB tables used

- `attendance_sessions` — academic year, class, optional subject, date, `scope` (`class` or `subject:{id}`), taken_by_teacher_id, finalized_at
- `student_attendance` — session, student, status
- `teacher_attendance` — teacher, date, status, recorded_by

## Routes

| Method | Path | Name | Notes |
|---|---|---|---|
| GET/POST/PUT/DELETE | `/admin/attendance/sessions*` | `admin.attendance.sessions.*` | Admin capture |
| POST | `/admin/attendance/sessions/{id}/finalize` | `admin.attendance.sessions.finalize` | Lock session |
| GET | `/admin/attendance/monthly` | `admin.attendance.monthly` | Class monthly % |
| GET/POST/DELETE | `/admin/attendance/teachers*` | `admin.attendance.teachers.*` | Teacher daily |
| GET/POST/PUT | `/teacher/attendance/sessions*` | `teacher.attendance.sessions.*` | Scoped capture |
| GET | `/teacher/attendance/monthly` | `teacher.attendance.monthly` | Scoped monthly |
| GET/POST | `/teacher/attendance/self*` | `teacher.attendance.self.*` | Self daily |
| GET | `/student/attendance` | `student.attendance` | Own summary |

## Key business rules

- Unique session per class + date + scope.
- Students in a session must belong to the class.
- Teachers cannot edit finalized sessions; admins can.
- Attendance %: Present + Late = attended; Excused excluded from denominator.
- Subject-teacher period attendance is enabled (assumption).

## Edge cases

- Duplicate session/date rejected with validation error.
- Subject teacher cannot take class-level attendance without class-teacher/PT-PD assignment.
- Students without a profile get 403 on attendance view.

## Status

Done (Phase 5).
