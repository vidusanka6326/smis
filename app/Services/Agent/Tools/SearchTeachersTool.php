<?php

namespace App\Services\Agent\Tools;

use App\Models\User;
use App\Services\Agent\AgentScope;

class SearchTeachersTool extends AbstractAgentTool
{
    public function __construct(private AgentScope $scope) {}

    public function name(): string
    {
        return 'search_teachers';
    }

    public function description(): string
    {
        return 'Search teachers by name or employee number. Office staff only.';
    }

    public function parameters(): array
    {
        return $this->objectSchema([
            'search' => $this->stringParam('Name or employee number fragment.'),
        ], ['search']);
    }

    public function authorized(User $user): bool
    {
        return $this->scope->canListTeachers($user);
    }

    public function handle(User $user, array $arguments): array
    {
        $search = $this->stringArg($arguments, 'search');

        if ($search === null) {
            return ['ok' => false, 'error' => 'search is required.'];
        }

        $resolved = $this->scope->resolveTeacher($search);

        if (isset($resolved['teacher'])) {
            $teacher = $resolved['teacher'];

            return [
                'ok' => true,
                'teachers' => [[
                    'id' => $teacher->id,
                    'name' => $teacher->user?->name,
                    'employee_no' => $teacher->employee_no,
                ]],
            ];
        }

        return [
            'ok' => true,
            'teachers' => $resolved['matches'],
        ];
    }
}
