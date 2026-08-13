# Admin Module

## Purpose

Admin dashboard, Officers (office staff accounts), teachers/students, academic structure configuration, system settings.

## User roles involved

Admin manages Officers and all school data. Officers share operational admin routes for data entry (ADR 0013). Teachers/students use separate role shells.

## DB tables used

- `users` (account creation)
- Spatie roles/permissions
- `activity_logs` (audit trail; ADR 0010)
- `academic_years`
- `grades`
- `streams`
- `subjects`
- `classes` (Eloquent model: `SchoolClass`)
- `class_subject` (pivot)

## Routes

| Method | Path | Name | Notes |
|---|---|---|---|
| GET | `/admin/dashboard` | `admin.dashboard` | School glance hero, 3 KPIs, 2 charts, attention + activity (`role:admin\|officer`) |
| resource | `/admin/officers` | `admin.officers.*` | Admin-only Officers CRUD (`manage-officers`; ADR 0013) |
| GET | `/admin/activity-logs` | `admin.activity-logs.index` | Audit viewer for admin + officer (`view-activity-log`); Flux action filter |
| resource | `/admin/academic-years` | `admin.academic-years.*` | except show |
| resource | `/admin/grades` | `admin.grades.*` | except show |
| resource | `/admin/streams` | `admin.streams.*` | except show |
| resource | `/admin/subjects` | `admin.subjects.*` | except show |
| resource | `/admin/classes` | `admin.classes.*` | except show; param `school_class` |

## Key business rules

- Academic CRUD requires `manage-system-config` (admin/officer) via policies.
- Officers section is **admin-only**; officers cannot create or manage other officers (ADR 0013).
- Create/edit screens use shared `x-form.*` sectioned multi-column layouts (not single-column field stacks).
- Dropdowns use Flux `flux:select` (including Alpine assignment rows via `x-bind:name` + `x-model`).
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

Done for Phase 2 academic structure, Phase 3 teacher/student admin management, Phase 9 activity log viewer, and dashboard analytics (Chart.js KPIs on `/admin/dashboard`).
