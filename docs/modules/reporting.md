# Reporting Module

## Purpose

Downloadable school reports (not analytics dashboards). Each role opens a card catalog, then a report page with filters, a data table, and PDF/CSV download.

## User roles involved

- Admin / officer — school-wide reports (`view-reports`)
- Class teacher — own class scope
- Subject teacher — own subject/students scope
- Student — own report card, attendance, and published results (`viewOwn`)

## DB tables used

Derived from existing domain tables (students, attendance, exams, marks, teachers, assignments). No dedicated `reports` cache table.

## Routes

| Method | Path | Name | Notes |
|---|---|---|---|
| GET | `/admin/reports` | `admin.reports.dashboard` | Card catalog |
| GET | `/admin/reports/attendance` | `admin.reports.attendance` | Monthly student attendance |
| GET | `/admin/reports/at-risk` | `admin.reports.at-risk` | Below 80% |
| GET | `/admin/reports/staff-attendance` | `admin.reports.staff-attendance` | Teacher attendance |
| GET | `/admin/reports/demographics` | `admin.reports.demographics` | Headcount |
| GET | `/admin/reports/enrollment` | `admin.reports.enrollment` | Class register |
| GET | `/admin/reports/examination` | `admin.reports.examination` | Pass rates |
| GET | `/admin/reports/exam-results` | `admin.reports.exam-results` | Student marks |
| GET | `/admin/reports/performance` | `admin.reports.performance` | Best/poor |
| GET | `/admin/reports/assignments` | `admin.reports.assignments` | Teacher assignments |
| GET | `/teacher/reports*` | `teacher.reports.*` | Scoped equivalents (no staff/demographics/assignments) |
| GET | `/student/reports` | `student.reports` | Student catalog |
| GET | `/student/report` | `student.report` | Report card |
| GET | `/student/reports/attendance` | `student.reports.attendance` | Own attendance |
| GET | `/student/reports/results` | `student.reports.results` | Own published marks |

Every report page accepts `export=csv` or `export=pdf` plus the same filters as the HTML view.

## Key business rules

- Teacher scope via assignments/homeroom.
- Best/poor: top/bottom N by average percentage (default 5); includes class code.
- Attendance at-risk: students with monthly attendance **&lt; 80%** (assumption).
- Month filters use Flux `x-form.month-select`.
- List/report filters use shared `x-list.filters`. Screen tables paginate; CSV/PDF export the full filtered set (ADR 0016 / 0017).
- Exam class comparison: average % and pass rate by student’s current class.
- Exports: CSV streamed downloads; PDF via DomPDF (`ReportPdfExporter`).

## Edge cases

- Empty exams/attendance return zeroed summaries / empty tables.
- Students without profiles cannot open own reports.
- At-risk list empty when everyone is at/above threshold.
- Teachers receive 403 if they filter to a class outside their scope.

## Status

Done (catalog + PDF/CSV, 2026-08-14).
