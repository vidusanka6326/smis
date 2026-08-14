<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Teachers\CreateTeacher;
use App\Actions\Teachers\UpdateTeacher;
use App\Enums\TeacherAssignmentRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTeacherRequest;
use App\Http\Requests\Admin\UpdateTeacherRequest;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Support\ListQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Teacher::class);

        $filters = ListQuery::filters($request, ['search', 'class_id', 'subject_id', 'role']);

        return view('admin.teachers.index', [
            'teachers' => ListQuery::paginate(
                Teacher::query()
                    ->with('user')
                    ->filter($filters)
                    ->latest('id'),
                $request,
            ),
            'filters' => $filters,
            'classes' => SchoolClass::query()->orderBy('code')->get(),
            'subjects' => Subject::query()->orderBy('name')->get(),
            'roles' => TeacherAssignmentRole::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Teacher::class);

        return view('admin.teachers.create', [
            'statuses' => UserStatus::cases(),
        ]);
    }

    public function store(StoreTeacherRequest $request, CreateTeacher $createTeacher): RedirectResponse
    {
        $teacher = $createTeacher->handle($request->validated());

        return redirect()
            ->route('admin.teachers.show', $teacher)
            ->with('status', __('Teacher created successfully.'));
    }

    public function show(Teacher $teacher): View
    {
        $this->authorize('view', $teacher);

        $teacher->load([
            'user',
            'assignments.schoolClass.grade',
            'assignments.subject',
            'assignments.academicYear',
            'homeroomClasses.academicYear',
        ]);

        return view('admin.teachers.show', [
            'teacher' => $teacher,
        ]);
    }

    public function edit(Teacher $teacher): View
    {
        $this->authorize('update', $teacher);

        return view('admin.teachers.edit', [
            'teacher' => $teacher->load('user'),
            'statuses' => UserStatus::cases(),
        ]);
    }

    public function update(UpdateTeacherRequest $request, Teacher $teacher, UpdateTeacher $updateTeacher): RedirectResponse
    {
        $updateTeacher->handle($teacher, $request->validated());

        return redirect()
            ->route('admin.teachers.show', $teacher)
            ->with('status', __('Teacher updated successfully.'));
    }

    public function destroy(Teacher $teacher): RedirectResponse
    {
        $this->authorize('delete', $teacher);

        $teacher->delete();
        $teacher->user?->delete();

        return redirect()
            ->route('admin.teachers.index')
            ->with('status', __('Teacher deleted successfully.'));
    }
}
