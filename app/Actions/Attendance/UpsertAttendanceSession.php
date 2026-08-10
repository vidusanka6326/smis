<?php

namespace App\Actions\Attendance;

use App\Enums\AttendanceStatus;
use App\Models\AttendanceSession;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpsertAttendanceSession
{
    /**
     * Create or update an attendance session and sync per-student statuses.
     *
     * @param  array{
     *     academic_year_id: int,
     *     school_class_id: int,
     *     subject_id?: int|null,
     *     date: string,
     *     notes?: string|null,
     *     taken_by_teacher_id?: int|null,
     *     finalize?: bool,
     *     records: array<int, array{student_id: int, status: string}>
     * }  $data
     */
    public function handle(array $data, ?AttendanceSession $existing = null): AttendanceSession
    {
        $schoolClass = SchoolClass::query()->with('subjects')->findOrFail($data['school_class_id']);
        $subjectId = isset($data['subject_id']) && $data['subject_id'] !== '' && $data['subject_id'] !== null
            ? (int) $data['subject_id']
            : null;

        if ((int) $schoolClass->academic_year_id !== (int) $data['academic_year_id']) {
            throw ValidationException::withMessages([
                'school_class_id' => __('The class must belong to the selected academic year.'),
            ]);
        }

        if ($subjectId !== null) {
            $subject = Subject::query()->findOrFail($subjectId);

            if (! $schoolClass->subjects->contains('id', $subject->id)) {
                throw ValidationException::withMessages([
                    'subject_id' => __('The subject must be linked to the selected class.'),
                ]);
            }
        }

        $scope = AttendanceSession::scopeKey($subjectId);
        $duplicate = AttendanceSession::query()
            ->where('school_class_id', $schoolClass->id)
            ->whereDate('date', $data['date'])
            ->where('scope', $scope)
            ->when($existing !== null, fn ($q) => $q->whereKeyNot($existing->id))
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'date' => __('An attendance session already exists for this class, date, and subject scope.'),
            ]);
        }

        $records = $data['records'] ?? [];
        $studentIds = collect($records)->pluck('student_id')->map(fn ($id) => (int) $id)->all();
        $classStudentIds = Student::query()
            ->where('current_class_id', $schoolClass->id)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        foreach ($studentIds as $studentId) {
            if (! in_array($studentId, $classStudentIds, true)) {
                throw ValidationException::withMessages([
                    'records' => __('All students must belong to the selected class.'),
                ]);
            }
        }

        if ($studentIds !== array_values(array_unique($studentIds))) {
            throw ValidationException::withMessages([
                'records' => __('Duplicate student attendance rows are not allowed.'),
            ]);
        }

        return DB::transaction(function () use ($data, $existing, $schoolClass, $subjectId, $scope, $records): AttendanceSession {
            $payload = [
                'academic_year_id' => $data['academic_year_id'],
                'school_class_id' => $schoolClass->id,
                'subject_id' => $subjectId,
                'date' => $data['date'],
                'scope' => $scope,
                'notes' => $data['notes'] ?? null,
                'taken_by_teacher_id' => $data['taken_by_teacher_id'] ?? $existing?->taken_by_teacher_id,
            ];

            if (! empty($data['finalize'])) {
                $payload['finalized_at'] = now();
            }

            if ($existing !== null) {
                $existing->update($payload);
                $session = $existing->refresh();
            } else {
                $session = AttendanceSession::query()->create($payload);
            }

            $keptStudentIds = [];

            foreach ($records as $record) {
                $studentId = (int) $record['student_id'];
                $keptStudentIds[] = $studentId;
                $status = AttendanceStatus::from($record['status']);

                StudentAttendance::query()->updateOrCreate(
                    [
                        'attendance_session_id' => $session->id,
                        'student_id' => $studentId,
                    ],
                    ['status' => $status],
                );
            }

            if ($existing !== null) {
                StudentAttendance::query()
                    ->where('attendance_session_id', $session->id)
                    ->whereNotIn('student_id', $keptStudentIds)
                    ->delete();
            }

            return $session->load(['schoolClass', 'subject', 'takenByTeacher.user', 'studentAttendances.student.user']);
        });
    }

    public function finalize(AttendanceSession $session, ?Teacher $teacher = null): AttendanceSession
    {
        if ($session->isFinalized()) {
            return $session;
        }

        $session->update([
            'finalized_at' => now(),
            'taken_by_teacher_id' => $teacher?->id ?? $session->taken_by_teacher_id,
        ]);

        return $session->refresh();
    }
}
