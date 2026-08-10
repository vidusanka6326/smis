<?php

namespace App\Models;

use Database\Factories\SchoolClassFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SchoolClass extends Model
{
    /** @use HasFactory<SchoolClassFactory> */
    use HasFactory;

    protected $table = 'classes';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'code',
        'academic_year_id',
        'grade_id',
        'stream_id',
        'class_teacher_id',
    ];

    /**
     * Build a stable class code for uniqueness within an academic year.
     */
    public static function buildCode(Grade $grade, string $sectionName, ?Stream $stream = null): string
    {
        $section = strtoupper(trim($sectionName));

        if ($stream !== null) {
            return sprintf('%d-%s-%s', $grade->number, strtoupper($stream->code), $section);
        }

        return sprintf('%d-%s', $grade->number, $section);
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
     * @return BelongsTo<Stream, $this>
     */
    public function stream(): BelongsTo
    {
        return $this->belongsTo(Stream::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function classTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'class_teacher_id');
    }

    /**
     * @return BelongsToMany<Subject, $this>
     */
    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'class_subject', 'school_class_id')
            ->withTimestamps();
    }
}
