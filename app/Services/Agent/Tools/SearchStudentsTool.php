<?php

namespace App\Services\Agent\Tools;

use App\Models\Student;
use App\Models\User;
use App\Services\Agent\AgentScope;
use App\Services\Reporting\TeacherReportScope;

class SearchStudentsTool extends AbstractAgentTool
{
    public function __construct(
        private AgentScope $scope,
        private TeacherReportScope $teacherReportScope,
    ) {}

    public function name(): string
    {
        return 'search_students';
    }

    public function description(): string
    {
        return 'Search students by name or admission number. Teachers only see students in their assigned classes.';
    }

    public function parameters(): array
    {
        return $this->objectSchema([
            'search' => $this->stringParam('Name or admission number fragment.'),
            'class_code' => $this->stringParam('Optional class code to narrow results.'),
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

        $query = Student::query()
            ->with(['user', 'currentClass'])
            ->where(function ($inner) use ($search): void {
                $inner->where('admission_no', 'like', '%'.$search.'%')
                    ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', '%'.$search.'%'));
            });

        $classCode = $this->stringArg($arguments, 'class_code');

        if ($classCode !== null) {
            $class = $this->scope->resolveClass($user, $classCode);
            $query->where('current_class_id', $class->id);
        } elseif ($user->isTeacher() && $user->teacher) {
            $query->whereIn('current_class_id', $this->teacherReportScope->accessibleClassIds($user->teacher));
        }

        $students = $query->orderBy('admission_no')->limit(20)->get();

        $visible = $students->filter(fn (Student $student): bool => $user->can('view', $student));

        return [
            'ok' => true,
            'students' => $visible->map(fn (Student $student): array => [
                'id' => $student->id,
                'name' => $student->user?->name,
                'admission_no' => $student->admission_no,
                'class' => $student->currentClass?->code,
            ])->values()->all(),
        ];
    }
}
