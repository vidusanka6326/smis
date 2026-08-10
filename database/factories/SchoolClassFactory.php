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
        $grade = Grade::factory()->create();
        $name = fake()->randomElement(['A', 'B', 'C', 'D']);

        return [
            'name' => $name,
            'code' => SchoolClass::buildCode($grade, $name),
            'academic_year_id' => AcademicYear::factory(),
            'grade_id' => $grade->id,
            'stream_id' => null,
            'class_teacher_id' => null,
        ];
    }
}
