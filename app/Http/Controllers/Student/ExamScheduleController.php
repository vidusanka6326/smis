<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExamScheduleController extends Controller
{
    public function __invoke(Request $request): View
    {
        $student = $request->user()->student;
        abort_unless($student !== null && $student->current_class_id !== null, 403);

        $student->load('currentClass.grade');
        $schoolClass = $student->currentClass;

        $exams = Exam::query()
            ->with(['examSubjects.subject'])
            ->where('academic_year_id', $schoolClass->academic_year_id)
            ->where(function ($query) use ($schoolClass): void {
                $query->where('school_class_id', $schoolClass->id)
                    ->orWhere(function ($gradeQuery) use ($schoolClass): void {
                        $gradeQuery->whereNull('school_class_id')
                            ->where('grade_id', $schoolClass->grade_id);
                    });
            })
            ->whereNotNull('starts_on')
            ->orderBy('starts_on')
            ->orderBy('name')
            ->get();

        return view('student.exam-schedule', [
            'student' => $student,
            'exams' => $exams,
        ]);
    }
}
