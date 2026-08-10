<?php

namespace App\Models;

use App\Enums\GradeLetter;
use Database\Factories\MarkFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mark extends Model
{
    /** @use HasFactory<MarkFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'exam_subject_id',
        'student_id',
        'marks_obtained',
        'grade_letter',
        'is_pass',
        'entered_by_teacher_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'marks_obtained' => 'decimal:2',
            'grade_letter' => GradeLetter::class,
            'is_pass' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<ExamSubject, $this>
     */
    public function examSubject(): BelongsTo
    {
        return $this->belongsTo(ExamSubject::class);
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return BelongsTo<Teacher, $this>
     */
    public function enteredByTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'entered_by_teacher_id');
    }
}
