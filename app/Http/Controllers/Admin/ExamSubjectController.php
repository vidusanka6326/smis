<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Examination\SyncExamSubjects;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SyncExamSubjectsRequest;
use App\Models\Exam;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ExamSubjectController extends Controller
{
    public function edit(Exam $exam): View
    {
        $this->authorize('update', $exam);

        $exam->load('examSubjects.subject');

        return view('admin.exams.subjects', [
            'exam' => $exam,
            'subjects' => Subject::query()->orderBy('name')->get(),
            'existing' => $exam->examSubjects,
        ]);
    }

    public function update(SyncExamSubjectsRequest $request, Exam $exam, SyncExamSubjects $sync): RedirectResponse
    {
        $subjects = collect($request->validated('subjects'))
            ->filter(fn (array $row): bool => filled($row['subject_id'] ?? null))
            ->values()
            ->all();

        $sync->handle($exam, $subjects);

        return redirect()
            ->route('admin.exams.subjects.edit', $exam)
            ->with('status', __('Exam subjects saved.'));
    }
}
