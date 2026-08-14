<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Mark;
use App\Models\Subject;
use App\Support\ListQuery;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResultController extends Controller
{
    public function __invoke(Request $request): View
    {
        $this->authorize('viewAny', Mark::class);

        $student = $request->user()->student;
        abort_unless($student !== null, 403);

        $filters = ListQuery::filters($request, ['exam_id', 'subject_id', 'result']);

        return view('student.results', [
            'student' => $student,
            'marks' => ListQuery::paginate(
                Mark::query()
                    ->with(['examSubject.exam', 'examSubject.subject'])
                    ->where('student_id', $student->id)
                    ->whereHas('examSubject.exam', fn ($q) => $q->whereNotNull('published_at'))
                    ->filter($filters)
                    ->latest(),
                $request,
            ),
            'filters' => $filters,
            'exams' => Exam::query()
                ->whereNotNull('published_at')
                ->whereHas('examSubjects.marks', fn ($q) => $q->where('student_id', $student->id))
                ->orderByDesc('starts_on')
                ->get(),
            'subjects' => Subject::query()->orderBy('name')->get(),
        ]);
    }
}
