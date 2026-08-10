<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Students\CreateStudent;
use App\Actions\Students\UpdateStudent;
use App\Enums\Gender;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStudentRequest;
use App\Http\Requests\Admin\UpdateStudentRequest;
use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Student::class);

        $filters = $request->only(['search', 'gender', 'grade_id', 'class_id', 'subject_id']);

        return view('admin.students.index', [
            'students' => Student::query()
                ->with(['user', 'currentClass.grade', 'currentClass.stream'])
                ->filter($filters)
                ->latest('id')
                ->paginate(20)
                ->withQueryString(),
            'filters' => $filters,
            'genders' => Gender::cases(),
            'grades' => Grade::query()->orderBy('number')->get(),
            'classes' => SchoolClass::query()->with('grade')->orderBy('code')->get(),
            'subjects' => Subject::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Student::class);

        return view('admin.students.create', $this->formOptions());
    }

    public function store(StoreStudentRequest $request, CreateStudent $createStudent): RedirectResponse
    {
        $student = $createStudent->handle($request->validated());

        return redirect()
            ->route('admin.students.show', $student)
            ->with('status', __('Student created successfully.'));
    }

    public function show(Student $student): View
    {
        $this->authorize('view', $student);

        $student->load([
            'user',
            'currentClass.grade',
            'currentClass.stream',
            'currentClass.subjects',
            'enrollments.schoolClass',
            'enrollments.academicYear',
        ]);

        return view('admin.students.show', [
            'student' => $student,
        ]);
    }

    public function edit(Student $student): View
    {
        $this->authorize('update', $student);

        return view('admin.students.edit', [
            ...$this->formOptions(),
            'student' => $student->load(['user', 'currentClass', 'enrollments']),
        ]);
    }

    public function update(UpdateStudentRequest $request, Student $student, UpdateStudent $updateStudent): RedirectResponse
    {
        $updateStudent->handle($student, $request->validated(), adminUpdate: true);

        return redirect()
            ->route('admin.students.show', $student)
            ->with('status', __('Student updated successfully.'));
    }

    public function destroy(Student $student): RedirectResponse
    {
        $this->authorize('delete', $student);

        $student->delete();
        $student->user?->delete();

        return redirect()
            ->route('admin.students.index')
            ->with('status', __('Student deleted successfully.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'statuses' => UserStatus::cases(),
            'genders' => Gender::cases(),
            'academicYears' => AcademicYear::query()->orderByDesc('starts_on')->get(),
            'schoolClasses' => SchoolClass::query()->with(['grade', 'academicYear'])->orderBy('code')->get(),
        ];
    }
}
