# Reporting Module

## Purpose

Grade/class/subject/gender-wise reports, attendance reports, examination statistics, best/poor performers, CSV + print export, dashboard charts.

## User roles involved

- Admin — all school analytics (`view-reports`)
- Class teacher — own class scope
- Subject teacher — own subject/students scope
- Student — own combined attendance + published results report (`viewOwn`)

## DB tables used

Derived from existing domain tables (students, attendance, exams, marks). No dedicated `reports` cache table.

## Routes

| Method | Path | Name | Notes |
|---|---|---|---|
| GET | `/admin/reports` | `admin.reports.dashboard` | Charts |
| GET | `/admin/reports/demographics` | `admin.reports.demographics` | + `?export=csv` / `?print=1` |
| GET | `/admin/reports/attendance` | `admin.reports.attendance` | Monthly |
| GET | `/admin/reports/examination` | `admin.reports.examination` | Pass rates |
| GET | `/admin/reports/performance` | `admin.reports.performance` | Best/poor |
| GET | `/teacher/reports*` | `teacher.reports.*` | Scoped |
| GET | `/student/report` | `student.report` | Own summary |

## Key business rules

- Teacher scope via assignments/homeroom.
- Best/poor: top/bottom N by average percentage (default 5).
- Exports: CSV streamed downloads; PDF via browser print (no DomPDF/Excel deps).

## Edge cases

- Empty exams/attendance return zeroed summaries.
- Students without profiles cannot open own report.

## Status

Done (Phase 7).
