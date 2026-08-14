<?php

namespace App\Models;

use Database\Factories\AcademicYearFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    /** @use HasFactory<AcademicYearFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'starts_on',
        'ends_on',
        'is_current',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'is_current' => 'boolean',
        ];
    }

    /**
     * @return HasMany<SchoolClass, $this>
     */
    public function schoolClasses(): HasMany
    {
        return $this->hasMany(SchoolClass::class);
    }

    /**
     * @param  Builder<AcademicYear>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<AcademicYear>
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(array_key_exists('is_current', $filters) && $filters['is_current'] !== null && $filters['is_current'] !== '', function (Builder $q) use ($filters): void {
                $q->where('is_current', filter_var($filters['is_current'], FILTER_VALIDATE_BOOLEAN));
            })
            ->when($filters['search'] ?? null, function (Builder $q, string $search): void {
                $q->where('name', 'like', "%{$search}%");
            });
    }
}
