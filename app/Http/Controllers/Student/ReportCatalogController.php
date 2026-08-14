<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Services\Reporting\ReportCatalog;
use Illuminate\View\View;

class ReportCatalogController extends Controller
{
    public function __invoke(ReportCatalog $catalog): View
    {
        $this->authorize('viewOwn', Report::class);

        return view('reports.catalog', [
            'title' => __('My reports'),
            'description' => __('Open a report to review your data, then download PDF or CSV.'),
            'reports' => $catalog->forStudent(),
        ]);
    }
}
