<?php

namespace App\Services\Agent;

use App\Enums\DayOfWeek;
use App\Enums\PermissionName;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TimetableEntry;
use App\Models\User;
use App\Services\Reporting\TeacherReportScope;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AgentScope
{
    public function __construct(private TeacherReportScope $teacherReportScope) {}

    public function currentAcademicYearId(): ?int
    {
        $id = AcademicYear::query()->where('is_current', true)->value('id')
            ?? AcademicYear::query()->latest('starts_on')->value('id');

        return $id !== null ? (int) $id : null;
    }

    public function requireAcademicYearId(): int
    {
        $id = $this->currentAcademicYearId();

        if ($id === null) {
            throw ValidationException::withMessages([
                'academic_year' => __('No academic year is configured.'),
            ]);
        }

        return $id;
    }

    /**
     * @return list<int>|null Null means every class in the current year.
     */
    public function accessibleClassIds(User $user): ?array
    {
        if ($user->isSchoolOffice()) {
            return null;
        }

        if ($user->isTeacher() && $user->teacher) {
            return $this->teacherReportScope->accessibleClassIds($user->teacher);
        }

        return [];
    }

    public function canViewClass(User $user, SchoolClass $schoolClass): bool
    {
        if ($user->can(PermissionName::ManageTimetable->value) || $user->can(PermissionName::ManageSystemConfig->value)) {
            return true;
        }

        if ($user->isTeacher() && $user->teacher && $user->can(PermissionName::ViewTimetable->value)) {
            return in_array($schoolClass->id, $this->teacherReportScope->accessibleClassIds($user->teacher), true);
        }

        return false;
    }

    public function canMutateTimetable(User $user): bool
    {
        return $user->can(PermissionName::ManageTimetable->value);
    }

    public function canListTeachers(User $user): bool
    {
        return $user->can(PermissionName::ManageTeachers->value)
            || $user->can(PermissionName::ManageTimetable->value);
    }

    public function canViewStudents(User $user): bool
    {
        return $user->can(PermissionName::ManageStudents->value)
            || ($user->isTeacher() && $user->teacher !== null);
    }

    public function canViewAttendance(User $user): bool
    {
        return $user->can(PermissionName::ViewAttendance->value)
            || $user->can(PermissionName::ManageAttendance->value);
    }

    public function canViewMarks(User $user): bool
    {
        return $user->can(PermissionName::ViewMarks->value)
            || $user->can(PermissionName::EnterMarks->value)
            || $user->can(PermissionName::ManageExaminations->value);
    }

    public function resolveClass(User $user, string $classCode): SchoolClass
    {
        $normalized = $this->normalizeClassCode($classCode);
        $yearId = $this->requireAcademicYearId();

        $schoolClass = SchoolClass::query()
            ->with(['grade', 'stream', 'classTeacher.user', 'subjects'])
            ->where('academic_year_id', $yearId)
            ->where(function ($query) use ($normalized, $classCode): void {
                $query->whereRaw('UPPER(REPLACE(code, ?, ?)) = ?', [' ', '', $normalized])
                    ->orWhereRaw('UPPER(code) = ?', [Str::upper(trim($classCode))]);
            })
            ->first();

        if ($schoolClass === null) {
            throw ValidationException::withMessages([
                'class_code' => __('No class matched ":code" in the current academic year.', ['code' => $classCode]),
            ]);
        }

        if (! $this->canViewClass($user, $schoolClass)) {
            throw ValidationException::withMessages([
                'class_code' => __('You do not have access to class :code.', ['code' => $schoolClass->code]),
            ]);
        }

        return $schoolClass;
    }

    /**
     * @return array{teacher: Teacher}|array{matches: list<array{id: int, name: string, employee_no: string}>}
     */
    public function resolveTeacher(string $name): array
    {
        $search = trim($name);

        $teachers = Teacher::query()
            ->with('user')
            ->where(function ($query) use ($search): void {
                $query->whereHas('user', function ($userQuery) use ($search): void {
                    $userQuery->where('name', 'like', '%'.$search.'%');
                })->orWhere('employee_no', 'like', '%'.$search.'%');
            })
            ->limit(8)
            ->get();

        if ($teachers->count() === 1) {
            return ['teacher' => $teachers->first()];
        }

        if ($teachers->isEmpty()) {
            throw ValidationException::withMessages([
                'teacher' => __('No teacher matched ":name".', ['name' => $name]),
            ]);
        }

        $matches = [];

        foreach ($teachers as $teacher) {
            $matches[] = [
                'id' => $teacher->id,
                'name' => $teacher->user->name,
                'employee_no' => $teacher->employee_no,
            ];
        }

        return [
            'matches' => $matches,
        ];
    }

    public function resolveTeacherOrFail(string $name): Teacher
    {
        $resolved = $this->resolveTeacher($name);

        if (isset($resolved['teacher'])) {
            return $resolved['teacher'];
        }

        $names = collect($resolved['matches'])->pluck('name')->implode(', ');

        throw ValidationException::withMessages([
            'teacher' => __('Multiple teachers matched ":name": :matches. Ask the user to pick one.', [
                'name' => $name,
                'matches' => $names,
            ]),
        ]);
    }

    public function resolveSubject(SchoolClass $schoolClass, string $name): Subject
    {
        $search = Str::lower(trim($name));
        $subjects = $schoolClass->subjects()
            ->get()
            ->filter(function (Subject $subject) use ($search): bool {
                return Str::contains(Str::lower($subject->name), $search)
                    || Str::lower($subject->code) === $search;
            })
            ->values();

        if ($subjects->count() === 1) {
            return $subjects->first();
        }

        if ($subjects->isEmpty()) {
            throw ValidationException::withMessages([
                'subject' => __('No subject matched ":name" for class :code.', [
                    'name' => $name,
                    'code' => $schoolClass->code,
                ]),
            ]);
        }

        throw ValidationException::withMessages([
            'subject' => __('Multiple subjects matched ":name". Linked subjects: :subjects.', [
                'name' => $name,
                'subjects' => $subjects->pluck('name')->implode(', '),
            ]),
        ]);
    }

    public function parseDay(string|int $value): DayOfWeek
    {
        if (is_numeric($value)) {
            $day = DayOfWeek::tryFrom((int) $value);

            if ($day === null) {
                throw ValidationException::withMessages([
                    'day_of_week' => __('Day must be Monday–Friday (1–5).'),
                ]);
            }

            return $day;
        }

        $normalized = Str::lower(trim($value));

        return match ($normalized) {
            'mon', 'monday' => DayOfWeek::Monday,
            'tue', 'tues', 'tuesday' => DayOfWeek::Tuesday,
            'wed', 'wednesday' => DayOfWeek::Wednesday,
            'thu', 'thur', 'thurs', 'thursday' => DayOfWeek::Thursday,
            'fri', 'friday' => DayOfWeek::Friday,
            default => throw ValidationException::withMessages([
                'day_of_week' => __('Could not parse weekday ":value". Use Monday–Friday.', ['value' => $value]),
            ]),
        };
    }

    public function normalizeClassCode(string $classCode): string
    {
        $code = Str::upper(str_replace(' ', '', trim($classCode)));

        if (! str_contains($code, '-')) {
            $withHyphen = preg_replace('/^(\d+)([A-Z].*)$/', '$1-$2', $code);
            $code = is_string($withHyphen) ? $withHyphen : $code;
        }

        return $code;
    }

    public function entryMatchesSlot(TimetableEntry $entry, DayOfWeek $day, int $period): bool
    {
        return (int) $entry->getRawOriginal('day_of_week') === $day->value
            && (int) $entry->period_number === $period;
    }
}
