# Admin Module

## Purpose

Admin dashboard, manage admins/teachers/students, academic structure configuration, system settings.

## User roles involved

Admin only for management; others use separate role dashboards.

## DB tables used

- `users` (account creation)
- Spatie roles/permissions
- `academic_years`
- `grades`
- `streams`
- `subjects`
- `classes` (Eloquent model: `SchoolClass`)
- `class_subject` (pivot)

## Routes

| Method | Path | Name | Notes |
|---|---|---|---|
| GET | `/admin/dashboard` | `admin.dashboard` | Shell + shortcuts |
| GET/POST | `/admin/users/create`, `/admin/users` | `admin.users.*` | `StoreUserRequest` + `CreateUser` |
| resource | `/admin/academic-years` | `admin.academic-years.*` | except show |
| resource | `/admin/grades` | `admin.grades.*` | except show |
| resource | `/admin/streams` | `admin.streams.*` | except show |
| resource | `/admin/subjects` | `admin.subjects.*` | except show |
| resource | `/admin/classes` | `admin.classes.*` | except show; param `school_class` |

## Key business rules

- Academic CRUD requires `manage-system-config` (admin) via policies.
- Grades 12–13 **require** a stream; grades 1–11 **must not** have a stream.
- Class codes are auto-built: `10-A` or `12-SCI-A`.
- Subjects attached to a class must apply to that class’s grade number.
- Optional `class_teacher_id` must reference a user with the `teacher` role.
- Only one academic year may be marked `is_current` (enforced via `SetCurrentAcademicYear`).
- Deleting academic years/grades/streams/subjects is blocked while related classes (or attachments) exist.

## Edge cases

- Non-admins receive 403 on all `/admin/*` routes (role middleware + policies).
- Empty subject selection clears the class–subject pivot.

## Status

Done for Phase 2 academic structure. Teacher/student management UI expands in Phase 3.
