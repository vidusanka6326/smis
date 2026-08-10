<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Academic\SyncClassSubjects;
use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSchoolClassRequest;
use App\Http\Requests\Admin\UpdateSchoolClassRequest;
use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Stream;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SchoolClassController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', SchoolClass::class);

        return view('admin.classes.index', [
            'schoolClasses' => SchoolClass::query()
                ->with(['academicYear', 'grade', 'stream', 'classTeacher'])
                ->latest('id')
                ->paginate(20),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', SchoolClass::class);

        return view('admin.classes.create', $this->formOptions());
    }

    public function store(StoreSchoolClassRequest $request, SyncClassSubjects $syncClassSubjects): RedirectResponse
    {
        $data = $request->validated();
        $subjectIds = $data['subject_ids'] ?? [];
        unset($data['subject_ids']);

        $schoolClass = DB::transaction(function () use ($data, $subjectIds, $syncClassSubjects): SchoolClass {
            $grade = Grade::query()->findOrFail($data['grade_id']);
            $stream = isset($data['stream_id'])
                ? Stream::query()->find($data['stream_id'])
                : null;

            $schoolClass = SchoolClass::query()->create([
                ...$data,
                'stream_id' => $stream?->id,
                'code' => SchoolClass::buildCode($grade, $data['name'], $stream),
            ]);

            $syncClassSubjects->handle($schoolClass, $subjectIds);

            return $schoolClass;
        });

        return redirect()
            ->route('admin.classes.index')
            ->with('status', __('Class :code created successfully.', ['code' => $schoolClass->code]));
    }

    public function edit(SchoolClass $schoolClass): View
    {
        $this->authorize('update', $schoolClass);

        $schoolClass->load('subjects');

        return view('admin.classes.edit', [
            ...$this->formOptions(),
            'schoolClass' => $schoolClass,
        ]);
    }

    public function update(
        UpdateSchoolClassRequest $request,
        SchoolClass $schoolClass,
        SyncClassSubjects $syncClassSubjects,
    ): RedirectResponse {
        $data = $request->validated();
        $subjectIds = $data['subject_ids'] ?? [];
        unset($data['subject_ids']);

        DB::transaction(function () use ($data, $subjectIds, $schoolClass, $syncClassSubjects): void {
            $grade = Grade::query()->findOrFail($data['grade_id']);
            $stream = isset($data['stream_id'])
                ? Stream::query()->find($data['stream_id'])
                : null;

            $schoolClass->update([
                ...$data,
                'stream_id' => $stream?->id,
                'code' => SchoolClass::buildCode($grade, $data['name'], $stream),
            ]);

            $syncClassSubjects->handle($schoolClass, $subjectIds);
        });

        return redirect()
            ->route('admin.classes.index')
            ->with('status', __('Class updated successfully.'));
    }

    public function destroy(SchoolClass $schoolClass): RedirectResponse
    {
        $this->authorize('delete', $schoolClass);

        $schoolClass->delete();

        return redirect()
            ->route('admin.classes.index')
            ->with('status', __('Class deleted successfully.'));
    }

    /**
     * @return array{
     *     academicYears: Collection<int, AcademicYear>,
     *     grades: Collection<int, Grade>,
     *     streams: Collection<int, Stream>,
     *     subjects: Collection<int, Subject>,
     *     teachers: Collection<int, User>
     * }
     */
    private function formOptions(): array
    {
        return [
            'academicYears' => AcademicYear::query()->orderByDesc('starts_on')->get(),
            'grades' => Grade::query()->orderBy('number')->get(),
            'streams' => Stream::query()->orderBy('name')->get(),
            'subjects' => Subject::query()->orderBy('name')->get(),
            'teachers' => User::query()->role(RoleName::Teacher)->orderBy('name')->get(),
        ];
    }
}
