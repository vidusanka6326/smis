<?php

namespace App\Http\Controllers\Teacher;

use App\Actions\Attendance\UpsertAttendanceSession;
use App\Enums\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreAttendanceSessionRequest;
use App\Http\Requests\Teacher\UpdateAttendanceSessionRequest;
use App\Models\AcademicYear;
use App\Models\AttendanceSession;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceSessionController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', AttendanceSession::class);

        $teacher = $request->user()->teacher;

        abort_unless($teacher !== null, 403);

        $sessions = AttendanceSession::query()
            ->with(['schoolClass', 'subject'])
            ->where(function ($query) use ($teacher): void {
                $homeroomIds = $teacher->homeroomClasses()->pluck('id');
                $assignedClassIds = $teacher->assignments()->pluck('school_class_id');
                $classIds = $homeroomIds->merge($assignedClassIds)->unique()->filter();

                $query->whereIn('school_class_id', $classIds);
            })
            ->orderByDesc('date')
            ->paginate(20);

        return view('teacher.attendance.sessions.index', [
            'sessions' => $sessions,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', AttendanceSession::class);

        $teacher = $request->user()->teacher;
        abort_unless($teacher !== null, 403);

        $academicYearId = (int) $request->integer(
            'academic_year_id',
            AcademicYear::query()->where('is_current', true)->value('id')
                ?? AcademicYear::query()->latest('starts_on')->value('id')
                ?? 0,
        );

        $accessibleClassIds = $teacher->homeroomClasses()->pluck('id')
            ->merge($teacher->assignments()->pluck('school_class_id'))
            ->unique()
            ->filter();

        $schoolClassId = (int) $request->integer('school_class_id', 0);
        $schoolClass = $schoolClassId > 0 && $accessibleClassIds->contains($schoolClassId)
            ? SchoolClass::query()->with('subjects')->findOrFail($schoolClassId)
            : null;

        $subjectId = $request->filled('subject_id') ? (int) $request->integer('subject_id') : null;

        if ($schoolClass !== null) {
            abort_unless(
                $request->user()->can('createForClass', [AttendanceSession::class, $schoolClass, $subjectId]),
                403,
            );
        }

        $students = $schoolClass
            ? Student::query()->with('user')->where('current_class_id', $schoolClass->id)->orderBy('admission_no')->get()
            : collect();

        return view('teacher.attendance.sessions.create', [
            'academicYears' => AcademicYear::query()->orderByDesc('starts_on')->get(),
            'schoolClasses' => SchoolClass::query()->whereIn('id', $accessibleClassIds)->orderBy('code')->get(),
            'subjects' => Subject::query()->orderBy('name')->get(),
            'statuses' => AttendanceStatus::cases(),
            'selectedAcademicYearId' => $academicYearId,
            'selectedSchoolClassId' => $schoolClassId,
            'selectedSubjectId' => $subjectId,
            'schoolClass' => $schoolClass,
            'students' => $students,
            'date' => $request->string('date')->toString() ?: now()->toDateString(),
        ]);
    }

    public function store(StoreAttendanceSessionRequest $request, UpsertAttendanceSession $upsert): RedirectResponse
    {
        $data = $request->validated();
        $data['taken_by_teacher_id'] = $request->user()->teacher?->id;

        $session = $upsert->handle($data);

        return redirect()
            ->route('teacher.attendance.sessions.edit', $session)
            ->with('status', __('Attendance session saved.'));
    }

    public function edit(Request $request, AttendanceSession $attendanceSession): View
    {
        $this->authorize('update', $attendanceSession);

        $attendanceSession->load(['schoolClass.subjects', 'studentAttendances']);

        $students = Student::query()
            ->with('user')
            ->where('current_class_id', $attendanceSession->school_class_id)
            ->orderBy('admission_no')
            ->get();

        return view('teacher.attendance.sessions.edit', [
            'session' => $attendanceSession,
            'statuses' => AttendanceStatus::cases(),
            'students' => $students,
            'existing' => $attendanceSession->studentAttendances->keyBy('student_id'),
        ]);
    }

    public function update(
        UpdateAttendanceSessionRequest $request,
        AttendanceSession $attendanceSession,
        UpsertAttendanceSession $upsert,
    ): RedirectResponse {
        $data = $request->validated();
        $data['taken_by_teacher_id'] = $request->user()->teacher?->id ?? $attendanceSession->taken_by_teacher_id;

        $session = $upsert->handle($data, $attendanceSession);

        return redirect()
            ->route('teacher.attendance.sessions.edit', $session)
            ->with('status', __('Attendance session updated.'));
    }

    public function finalize(Request $request, AttendanceSession $attendanceSession, UpsertAttendanceSession $upsert): RedirectResponse
    {
        $this->authorize('finalize', $attendanceSession);
        $upsert->finalize($attendanceSession, $request->user()->teacher);

        return redirect()
            ->route('teacher.attendance.sessions.edit', $attendanceSession)
            ->with('status', __('Attendance session finalized.'));
    }
}
