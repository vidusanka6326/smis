<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Examination\PublishExam;
use App\Actions\Examination\UpsertExam;
use App\Enums\ExamType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreExamRequest;
use App\Http\Requests\Admin\UpdateExamRequest;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Support\ListQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Exam::class);

        $filters = ListQuery::filters($request, ['search', 'academic_year_id', 'type', 'grade_id', 'school_class_id', 'status']);

        return view('admin.exams.index', [
            'exams' => ListQuery::paginate(
                Exam::query()
                    ->with(['academicYear', 'grade', 'schoolClass'])
                    ->filter($filters)
                    ->orderByDesc('starts_on'),
                $request,
            ),
            'filters' => $filters,
            'types' => ExamType::cases(),
            'academicYears' => AcademicYear::query()->orderByDesc('starts_on')->get(),
            'grades' => Grade::query()->orderBy('number')->get(),
            'schoolClasses' => SchoolClass::query()->orderBy('code')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Exam::class);

        return view('admin.exams.create', $this->formData());
    }

    public function store(StoreExamRequest $request, UpsertExam $upsert): RedirectResponse
    {
        $exam = $upsert->handle($request->validated(), $request->user());

        return redirect()
            ->route('admin.exams.subjects.edit', $exam)
            ->with('status', __('Exam created. Configure subjects next.'));
    }

    public function edit(Exam $exam): View
    {
        $this->authorize('update', $exam);

        return view('admin.exams.edit', array_merge($this->formData(), [
            'exam' => $exam->load(['examSubjects.subject']),
        ]));
    }

    public function update(UpdateExamRequest $request, Exam $exam, UpsertExam $upsert): RedirectResponse
    {
        $exam = $upsert->handle($request->validated(), $request->user(), $exam);

        return redirect()
            ->route('admin.exams.edit', $exam)
            ->with('status', __('Exam updated.'));
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        $this->authorize('delete', $exam);
        $exam->delete();

        return redirect()
            ->route('admin.exams.index')
            ->with('status', __('Exam deleted.'));
    }

    public function publish(Exam $exam, PublishExam $publish): RedirectResponse
    {
        $this->authorize('publish', $exam);
        $publish->handle($exam, true);

        return redirect()
            ->route('admin.exams.edit', $exam)
            ->with('status', __('Exam results published.'));
    }

    public function unpublish(Exam $exam, PublishExam $publish): RedirectResponse
    {
        $this->authorize('publish', $exam);
        $publish->handle($exam, false);

        return redirect()
            ->route('admin.exams.edit', $exam)
            ->with('status', __('Exam unpublished. Marks can be edited again.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'types' => ExamType::cases(),
            'academicYears' => AcademicYear::query()->orderByDesc('starts_on')->get(),
            'grades' => Grade::query()->orderBy('number')->get(),
            'schoolClasses' => SchoolClass::query()->orderBy('code')->get(),
        ];
    }
}
