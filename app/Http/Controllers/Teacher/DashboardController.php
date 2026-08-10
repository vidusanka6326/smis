<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $teacher = $request->user()->teacher;

        return view('teacher.dashboard', [
            'teacher' => $teacher?->load([
                'assignments.schoolClass.grade',
                'assignments.subject',
                'assignments.academicYear',
                'homeroomClasses.grade',
            ]),
        ]);
    }
}
