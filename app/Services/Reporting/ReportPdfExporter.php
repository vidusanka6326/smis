<?php

namespace App\Services\Reporting;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ReportPdfExporter
{
    /**
     * @param  list<array{title: string, headers: list<string>, rows: list<list<scalar|null>>}>  $tables
     */
    public function download(
        string $filename,
        string $title,
        array $tables,
        ?string $subtitle = null,
        string $orientation = 'portrait',
    ): Response {
        $pdf = Pdf::loadView('reports.pdf.document', [
            'title' => $title,
            'subtitle' => $subtitle,
            'generatedAt' => now(),
            'school' => config('app.name'),
            'tables' => $tables,
        ])->setPaper('a4', $orientation);

        return $pdf->download($filename);
    }
}
