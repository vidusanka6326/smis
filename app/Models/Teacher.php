<?php

namespace App\Models;

use App\Enums\TeacherAssignmentRole;
use Database\Factories\TeacherFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Teacher extends Model
{
    /** @use HasFactory<TeacherFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'employee_no',
        'phone',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<TeacherDataSheet, $this>
     */
    public function dataSheets(): HasMany
    {
        return $this->hasMany(TeacherDataSheet::class);
    }

    /**
     * @return HasOne<TeacherDataSheet, $this>
     */
    public function dataSheetForYear(int $year): HasOne
    {
        return $this->hasOne(TeacherDataSheet::class)->where('year', $year);
    }

    /**
     * @return HasMany<TeacherAssignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(TeacherAssignment::class);
    }

    /**
     * @return HasMany<SchoolClass, $this>
     */
    public function homeroomClasses(): HasMany
    {
        return $this->hasMany(SchoolClass::class, 'class_teacher_id');
    }

    /**
     * @param  Builder<Teacher>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Teacher>
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['class_id'] ?? null, function (Builder $q, int|string $classId) use ($filters): void {
                $q->where(function (Builder $inner) use ($classId, $filters): void {
                    $role = $filters['role'] ?? null;

                    if ($role === null || $role === TeacherAssignmentRole::ClassTeacher->value) {
                        $inner->whereHas('homeroomClasses', fn (Builder $classQuery) => $classQuery->whereKey($classId));
                    }

                    $inner->orWhereHas('assignments', function (Builder $assignmentQuery) use ($classId, $role): void {
                        $assignmentQuery->where('school_class_id', $classId)
                            ->when($role, fn (Builder $roleQuery, string $assignmentRole) => $roleQuery->where('role_in_assignment', $assignmentRole));
                    });
                });
            })
            ->when($filters['subject_id'] ?? null, function (Builder $q, int|string $subjectId): void {
                $q->whereHas('assignments', fn (Builder $assignmentQuery) => $assignmentQuery->where('subject_id', $subjectId));
            })
            ->when($filters['role'] ?? null, function (Builder $q, string $role): void {
                $q->whereHas('assignments', fn (Builder $assignmentQuery) => $assignmentQuery->where('role_in_assignment', $role));
            })
            ->when($filters['search'] ?? null, function (Builder $q, string $search): void {
                $q->where(function (Builder $inner) use ($search): void {
                    $inner->where('employee_no', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhereHas('user', function (Builder $userQuery) use ($search): void {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            });
    }

    /**
     * Whether this teacher is the class teacher for the given class (homeroom or assignment).
     */
    public function isClassTeacherOf(SchoolClass $schoolClass): bool
    {
        if ($this->homeroomClasses()->whereKey($schoolClass->id)->exists()) {
            return true;
        }

        return $this->assignments()
            ->where('school_class_id', $schoolClass->id)
            ->where('role_in_assignment', TeacherAssignmentRole::ClassTeacher)
            ->exists();
    }

    /**
     * Whether this teacher may take/edit student attendance for a class session (optionally subject-scoped).
     *
     * Assumption: subject-teacher period attendance is enabled; class teachers may take any session in their class.
     */
    public function canTakeStudentAttendance(SchoolClass $schoolClass, ?int $subjectId = null): bool
    {
        if ($this->isClassTeacherOf($schoolClass)) {
            return true;
        }

        if ($subjectId === null) {
            return $this->assignments()
                ->where('school_class_id', $schoolClass->id)
                ->where('role_in_assignment', TeacherAssignmentRole::PtPdTeacher)
                ->exists();
        }

        return $this->assignments()
            ->where('school_class_id', $schoolClass->id)
            ->where('subject_id', $subjectId)
            ->whereIn('role_in_assignment', [
                TeacherAssignmentRole::SubjectTeacher,
                TeacherAssignmentRole::PtPdTeacher,
            ])
            ->exists();
    }

    /**
     * Whether this teacher may view student attendance for a class/subject context.
     */
    public function canViewStudentAttendance(SchoolClass $schoolClass, ?int $subjectId = null): bool
    {
        if ($this->canTakeStudentAttendance($schoolClass, $subjectId)) {
            return true;
        }

        if ($subjectId === null) {
            return false;
        }

        return $this->assignments()
            ->where('school_class_id', $schoolClass->id)
            ->where('subject_id', $subjectId)
            ->exists();
    }

    /**
     * Whether this teacher may enter marks for a subject in a given class.
     *
     * Assumption: class teachers may enter marks for all subjects in their own class.
     * Subject teachers enter only their assigned subject. PT/PD cannot enter marks.
     */
    public function canEnterMarksFor(SchoolClass $schoolClass, int $subjectId): bool
    {
        if ($this->isClassTeacherOf($schoolClass)) {
            return true;
        }

        return $this->assignments()
            ->where('school_class_id', $schoolClass->id)
            ->where('subject_id', $subjectId)
            ->where('role_in_assignment', TeacherAssignmentRole::SubjectTeacher)
            ->exists();
    }

    /**
     * Whether this teacher may view marks for a subject in a given class.
     */
    public function canViewMarksFor(SchoolClass $schoolClass, int $subjectId): bool
    {
        return $this->canEnterMarksFor($schoolClass, $subjectId);
    }
}
