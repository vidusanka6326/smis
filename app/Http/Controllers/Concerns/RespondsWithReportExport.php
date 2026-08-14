<?php

namespace App\Http\Controllers\Concerns;

use App\Services\Reporting\ReportCsvExporter;
use App\Services\Reporting\ReportPdfExporter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

trait RespondsWithReportExport
{
    /**
     * @param  list<string>  $csvHeaders
     * @param  iterable<int, list<scalar|null>>  $csvRows
     * @param  list<array{title: string, headers: list<string>, rows: list<list<scalar|null>>}>  $pdfTables
     */
    protected function exportIfRequested(
        Request $request,
        ReportCsvExporter $csv,
        ReportPdfExporter $pdf,
        string $filenameBase,
        array $csvHeaders,
        iterable $csvRows,
        string $pdfTitle,
        array $pdfTables,
        ?string $pdfSubtitle = null,
        string $orientation = 'portrait',
    ): ?Response {
        $export = $request->string('export')->toString();

        if ($export === 'csv') {
            return $csv->download($filenameBase.'.csv', $csvHeaders, $csvRows);
        }

        if ($export === 'pdf') {
            return $pdf->download($filenameBase.'.pdf', $pdfTitle, $pdfTables, $pdfSubtitle, $orientation);
        }

        return null;
    }

    /**
     * @return array{0: string, 1: Carbon, 2: Carbon}
     */
    protected function monthRange(Request $request): array
    {
        $month = $request->string('month')->toString() ?: now()->format('Y-m');
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = (clone $start)->endOfMonth();

        return [$month, $start, $end];
    }
}
