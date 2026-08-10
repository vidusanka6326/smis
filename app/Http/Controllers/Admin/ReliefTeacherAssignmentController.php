<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Timetable\AssignReliefTeacher;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreReliefTeacherAssignmentRequest;
use App\Models\ReliefTeacherAssignment;
use App\Models\Teacher;
use App\Models\TimetableEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReliefTeacherAssignmentController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', ReliefTeacherAssignment::class);

        return view('admin.relief-assignments.index', [
            'assignments' => ReliefTeacherAssignment::query()
                ->with([
                    'reliefTeacher.user',
                    'timetableEntry.schoolClass',
                    'timetableEntry.subject',
                    'timetableEntry.teacher.user',
                    'assignedByUser',
                ])
                ->latest('date')
                ->paginate(20),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', ReliefTeacherAssignment::class);

        return view('admin.relief-assignments.create', [
            'entries' => TimetableEntry::query()
                ->with(['schoolClass', 'subject', 'teacher.user', 'academicYear'])
                ->orderBy('day_of_week')
                ->orderBy('period_number')
                ->get(),
            'teachers' => Teacher::query()->with('user')->orderBy('employee_no')->get(),
        ]);
    }

    public function store(
        StoreReliefTeacherAssignmentRequest $request,
        AssignReliefTeacher $assignReliefTeacher,
    ): RedirectResponse {
        $data = $request->validated();
        $entry = TimetableEntry::query()->findOrFail($data['timetable_entry_id']);

        $assignReliefTeacher->handle($entry, $data, $request->user());

        return redirect()
            ->route('admin.relief-assignments.index')
            ->with('status', __('Relief teacher assigned.'));
    }

    public function destroy(ReliefTeacherAssignment $reliefTeacherAssignment): RedirectResponse
    {
        $this->authorize('delete', $reliefTeacherAssignment);

        $reliefTeacherAssignment->delete();

        return redirect()
            ->route('admin.relief-assignments.index')
            ->with('status', __('Relief assignment removed.'));
    }
}
