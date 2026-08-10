<?php

namespace App\Models;

use Database\Factories\ReliefTeacherAssignmentFactory;
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
}
