<?php

namespace App\Models;

use App\Enums\DayOfWeek;
use Database\Factories\TimetableEntryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimetableEntry extends Model
{
    /** @use HasFactory<TimetableEntryFactory> */
    use HasFactory;

    protected $table = 'timetables';

    /**
     * Default number of periods per school day.
     */
    public const MAX_PERIODS = 8;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'academic_year_id',
        'school_class_id',
        'day_of_week',
        'period_number',
        'subject_id',
        'teacher_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'day_of_week' => DayOfWeek::class,
            'period_number' => 'integer',
        ];
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
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * @return HasMany<ReliefTeacherAssignment, $this>
     */
    public function reliefAssignments(): HasMany
    {
        return $this->hasMany(ReliefTeacherAssignment::class, 'timetable_entry_id');
    }
}
