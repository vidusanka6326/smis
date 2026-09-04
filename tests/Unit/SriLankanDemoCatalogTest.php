<?php

use Database\Seeders\SriLankanDemoCatalog;
use Tests\TestCase;

uses(TestCase::class);

test('demo catalog matches the requested school population', function () {
    expect(SriLankanDemoCatalog::expectedOfficerCount())->toBe(5)
        ->and(SriLankanDemoCatalog::expectedTeacherCount())->toBe(30)
        ->and(SriLankanDemoCatalog::expectedStudentCount())->toBe(600)
        ->and(SriLankanDemoCatalog::expectedClassCount())->toBe(28);
});

test('junior secondary classes use twelve national curriculum subjects', function () {
    expect(SriLankanDemoCatalog::JUNIOR_SUBJECT_CODES)->toHaveCount(12)
        ->and(SriLankanDemoCatalog::subjectCodesFor(6, null, 'A'))->toHaveCount(12)
        ->and(array_sum(SriLankanDemoCatalog::weeklyPeriodCounts(8)))->toBe(40);
});

test('ordinary level classes use nine subjects', function () {
    expect(SriLankanDemoCatalog::OL_SUBJECT_CODES)->toHaveCount(9)
        ->and(SriLankanDemoCatalog::subjectCodesFor(10, null, 'A'))->toHaveCount(9)
        ->and(array_sum(SriLankanDemoCatalog::weeklyPeriodCounts(11)))->toBe(40);
});

test('advanced level streams use three subjects each', function () {
    expect(SriLankanDemoCatalog::subjectCodesFor(12, 'SCI', 'A'))->toBe(['CMAT', 'PHY', 'CHE'])
        ->and(SriLankanDemoCatalog::subjectCodesFor(12, 'SCI', 'B'))->toBe(['BIO', 'PHY', 'CHE'])
        ->and(SriLankanDemoCatalog::subjectCodesFor(13, 'COM', 'A'))->toBe(['ACC', 'BST', 'ECO'])
        ->and(SriLankanDemoCatalog::subjectCodesFor(13, 'ART', 'A'))->toBe(['POL', 'LOG', 'GEO'])
        ->and(SriLankanDemoCatalog::subjectCodesFor(12, 'TEC', 'A'))->toBe(['ETEC', 'SFT', 'ICT']);
});

test('teacher emails employee numbers and homerooms are unique', function () {
    $teachers = SriLankanDemoCatalog::teachers();
    $emails = array_column($teachers, 'email');
    $employeeNos = array_column($teachers, 'employee_no');
    $homerooms = array_values(array_filter(array_column($teachers, 'homeroom')));

    expect(count(array_unique($emails)))->toBe(30)
        ->and(count(array_unique($employeeNos)))->toBe(30)
        ->and(count(array_unique($homerooms)))->toBe(count($homerooms))
        ->and(count($homerooms))->toBe(25)
        ->and(count($teachers) - count($homerooms))->toBe(5);
});
