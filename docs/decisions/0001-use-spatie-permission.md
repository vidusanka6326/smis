# ADR 0001 — Use Spatie laravel-permission

## Context

The system requires RBAC for Admin, Teacher, and Student, plus granular permissions (e.g. `enter-marks`, `manage-timetable`). Teacher subtype scoping (class / subject / PT-PD) must layer on top without exploding the role set.

## Decision

Use [spatie/laravel-permission](https://github.com/spatie/laravel-permission) for roles and permissions. Teacher assignment scope lives in dedicated pivot tables checked inside Policies alongside Spatie permissions.

## Consequences

- Standard Spatie tables and `HasRoles` on `User`.
- Policies combine `hasPermissionTo` / role checks with assignment queries.
- No per-class Spatie roles.
