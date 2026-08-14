<?php

namespace App\Services\Agent\Tools;

use App\Models\Exam;
use App\Models\User;
use App\Services\Agent\AgentScope;
use App\Services\Reporting\ExamResultsReport;
use App\Services\Reporting\TeacherReportScope;

class GetExamResultsTool extends AbstractAgentTool
{
    public function __construct(
        private AgentScope $scope,
        private ExamResultsReport $examResultsReport,
        private TeacherReportScope $teacherReportScope,
    ) {}

    public function name(): string
    {
        return 'get_exam_results';
    }

    public function description(): string
    {
        return 'Fetch exam results by exam name. Teachers only see students in their assigned classes.';
    }

    public function parameters(): array
    {
        return $this->objectSchema([
            'exam_name' => $this->stringParam('Exam name fragment.'),
            'class_code' => $this->stringParam('Optional class code to narrow results.'),
            'result' => $this->stringParam('Optional pass or fail filter.'),
        ], ['exam_name']);
    }

    public function authorized(User $user): bool
    {
        return $this->scope->canViewMarks($user);
    }

    public function handle(User $user, array $arguments): array
    {
        $examName = $this->stringArg($arguments, 'exam_name');

        if ($examName === null) {
            return ['ok' => false, 'error' => 'exam_name is required.'];
        }

        $yearId = $this->scope->requireAcademicYearId();
        $exams = Exam::query()
            ->where('academic_year_id', $yearId)
            ->where('name', 'like', '%'.$examName.'%')
            ->limit(5)
            ->get()
            ->filter(fn (Exam $exam): bool => $user->can('view', $exam))
            ->values();

        if ($exams->count() !== 1) {
            return [
                'ok' => false,
                'error' => $exams->isEmpty()
                    ? 'No accessible exam matched that name.'
                    : 'Multiple exams matched. Ask the user to pick one.',
                'matches' => $exams->map(fn (Exam $exam): array => [
                    'id' => $exam->id,
                    'name' => $exam->name,
                ])->all(),
            ];
        }

        $exam = $exams->first();
        $studentIds = null;
        $classCode = $this->stringArg($arguments, 'class_code');

        if ($user->isTeacher() && $user->teacher) {
            $studentIds = $this->teacherReportScope->accessibleStudentIds($user->teacher);
        }

        if ($classCode !== null) {
            $class = $this->scope->resolveClass($user, $classCode);
            $classStudentIds = array_values(array_map(
                fn (mixed $id): int => (int) $id,
                $class->students()->pluck('id')->all(),
            ));
            $studentIds = $studentIds === null
                ? $classStudentIds
                : array_values(array_intersect($studentIds, $classStudentIds));
        }

        $result = $this->stringArg($arguments, 'result');
        $rows = $this->examResultsReport->forExam($exam, null, $studentIds, $result);

        return [
            'ok' => true,
            'exam' => $exam->name,
            'results' => array_slice($rows, 0, 40),
            'count' => count($rows),
        ];
    }
}
