<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\RoleDashboardMetrics;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, RoleDashboardMetrics $metrics): View
    {
        $teacher = $request->user()->teacher?->load([
            'assignments.schoolClass.grade',
            'assignments.subject',
            'assignments.academicYear',
            'homeroomClasses.grade',
        ]);

        $payload = $teacher !== null
            ? $metrics->forTeacher($teacher)
            : [
                'stats' => null,
                'charts' => null,
                'todaySlots' => [],
                'exam' => null,
                'examStats' => null,
            ];

        return view('teacher.dashboard', [
            'teacher' => $teacher,
            ...$payload,
        ]);
    }
}
