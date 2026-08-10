<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Attendance\UpsertTeacherAttendance;
use App\Enums\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTeacherAttendanceRequest;
use App\Models\Teacher;
use App\Models\TeacherAttendance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherAttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', TeacherAttendance::class);

        $records = TeacherAttendance::query()
            ->with(['teacher.user', 'recordedBy'])
            ->when($request->filled('teacher_id'), fn ($q) => $q->where('teacher_id', $request->integer('teacher_id')))
            ->orderByDesc('date')
            ->paginate(20)
            ->withQueryString();

        return view('admin.attendance.teachers.index', [
            'records' => $records,
            'teachers' => Teacher::query()->with('user')->orderBy('employee_no')->get(),
            'statuses' => AttendanceStatus::cases(),
            'selectedTeacherId' => $request->integer('teacher_id'),
        ]);
    }

    public function store(StoreTeacherAttendanceRequest $request, UpsertTeacherAttendance $upsert): RedirectResponse
    {
        $upsert->handle($request->validated(), $request->user());

        return redirect()
            ->route('admin.attendance.teachers.index')
            ->with('status', __('Teacher attendance saved.'));
    }

    public function destroy(TeacherAttendance $teacherAttendance): RedirectResponse
    {
        $this->authorize('delete', $teacherAttendance);
        $teacherAttendance->delete();

        return redirect()
            ->route('admin.attendance.teachers.index')
            ->with('status', __('Teacher attendance deleted.'));
    }
}
