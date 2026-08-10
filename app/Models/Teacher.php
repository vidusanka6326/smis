<?php

namespace App\Models;

use App\Enums\TeacherAssignmentRole;
use Database\Factories\TeacherFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Teacher extends Model
{
    /** @use HasFactory<TeacherFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'employee_no',
        'phone',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<TeacherAssignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(TeacherAssignment::class);
    }

    /**
     * @return HasMany<SchoolClass, $this>
     */
    public function homeroomClasses(): HasMany
    {
        return $this->hasMany(SchoolClass::class, 'class_teacher_id');
    }

    /**
     * Whether this teacher is the class teacher for the given class (homeroom or assignment).
     */
    public function isClassTeacherOf(SchoolClass $schoolClass): bool
    {
        if ($this->homeroomClasses()->whereKey($schoolClass->id)->exists()) {
            return true;
        }

        return $this->assignments()
            ->where('school_class_id', $schoolClass->id)
            ->where('role_in_assignment', TeacherAssignmentRole::ClassTeacher)
            ->exists();
    }
}
