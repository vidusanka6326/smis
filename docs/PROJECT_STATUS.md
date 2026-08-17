# Project Status — Smart School Data Gathering & Management System

## Current Phase

**SMIS Agent** (Done)

Role-scoped Gemini agent (admin / officer / teacher). Tools cover every staff UI action the signed-in user is already allowed to perform, still gated by Policies and Actions.

**UI — Report catalog & PDF/CSV exports** (Done)

Reports is a card catalog per role (not an analytics dashboard). Each report has filters plus PDF (DomPDF) and CSV download (ADR 0017).

**UI — List filters & pagination** (Done)

Shared `x-list.filters` / table / Flux pagination on every index, plus per-page (10/20/50/100).

`migrate:fresh --seed` now loads a Type 1AB-style school: 1 admin, 5 officers, 30 teachers, 600 students, 28 classes (grades 6–13). Junior 12 subjects, O/L 9, A/L 3 per stream (ADR 0015). Sinhala personal names in English; UI copy stays English.

**UI — Flux selectors** (Done)

`flux:select` now opens a Flux dropdown menu (not the browser’s native picker). Hidden native `<select>` remains for form submit. Date fields stay `flux:input type="date"` (calendar picker is Pro-only).

**Auth — Officer role** (Done)

Admin-only Officers CRUD replaces Create user; officers get school data-entry access + activity log (ADR 0013).

## Module Tracker

| Module | Status | % Complete | Test Coverage | Last Updated | Notes |
|---|---|---|---|---|---|
| Auth | Done | 100% | Feature + policy tests passing | 2026-08-14 | Roles incl. officer; Officers CRUD; inactive gate; role dashboards |
| Admin | Done | 100% | Academic + people + attendance + exams + reports + activity log + officers + dashboard | 2026-08-14 | Shared list filters + pagination (ADR 0016) |
| Teacher | Done | 100% | Profiles, assignments, students, timetable, attendance, marks, reports, dashboard | 2026-08-14 | Scoped lists filtered + paginated |
| Student | Done | 100% | CRUD, enrollment, filters, timetable, attendance, results, own report, dashboard | 2026-08-14 | Attendance/results lists paginated |
| Attendance | Done | 100% | Feature + policy + % calculator unit coverage | 2026-08-10 | Sessions, teacher attendance, monthly summaries; audited upserts |
| Timetable | Done | 100% | Feature + policy + conflict + period schedule unit | 2026-08-10 | Visual period×day grid + default times |
| Examination | Done | 100% | Feature + policy + grade/pass unit branch coverage | 2026-08-10 | Exams, subjects, marks, publish lock; audited marks/publish |
| Reporting | Done | 100% | Feature + policy + ranking/stats + at-risk/by_class + catalog/PDF tests | 2026-08-14 | Catalog + PDF/CSV; extra reports (at-risk, staff attendance, enrollment, exam results, assignments) |
| API (Sanctum) | Skipped | 0% | — | 2026-08-10 | Phase 8 skipped; see ADR 0009 |
| Hardening / audit | Done | 100% | Feature + policy + unit logger tests | 2026-08-10 | Custom `activity_logs` (ADR 0010) |
| SMIS Agent | Done | 100% | Feature + unit (access, tools, orchestrator, Gemini, Livewire chat) | 2026-08-17 | Gemini-only; compact waiting row keeps chat visible |

## Deliverables Checklist

- [x] Functional centralized school management web system (Phases 1–7)
- [x] Role-based authentication (Admin / Teacher / Student)
- [x] Student management module (grade/class/subject/gender filters; streams via class)
- [x] Teacher management module (class/subject/PT-PD assignments)
- [x] Attendance management module (student + teacher, monthly summaries)
- [x] Timetable management module (class/teacher views, relief, conflict detection)
- [x] Examination management module (term tests, scholarship, O/L, A/L)
- [x] Reporting & analytics module (grade/class/subject/gender-wise, best/poor performers)
- [x] REST API mirroring web functionality — **Skipped** (web-only; ADR 0009)
- [ ] Automated test suite meeting coverage targets (≥80% line coverage not yet measured — no pcov/xdebug)
- [x] Complete `/docs` documentation set (through Phase 9)
- [x] `docs/PROJECT_STATUS.md` kept current

## Changelog

- **2026-08-17** — Agent waiting UI is a compact status row. Welcome prompts and prior messages stay visible during long Gemini turns (no blank pane).
- **2026-08-17** — Agent chat shows a Thinking spinner as soon as you send. Stream targets stay in the DOM so long Gemini turns no longer blank the pane. Gemini thinking budget is 0 for faster replies.
- **2026-08-17** — SMIS Agent is Gemini-only (ADR 0021). OpenRouter and `AGENT_LLM_PROVIDERS` removed. Empty function-call `args` encode as `{}`; 502/503 and timeouts show in chat.
- **2026-08-15** — SMIS Agent supports OpenRouter and Gemini. `AGENT_LLM_PROVIDERS` (default `openrouter,gemini`) uses the first listed provider that has an API key (ADR 0020).
- **2026-08-14** — SMIS Agent now uses OpenRouter `openai/gpt-oss-20b:free` (`chat/completions`). Gemini client removed (ADR 0019).
- **2026-08-14** — Refactored SMIS Agent chat UI: full-height shell, wider history with two-line titles, compact composer, and provider quota/setup errors as callouts.
- **2026-08-14** — Fixed SMIS Agent chat failing with a generic error: no-argument tools (`list_capabilities`, `get_dashboard_summary`) sent `properties: []`; Gemini requires a JSON object `{}`.
- **2026-08-14** — SMIS Agent now covers every staff UI action the signed-in user can already perform (academic structure, people, timetable, attendance, exams, reports, activity log), still gated by Policies/Actions (ADR 0018).
- **2026-08-14** — SMIS Agent now calls `gemini-flash-latest:generateContent` (the Google AI Studio sample). `gemini-2.5-flash` 404s for new keys; quota/key errors are shown in chat.
- **2026-08-14** — SMIS Agent: Gemini streaming chat for admin/officer/teacher. Permissioned tools look up free periods, free teachers, attendance, exams, and assign timetable slots or relief (ADR 0018).
- **2026-08-14** — Reports catalog per role (cards, not analytics dashboards). Each report filters data and downloads PDF (DomPDF) or CSV. Added at-risk, teacher attendance, enrollment, exam results, and teacher-assignment reports (ADR 0017).
- **2026-08-14** — Aligned the compact per-page select with other filter controls (same height as Apply).
- **2026-08-14** — Compacted list filter bars to a single row (no heading copy; small per-page select; Apply at the end).
- **2026-08-14** — Shared list kit (ADR 0016): filters + Flux pagination + per-page on every index (students, teachers, officers, academic structure, exams, marks, attendance, activity log, relief, reports, student results).
- **2026-08-14** — Flux selects open a custom dropdown menu (ADR 0014); native OS picker is hidden. Fixed Blade `@if` leaking into the trigger button.
- **2026-08-14** — Dashboard sidebar brand shows muted subtitle “Never miss a class” under SMIS.
- **2026-08-14** — Finished Flux form controls: month filters (`x-form.month-select`), attendance finalize checkboxes, marks number inputs, remaining native action buttons.
- **2026-08-14** — Replaced remaining native HTML selects with Flux `flux:select` (teacher assignment rows, attendance roster status, activity-log filter).
- **2026-08-14** — Officer role + admin-only Officers section; removed Create user; officers share data-entry routes + activity log (ADR 0013).
- **2026-08-14** — Split form layouts: shared `x-form` page/section/grid components; all create/edit screens sectioned + multi-column.
- **2026-08-14** — Decluttered role dashboards: hero + few KPIs + one chart + action lists (cut widget spam).
- **2026-08-14** — Aligned system colors to the SMIS logo (teal → mint primary, light/dark).
- **2026-08-14** — Applied violet `shad.css` theme (light + dark) with Plus Jakarta Sans / Lora; Flux accent → primary.
- **2026-08-14** — Applied coral/peach `sha.css` theme (light + dark) with Montserrat/Merriweather; Flux accent → primary.
- **2026-08-14** — Full-coverage role dashboards: denser KPIs, subject/class exam charts, at-risk & ranking panels, student attendance/subject analytics.
- **2026-08-14** — Reports enrichment + shad theme: at-risk attendance (&lt;80%), exam by-class stats, student report card (P/A/L/E + grouped exams), mint/Outfit Flux accent; reporting unit/feature tests updated.
- **2026-08-10** — Added root `README.md` (stack, setup, demo accounts, docs index).
- **2026-08-10** — Replaced starter welcome page with branded SMIS homepage (hero + module overview + auth CTAs).
- **2026-08-10** — Removed Fortify passkeys (login/settings UI, `@laravel/passkeys`, `passkeys` table); password + 2FA remain (ADR 0012).
- **2026-08-10** — Dashboard/timetable UI: Chart.js KPIs on admin/teacher/student dashboards (`RoleDashboardMetrics`); shared `x-timetable.grid` with period clock times (ADR 0011); **233 tests** passing.
- **2026-08-10** — Phase 9: custom `activity_logs` + `ActivityLogger`; wired into user create, marks, exam publish, attendance; admin viewer; ADR 0010; **228 tests** passing. Coverage % still blocked without pcov/xdebug.
- **2026-08-10** — Phase 8 skipped by product decision: no Sanctum `/api/v1` for current release (ADR 0009).
- **2026-08-10** — Phase 7 complete: demographics/attendance/exam analytics; best/poor rankings; Chart.js dashboards; CSV + print export; 217 tests passing.
- **2026-08-10** — Phase 6 complete: exams + exam subjects + marks entry; grade-letter/pass-fail calculators; publish lock; 196 tests passing.
- **2026-08-10** — Phase 5 complete: attendance sessions + student/teacher attendance; role-scoped capture; monthly % summaries; 160 tests passing.
- **2026-08-10** — Phase 4 complete: `timetables` + `relief_teacher_assignments`; admin class timetable builder with conflict detection; teacher/student timetable views; manual relief workflow; 133 tests passing.
- **2026-08-10** — Phase 3 complete: teacher/student profiles, assignments, enrollments; class-teacher scoped student CRUD; filters; 117 tests passing.
- **2026-08-10** — Phase 2 complete: academic years, grades, streams, subjects, classes CRUD; 99 tests passing.
- **2026-08-10** — Phase 1 complete: Spatie roles/permissions; admin user creation; role dashboards; 58 tests passing.
- **2026-08-10** — Phase 0 scaffolding complete.

## Known Issues / TODO

- Sanctum / `/api/v1` **skipped** for current release (can revisit later without rewriting Policies/Actions).
- Overall ≥80% line coverage not yet measured — install pcov or xdebug, then run `php artisan test --coverage` and log in `docs/testing/coverage-log.md`.
- `admins` profile extension table still deferred.
- Period count fixed at 8 (Mon–Fri); make configurable later if needed.
- Spreadsheet export is CSV only (no Excel package).
- Re-run `RolesAndPermissionsSeeder` on existing environments to pick up `view-activity-log`, `manage-officers` / `officer` role, and `use-smis-agent`.
- SMIS Agent needs `GEMINI_API_KEY`. Optional `GEMINI_MODEL` (default `gemini-flash-latest`). Tests use a scripted LLM.

## Decisions Needed From Product Owner

| Topic | Assumption (until decided) | Status |
|---|---|---|
| Auth stack: Breeze vs Fortify/Livewire | Keep Fortify + Livewire + Flux | Assumed |
| Local DB: SQLite vs MySQL | SQLite for local/tests; MySQL production | Assumed |
| Default admin seed credentials | `admin@smis.test` / `password` (Chamara Wickramasinghe, local only) | Assumed |
| Demo teacher/student seeds | `class.teacher@smis.test` (Nimal Perera, 10-A), `subject.teacher@smis.test` (Chaminda Jayasinghe), `student@smis.test` (Kasun Perera) / `password` | Assumed |
| Demo officer seed | `officer@smis.test` plus 4 officers / `password` (local only) | Assumed |
| Periods per day | 8 periods, Monday–Friday; default times via PeriodSchedule (ADR 0011) | Assumed |
| Relief teacher allocation | Manual assignment with conflict detection | Assumed |
| Subject-teacher attendance | Enabled by default for assigned subject/class | Assumed |
| Attendance % formula | Present+Late attended; Excused excluded from denominator | Assumed |
| Class teacher attendance scope | May take class-level and any subject session in own class | Assumed |
| Grade letters | A≥75, B≥65, C≥55, S≥40, else F (of max marks) | Assumed |
| Pass/fail | `marks_obtained >= pass_mark` (per exam subject; default pass 40/100) | Assumed |
| Class teacher marks entry for all subjects | Allowed for own class | Assumed |
| Report exports | CSV download + DomPDF PDF files (ADR 0017) | **Decided** |
| SMIS Agent | Gemini function-calling over existing Policies/Actions | **Decided — ADR 0021** |
| Best/poor performers | Top/bottom 5 by average % across exam subjects | Assumed |
| Attendance at-risk threshold | Monthly attendance **&lt; 80%** flagged as needs attention | Assumed |
| REST API (Phase 8) | Skip for current web-only release | **Decided — skipped** |
| Audit log implementation | Custom `activity_logs` (no Spatie Activitylog package) | **Decided — ADR 0010** |
| Stream names for Grades 12–13 | Science, Commerce, Arts, Technology; Science A = physical, B = biological | Assumed |
| Class code format | `{grade}-{section}` or `{grade}-{STREAM}-{section}` | Assumed |
| Class teacher limited student fields | Name, email, admission, DOB, gender, guardian; no status/password/class move | Assumed |
| SMIS Agent class timetable scope | Teachers may inspect timetables of classes they are assigned to (not only their own teaching slots) | Assumed |
