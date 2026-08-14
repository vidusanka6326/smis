<?php

namespace App\Models;

use App\Enums\Gender;
use Database\Factories\StudentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    /** @use HasFactory<StudentFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'admission_no',
        'date_of_birth',
        'gender',
        'guardian_name',
        'guardian_phone',
        'guardian_email',
        'guardian_relationship',
        'current_class_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'gender' => Gender::class,
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<SchoolClass, $this>
     */
    public function currentClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'current_class_id');
    }

    /**
     * @return HasMany<StudentEnrollment, $this>
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    /**
     * @param  Builder<Student>  $query
     * @return Builder<Student>
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['gender'] ?? null, fn (Builder $q, string $gender) => $q->where('gender', $gender))
            ->when($filters['class_id'] ?? null, fn (Builder $q, int|string $classId) => $q->where('current_class_id', $classId))
            ->when($filters['grade_id'] ?? null, function (Builder $q, int|string $gradeId): void {
                $q->whereHas('currentClass', fn (Builder $classQuery) => $classQuery->where('grade_id', $gradeId));
            })
            ->when($filters['subject_id'] ?? null, function (Builder $q, int|string $subjectId): void {
                $q->whereHas('currentClass.subjects', fn (Builder $subjectQuery) => $subjectQuery->where('subjects.id', $subjectId));
            })
            ->when($filters['status'] ?? null, function (Builder $q, string $status): void {
                $q->whereHas('user', fn (Builder $userQuery) => $userQuery->where('status', $status));
            })
            ->when($filters['search'] ?? null, function (Builder $q, string $search): void {
                $q->where(function (Builder $inner) use ($search): void {
                    $inner->where('admission_no', 'like', "%{$search}%")
                        ->orWhereHas('user', function (Builder $userQuery) use ($search): void {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            });
    }
}
