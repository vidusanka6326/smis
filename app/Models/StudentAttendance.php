<?php

namespace App\Models;

use App\Enums\AttendanceStatus;
use Database\Factories\StudentAttendanceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAttendance extends Model
{
    /** @use HasFactory<StudentAttendanceFactory> */
    use HasFactory;

    protected $table = 'student_attendance';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'attendance_session_id',
        'student_id',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => AttendanceStatus::class,
        ];
    }

    /**
     * @return BelongsTo<AttendanceSession, $this>
     */
    public function attendanceSession(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class);
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
