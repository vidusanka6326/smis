<?php

namespace App\Services\Attendance;

use App\Enums\AttendanceStatus;
use App\Models\AttendanceSession;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Teacher;
use App\Models\TeacherAttendance;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class AttendanceMonthlySummary
{
    public function __construct(private AttendancePercentageCalculator $calculator) {}

    /**
     * @return array{
     *     percentage: float,
     *     counts: array{present: int, absent: int, late: int, excused: int},
     *     records: Collection<int, StudentAttendance>
     * }
     */
    public function forStudent(Student $student, CarbonInterface $monthStart, CarbonInterface $monthEnd): array
    {
        $records = StudentAttendance::query()
            ->with(['attendanceSession.schoolClass', 'attendanceSession.subject'])
            ->where('student_id', $student->id)
            ->whereHas('attendanceSession', function ($query) use ($monthStart, $monthEnd): void {
                $query->whereDate('date', '>=', $monthStart->toDateString())
                    ->whereDate('date', '<=', $monthEnd->toDateString());
            })
            ->get();

        return $this->summarize($records->pluck('status')->all(), $records);
    }

    /**
     * @return array{
     *     percentage: float,
     *     counts: array{present: int, absent: int, late: int, excused: int},
     *     records: Collection<int, TeacherAttendance>
     * }
     */
    public function forTeacher(Teacher $teacher, CarbonInterface $monthStart, CarbonInterface $monthEnd): array
    {
        $records = TeacherAttendance::query()
            ->where('teacher_id', $teacher->id)
            ->whereDate('date', '>=', $monthStart->toDateString())
            ->whereDate('date', '<=', $monthEnd->toDateString())
            ->orderBy('date')
            ->get();

        return $this->summarize($records->pluck('status')->all(), $records);
    }

    /**
     * Class monthly rollup across class-level sessions in the month.
     *
     * @return list<array{student: Student, percentage: float, counts: array{present: int, absent: int, late: int, excused: int}}>
     */
    public function forClass(int $schoolClassId, CarbonInterface $monthStart, CarbonInterface $monthEnd, ?int $subjectId = null): array
    {
        $scope = AttendanceSession::scopeKey($subjectId);

        $sessionIds = AttendanceSession::query()
            ->where('school_class_id', $schoolClassId)
            ->where('scope', $scope)
            ->whereDate('date', '>=', $monthStart->toDateString())
            ->whereDate('date', '<=', $monthEnd->toDateString())
            ->pluck('id');

        $students = Student::query()
            ->with('user')
            ->where('current_class_id', $schoolClassId)
            ->orderBy('admission_no')
            ->get();

        $byStudent = StudentAttendance::query()
            ->whereIn('attendance_session_id', $sessionIds)
            ->get()
            ->groupBy('student_id');

        $rows = [];

        foreach ($students as $student) {
            /** @var Collection<int, StudentAttendance> $records */
            $records = $byStudent->get($student->id, collect());
            $summary = $this->summarize($records->pluck('status')->all(), $records);
            $rows[] = [
                'student' => $student,
                'percentage' => $summary['percentage'],
                'counts' => $summary['counts'],
            ];
        }

        return $rows;
    }

    /**
     * @param  list<AttendanceStatus|string>  $statuses
     * @param  Collection<int, mixed>  $records
     * @return array{
     *     percentage: float,
     *     counts: array{present: int, absent: int, late: int, excused: int},
     *     records: Collection<int, mixed>
     * }
     */
    private function summarize(array $statuses, Collection $records): array
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

        return [
            'percentage' => $this->calculator->percentage($statuses),
            'counts' => $counts,
            'records' => $records,
        ];
    }
}
