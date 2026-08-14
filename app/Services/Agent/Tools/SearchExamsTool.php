<?php

namespace App\Services\Agent\Tools;

use App\Models\Exam;
use App\Models\User;
use App\Services\Agent\AgentScope;

class SearchExamsTool extends AbstractAgentTool
{
    public function __construct(private AgentScope $scope) {}

    public function name(): string
    {
        return 'search_exams';
    }

    public function description(): string
    {
        return 'Search exams in the current academic year by name. Students never use this tool.';
    }

    public function parameters(): array
    {
        return $this->objectSchema([
            'search' => $this->stringParam('Exam name fragment such as First Term.'),
        ]);
    }

    public function authorized(User $user): bool
    {
        return $this->scope->canViewMarks($user);
    }

    public function handle(User $user, array $arguments): array
    {
        $yearId = $this->scope->requireAcademicYearId();
        $search = $this->stringArg($arguments, 'search');

        $exams = [];

        foreach (Exam::query()
            ->with(['grade', 'schoolClass'])
            ->where('academic_year_id', $yearId)
            ->when($search !== null, fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->orderByDesc('starts_on')
            ->limit(15)
            ->get() as $exam) {
            if (! $user->can('view', $exam)) {
                continue;
            }

            $exams[] = [
                'id' => $exam->id,
                'name' => $exam->name,
                'type' => (string) $exam->getRawOriginal('type'),
                'grade' => $exam->grade?->number,
                'class' => $exam->schoolClass?->code,
                'published' => $exam->isPublished(),
            ];
        }

        return [
            'ok' => true,
            'exams' => $exams,
        ];
    }
}
