<?php

namespace App\Services\Reporting;

use App\Enums\AttendanceStatus;
use App\Models\Teacher;
use App\Models\TeacherAttendance;
use App\Services\Attendance\AttendancePercentageCalculator;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class StaffAttendanceReport
{
    public function __construct(private AttendancePercentageCalculator $calculator) {}

    /**
     * @return list<array{teacher_id: int, name: string, employee_no: string, percentage: float, present: int, absent: int, late: int, excused: int}>
     */
    public function forMonth(CarbonInterface $monthStart, CarbonInterface $monthEnd, ?int $teacherId = null): array
    {
        $teachers = Teacher::query()
            ->with('user')
            ->when($teacherId !== null, fn ($q) => $q->whereKey($teacherId))
            ->get()
            ->sortBy(fn (Teacher $teacher): string => (string) ($teacher->user?->name ?? ''))
            ->values();

        $records = TeacherAttendance::query()
            ->whereDate('date', '>=', $monthStart->toDateString())
            ->whereDate('date', '<=', $monthEnd->toDateString())
            ->when($teacherId !== null, fn ($q) => $q->where('teacher_id', $teacherId))
            ->get()
            ->groupBy('teacher_id');

        return $teachers->map(function (Teacher $teacher) use ($records): array {
            /** @var Collection<int, TeacherAttendance> $rows */
            $rows = $records->get($teacher->id, collect());
            $statuses = $rows->pluck('status')->all();
            $counts = $this->countStatuses($statuses);

            return [
                'teacher_id' => $teacher->id,
                'name' => $teacher->user?->name ?? (string) $teacher->id,
                'employee_no' => (string) ($teacher->employee_no ?? '—'),
                'percentage' => $this->calculator->percentage($statuses),
                'present' => $counts[AttendanceStatus::Present->value],
                'absent' => $counts[AttendanceStatus::Absent->value],
                'late' => $counts[AttendanceStatus::Late->value],
                'excused' => $counts[AttendanceStatus::Excused->value],
            ];
        })->all();
    }

    /**
     * @param  list<AttendanceStatus|string>  $statuses
     * @return array{present: int, absent: int, late: int, excused: int}
     */
    private function countStatuses(array $statuses): array
    {
        $counts = [
            AttendanceStatus::Present->value => 0,
            AttendanceStatus::Absent->value => 0,
            AttendanceStatus::Late->value => 0,
            AttendanceStatus::Excused->value => 0,
        ];

        foreach ($statuses as $status) {
            $enum = $status instanceof AttendanceStatus ? $status : AttendanceStatus::from((string) $status);
            $counts[$enum->value]++;
        }

        return $counts;
    }
}
