# ADR 0017 — Report catalog with DomPDF downloads

## Context

Reports opened as analytics dashboards with Chart.js widgets. Exports were CSV plus browser print-to-PDF (ADR 0008), which did not give a real PDF file. Staff asked for a catalog of reports, each with filters and CSV/PDF download.

## Decision

1. `/admin/reports`, `/teacher/reports`, and `/student/reports` are card catalogs, not dashboards. Analytics stay on role dashboards.
2. Each report is its own page: filter, table, **Download CSV** and **Download PDF**.
3. PDF uses `barryvdh/laravel-dompdf` (`ReportPdfExporter` + a print-only HTML view). CSV stays `ReportCsvExporter`.
4. Admin/officer catalog includes attendance, at-risk, teacher attendance, demographics, enrollment, exam stats, exam results, performance, and teacher assignments. Teachers get the scoped subset. Students get report card, attendance, and results.

## Consequences

- Composer now includes DomPDF. PDF tests assert `application/pdf` and `%PDF`.
- ADR 0008 still describes CSV; PDF is no longer print-only.
- Screen tables paginate; CSV/PDF export the full filtered set.
