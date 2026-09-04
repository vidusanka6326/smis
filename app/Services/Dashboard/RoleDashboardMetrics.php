<?php

namespace App\Services\Dashboard;

use App\Enums\AttendanceStatus;
use App\Enums\DayOfWeek;
use App\Enums\TeacherAssignmentRole;
use App\Models\ActivityLog;
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
use App\Services\Reporting\PerformanceRankingService;
use App\Services\Reporting\TeacherReportScope;
use App\Services\Timetable\TimetableConflictDetector;

class RoleDashboardMetrics
{
    public function __construct(
        private DemographicsReport $demographics,
        private AttendanceAnalyticsReport $attendance,
        private ExaminationStatisticsReport $examination,
        private PerformanceRankingService $ranking,
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
        $ranks = $latestExam !== null
            ? $this->ranking->forExam($latestExam, null, null, 5)
            : ['best' => [], 'poor' => []];

        $draftExams = Exam::query()
            ->whereNull('published_at')
            ->orderByDesc('starts_on')
            ->limit(6)
            ->get(['id', 'name', 'starts_on']);

        $recentActivity = ActivityLog::query()
            ->with('causer')
            ->latest('created_at')
            ->limit(8)
            ->get();

        return [
            'stats' => [
                'students' => Student::query()->count(),
                'teachers' => Teacher::query()->count(),
                'classes' => SchoolClass::query()->count(),
                'boys' => $demo['by_gender']['B'] ?? 0,
                'girls' => $demo['by_gender']['G'] ?? 0,
                'published_exams' => Exam::query()->whereNotNull('published_at')->count(),
                'draft_exams' => Exam::query()->whereNull('published_at')->count(),
                'attendance_tracked' => $attendanceSummary['summary']['tracked_students'],
                'avg_attendance' => $attendanceSummary['summary']['class_average']
                    ?? $this->averageAttendance($attendanceSummary['student_rows']),
                'at_risk_count' => $attendanceSummary['summary']['at_risk_count'],
                'pass_rate' => $examStats['pass_rate'] ?? null,
                'fail_count' => $examStats['fail_count'] ?? 0,
                'pass_count' => $examStats['pass_count'] ?? 0,
                'average_percentage' => $examStats['average_percentage'] ?? null,
            ],
            'exam' => $latestExam,
            'examStats' => $examStats,
            'atRiskPreview' => array_slice($attendanceSummary['at_risk'], 0, 8),
            'bestPreview' => $ranks['best'],
            'poorPreview' => $ranks['poor'],
            'draftExams' => $draftExams,
            'recentActivity' => $recentActivity,
            'charts' => [
                'gender' => [
                    'labels' => [__('Boys'), __('Girls')],
                    'data' => [
                        $demo['by_gender']['B'] ?? 0,
                        $demo['by_gender']['G'] ?? 0,
                    ],
                ],
                'grades' => [
                    'labels' => array_column($demo['by_grade'], 'grade'),
                    'data' => array_column($demo['by_grade'], 'count'),
                ],
                'classes' => [
                    'labels' => array_column($demo['by_class'], 'code'),
                    'data' => array_column($demo['by_class'], 'count'),
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
                'subject_pass_rates' => [
                    'labels' => array_column($examStats['by_subject'] ?? [], 'subject'),
                    'data' => array_column($examStats['by_subject'] ?? [], 'pass_rate'),
                ],
                'class_exam_averages' => [
                    'labels' => array_column($examStats['by_class'] ?? [], 'code'),
                    'data' => array_column($examStats['by_class'] ?? [], 'average_percentage'),
                ],
                'pass_fail' => [
                    'labels' => [__('Pass'), __('Fail')],
                    'data' => [
                        $examStats['pass_count'] ?? 0,
                        $examStats['fail_count'] ?? 0,
                    ],
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
        $ranks = $latestExam !== null
            ? $this->ranking->forExam($latestExam, null, $studentIds, 5)
            : ['best' => [], 'poor' => []];

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

        // ── Role detection ──────────────────────────────────────────────────
        $isClassTeacher = $teacher->homeroomClasses->isNotEmpty()
            || $teacher->assignments->contains(
                'role_in_assignment', TeacherAssignmentRole::ClassTeacher
            );

        $subjectAssignments = $teacher->assignments->filter(
            fn ($a) => $a->role_in_assignment === TeacherAssignmentRole::SubjectTeacher
                && $a->subject !== null
        );
        $isSubjectTeacher = $subjectAssignments->isNotEmpty();

        // ── Per-subject metrics (for subject teacher dashboard) ─────────────
        $subjectMetrics = [];
        if ($isSubjectTeacher && $latestExam !== null) {
            $distinctSubjects = $subjectAssignments->unique('subject_id')->values();
            foreach ($distinctSubjects as $assignment) {
                $subject = $assignment->subject;
                $subjectStudentIds = $this->teacherScope->accessibleStudentIds($teacher, $subject->id);
                $subjectStats = $this->examination->forExam($latestExam, $subject->id, $subjectStudentIds);

                // Class distribution for this subject
                $classCodes = $subjectAssignments
                    ->where('subject_id', $subject->id)
                    ->map(fn ($a) => $a->schoolClass?->code)
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                $subjectMetrics[] = [
                    'subject_id' => $subject->id,
                    'subject_name' => $subject->name,
                    'classes' => $classCodes,
                    'student_count' => count($subjectStudentIds),
                    'pass_rate' => $subjectStats['pass_rate'] ?? 0.0,
                    'average' => $subjectStats['average_percentage'] ?? 0.0,
                    'pass_count' => $subjectStats['pass_count'] ?? 0,
                    'fail_count' => $subjectStats['fail_count'] ?? 0,
                    'by_grade_letter' => $subjectStats['by_grade_letter'] ?? [],
                    'chart_id' => 'subjectChart_'.$subject->id,
                ];
            }
        }

        return [
            'stats' => [
                'students' => $demo['total'],
                'classes' => count($classIds),
                'assignments' => $teacher->assignments->count(),
                'homerooms' => $teacher->homeroomClasses->count(),
                'boys' => $demo['by_gender']['B'] ?? 0,
                'girls' => $demo['by_gender']['G'] ?? 0,
                'attendance_tracked' => $attendanceSummary['summary']['tracked_students'],
                'avg_attendance' => $attendanceSummary['summary']['class_average']
                    ?? $this->averageAttendance($attendanceSummary['student_rows']),
                'at_risk_count' => $attendanceSummary['summary']['at_risk_count'],
                'pass_rate' => $examStats['pass_rate'] ?? null,
                'fail_count' => $examStats['fail_count'] ?? 0,
                'pass_count' => $examStats['pass_count'] ?? 0,
                'average_percentage' => $examStats['average_percentage'] ?? null,
                'lessons_today' => count($todaySlots),
            ],
            'exam' => $latestExam,
            'examStats' => $examStats,
            'todaySlots' => $todaySlots,
            'atRiskPreview' => array_slice($attendanceSummary['at_risk'], 0, 8),
            'bestPreview' => $ranks['best'],
            'poorPreview' => $ranks['poor'],
            'isClassTeacher' => $isClassTeacher,
            'isSubjectTeacher' => $isSubjectTeacher,
            'subjectMetrics' => $subjectMetrics,
            'charts' => [
                'gender' => [
                    'labels' => [__('Boys'), __('Girls')],
                    'data' => [
                        $demo['by_gender']['B'] ?? 0,
                        $demo['by_gender']['G'] ?? 0,
                    ],
                ],
                'classes' => [
                    'labels' => array_column($demo['by_class'], 'code'),
                    'data' => array_column($demo['by_class'], 'count'),
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
                'subject_pass_rates' => [
                    'labels' => array_column($examStats['by_subject'] ?? [], 'subject'),
                    'data' => array_column($examStats['by_subject'] ?? [], 'pass_rate'),
                ],
                'pass_fail' => [
                    'labels' => [__('Pass'), __('Fail')],
                    'data' => [
                        $examStats['pass_count'] ?? 0,
                        $examStats['fail_count'] ?? 0,
                    ],
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

        $attendanceRecords = StudentAttendance::query()
            ->where('student_id', $student->id)
            ->whereHas('attendanceSession', function ($query) use ($monthStart, $monthEnd): void {
                $query->whereBetween('date', [
                    $monthStart->toDateString(),
                    $monthEnd->toDateString(),
                ]);
            })
            ->get();

        $statuses = $attendanceRecords->pluck('status')->all();
        $attendanceCounts = $this->attendance->countStatuses(collect($statuses));

        $attendancePercent = $statuses === []
            ? null
            : $this->attendancePercentage->percentage($statuses);

        $allMarks = Mark::query()
            ->with(['examSubject.exam', 'examSubject.subject'])
            ->where('student_id', $student->id)
            ->whereHas('examSubject.exam', fn ($q) => $q->whereNotNull('published_at'))
            ->latest('id')
            ->get();

        $recentMarks = $allMarks->take(10)->values();

        $letterCounts = $allMarks
            ->groupBy(fn (Mark $mark) => $mark->grade_letter->value)
            ->map->count()
            ->all();

        $passCount = $allMarks->where('is_pass', true)->count();
        $failCount = $allMarks->where('is_pass', false)->count();

        $overallAverage = null;
        $subjectBuckets = [];
        foreach ($allMarks as $mark) {
            $max = (float) ($mark->examSubject?->max_marks ?? 0);
            $obtained = (float) $mark->marks_obtained;
            $pct = $max > 0 ? ($obtained / $max) * 100 : 0.0;
            $subjectName = $mark->examSubject?->subject?->name ?? '—';
            $subjectBuckets[$subjectName] ??= ['sum' => 0.0, 'count' => 0];
            $subjectBuckets[$subjectName]['sum'] += $pct;
            $subjectBuckets[$subjectName]['count']++;
        }

        if ($allMarks->isNotEmpty()) {
            $sumPct = 0.0;
            foreach ($allMarks as $mark) {
                $max = (float) ($mark->examSubject?->max_marks ?? 0);
                $obtained = (float) $mark->marks_obtained;
                $sumPct += $max > 0 ? ($obtained / $max) * 100 : 0;
            }
            $overallAverage = round($sumPct / $allMarks->count(), 1);
        }

        ksort($subjectBuckets);
        $subjectLabels = array_keys($subjectBuckets);
        $subjectAverages = array_map(
            fn (array $bucket): float => round($bucket['sum'] / max($bucket['count'], 1), 1),
            array_values($subjectBuckets),
        );

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

        $failedMarks = $allMarks->where('is_pass', false)->take(6)->values();

        return [
            'stats' => [
                'attendance_percent' => $attendancePercent,
                'published_marks' => $allMarks->count(),
                'subjects' => $student->currentClass?->subjects?->count() ?? 0,
                'sessions_this_month' => count($statuses),
                'present' => $attendanceCounts[AttendanceStatus::Present->value],
                'absent' => $attendanceCounts[AttendanceStatus::Absent->value],
                'late' => $attendanceCounts[AttendanceStatus::Late->value],
                'excused' => $attendanceCounts[AttendanceStatus::Excused->value],
                'pass_count' => $passCount,
                'fail_count' => $failCount,
                'overall_average' => $overallAverage,
                'lessons_today' => count($todaySlots),
            ],
            'recentMarks' => $recentMarks,
            'failedMarks' => $failedMarks,
            'todaySlots' => $todaySlots,
            'charts' => [
                'letters' => [
                    'labels' => array_keys($letterCounts),
                    'data' => array_values($letterCounts),
                ],
                'attendance_status' => [
                    'labels' => [__('Present'), __('Absent'), __('Late'), __('Excused')],
                    'data' => [
                        $attendanceCounts[AttendanceStatus::Present->value],
                        $attendanceCounts[AttendanceStatus::Absent->value],
                        $attendanceCounts[AttendanceStatus::Late->value],
                        $attendanceCounts[AttendanceStatus::Excused->value],
                    ],
                ],
                'pass_fail' => [
                    'labels' => [__('Pass'), __('Fail')],
                    'data' => [$passCount, $failCount],
                ],
                'subject_averages' => [
                    'labels' => $subjectLabels,
                    'data' => $subjectAverages,
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
