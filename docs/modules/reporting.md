# Reporting Module

## Purpose

Grade/class/subject/gender-wise reports, attendance reports (including at-risk students), examination statistics with class comparison, best/poor performers, CSV + print export, dashboard charts.

## User roles involved

- Admin — all school analytics (`view-reports`)
- Class teacher — own class scope
- Subject teacher — own subject/students scope
- Student — own combined attendance + published results report card (`viewOwn`)

## DB tables used

Derived from existing domain tables (students, attendance, exams, marks). No dedicated `reports` cache table.

## Routes

| Method | Path | Name | Notes |
|---|---|---|---|
| GET | `/admin/reports` | `admin.reports.dashboard` | Charts + at-risk KPI |
| GET | `/admin/reports/demographics` | `admin.reports.demographics` | + `?export=csv` / `?print=1` |
| GET | `/admin/reports/attendance` | `admin.reports.attendance` | Monthly; class + at-risk + full list |
| GET | `/admin/reports/examination` | `admin.reports.examination` | Pass rates; by subject + by class |
| GET | `/admin/reports/performance` | `admin.reports.performance` | Best/poor with class codes |
| GET | `/teacher/reports*` | `teacher.reports.*` | Scoped equivalents |
| GET | `/student/report` | `student.report` | Own report card (P/A/L/E, grouped exams) |

## Key business rules

- Teacher scope via assignments/homeroom.
- Best/poor: top/bottom N by average percentage (default 5); includes class code.
- Attendance at-risk: students with monthly attendance **&lt; 80%** (assumption).
- Exam class comparison: average % and pass rate by student’s current class.
- Exports: CSV streamed downloads; PDF via browser print (no DomPDF/Excel deps).

## Edge cases

- Empty exams/attendance return zeroed summaries.
- Students without profiles cannot open own report.
- At-risk list empty when everyone is at/above threshold.

## Status

Done (Phase 7 + 2026-08-14 enrichment).
