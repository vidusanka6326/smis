<?php

namespace App\Services\Agent\Tools;

use App\Enums\PermissionName;
use App\Models\Subject;
use App\Models\User;
use App\Services\Agent\AgentScope;

class LookupClassTool extends AbstractAgentTool
{
    public function __construct(private AgentScope $scope) {}

    public function name(): string
    {
        return 'lookup_class';
    }

    public function description(): string
    {
        return 'Resolve a class by code such as 10-A and return subjects, grade, and class teacher.';
    }

    public function parameters(): array
    {
        return $this->objectSchema([
            'class_code' => $this->stringParam('Class code such as 10-A or 12-SCI-A.'),
        ], ['class_code']);
    }

    public function authorized(User $user): bool
    {
        return $user->can(PermissionName::ViewTimetable->value)
            || $user->can(PermissionName::ManageSystemConfig->value);
    }

    public function handle(User $user, array $arguments): array
    {
        $code = $this->stringArg($arguments, 'class_code');

        if ($code === null) {
            return ['ok' => false, 'error' => 'class_code is required.'];
        }

        $class = $this->scope->resolveClass($user, $code);

        return [
            'ok' => true,
            'class' => [
                'id' => $class->id,
                'code' => $class->code,
                'name' => $class->name,
                'grade' => $class->grade?->number,
                'stream' => $class->stream?->name,
                'class_teacher' => $class->classTeacher?->user?->name,
                'subjects' => $class->subjects->map(fn (Subject $subject): array => [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'code' => $subject->code,
                ])->values()->all(),
            ],
        ];
    }
}
