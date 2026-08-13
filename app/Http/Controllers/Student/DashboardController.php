<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\RoleDashboardMetrics;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, RoleDashboardMetrics $metrics): View
    {
        $student = $request->user()->student?->load([
            'currentClass.grade',
            'currentClass.stream',
            'currentClass.subjects',
            'enrollments.schoolClass',
            'enrollments.academicYear',
        ]);

        $payload = $student !== null
            ? $metrics->forStudent($student)
            : [
                'stats' => null,
                'charts' => null,
                'todaySlots' => [],
                'recentMarks' => collect(),
                'failedMarks' => collect(),
            ];

        return view('student.dashboard', [
            'student' => $student,
            ...$payload,
        ]);
    }
}
