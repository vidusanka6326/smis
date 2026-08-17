<?php

namespace App\Enums;

enum TeacherAssignmentRole: string
{
    case ClassTeacher = 'class_teacher';
    case SubjectTeacher = 'subject_teacher';
    case PtPdTeacher = 'pt_pd_teacher';

    public function label(): string
    {
        return match ($this) {
            self::ClassTeacher => __('Class teacher'),
            self::SubjectTeacher => __('Subject teacher'),
            self::PtPdTeacher => __('PT/PD teacher'),
        };
    }

    public function requiresSubject(): bool
    {
        return $this === self::SubjectTeacher;
    }
}
