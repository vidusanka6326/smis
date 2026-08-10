<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\RoleDashboardMetrics;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(RoleDashboardMetrics $metrics): View
    {
        $payload = $metrics->forAdmin();

        return view('admin.dashboard', $payload);
    }
}
