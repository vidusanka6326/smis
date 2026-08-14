<?php

namespace App\Services\Agent\Tools;

use App\Models\Student;
use App\Models\User;
use App\Services\Agent\AgentScope;
use App\Services\Attendance\AttendanceMonthlySummary;

class GetStudentSummaryTool extends AbstractAgentTool
{
    public function __construct(
        private AgentScope $scope,
        private AttendanceMonthlySummary $attendanceMonthlySummary,
    ) {}

    public function name(): string
    {
        return 'get_student_summary';
    }

    public function description(): string
    {
        return 'Show a student profile plus this month’s attendance percentage.';
    }

    public function parameters(): array
    {
        return $this->objectSchema([
            'search' => $this->stringParam('Student name or admission number.'),
        ], ['search']);
    }

    public function authorized(User $user): bool
    {
        return $this->scope->canViewStudents($user);
    }

    public function handle(User $user, array $arguments): array
    {
        $search = $this->stringArg($arguments, 'search');

        if ($search === null) {
            return ['ok' => false, 'error' => 'search is required.'];
        }

        $students = Student::query()
            ->with(['user', 'currentClass'])
            ->where(function ($inner) use ($search): void {
                $inner->where('admission_no', 'like', '%'.$search.'%')
                    ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', '%'.$search.'%'));
            })
            ->limit(8)
            ->get()
            ->filter(fn (Student $student): bool => $user->can('view', $student))
            ->values();

        if ($students->count() !== 1) {
            return [
                'ok' => $students->isNotEmpty(),
                'error' => $students->isEmpty()
                    ? 'No accessible student matched that search.'
                    : 'Multiple students matched. Ask the user to pick one.',
                'matches' => $students->map(fn (Student $student): array => [
                    'id' => $student->id,
                    'name' => $student->user?->name,
                    'admission_no' => $student->admission_no,
                    'class' => $student->currentClass?->code,
                ])->all(),
            ];
        }

        $student = $students->first();
        $monthStart = now()->startOfMonth();
        $summary = $this->attendanceMonthlySummary->forStudent($student, $monthStart, $monthStart->copy()->endOfMonth());

        return [
            'ok' => true,
            'student' => [
                'id' => $student->id,
                'name' => $student->user?->name,
                'admission_no' => $student->admission_no,
                'class' => $student->currentClass?->code,
            ],
            'attendance_this_month' => [
                'percentage' => $summary['percentage'],
                'counts' => $summary['counts'],
            ],
        ];
    }
}
