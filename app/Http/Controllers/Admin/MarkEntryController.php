<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Examination\UpsertMarks;
use App\Enums\ExamType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpsertMarksRequest;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\ExamSubject;
use App\Support\ListQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarkEntryController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Exam::class);

        $filters = ListQuery::filters($request, ['search', 'academic_year_id', 'type', 'status']);

        return view('admin.marks.index', [
            'exams' => ListQuery::paginate(
                Exam::query()
                    ->with(['academicYear', 'examSubjects.subject'])
                    ->filter($filters)
                    ->orderByDesc('starts_on'),
                $request,
            ),
            'filters' => $filters,
            'types' => ExamType::cases(),
            'academicYears' => AcademicYear::query()->orderByDesc('starts_on')->get(),
        ]);
    }

    public function edit(ExamSubject $examSubject): View
    {
        $this->authorize('enterMarks', $examSubject);

        $examSubject->load(['exam', 'subject', 'marks']);
        $students = $examSubject->exam->eligibleStudents();
        $existing = $examSubject->marks->keyBy('student_id');

        return view('admin.marks.edit', [
            'examSubject' => $examSubject,
            'students' => $students,
            'existing' => $existing,
        ]);
    }

    public function update(UpsertMarksRequest $request, ExamSubject $examSubject, UpsertMarks $upsert): RedirectResponse
    {
        $upsert->handle(
            $examSubject,
            $request->validated('records'),
            $request->user()->teacher,
        );

        return redirect()
            ->route('admin.marks.edit', $examSubject)
            ->with('status', __('Marks saved.'));
    }
}
