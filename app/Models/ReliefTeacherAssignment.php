<?php

namespace App\Models;

use Database\Factories\ReliefTeacherAssignmentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReliefTeacherAssignment extends Model
{
    /** @use HasFactory<ReliefTeacherAssignmentFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'timetable_entry_id',
        'relief_teacher_id',
        'date',
        'reason',
        'assigned_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<TimetableEntry, $this>
     */
    public function timetableEntry(): BelongsTo
    {
        return $this->belongsTo(TimetableEntry::class, 'timetable_entry_id');
    }

    /**
     * @return BelongsTo<Teacher, $this>
     */
    public function reliefTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'relief_teacher_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assignedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * @param  Builder<ReliefTeacherAssignment>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<ReliefTeacherAssignment>
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['date_from'] ?? null, fn (Builder $q, string $date) => $q->whereDate('date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $q, string $date) => $q->whereDate('date', '<=', $date))
            ->when($filters['school_class_id'] ?? null, function (Builder $q, int|string $classId): void {
                $q->whereHas('timetableEntry', fn (Builder $entry) => $entry->where('school_class_id', $classId));
            })
            ->when($filters['teacher_id'] ?? null, function (Builder $q, int|string $teacherId): void {
                $q->where(function (Builder $inner) use ($teacherId): void {
                    $inner->where('relief_teacher_id', $teacherId)
                        ->orWhereHas('timetableEntry', fn (Builder $entry) => $entry->where('teacher_id', $teacherId));
                });
            })
            ->when($filters['search'] ?? null, function (Builder $q, string $search): void {
                $q->where('reason', 'like', "%{$search}%");
            });
    }
}
