<?php

namespace App\Services\Agent\Tools;

use App\Enums\PermissionName;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\Agent\AgentScope;

class ListClassesTool extends AbstractAgentTool
{
    public function __construct(private AgentScope $scope) {}

    public function name(): string
    {
        return 'list_classes';
    }

    public function description(): string
    {
        return 'List classes in the current academic year that the user may access. Use this when the class code is unknown.';
    }

    public function parameters(): array
    {
        return $this->objectSchema([
            'search' => $this->stringParam('Optional class code or name fragment such as 10 or 10-A.'),
        ]);
    }

    public function authorized(User $user): bool
    {
        return $user->can(PermissionName::ManageSystemConfig->value)
            || $user->can(PermissionName::ViewTimetable->value);
    }

    public function handle(User $user, array $arguments): array
    {
        $yearId = $this->scope->requireAcademicYearId();
        $classIds = $this->scope->accessibleClassIds($user);
        $search = $this->stringArg($arguments, 'search');

        $classes = SchoolClass::query()
            ->with(['grade', 'stream'])
            ->where('academic_year_id', $yearId)
            ->when($classIds !== null, fn ($query) => $query->whereIn('id', $classIds ?? []))
            ->when($search !== null, function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('code', 'like', '%'.$search.'%')
                        ->orWhere('name', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('code')
            ->limit(40)
            ->get()
            ->map(fn (SchoolClass $class): array => [
                'id' => $class->id,
                'code' => $class->code,
                'name' => $class->name,
                'grade' => $class->grade?->number,
            ])
            ->all();

        return [
            'ok' => true,
            'classes' => $classes,
        ];
    }
}
