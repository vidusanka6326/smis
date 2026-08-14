<?php

namespace App\Services\Agent;

use App\Enums\DayOfWeek;
use App\Enums\Gender;
use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Stream;
use App\Models\Student;
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

    public function resolveAcademicYear(?string $name = null): AcademicYear
    {
        if ($name === null) {
            $year = AcademicYear::query()->where('is_current', true)->first()
                ?? AcademicYear::query()->latest('starts_on')->first();

            if ($year === null) {
                throw ValidationException::withMessages([
                    'academic_year' => __('No academic year is configured.'),
                ]);
            }

            return $year;
        }

        $search = trim($name);
        $years = AcademicYear::query()
            ->where('name', 'like', '%'.$search.'%')
            ->orderByDesc('starts_on')
            ->limit(8)
            ->get();

        if ($years->count() === 1) {
            return $years->first();
        }

        if ($years->isEmpty()) {
            throw ValidationException::withMessages([
                'academic_year' => __('No academic year matched ":name".', ['name' => $name]),
            ]);
        }

        throw ValidationException::withMessages([
            'academic_year' => __('Multiple academic years matched ":name": :matches.', [
                'name' => $name,
                'matches' => $years->pluck('name')->implode(', '),
            ]),
        ]);
    }

    public function resolveGrade(string $value): Grade
    {
        $search = trim($value);

        if (is_numeric($search)) {
            $grade = Grade::query()->where('number', (int) $search)->first();

            if ($grade === null) {
                throw ValidationException::withMessages([
                    'grade' => __('No grade numbered :number exists.', ['number' => $search]),
                ]);
            }

            return $grade;
        }

        $grades = Grade::query()
            ->where('name', 'like', '%'.$search.'%')
            ->orderBy('number')
            ->limit(8)
            ->get();

        if ($grades->count() === 1) {
            return $grades->first();
        }

        if ($grades->isEmpty()) {
            throw ValidationException::withMessages([
                'grade' => __('No grade matched ":name".', ['name' => $value]),
            ]);
        }

        throw ValidationException::withMessages([
            'grade' => __('Multiple grades matched ":name": :matches.', [
                'name' => $value,
                'matches' => $grades->pluck('name')->implode(', '),
            ]),
        ]);
    }

    public function resolveStream(string $value): Stream
    {
        $search = trim($value);
        $streams = Stream::query()
            ->where(function ($query) use ($search): void {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('code', 'like', '%'.$search.'%');
            })
            ->orderBy('name')
            ->limit(8)
            ->get();

        if ($streams->count() === 1) {
            return $streams->first();
        }

        if ($streams->isEmpty()) {
            throw ValidationException::withMessages([
                'stream' => __('No stream matched ":name".', ['name' => $value]),
            ]);
        }

        throw ValidationException::withMessages([
            'stream' => __('Multiple streams matched ":name": :matches.', [
                'name' => $value,
                'matches' => $streams->pluck('name')->implode(', '),
            ]),
        ]);
    }

    public function resolveSubjectByName(string $name): Subject
    {
        $search = Str::lower(trim($name));
        $subjects = Subject::query()
            ->where(function ($query) use ($name, $search): void {
                $query->where('name', 'like', '%'.$name.'%')
                    ->orWhereRaw('LOWER(code) = ?', [$search]);
            })
            ->orderBy('name')
            ->limit(8)
            ->get();

        if ($subjects->count() === 1) {
            return $subjects->first();
        }

        if ($subjects->isEmpty()) {
            throw ValidationException::withMessages([
                'subject' => __('No subject matched ":name".', ['name' => $name]),
            ]);
        }

        throw ValidationException::withMessages([
            'subject' => __('Multiple subjects matched ":name": :matches.', [
                'name' => $name,
                'matches' => $subjects->pluck('name')->implode(', '),
            ]),
        ]);
    }

    public function resolveStudent(User $user, string $search): Student
    {
        $resolved = $this->matchStudents($user, $search);

        if (isset($resolved['student'])) {
            return $resolved['student'];
        }

        $names = collect($resolved['matches'])->map(
            fn (array $match): string => $match['name'].' ('.$match['admission_no'].')',
        )->implode(', ');

        throw ValidationException::withMessages([
            'student' => $resolved['matches'] === []
                ? __('No accessible student matched ":search".', ['search' => $search])
                : __('Multiple students matched ":search": :matches. Ask the user to pick one.', [
                    'search' => $search,
                    'matches' => $names,
                ]),
        ]);
    }

    /**
     * @return array{student: Student}|array{matches: list<array{id: int, name: string, admission_no: string, class: string|null}>}
     */
    public function matchStudents(User $user, string $search): array
    {
        $term = trim($search);

        $students = Student::query()
            ->with(['user', 'currentClass'])
            ->where(function ($query) use ($term): void {
                $query->where('admission_no', 'like', '%'.$term.'%')
                    ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', '%'.$term.'%'));
            })
            ->limit(8)
            ->get()
            ->filter(fn (Student $student): bool => $user->can('view', $student))
            ->values();

        if ($students->count() === 1) {
            return ['student' => $students->first()];
        }

        $matches = [];

        foreach ($students as $student) {
            $matches[] = [
                'id' => $student->id,
                'name' => $student->user->name,
                'admission_no' => $student->admission_no,
                'class' => $student->currentClass?->code,
            ];
        }

        return ['matches' => $matches];
    }

    public function resolveExam(User $user, string $name): Exam
    {
        $yearId = $this->requireAcademicYearId();
        $exams = Exam::query()
            ->where('academic_year_id', $yearId)
            ->where('name', 'like', '%'.trim($name).'%')
            ->limit(8)
            ->get()
            ->filter(fn (Exam $exam): bool => $user->can('view', $exam))
            ->values();

        if ($exams->count() === 1) {
            return $exams->first();
        }

        if ($exams->isEmpty()) {
            throw ValidationException::withMessages([
                'exam' => __('No accessible exam matched ":name".', ['name' => $name]),
            ]);
        }

        throw ValidationException::withMessages([
            'exam' => __('Multiple exams matched ":name": :matches.', [
                'name' => $name,
                'matches' => $exams->pluck('name')->implode(', '),
            ]),
        ]);
    }

    public function resolveOfficer(string $name): User
    {
        $search = trim($name);
        $officers = User::query()
            ->role(RoleName::Officer)
            ->where(function ($query) use ($search): void {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            })
            ->limit(8)
            ->get();

        if ($officers->count() === 1) {
            return $officers->first();
        }

        if ($officers->isEmpty()) {
            throw ValidationException::withMessages([
                'officer' => __('No officer matched ":name".', ['name' => $name]),
            ]);
        }

        throw ValidationException::withMessages([
            'officer' => __('Multiple officers matched ":name": :matches.', [
                'name' => $name,
                'matches' => $officers->pluck('name')->implode(', '),
            ]),
        ]);
    }

    public function parseGender(string $value): Gender
    {
        return match (Str::lower(trim($value))) {
            'g', 'girl', 'female', 'f' => Gender::Girl,
            'b', 'boy', 'male', 'm' => Gender::Boy,
            default => throw ValidationException::withMessages([
                'gender' => __('Gender must be G/B (girl or boy). Got ":value".', ['value' => $value]),
            ]),
        };
    }

    /**
     * Pick a student from an already-scoped collection (class roster or exam-eligible list).
     *
     * @param  iterable<int, Student>  $students
     */
    public function pickStudent(iterable $students, string $search): Student
    {
        $term = Str::lower(trim($search));
        $matches = collect($students)->filter(function (Student $student) use ($term): bool {
            $name = Str::lower((string) $student->user->name);
            $admission = Str::lower((string) $student->admission_no);

            return $admission === $term
                || Str::contains($admission, $term)
                || Str::contains($name, $term);
        })->values();

        if ($matches->count() === 1) {
            return $matches->first();
        }

        if ($matches->isEmpty()) {
            throw ValidationException::withMessages([
                'student' => __('No accessible student matched ":search".', ['search' => $search]),
            ]);
        }

        throw ValidationException::withMessages([
            'student' => __('Multiple students matched ":search": :matches. Ask the user to pick one.', [
                'search' => $search,
                'matches' => $matches->map(
                    fn (Student $student): string => $student->user->name.' ('.$student->admission_no.')',
                )->implode(', '),
            ]),
        ]);
    }
}
