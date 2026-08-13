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
     * Seed grades 1–13, A/L streams, national-curriculum subjects, and 6–13 classes.
     */
    public function run(): void
    {
        $academicYear = AcademicYear::query()->updateOrCreate(
            ['name' => SriLankanDemoCatalog::ACADEMIC_YEAR_NAME],
            [
                'starts_on' => '2026-01-01',
                'ends_on' => '2026-12-31',
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

        foreach (SriLankanDemoCatalog::streams() as $stream) {
            Stream::query()->updateOrCreate(
                ['code' => $stream['code']],
                ['name' => $stream['name']],
            );
        }

        foreach (SriLankanDemoCatalog::subjects() as $subject) {
            Subject::query()->updateOrCreate(
                ['code' => $subject['code']],
                $subject,
            );
        }

        $grades = Grade::query()->get()->keyBy('number');
        $streams = Stream::query()->get()->keyBy('code');
        $subjects = Subject::query()->get()->keyBy('code');

        foreach (SriLankanDemoCatalog::classPlans() as $plan) {
            $grade = $grades->get($plan['grade']);
            $stream = $plan['stream'] !== null ? $streams->get($plan['stream']) : null;

            $schoolClass = SchoolClass::query()->updateOrCreate(
                [
                    'academic_year_id' => $academicYear->id,
                    'code' => SchoolClass::buildCode($grade, $plan['section'], $stream),
                ],
                [
                    'name' => $plan['section'],
                    'grade_id' => $grade->id,
                    'stream_id' => $stream?->id,
                ],
            );

            $subjectIds = collect(SriLankanDemoCatalog::subjectCodesFor(
                $plan['grade'],
                $plan['stream'],
                $plan['section'],
            ))->map(fn (string $code) => $subjects->get($code)?->id)
                ->filter()
                ->values()
                ->all();

            $schoolClass->subjects()->sync($subjectIds);
        }
    }
}
