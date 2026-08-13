# ADR 0013 — Officer role for school office data entry

## Context

Generic “Create user” let admins invent any role from one form. School office staff needed a dedicated path for day-to-day data entry without becoming full admins, and officer accounts must only be managed by admins.

## Decision

1. Add Spatie role `officer` with operational permissions (teachers/students, academic config, timetable, attendance, exams/marks, reports, activity log).
2. Replace Create user with an **Officers** CRUD section (`/admin/officers`) restricted to `role:admin` + `manage-officers`.
3. Share `/admin/*` operational routes with `role:admin|officer`; Officers nav item remains admin-only.
4. Treat admin + officer as `User::isSchoolOffice()` in policies that previously gated school-wide access on `isAdmin()` alone.

## Consequences

- Teachers/students are still created via their dedicated modules, not a generic user form.
- Existing environments must re-run `RolesAndPermissionsSeeder` (and optionally `OfficerUserSeeder`) to pick up the role/permission.
- Officers land on the admin dashboard and activity log; they cannot create other officers.
