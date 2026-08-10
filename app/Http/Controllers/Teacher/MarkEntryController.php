<?php

namespace App\Http\Controllers\Teacher;

use App\Actions\Examination\UpsertMarks;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\UpsertMarksRequest;
use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\SchoolClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarkEntryController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Exam::class);

        $teacher = $request->user()->teacher;
        abort_unless($teacher !== null, 403);

        $examSubjects = ExamSubject::query()
            ->with(['exam.academicYear', 'subject'])
            ->whereHas('exam', fn ($q) => $q->whereNull('published_at')->orWhereNotNull('published_at'))
            ->orderByDesc('id')
            ->get()
            ->filter(fn (ExamSubject $examSubject): bool => $request->user()->can('view', $examSubject))
            ->values();

        return view('teacher.marks.index', [
            'examSubjects' => $examSubjects,
        ]);
    }

    public function edit(Request $request, ExamSubject $examSubject): View
    {
        $this->authorize('enterMarks', $examSubject);

        $teacher = $request->user()->teacher;
        abort_unless($teacher !== null, 403);

        $examSubject->load(['exam', 'subject', 'marks']);
        $students = $examSubject->exam->eligibleStudents()->filter(function ($student) use ($teacher, $examSubject) {
            if ($student->current_class_id === null) {
                return false;
            }

            $schoolClass = SchoolClass::query()->find($student->current_class_id);

            return $schoolClass !== null
                && $teacher->canEnterMarksFor($schoolClass, (int) $examSubject->subject_id);
        })->values();

        return view('teacher.marks.edit', [
            'examSubject' => $examSubject,
            'students' => $students,
            'existing' => $examSubject->marks->keyBy('student_id'),
        ]);
    }

    public function update(UpsertMarksRequest $request, ExamSubject $examSubject, UpsertMarks $upsert): RedirectResponse
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher !== null, 403);

        $allowedStudentIds = $examSubject->exam->eligibleStudents()
            ->filter(function ($student) use ($teacher, $examSubject) {
                if ($student->current_class_id === null) {
                    return false;
                }

                $schoolClass = SchoolClass::query()->find($student->current_class_id);

                return $schoolClass !== null
                    && $teacher->canEnterMarksFor($schoolClass, (int) $examSubject->subject_id);
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $records = collect($request->validated('records'))
            ->filter(fn (array $row): bool => in_array((int) $row['student_id'], $allowedStudentIds, true))
            ->values()
            ->all();

        $upsert->handle($examSubject, $records, $teacher, replaceAll: false);

        return redirect()
            ->route('teacher.marks.edit', $examSubject)
            ->with('status', __('Marks saved.'));
    }
}
