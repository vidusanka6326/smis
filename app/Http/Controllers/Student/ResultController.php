<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Mark;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResultController extends Controller
{
    public function __invoke(Request $request): View
    {
        $this->authorize('viewAny', Mark::class);

        $student = $request->user()->student;
        abort_unless($student !== null, 403);

        $marks = Mark::query()
            ->with(['examSubject.exam', 'examSubject.subject'])
            ->where('student_id', $student->id)
            ->whereHas('examSubject.exam', fn ($q) => $q->whereNotNull('published_at'))
            ->latest()
            ->get();

        return view('student.results', [
            'student' => $student,
            'marks' => $marks,
        ]);
    }
}
