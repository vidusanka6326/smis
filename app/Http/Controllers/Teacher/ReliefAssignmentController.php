<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ReliefTeacherAssignment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReliefAssignmentController extends Controller
{
    public function index(Request $request): View
    {
        $teacher = $request->user()->teacher;

        $assignments = ReliefTeacherAssignment::query()
            ->with([
                'timetableEntry.schoolClass',
                'timetableEntry.subject',
                'timetableEntry.teacher.user',
            ])
            ->where('relief_teacher_id', $teacher->id)
            ->orderBy('date', 'desc')
            ->paginate(15);

        return view('teacher.relief-assignments.index', [
            'assignments' => $assignments,
        ]);
    }
}
