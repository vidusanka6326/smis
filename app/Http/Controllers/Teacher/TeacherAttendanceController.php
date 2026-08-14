<?php

namespace App\Http\Controllers\Teacher;

use App\Actions\Attendance\UpsertTeacherAttendance;
use App\Enums\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreTeacherAttendanceRequest;
use App\Models\TeacherAttendance;
use App\Support\ListQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherAttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', TeacherAttendance::class);

        $teacher = $request->user()->teacher;
        abort_unless($teacher !== null, 403);

        $filters = ListQuery::filters($request, ['status', 'date_from', 'date_to']);

        return view('teacher.attendance.self.index', [
            'records' => ListQuery::paginate(
                TeacherAttendance::query()
                    ->where('teacher_id', $teacher->id)
                    ->filter($filters)
                    ->orderByDesc('date'),
                $request,
            ),
            'filters' => $filters,
            'teacher' => $teacher,
            'statuses' => AttendanceStatus::cases(),
        ]);
    }

    public function store(StoreTeacherAttendanceRequest $request, UpsertTeacherAttendance $upsert): RedirectResponse
    {
        $upsert->handle($request->validated(), $request->user());

        return redirect()
            ->route('teacher.attendance.self.index')
            ->with('status', __('Your attendance was saved.'));
    }
}
