<?php

namespace App\Enums;

enum ActivityAction: string
{
    case UserCreated = 'user.created';
    case MarksUpserted = 'marks.upserted';
    case ExamPublished = 'exam.published';
    case ExamUnpublished = 'exam.unpublished';
    case AttendanceSessionUpserted = 'attendance.session.upserted';
    case AttendanceSessionFinalized = 'attendance.session.finalized';
    case TeacherAttendanceUpserted = 'attendance.teacher.upserted';
    case AgentMutated = 'agent.mutated';

    public function label(): string
    {
        return match ($this) {
            self::UserCreated => __('User created'),
            self::MarksUpserted => __('Marks updated'),
            self::ExamPublished => __('Exam published'),
            self::ExamUnpublished => __('Exam unpublished'),
            self::AttendanceSessionUpserted => __('Attendance session saved'),
            self::AttendanceSessionFinalized => __('Attendance session finalized'),
            self::TeacherAttendanceUpserted => __('Teacher attendance saved'),
            self::AgentMutated => __('SMIS Agent action'),
        };
    }
}
