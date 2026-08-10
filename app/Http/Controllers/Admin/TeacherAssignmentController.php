<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Teachers\SyncTeacherAssignments;
use App\Enums\TeacherAssignmentRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SyncTeacherAssignmentsRequest;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TeacherAssignmentController extends Controller
{
    public function edit(Teacher $teacher): View
    {
        $this->authorize('manageAssignments', $teacher);

        $academicYearId = (int) request()->integer(
            'academic_year_id',
            AcademicYear::query()->where('is_current', true)->value('id')
                ?? AcademicYear::query()->latest('starts_on')->value('id')
                ?? 0,
        );

        $teacher->load([
            'assignments' => fn ($query) => $query->where('academic_year_id', $academicYearId),
            'assignments.schoolClass',
            'assignments.subject',
        ]);

        return view('admin.teachers.assignments', [
            'teacher' => $teacher,
            'academicYears' => AcademicYear::query()->orderByDesc('starts_on')->get(),
            'selectedAcademicYearId' => $academicYearId,
            'schoolClasses' => SchoolClass::query()
                ->with(['grade', 'subjects'])
                ->when($academicYearId > 0, fn ($q) => $q->where('academic_year_id', $academicYearId))
                ->orderBy('code')
                ->get(),
            'subjects' => Subject::query()->orderBy('name')->get(),
            'roles' => TeacherAssignmentRole::cases(),
        ]);
    }

    public function update(
        SyncTeacherAssignmentsRequest $request,
        Teacher $teacher,
        SyncTeacherAssignments $syncTeacherAssignments,
    ): RedirectResponse {
        $data = $request->validated();

        $syncTeacherAssignments->handle(
            $teacher,
            (int) $data['academic_year_id'],
            $data['assignments'] ?? [],
        );

        return redirect()
            ->route('admin.teachers.assignments.edit', [
                'teacher' => $teacher,
                'academic_year_id' => $data['academic_year_id'],
            ])
            ->with('status', __('Teacher assignments updated successfully.'));
    }
}
