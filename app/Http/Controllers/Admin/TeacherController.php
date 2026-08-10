<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Teachers\CreateTeacher;
use App\Actions\Teachers\UpdateTeacher;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTeacherRequest;
use App\Http\Requests\Admin\UpdateTeacherRequest;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TeacherController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Teacher::class);

        return view('admin.teachers.index', [
            'teachers' => Teacher::query()
                ->with('user')
                ->latest('id')
                ->paginate(20),
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
