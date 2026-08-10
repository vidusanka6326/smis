# Admin Module

## Purpose

Admin dashboard, manage admins/teachers/students, academic structure configuration, system settings.

## User roles involved

Admin only for management; others use separate role dashboards.

## DB tables used

- `users` (account creation)
- Spatie roles/permissions

Academic tables arrive in Phase 2.

## Routes

| Method | Path | Name | Notes |
|---|---|---|---|
| GET | `/admin/dashboard` | `admin.dashboard` | Shell |
| GET | `/admin/users/create` | `admin.users.create` | Form |
| POST | `/admin/users` | `admin.users.store` | `StoreUserRequest` + `CreateUser` action |

## Key business rules

- Full system configuration rights arrive with Phase 2 academic CRUD.
- Can create users of any role (`admin`, `teacher`, `student`) and set active/inactive status.

## Edge cases

- Non-admins receive 403 on all `/admin/*` routes.

## Status

In Progress (Phase 1 shell + user creation; Phase 2 for academic structure).
