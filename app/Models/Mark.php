<?php

namespace App\Models;

use App\Enums\GradeLetter;
use Database\Factories\MarkFactory;
use Illuminate\Database\Eloquent\Builder;
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

    /**
     * @param  Builder<Mark>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Mark>
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['exam_id'] ?? null, function (Builder $q, int|string $examId): void {
                $q->whereHas('examSubject', fn (Builder $subjectQuery) => $subjectQuery->where('exam_id', $examId));
            })
            ->when($filters['subject_id'] ?? null, function (Builder $q, int|string $subjectId): void {
                $q->whereHas('examSubject', fn (Builder $subjectQuery) => $subjectQuery->where('subject_id', $subjectId));
            })
            ->when(($filters['result'] ?? null) === 'pass', fn (Builder $q) => $q->where('is_pass', true))
            ->when(($filters['result'] ?? null) === 'fail', fn (Builder $q) => $q->where('is_pass', false));
    }
}
