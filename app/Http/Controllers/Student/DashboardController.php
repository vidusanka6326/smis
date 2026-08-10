<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $student = $request->user()->student;

        return view('student.dashboard', [
            'student' => $student?->load([
                'currentClass.grade',
                'currentClass.stream',
                'currentClass.subjects',
                'enrollments.schoolClass',
                'enrollments.academicYear',
            ]),
        ]);
    }
}
