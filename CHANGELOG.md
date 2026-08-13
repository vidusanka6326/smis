# Changelog

All notable phase-level changes for **Smart School Data Gathering & Management System (SMIS)**.

Detailed day-to-day notes live in `docs/PROJECT_STATUS.md`.

## [Unreleased]

### Realistic demo school (2026-08-14)

- Seeded a Type 1AB-style dataset: 1 admin, 5 officers, 30 teachers, 600 students, 28 classes (grades 6–13).
- Subjects follow the Sri Lankan national curriculum (12 junior, 9 O/L, 3 A/L per stream). Names are Sinhala in English; UI copy stays English (ADR 0015).

### Flux selectors (2026-08-14)

- Replaced remaining native HTML `<select>` controls with Flux `flux:select`.
- Covers teacher assignment rows (Alpine), attendance roster status, and the activity-log action filter.
- Month filters now use Flux `x-form.month-select`; marks grids use `flux:input`; finalize uses `flux:checkbox`.
- Override Flux free select to a dropdown menu (ADR 0014) so Grade/Gender/Class filters no longer open the browser picker.

### Officer role (2026-08-14)

- Removed generic Create user; admins manage Officers under Admin → Officers.
- New `officer` role for school office data entry with shared `/admin` operational routes + activity log (ADR 0013).

### Split form layouts (2026-08-14)

- Shared Blade form kit (`x-form.page`, `section`, `grid`, `full`, `actions`) for sectioned, multi-column create/edit screens.
- Applied across admin/teacher CRUD, attendance, exams/marks, settings, and auth register/reset.

### Reports enrichment + shad theme (2026-08-14)

- Attendance reports flag students below 80% with class summaries; exam reports add by-class comparison.
- Student report card shows P/A/L/E, exam averages, and results grouped by exam.
- App theme: teal/mint tokens matched to the SMIS logo (light + dark); Flux accent mapped to primary.
- Role dashboards expanded to fill the workspace: denser KPIs, subject/class charts, at-risk & ranking panels, student subject averages.

### Dashboard analytics + timetable UI (2026-08-10)

- Role dashboards (admin/teacher/student) show Chart.js KPIs and quick links via `RoleDashboardMetrics`.
- Shared `x-timetable.grid` period×day visual timetable with default period clock times (ADR 0011).
- Report dashboards reuse shared chart/stat Blade components.

### Remove passkeys (2026-08-10)

- Disabled Fortify passkeys; removed login/settings passkey UI and `@laravel/passkeys` (ADR 0012).
- Dropped `passkeys` table; authentication is password (+ optional 2FA) only.

### Marketing homepage (2026-08-10)

- Replaced Laravel starter welcome page with a branded SMIS landing page at `/`.
- Full-bleed classroom hero, Sora/Source Serif typography, sign-in or dashboard CTA.

### Phase 9 — Hardening & audit log (2026-08-10)

- Custom `activity_logs` table + `ActivityLogger` (ADR 0010); no new Composer packages.
- Audits user creation, marks upsert, exam publish/unpublish, attendance session upsert/finalize, teacher attendance.
- Admin activity log viewer (`view-activity-log`); **228 tests** passing.
- Line coverage ≥80% still unmeasured (pcov/xdebug not installed).

### Phase 8 — API layer (2026-08-10) — skipped

- Product decision: no Sanctum `/api/v1` for the current web-only release (ADR 0009).
- Domain Actions/Policies remain reusable if an API is added later.

### Phase 7 — Reporting & analytics (2026-08-10)

- Added demographics, attendance, examination, and performance reports with role scoping.
- Chart.js dashboards; CSV + print exports; ranking/stats unit coverage; 217 tests passing.

### Phase 6 — Examination module (2026-08-10)

- Added `exams`, `exam_subjects`, and `marks` with scoped mark entry and publish lock.
- Grade-letter and pass/fail calculators with full unit branch coverage; ExaminationSeeder demo; 196 tests passing.

### Phase 5 — Attendance module (2026-08-10)

- Added `attendance_sessions`, `student_attendance`, `teacher_attendance` with role-scoped capture.
- Monthly percentage summaries; finalize lock for teachers; AttendanceSeeder demo data; 160 tests passing.

### Phase 4 — Timetable module (2026-08-10)

- Added `timetables` and `relief_teacher_assignments` with conflict detection service.
- Admin class timetable builder + manual relief workflow.
- Teacher/student timetable views; TimetableSeeder demo slots; 133 tests passing.

### Phase 3 — Teacher & Student modules (2026-08-10)

- Added `teachers`, `students`, `teacher_class_subject_assignments`, `student_enrollments`.
- Admin teacher/student CRUD; assignment sync; class-teacher scoped student management.
- Student categorization filters (grade/class/subject/gender); role dashboards show profile data.
- Retargeted `classes.class_teacher_id` to `teachers`; 117 tests passing.

### Phase 2 — Academic structure & Admin core (2026-08-10)

- Added `academic_years`, `grades`, `streams`, `subjects`, `classes`, and `class_subject` tables.
- Admin CRUD UI + policies gated by `manage-system-config`.
- Stream eligibility rules for grades 12–13; auto class codes; subject sync per class grade.
- `AcademicStructureSeeder` demo data; 99 tests passing.

### Phase 1 — Auth & Authorization (2026-08-10)

- Seeded Spatie roles (`admin`, `teacher`, `student`) and permissions.
- Extended `users` with `status` + SoftDeletes; wired `HasRoles`.
- Disabled public registration; admin-only user creation flow.
- Role-specific dashboard shells and login redirects.
- `EnsureUserIsActive` middleware; inactive users cannot log in.
- `UserPolicy` with full ability coverage tests.

### Phase 0 — Scaffolding (2026-08-10)

- Initialized documentation tree under `/docs` (architecture, modules, api, decisions, testing, setup).
- Added Cursor operating rules (`.cursor/rules/project.mdc`).
- Installed and published `spatie/laravel-permission`.
- Created base application folders: `app/Actions`, `app/Services`, `app/Policies`, `app/Enums`.
- Confirmed CI workflow (`.github/workflows/tests.yml`).
- Documented ADRs: Spatie permission, Fortify/Livewire vs Breeze, SQLite local / MySQL production.
- Added Phase 0 scaffolding feature test.
