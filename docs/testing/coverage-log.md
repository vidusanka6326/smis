# Coverage Log

## 2026-08-10 — Phase 2

Command: `php artisan test --compact` — **99 passed** (395 assertions)

Notable suites:
- Admin academic CRUD: academic years, grades, streams, subjects, classes (validation + 403s)
- `AcademicStructurePolicyTest` — every ability × admin/teacher/student for all five academic models
- Unit: grade stream eligibility; class code builder

Notes: Full `--coverage` percentage still deferred; academic structure policy abilities meet pass/fail coverage via shared `ChecksSystemConfigPermission` trait.

## 2026-08-10 — Phase 1

Command: `php artisan test --compact` — **58 passed**

Notable suites:
- Auth roles/permissions, admin user creation (incl. 403s), role dashboards, inactive login
- `UserPolicy` — every ability covered with allow + deny cases
- Existing Fortify auth/settings tests still green (registration assertions updated for disabled signup)

Notes: Full `--coverage` percentage deferred until more domain code exists; authorization abilities for `UserPolicy` meet the 100% ability-method pass/fail requirement.

## 2026-08-10 — Phase 0

Scaffolding only. Domain coverage N/A.

Commands:
- `php artisan test --compact --filter=PhaseZeroScaffolding` — 2 passed
- `php artisan test --compact` — 35 passed (starter kit + Phase 0)

Notes: Full `--coverage` baseline deferred until Phase 1 domain code exists; Phase 0 verifies structure + Spatie install via feature smoke test.
