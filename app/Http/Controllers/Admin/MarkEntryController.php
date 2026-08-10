<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Examination\UpsertMarks;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpsertMarksRequest;
use App\Models\Exam;
use App\Models\ExamSubject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarkEntryController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Exam::class);

        $exams = Exam::query()
            ->with(['academicYear', 'examSubjects.subject'])
            ->orderByDesc('starts_on')
            ->paginate(20);

        return view('admin.marks.index', [
            'exams' => $exams,
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
