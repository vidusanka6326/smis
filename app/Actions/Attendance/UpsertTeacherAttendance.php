<?php

namespace App\Actions\Attendance;

use App\Enums\AttendanceStatus;
use App\Models\Teacher;
use App\Models\TeacherAttendance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpsertTeacherAttendance
{
    /**
     * Create or update a teacher's daily attendance record.
     *
     * @param  array{
     *     teacher_id: int,
     *     date: string,
     *     status: string,
     *     notes?: string|null
     * }  $data
     */
    public function handle(array $data, User $recordedBy, ?TeacherAttendance $existing = null): TeacherAttendance
    {
        $teacher = Teacher::query()->findOrFail($data['teacher_id']);

        $duplicate = TeacherAttendance::query()
            ->where('teacher_id', $teacher->id)
            ->whereDate('date', $data['date'])
            ->when($existing !== null, fn ($q) => $q->whereKeyNot($existing->id))
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'date' => __('Attendance already exists for this teacher on the selected date.'),
            ]);
        }

        return DB::transaction(function () use ($data, $recordedBy, $existing, $teacher): TeacherAttendance {
            $payload = [
                'teacher_id' => $teacher->id,
                'date' => $data['date'],
                'status' => AttendanceStatus::from($data['status']),
                'recorded_by' => $recordedBy->id,
                'notes' => $data['notes'] ?? null,
            ];

            if ($existing !== null) {
                $existing->update($payload);

                return $existing->refresh()->load(['teacher.user', 'recordedBy']);
            }

            return TeacherAttendance::query()->create($payload)->load(['teacher.user', 'recordedBy']);
        });
    }
}
