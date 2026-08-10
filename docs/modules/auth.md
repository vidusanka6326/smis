# Auth Module

## Purpose

Authentication and authorization foundation: login, logout, password reset, roles/permissions, middleware, policies. No public registration — admins create accounts.

## User roles involved

Admin, Teacher, Student (Spatie roles). Teacher subtypes (class / subject / PT-PD) via assignment tables in Phase 3 — not extra Spatie roles.

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
| GET | `/admin/dashboard` | `admin.dashboard` | auth, verified, active, role:admin | Admin shell |
| GET | `/teacher/dashboard` | `teacher.dashboard` | auth, verified, active, role:teacher | Teacher shell |
| GET | `/student/dashboard` | `student.dashboard` | auth, verified, active, role:student | Student shell |
| GET/POST | `/admin/users/create`, `/admin/users` | `admin.users.*` | role:admin + UserPolicy | Admin-only account creation |

Public `/register` is **disabled** (Fortify registration feature removed).

## Permissions (seeded)

See `App\Enums\PermissionName`. Admin receives all; teacher gets timetable/attendance/marks/reports view-or-enter subset; student gets view-only timetable/attendance/marks.

## Key business rules

- Only Admin may create user accounts (`UserPolicy::create` + `manage-users` permission).
- Rate-limited login (Fortify); password rules via `Password::defaults()`.
- Inactive users cannot authenticate (`Fortify::authenticateUsing`) and are logged out by `EnsureUserIsActive`.
- Authorization via Policies + Spatie permissions; route middleware enforces role shells.

## Edge cases

- Cross-role dashboard access returns 403 (tested).
- Teacher/student cannot hit admin user-creation routes (403).
- Soft-deleted users are excluded from default queries; account self-delete soft-deletes.

## Status

Done (Phase 1).
