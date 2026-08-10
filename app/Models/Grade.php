<?php

namespace App\Models;

use Database\Factories\GradeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grade extends Model
{
    /** @use HasFactory<GradeFactory> */
    use HasFactory;

    /**
     * Grades that may be assigned a stream (A/L).
     */
    public const STREAM_ELIGIBLE_NUMBERS = [12, 13];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'number',
        'name',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'number' => 'integer',
        ];
    }

    public function allowsStream(): bool
    {
        return in_array($this->number, self::STREAM_ELIGIBLE_NUMBERS, true);
    }

    /**
     * @return HasMany<SchoolClass, $this>
     */
    public function schoolClasses(): HasMany
    {
        return $this->hasMany(SchoolClass::class);
    }
}
