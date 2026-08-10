# ADR 0008 — Report exports without DomPDF/Excel packages

## Context

Phase 7 asks for exportable PDF/Excel reports and Chart.js dashboards. Adding DomPDF or PhpSpreadsheet would change Composer dependencies, which requires approval.

## Decision

1. **Charts:** Chart.js 4 via CDN on admin/teacher dashboards.
2. **Tabular export:** streamed CSV (`ReportCsvExporter`) — Excel-compatible.
3. **PDF:** print-optimized views with `?print=1` (browser print-to-PDF).
4. **Best/poor:** top/bottom N by average exam percentage (default 5).

## Consequences

- No new Composer packages for Phase 7.
- Product owner can later approve DomPDF / maatwebsite/excel without schema changes.
- CSV is the supported machine-readable export format for now.
