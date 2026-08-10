<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Stream;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class AcademicStructureSeeder extends Seeder
{
    /**
     * Seed grades 1–13, A/L streams, a current academic year, subjects, and sample classes.
     */
    public function run(): void
    {
        $academicYear = AcademicYear::query()->updateOrCreate(
            ['name' => '2025/2026'],
            [
                'starts_on' => '2025-01-01',
                'ends_on' => '2025-12-31',
                'is_current' => true,
            ],
        );

        AcademicYear::query()
            ->whereKeyNot($academicYear->id)
            ->where('is_current', true)
            ->update(['is_current' => false]);

        foreach (range(1, 13) as $number) {
            Grade::query()->updateOrCreate(
                ['number' => $number],
                ['name' => sprintf('Grade %d', $number)],
            );
        }

        $streams = [
            ['name' => 'Science', 'code' => 'SCI'],
            ['name' => 'Commerce', 'code' => 'COM'],
            ['name' => 'Arts', 'code' => 'ART'],
            ['name' => 'Technology', 'code' => 'TEC'],
        ];

        foreach ($streams as $stream) {
            Stream::query()->updateOrCreate(
                ['code' => $stream['code']],
                ['name' => $stream['name']],
            );
        }

        $subjects = [
            ['name' => 'Mathematics', 'code' => 'MATH', 'min_grade' => 1, 'max_grade' => 13],
            ['name' => 'English', 'code' => 'ENG', 'min_grade' => 1, 'max_grade' => 13],
            ['name' => 'Science', 'code' => 'SCI', 'min_grade' => 6, 'max_grade' => 11],
            ['name' => 'History', 'code' => 'HIS', 'min_grade' => 6, 'max_grade' => 11],
            ['name' => 'Physics', 'code' => 'PHY', 'min_grade' => 12, 'max_grade' => 13],
            ['name' => 'Chemistry', 'code' => 'CHE', 'min_grade' => 12, 'max_grade' => 13],
            ['name' => 'Biology', 'code' => 'BIO', 'min_grade' => 12, 'max_grade' => 13],
            ['name' => 'Accounting', 'code' => 'ACC', 'min_grade' => 12, 'max_grade' => 13],
        ];

        foreach ($subjects as $subject) {
            Subject::query()->updateOrCreate(
                ['code' => $subject['code']],
                $subject,
            );
        }

        $grade10 = Grade::query()->where('number', 10)->firstOrFail();
        $grade12 = Grade::query()->where('number', 12)->firstOrFail();
        $science = Stream::query()->where('code', 'SCI')->firstOrFail();
        $math = Subject::query()->where('code', 'MATH')->firstOrFail();
        $english = Subject::query()->where('code', 'ENG')->firstOrFail();
        $physics = Subject::query()->where('code', 'PHY')->firstOrFail();

        $class10a = SchoolClass::query()->updateOrCreate(
            [
                'academic_year_id' => $academicYear->id,
                'code' => SchoolClass::buildCode($grade10, 'A'),
            ],
            [
                'name' => 'A',
                'grade_id' => $grade10->id,
                'stream_id' => null,
                'class_teacher_id' => null,
            ],
        );
        $class10a->subjects()->sync([$math->id, $english->id]);

        $class12sciA = SchoolClass::query()->updateOrCreate(
            [
                'academic_year_id' => $academicYear->id,
                'code' => SchoolClass::buildCode($grade12, 'A', $science),
            ],
            [
                'name' => 'A',
                'grade_id' => $grade12->id,
                'stream_id' => $science->id,
                'class_teacher_id' => null,
            ],
        );
        $class12sciA->subjects()->sync([$math->id, $english->id, $physics->id]);
    }
}
