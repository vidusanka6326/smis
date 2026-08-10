<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SchoolClass>
 */
class SchoolClassFactory extends Factory
{
    protected $model = SchoolClass::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => strtoupper(fake()->unique()->bothify('?###')),
            'code' => 'TMP',
            'academic_year_id' => AcademicYear::factory(),
            'grade_id' => Grade::factory(),
            'stream_id' => null,
            'class_teacher_id' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (SchoolClass $schoolClass): void {
            if ($schoolClass->code !== 'TMP') {
                return;
            }

            $grade = Grade::query()->find($schoolClass->grade_id);

            if ($grade === null) {
                return;
            }

            $schoolClass->code = SchoolClass::buildCode($grade, $schoolClass->name);
        })->afterCreating(function (SchoolClass $schoolClass): void {
            if ($schoolClass->code !== 'TMP') {
                return;
            }

            $grade = $schoolClass->grade()->first();

            if ($grade === null) {
                return;
            }

            $schoolClass->forceFill([
                'code' => SchoolClass::buildCode($grade, $schoolClass->name),
            ])->saveQuietly();
        });
    }
}
