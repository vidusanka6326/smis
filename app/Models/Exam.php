<?php

namespace App\Models;

use App\Enums\ExamType;
use Database\Factories\ExamFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    /** @use HasFactory<ExamFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'type',
        'academic_year_id',
        'grade_id',
        'school_class_id',
        'starts_on',
        'ends_on',
        'published_at',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ExamType::class,
            'starts_on' => 'date',
            'ends_on' => 'date',
            'published_at' => 'datetime',
        ];
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null;
    }

    /**
     * @return BelongsTo<AcademicYear, $this>
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * @return BelongsTo<Grade, $this>
     */
    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    /**
     * @return BelongsTo<SchoolClass, $this>
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<ExamSubject, $this>
     */
    public function examSubjects(): HasMany
    {
        return $this->hasMany(ExamSubject::class);
    }

    /**
     * @param  Builder<Exam>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Exam>
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['academic_year_id'] ?? null, fn (Builder $q, int|string $id) => $q->where('academic_year_id', $id))
            ->when($filters['type'] ?? null, fn (Builder $q, string $type) => $q->where('type', $type))
            ->when($filters['grade_id'] ?? null, fn (Builder $q, int|string $id) => $q->where('grade_id', $id))
            ->when($filters['school_class_id'] ?? null, fn (Builder $q, int|string $id) => $q->where('school_class_id', $id))
            ->when(($filters['status'] ?? null) === 'published', fn (Builder $q) => $q->whereNotNull('published_at'))
            ->when(($filters['status'] ?? null) === 'draft', fn (Builder $q) => $q->whereNull('published_at'))
            ->when($filters['search'] ?? null, fn (Builder $q, string $search) => $q->where('name', 'like', "%{$search}%"));
    }

    /**
     * Students eligible for this exam (class-scoped or all in grade for the year).
     *
     * @return Collection<int, Student>
     */
    public function eligibleStudents()
    {
        $query = Student::query()->with('user');

        if ($this->school_class_id !== null) {
            return $query->where('current_class_id', $this->school_class_id)->orderBy('admission_no')->get();
        }

        if ($this->grade_id !== null) {
            return $query
                ->whereHas('currentClass', function ($classQuery): void {
                    $classQuery->where('grade_id', $this->grade_id)
                        ->where('academic_year_id', $this->academic_year_id);
                })
                ->orderBy('admission_no')
                ->get();
        }

        return $query
            ->whereHas('currentClass', fn ($q) => $q->where('academic_year_id', $this->academic_year_id))
            ->orderBy('admission_no')
            ->get();
    }
}
