<?php

namespace App\Http\Controllers\Teacher;

use App\Actions\Students\CreateStudent;
use App\Actions\Students\UpdateStudent;
use App\Enums\Gender;
use App\Enums\TeacherAssignmentRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreStudentRequest;
use App\Http\Requests\Teacher\UpdateStudentRequest;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Support\ListQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Student::class);

        $teacher = $request->user()->teacher;
        abort_if($teacher === null, 403);

        $classIds = $this->homeroomClassIds($teacher->id);
        $filters = ListQuery::filters($request, ['search', 'gender', 'class_id']);

        return view('teacher.students.index', [
            'students' => ListQuery::paginate(
                Student::query()
                    ->with(['user', 'currentClass'])
                    ->whereIn('current_class_id', $classIds)
                    ->filter($filters)
                    ->latest('id'),
                $request,
            ),
            'filters' => $filters,
            'genders' => Gender::cases(),
            'classes' => SchoolClass::query()->whereIn('id', $classIds)->orderBy('code')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Student::class);

        return view('teacher.students.create', $this->formOptions($request));
    }

    public function store(StoreStudentRequest $request, CreateStudent $createStudent): RedirectResponse
    {
        $student = $createStudent->handle($request->validated());

        return redirect()
            ->route('teacher.students.index')
            ->with('status', __('Student created successfully.'));
    }

    public function edit(Request $request, Student $student): View
    {
        $this->authorize('update', $student);

        return view('teacher.students.edit', [
            'student' => $student->load('user'),
            'genders' => Gender::cases(),
        ]);
    }

    public function update(UpdateStudentRequest $request, Student $student, UpdateStudent $updateStudent): RedirectResponse
    {
        $updateStudent->handle($student, [
            ...$request->validated(),
            'status' => $student->user->status->value,
        ], adminUpdate: false);

        return redirect()
            ->route('teacher.students.index')
            ->with('status', __('Student updated successfully.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(Request $request): array
    {
        $teacher = $request->user()->teacher;
        abort_if($teacher === null, 403);

        $classIds = $this->homeroomClassIds($teacher->id);

        return [
            'genders' => Gender::cases(),
            'academicYears' => AcademicYear::query()->orderByDesc('starts_on')->get(),
            'schoolClasses' => SchoolClass::query()
                ->with(['grade', 'academicYear'])
                ->whereIn('id', $classIds)
                ->orderBy('code')
                ->get(),
        ];
    }

    /**
     * @return list<int>
     */
    private function homeroomClassIds(int $teacherId): array
    {
        $homeroomIds = SchoolClass::query()
            ->where('class_teacher_id', $teacherId)
            ->pluck('id');

        $assignmentIds = SchoolClass::query()
            ->whereHas('teacherAssignments', function ($query) use ($teacherId): void {
                $query->where('teacher_id', $teacherId)
                    ->where('role_in_assignment', TeacherAssignmentRole::ClassTeacher);
            })
            ->pluck('id');

        return $homeroomIds->merge($assignmentIds)->unique()->values()->all();
    }
}
