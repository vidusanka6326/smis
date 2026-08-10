<?php

namespace App\Models;

use Database\Factories\AttendanceSessionFactory;
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
}
