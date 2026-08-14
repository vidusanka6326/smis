<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Services\Reporting\ReportCatalog;
use Illuminate\View\View;

class ReportDashboardController extends Controller
{
    public function __invoke(ReportCatalog $catalog): View
    {
        $this->authorize('viewAny', Report::class);

        return view('reports.catalog', [
            'title' => __('Reports'),
            'description' => __('Reports for your classes. Open one to filter and download PDF or CSV.'),
            'reports' => $catalog->forTeacher(),
        ]);
    }
}
