# ADR 0008 — Report exports without DomPDF/Excel packages

## Context

Phase 7 asks for exportable PDF/Excel reports and Chart.js dashboards. Adding DomPDF or PhpSpreadsheet would change Composer dependencies, which requires approval.

## Decision

1. **Charts:** Chart.js 4 via CDN on **role dashboards** (not on Reports).
2. **Tabular export:** streamed CSV (`ReportCsvExporter`) — Excel-compatible.
3. **PDF:** originally browser print (`?print=1`). **Superseded for report downloads by ADR 0017** (DomPDF).
4. **Best/poor:** top/bottom N by average exam percentage (default 5).

## Consequences

- CSV remains the spreadsheet export (Excel packages still not installed).
- Report PDF downloads are documented in ADR 0017.
