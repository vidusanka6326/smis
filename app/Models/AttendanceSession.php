<?php

namespace App\Models;

use Database\Factories\AttendanceSessionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceSession extends Model
{
    /** @use HasFactory<AttendanceSessionFactory> */
    use HasFactory;

    public const SCOPE_CLASS = 'class';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'academic_year_id',
        'school_class_id',
        'subject_id',
        'date',
        'scope',
        'taken_by_teacher_id',
        'finalized_at',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'finalized_at' => 'datetime',
        ];
    }

    public static function scopeKey(?int $subjectId): string
    {
        return $subjectId === null
            ? self::SCOPE_CLASS
            : 'subject:'.$subjectId;
    }

    public function isClassSession(): bool
    {
        return $this->scope === self::SCOPE_CLASS;
    }

    public function isFinalized(): bool
    {
        return $this->finalized_at !== null;
    }

    /**
     * @return BelongsTo<AcademicYear, $this>
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * @return BelongsTo<SchoolClass, $this>
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    /**
     * @return BelongsTo<Subject, $this>
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * @return BelongsTo<Teacher, $this>
     */
    public function takenByTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'taken_by_teacher_id');
    }

    /**
     * @return HasMany<StudentAttendance, $this>
     */
    public function studentAttendances(): HasMany
    {
        return $this->hasMany(StudentAttendance::class);
    }

    /**
     * @param  Builder<AttendanceSession>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<AttendanceSession>
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['academic_year_id'] ?? null, fn (Builder $q, int|string $id) => $q->where('academic_year_id', $id))
            ->when($filters['school_class_id'] ?? null, fn (Builder $q, int|string $id) => $q->where('school_class_id', $id))
            ->when($filters['subject_id'] ?? null, fn (Builder $q, int|string $id) => $q->where('subject_id', $id))
            ->when(($filters['scope'] ?? null) === 'class', fn (Builder $q) => $q->where('scope', self::SCOPE_CLASS))
            ->when(($filters['scope'] ?? null) === 'subject', fn (Builder $q) => $q->where('scope', '!=', self::SCOPE_CLASS))
            ->when(($filters['status'] ?? null) === 'finalized', fn (Builder $q) => $q->whereNotNull('finalized_at'))
            ->when(($filters['status'] ?? null) === 'open', fn (Builder $q) => $q->whereNull('finalized_at'))
            ->when($filters['date_from'] ?? null, fn (Builder $q, string $date) => $q->whereDate('date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $q, string $date) => $q->whereDate('date', '<=', $date));
    }
}
