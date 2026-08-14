<?php

namespace App\Models;

use Database\Factories\SubjectFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Subject extends Model
{
    /** @use HasFactory<SubjectFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'code',
        'min_grade',
        'max_grade',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'min_grade' => 'integer',
            'max_grade' => 'integer',
        ];
    }

    /**
     * @param  Builder<Subject>  $query
     * @return Builder<Subject>
     */
    public function scopeForGrade(Builder $query, int $gradeNumber): Builder
    {
        return $query
            ->where('min_grade', '<=', $gradeNumber)
            ->where('max_grade', '>=', $gradeNumber);
    }

    public function appliesToGrade(int $gradeNumber): bool
    {
        return $gradeNumber >= $this->min_grade && $gradeNumber <= $this->max_grade;
    }

    /**
     * @return BelongsToMany<SchoolClass, $this>
     */
    public function schoolClasses(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'class_subject')
            ->withTimestamps();
    }

    /**
     * @param  Builder<Subject>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Subject>
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['grade'] ?? null, function (Builder $q, int|string $grade): void {
                $q->forGrade((int) $grade);
            })
            ->when($filters['search'] ?? null, function (Builder $q, string $search): void {
                $q->where(function (Builder $inner) use ($search): void {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            });
    }
}
