# Coverage Log

## 2026-08-14 — SMIS Agent chat UI

Command: `php artisan test --compact tests/Feature/Agent tests/Unit/Agent` — **43 passed** (98 assertions). PHPStan clean on chat UI files.

Notes: Full-height chat shell, conversation list titles, quota errors as Flux callouts with an AI Studio link.

## 2026-08-14 — Gemini empty-properties 400

Command: `php artisan test --compact tests/Feature/Agent tests/Unit/Agent` — **38 passed** (89 assertions).

Notes: `list_capabilities` / `get_dashboard_summary` now JSON-encode `properties` as `{}`. Debug 400s include Gemini’s upstream message. PHPStan clean on `app/Services/Agent`.

## 2026-08-14 — SMIS Agent full staff-UI coverage

Command: `php artisan test --compact tests/Feature/Agent tests/Unit/Agent` — **35 passed** (82 assertions). PHPStan clean on `app/Services/Agent`.

Notable suites:
- Admin/officer/teacher access; students still 403
- Teacher cannot create grades, teachers, or officers through the registry
- Class teacher can create a student in their homeroom; cannot create in another class
- Officer can create a student; admin can save attendance and create a grade
- Subject teacher can enter marks through `enter_marks`

Notes: Tools call existing Actions and re-check Policies. Full `--coverage` still blocked without pcov/xdebug.

## 2026-08-14 — Gemini generateContent (`gemini-flash-latest`)

Command: `php artisan test --compact tests/Unit/Agent/GeminiAgentLlmTest.php tests/Feature/Agent/AgentOrchestratorTest.php tests/Feature/Agent/AgentChatTest.php tests/Unit/Agent/AgentMarkdownTest.php` — **11 passed** (31 assertions)

Notes: Agent now posts to `models/gemini-flash-latest:generateContent`. Tests fake 200/404/429 and assert chat surfaces quota errors. Full `--coverage` still blocked without pcov/xdebug.

## 2026-08-14 — Report catalog and PDF/CSV downloads

Command: `php artisan test --compact` — **289 passed** (1019 assertions)

Notable suites:
- Catalog cards for admin/teacher/student (no analytics dashboard on Reports)
- PDF (`application/pdf`, `%PDF`) and CSV exports
- New reports: enrollment, at-risk, staff attendance, exam results, teacher assignments
- Teacher 403 when filtering enrollment to another class; student catalog `viewOwn`

Notes: DomPDF via `barryvdh/laravel-dompdf` (ADR 0017). Full `--coverage` still blocked without pcov/xdebug.

## 2026-08-14 — Compact list filter bar

Command: `php artisan test --compact tests/Feature/ListFiltersPaginationTest.php tests/Feature/Admin/StudentManagementTest.php tests/Feature/Admin/ActivityLogTest.php` — **16 passed**

Notes: Filter heading copy removed; Apply sits on the same row as fields; per-page select is `w-24` / `size="sm"`.

## 2026-08-14 — Shared list filters and pagination

Command: `php artisan test --compact` — **276 passed** (971 assertions)

Notable suites:
- `ListQuery` unit tests (trim/drop empty filters, per-page allow-list, collection pagination)
- Feature: student pagination with query string, teacher search, class-by-grade, exam type, officer status, teacher/student filter bars
- Existing student/activity-log/officer index tests still pass

Notes: Pagination view is `pagination::flux` (HTTP links, not Livewire `wire:click`). Full `--coverage` still blocked without pcov/xdebug.

## 2026-08-14 — Flux select dropdown override

Command: `php artisan test --compact` — **261 passed** (874 assertions)

Notable suites:
- Student filters assert Flux dropdown markup and no leaked `@if="@if"` Blade
- Teacher assignment form asserts `data-flux-dropdown`

Notes: Free Flux `flux:select` is a native `<select>`; published override uses `flux:dropdown` + `flux:menu`. Date inputs unchanged (Pro datepicker). Full `--coverage` still blocked without pcov/xdebug.

## 2026-08-14 — Flux selectors (complete pass)

Command: `php artisan test --compact` — **261 passed** (868 assertions)

Notable suites:
- Admin/teacher/student month filters render Flux selects (`F Y` labels, no `type="month"`)
- Admin/teacher marks entry uses Flux number inputs
- Attendance create uses Flux checkbox for finalize
- Timetable grid Edit/Delete still render after Flux buttons

Notes: Date fields remain `flux:input type="date"` because Flux calendar datepicker is Pro-only. Full `--coverage` still blocked without pcov/xdebug.

## 2026-08-14 — Flux selectors

Command: `php artisan test --compact` — **259 passed** (848 assertions)

Notable suites:
- Admin teacher assignment form renders Flux native selects
- Admin/teacher attendance roster status uses Flux selects
- Activity log action filter uses Flux select

Notes: View-only UI swap; POST/PUT attendance and assignment tests still pass. Full `--coverage` still blocked without pcov/xdebug.

## 2026-08-10 — Dashboard analytics + timetable UI

Command: `php artisan test --compact` — **233 passed** (722 assertions)

Notable suites:
- Admin/Teacher/Student dashboard analytics feature tests
- PeriodSchedule unit (clock ranges + break after P4)
- Existing role dashboard + timetable view regressions

Notes: Chart.js KPIs on role dashboards; shared timetable grid. Full `--coverage` still blocked without pcov/xdebug.

## 2026-08-10 — Phase 9

Command: `php artisan test --compact` — **228 passed** (701 assertions)

Command: `php artisan test --compact --coverage` — **failed**: Code coverage driver not available (Xdebug/PCOV missing).

Notable suites:
- Admin activity log viewer + action filter; teacher denied
- ActivityLogPolicy admin allow / teacher+student deny
- ActivityLogger unit persistence
- Indirect: user create, marks, exam publish, post-finalization attendance edit write logs

Notes: Custom audit log shipped (ADR 0010). ≥80% line coverage still blocked until pcov or xdebug is installed in the environment.

## 2026-08-10 — Phase 7

Command: `php artisan test --compact` — **217 passed** (670 assertions)

Notable suites:
- Admin/teacher report dashboards and CSV export
- Student own report; ReportPolicy viewAny vs viewOwn
- PerformanceRankingService + ExaminationStatisticsReport unit coverage

Notes: DomPDF/XLSX deferred (CSV + browser print). Full `--coverage` still deferred.

## 2026-08-10 — Phase 6

Command: `php artisan test --compact` — **196 passed** (623 assertions)

Notable suites:
- Admin exam create/subjects/publish; mark entry lock after publish
- Teacher subject-scoped mark entry denial for other subjects
- Student published-only results; Exam/ExamSubject/Mark policies
- GradeLetterCalculator + PassFailCalculator boundary unit coverage

Notes: Full `--coverage` percentage still deferred. Marks audit log deferred to Phase 9.

## 2026-08-10 — Phase 5

Command: `php artisan test --compact` — **160 passed** (565 assertions)

Notable suites:
- Admin/teacher student attendance sessions + finalize lock
- Teacher self attendance + subject-teacher scope denial for class-level
- Student own attendance view; AttendanceSession/TeacherAttendance policies
- AttendancePercentageCalculator unit branch coverage

Notes: Full `--coverage` percentage still deferred. Audit log for post-finalization admin edits deferred to Phase 9.

## 2026-08-10 — Phase 4

Command: `php artisan test --compact` — **133 passed** (510 assertions)

Notable suites:
- Admin timetable create + teacher/class conflict rejection
- Relief assignment weekday + identity + busy checks
- Teacher/student timetable views; TimetableEntry/Relief policies
- Conflict detector feature coverage

Notes: Full `--coverage` percentage still deferred. Periods fixed at 8 (Mon–Fri).

## 2026-08-10 — Phase 3

Command: `php artisan test --compact` — **117 passed** (461 assertions)

Notable suites:
- Admin teacher/student CRUD + assignment sync
- Class-teacher scoped student create (allow own class / deny other)
- `TeacherPolicy` / `StudentPolicy` ability coverage including `createInClass` and `manageAssignments`
- Unit: `TeacherAssignmentRole::requiresSubject`

Notes: Full `--coverage` percentage still deferred.

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
