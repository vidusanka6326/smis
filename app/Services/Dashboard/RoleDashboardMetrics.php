<?php

namespace App\Services\Dashboard;

use App\Enums\DayOfWeek;
use App\Models\Exam;
use App\Models\Mark;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Teacher;
use App\Models\TimetableEntry;
use App\Services\Attendance\AttendancePercentageCalculator;
use App\Services\Reporting\AttendanceAnalyticsReport;
use App\Services\Reporting\DemographicsReport;
use App\Services\Reporting\ExaminationStatisticsReport;
use App\Services\Reporting\TeacherReportScope;
use App\Services\Timetable\TimetableConflictDetector;

class RoleDashboardMetrics
{
    public function __construct(
        private DemographicsReport $demographics,
        private AttendanceAnalyticsReport $attendance,
        private ExaminationStatisticsReport $examination,
        private TeacherReportScope $teacherScope,
        private AttendancePercentageCalculator $attendancePercentage,
        private TimetableConflictDetector $timetable,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function forAdmin(): array
    {
        $demo = $this->demographics->summarize();
        $monthStart = now()->startOfMonth();
        $attendanceSummary = $this->attendance->forMonth($monthStart, (clone $monthStart)->endOfMonth());
        $latestExam = Exam::query()->whereNotNull('published_at')->latest('published_at')->first();
        $examStats = $latestExam !== null ? $this->examination->forExam($latestExam) : null;

        $byGrade = $demo['by_grade'];

        return [
            'stats' => [
                'students' => Student::query()->count(),
                'teachers' => Teacher::query()->count(),
                'classes' => SchoolClass::query()->count(),
                'published_exams' => Exam::query()->whereNotNull('published_at')->count(),
                'draft_exams' => Exam::query()->whereNull('published_at')->count(),
                'attendance_tracked' => count($attendanceSummary['student_rows']),
                'avg_attendance' => $attendanceSummary['summary']['class_average']
                    ?? $this->averageAttendance($attendanceSummary['student_rows']),
                'at_risk_count' => $attendanceSummary['summary']['at_risk_count'],
                'pass_rate' => $examStats['pass_rate'] ?? null,
            ],
            'exam' => $latestExam,
            'examStats' => $examStats,
            'charts' => [
                'gender' => [
                    'labels' => [__('Boys'), __('Girls')],
                    'data' => [
                        $demo['by_gender']['B'] ?? 0,
                        $demo['by_gender']['G'] ?? 0,
                    ],
                ],
                'grades' => [
                    'labels' => array_column($byGrade, 'grade'),
                    'data' => array_column($byGrade, 'count'),
                ],
                'letters' => [
                    'labels' => array_keys($examStats['by_grade_letter'] ?? []),
                    'data' => array_values($examStats['by_grade_letter'] ?? []),
                ],
                'attendance_by_class' => [
                    'labels' => array_column($attendanceSummary['class_rows'], 'code'),
                    'data' => array_map(
                        fn (array $row): float => (float) ($row['percentage'] ?? 0),
                        $attendanceSummary['class_rows'],
                    ),
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function forTeacher(Teacher $teacher): array
    {
        $classIds = $this->teacherScope->accessibleClassIds($teacher);
        $demo = $this->demographics->summarize($classIds);
        $monthStart = now()->startOfMonth();
        $attendanceSummary = $this->attendance->forMonth($monthStart, (clone $monthStart)->endOfMonth(), $classIds);
        $latestExam = Exam::query()->whereNotNull('published_at')->latest('published_at')->first();
        $studentIds = $this->teacherScope->accessibleStudentIds($teacher);
        $examStats = $latestExam !== null
            ? $this->examination->forExam($latestExam, null, $studentIds)
            : null;

        $today = DayOfWeek::tryFrom((int) now()->isoWeekday());
        $todaySlots = [];

        if ($today !== null) {
            $yearId = $teacher->assignments->first()?->academic_year_id
                ?? $teacher->homeroomClasses->first()?->academic_year_id;

            if ($yearId !== null) {
                $entries = $this->timetable->entriesForTeacher($teacher->id, (int) $yearId);
                $todaySlots = $entries
                    ->filter(fn (TimetableEntry $entry) => $entry->day_of_week === $today)
                    ->sortBy('period_number')
                    ->values()
                    ->all();
            }
        }

        return [
            'stats' => [
                'students' => $demo['total'],
                'classes' => count($classIds),
                'assignments' => $teacher->assignments->count(),
                'homerooms' => $teacher->homeroomClasses->count(),
                'attendance_tracked' => count($attendanceSummary['student_rows']),
                'avg_attendance' => $attendanceSummary['summary']['class_average']
                    ?? $this->averageAttendance($attendanceSummary['student_rows']),
                'at_risk_count' => $attendanceSummary['summary']['at_risk_count'],
                'pass_rate' => $examStats['pass_rate'] ?? null,
            ],
            'exam' => $latestExam,
            'examStats' => $examStats,
            'todaySlots' => $todaySlots,
            'charts' => [
                'gender' => [
                    'labels' => [__('Boys'), __('Girls')],
                    'data' => [
                        $demo['by_gender']['B'] ?? 0,
                        $demo['by_gender']['G'] ?? 0,
                    ],
                ],
                'letters' => [
                    'labels' => array_keys($examStats['by_grade_letter'] ?? []),
                    'data' => array_values($examStats['by_grade_letter'] ?? []),
                ],
                'attendance_by_class' => [
                    'labels' => array_column($attendanceSummary['class_rows'], 'code'),
                    'data' => array_map(
                        fn (array $row): float => (float) ($row['percentage'] ?? 0),
                        $attendanceSummary['class_rows'],
                    ),
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function forStudent(Student $student): array
    {
        $monthStart = now()->startOfMonth();
        $monthEnd = (clone $monthStart)->endOfMonth();

        $statuses = StudentAttendance::query()
            ->where('student_id', $student->id)
            ->whereHas('attendanceSession', function ($query) use ($monthStart, $monthEnd): void {
                $query->whereBetween('date', [
                    $monthStart->toDateString(),
                    $monthEnd->toDateString(),
                ]);
            })
            ->pluck('status')
            ->all();

        $attendancePercent = $statuses === []
            ? null
            : $this->attendancePercentage->percentage($statuses);

        $marks = Mark::query()
            ->with(['examSubject.exam', 'examSubject.subject'])
            ->where('student_id', $student->id)
            ->whereHas('examSubject.exam', fn ($q) => $q->whereNotNull('published_at'))
            ->latest('id')
            ->limit(8)
            ->get();

        $letterCounts = $marks
            ->groupBy(fn (Mark $mark) => $mark->grade_letter->value)
            ->map->count()
            ->all();

        $today = DayOfWeek::tryFrom((int) now()->isoWeekday());
        $todaySlots = [];

        if ($today !== null && $student->current_class_id !== null && $student->currentClass !== null) {
            $class = $student->currentClass;
            $entries = $this->timetable->entriesForClass($class->id, (int) $class->academic_year_id);
            $todaySlots = $entries
                ->filter(fn (TimetableEntry $entry) => $entry->day_of_week === $today)
                ->sortBy('period_number')
                ->values()
                ->all();
        }

        return [
            'stats' => [
                'attendance_percent' => $attendancePercent,
                'published_marks' => $marks->count(),
                'subjects' => $student->currentClass?->subjects?->count() ?? 0,
                'sessions_this_month' => count($statuses),
            ],
            'recentMarks' => $marks,
            'todaySlots' => $todaySlots,
            'charts' => [
                'letters' => [
                    'labels' => array_keys($letterCounts),
                    'data' => array_values($letterCounts),
                ],
            ],
        ];
    }

    /**
     * @param  list<array{percentage?: float|int}>  $rows
     */
    private function averageAttendance(array $rows): ?float
    {
        if ($rows === []) {
            return null;
        }

        $sum = 0.0;
        foreach ($rows as $row) {
            $sum += (float) ($row['percentage'] ?? 0);
        }

        return round($sum / count($rows), 1);
    }
}
