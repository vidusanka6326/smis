<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Attendance\UpsertAttendanceSession;
use App\Enums\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAttendanceSessionRequest;
use App\Http\Requests\Admin\UpdateAttendanceSessionRequest;
use App\Models\AcademicYear;
use App\Models\AttendanceSession;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Support\ListQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceSessionController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', AttendanceSession::class);

        $filters = ListQuery::filters($request, ['academic_year_id', 'school_class_id', 'subject_id', 'scope', 'status', 'date_from', 'date_to']);

        $academicYearId = (int) ($filters['academic_year_id'] ?? AcademicYear::query()->where('is_current', true)->value('id')
            ?? AcademicYear::query()->latest('starts_on')->value('id')
            ?? 0);

        if ($academicYearId > 0 && ! isset($filters['academic_year_id'])) {
            $filters['academic_year_id'] = (string) $academicYearId;
        }

        return view('admin.attendance.sessions.index', [
            'sessions' => ListQuery::paginate(
                AttendanceSession::query()
                    ->with(['schoolClass', 'subject', 'takenByTeacher.user'])
                    ->filter($filters)
                    ->orderByDesc('date'),
                $request,
            ),
            'filters' => $filters,
            'academicYears' => AcademicYear::query()->orderByDesc('starts_on')->get(),
            'schoolClasses' => SchoolClass::query()
                ->when($academicYearId > 0, fn ($q) => $q->where('academic_year_id', $academicYearId))
                ->orderBy('code')
                ->get(),
            'subjects' => Subject::query()->orderBy('name')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', AttendanceSession::class);

        $academicYearId = (int) $request->integer(
            'academic_year_id',
            AcademicYear::query()->where('is_current', true)->value('id')
                ?? AcademicYear::query()->latest('starts_on')->value('id')
                ?? 0,
        );
        $schoolClassId = (int) $request->integer('school_class_id', 0);
        $schoolClass = $schoolClassId > 0
            ? SchoolClass::query()->with('subjects')->findOrFail($schoolClassId)
            : null;

        $students = $schoolClass
            ? Student::query()->with('user')->where('current_class_id', $schoolClass->id)->orderBy('admission_no')->get()
            : collect();

        return view('admin.attendance.sessions.create', [
            'academicYears' => AcademicYear::query()->orderByDesc('starts_on')->get(),
            'schoolClasses' => SchoolClass::query()
                ->when($academicYearId > 0, fn ($q) => $q->where('academic_year_id', $academicYearId))
                ->orderBy('code')
                ->get(),
            'subjects' => Subject::query()->orderBy('name')->get(),
            'teachers' => Teacher::query()->with('user')->orderBy('employee_no')->get(),
            'statuses' => AttendanceStatus::cases(),
            'selectedAcademicYearId' => $academicYearId,
            'selectedSchoolClassId' => $schoolClassId,
            'schoolClass' => $schoolClass,
            'students' => $students,
            'date' => $request->string('date')->toString() ?: now()->toDateString(),
        ]);
    }

    public function store(StoreAttendanceSessionRequest $request, UpsertAttendanceSession $upsert): RedirectResponse
    {
        $session = $upsert->handle($request->validated());

        return redirect()
            ->route('admin.attendance.sessions.edit', $session)
            ->with('status', __('Attendance session saved.'));
    }

    public function edit(AttendanceSession $attendanceSession): View
    {
        $this->authorize('update', $attendanceSession);

        $attendanceSession->load(['schoolClass.subjects', 'studentAttendances']);

        $students = Student::query()
            ->with('user')
            ->where('current_class_id', $attendanceSession->school_class_id)
            ->orderBy('admission_no')
            ->get();

        $existing = $attendanceSession->studentAttendances->keyBy('student_id');

        return view('admin.attendance.sessions.edit', [
            'session' => $attendanceSession,
            'academicYears' => AcademicYear::query()->orderByDesc('starts_on')->get(),
            'schoolClasses' => SchoolClass::query()
                ->where('academic_year_id', $attendanceSession->academic_year_id)
                ->orderBy('code')
                ->get(),
            'subjects' => Subject::query()->orderBy('name')->get(),
            'teachers' => Teacher::query()->with('user')->orderBy('employee_no')->get(),
            'statuses' => AttendanceStatus::cases(),
            'students' => $students,
            'existing' => $existing,
        ]);
    }

    public function update(
        UpdateAttendanceSessionRequest $request,
        AttendanceSession $attendanceSession,
        UpsertAttendanceSession $upsert,
    ): RedirectResponse {
        $session = $upsert->handle($request->validated(), $attendanceSession);

        return redirect()
            ->route('admin.attendance.sessions.edit', $session)
            ->with('status', __('Attendance session updated.'));
    }

    public function destroy(AttendanceSession $attendanceSession): RedirectResponse
    {
        $this->authorize('delete', $attendanceSession);
        $attendanceSession->delete();

        return redirect()
            ->route('admin.attendance.sessions.index')
            ->with('status', __('Attendance session deleted.'));
    }

    public function finalize(AttendanceSession $attendanceSession, UpsertAttendanceSession $upsert): RedirectResponse
    {
        $this->authorize('finalize', $attendanceSession);
        $upsert->finalize($attendanceSession);

        return redirect()
            ->route('admin.attendance.sessions.edit', $attendanceSession)
            ->with('status', __('Attendance session finalized.'));
    }
}
