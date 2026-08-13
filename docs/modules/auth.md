# Auth Module

## Purpose

Authentication and authorization foundation: login, logout, password reset, roles/permissions, middleware, policies. No public registration — admins create officers; teachers/students via dedicated modules.

## User roles involved

Admin, Officer, Teacher, Student (Spatie roles). Teacher subtypes (class / subject / PT-PD) via assignment tables — not extra Spatie roles. Officers are school office staff for data entry (ADR 0013).

## DB tables used

- `users` — `status` (`active`/`inactive`), SoftDeletes
- Spatie: `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`

## Routes

| Method | Path | Name | Middleware | Notes |
|---|---|---|---|---|
| GET/POST | `/login` | `login` / `login.store` | guest | Fortify |
| POST | `/logout` | `logout` | auth | Fortify |
| GET/POST | password reset | Fortify routes | guest | Enabled |
| GET | `/dashboard` | `dashboard` | auth, verified, active | Redirects to role dashboard |
| GET | `/admin/dashboard` | `admin.dashboard` | auth, verified, active, role:admin\|officer | Admin/officer shell |
| GET | `/teacher/dashboard` | `teacher.dashboard` | auth, verified, active, role:teacher | Teacher shell |
| GET | `/student/dashboard` | `student.dashboard` | auth, verified, active, role:student | Student shell |
| resource | `/admin/officers` | `admin.officers.*` | role:admin + `manage-officers` | Officers CRUD (ADR 0013) |

Public `/register` is **disabled** (Fortify registration feature removed).

## Permissions (seeded)

See `App\Enums\PermissionName`. Admin receives all (including `manage-officers` + `view-activity-log`); officer gets operational data-entry + activity log; teacher gets timetable/attendance/marks/reports subset; student gets view-only timetable/attendance/marks.

## Audit trail

Sensitive Actions write to `activity_logs` via `App\Services\Audit\ActivityLogger` (ADR 0010). UI: `GET /admin/activity-logs` (admin + officer).

## Key business rules

- Only Admin may manage officer accounts (`UserPolicy::manageOfficers` + `manage-officers`).
- Teachers/students are created via Admin/Teacher student & teacher modules — not a generic Create user form.
- Rate-limited login (Fortify); password rules via `Password::defaults()`.
- Inactive users cannot authenticate (`Fortify::authenticateUsing`) and are logged out by `EnsureUserIsActive`.
- Authorization via Policies + Spatie permissions; route middleware enforces role shells.
- Optional TOTP 2FA remains; WebAuthn passkeys are **not** enabled (ADR 0012).

## Edge cases

- Cross-role dashboard access returns 403 (tested).
- Officer cannot open Officers section (403); teacher/student cannot hit admin routes (403).
- Soft-deleted users are excluded from default queries; account self-delete soft-deletes.

## Status

Done (Phase 1 + Officer role ADR 0013).
