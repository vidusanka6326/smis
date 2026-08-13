<?php

use App\Enums\GradeLetter;
use App\Services\Reporting\ExaminationStatisticsReport;

beforeEach(function () {
    $this->report = new ExaminationStatisticsReport;
});

test('summarize rows computes pass rate and averages', function () {
    $summary = $this->report->summarizeRows([
        ['marks_obtained' => 80, 'max_marks' => 100, 'is_pass' => true, 'grade_letter' => GradeLetter::A],
        ['marks_obtained' => 30, 'max_marks' => 100, 'is_pass' => false, 'grade_letter' => GradeLetter::F],
        ['marks_obtained' => 60, 'max_marks' => 100, 'is_pass' => true, 'grade_letter' => GradeLetter::C],
    ]);

    expect($summary['total_marks'])->toBe(3)
        ->and($summary['pass_count'])->toBe(2)
        ->and($summary['fail_count'])->toBe(1)
        ->and($summary['pass_rate'])->toBe(66.67)
        ->and($summary['average_marks'])->toBe(56.67)
        ->and($summary['average_percentage'])->toBe(56.67)
        ->and($summary['by_grade_letter']['A'])->toBe(1)
        ->and($summary['by_grade_letter']['F'])->toBe(1);
});

test('empty rows return zeros', function () {
    $summary = $this->report->summarizeRows([]);

    expect($summary['total_marks'])->toBe(0)
        ->and($summary['pass_rate'])->toBe(0.0)
        ->and($summary['average_percentage'])->toBe(0.0);
});

test('invalid max marks throws', function () {
    $this->report->summarizeRows([
        ['marks_obtained' => 10, 'max_marks' => 0, 'is_pass' => false, 'grade_letter' => 'F'],
    ]);
})->throws(InvalidArgumentException::class);

test('perfect pass rate is one hundred', function () {
    $summary = $this->report->summarizeRows([
        ['marks_obtained' => 50, 'max_marks' => 50, 'is_pass' => true, 'grade_letter' => 'A'],
    ]);

    expect($summary['pass_rate'])->toBe(100.0)
        ->and($summary['average_percentage'])->toBe(100.0);
});

test('summarize rows builds class comparison', function () {
    $summary = $this->report->summarizeRows([
        ['marks_obtained' => 80, 'max_marks' => 100, 'is_pass' => true, 'grade_letter' => 'A', 'school_class_id' => 1, 'class_code' => '10-A'],
        ['marks_obtained' => 20, 'max_marks' => 100, 'is_pass' => false, 'grade_letter' => 'F', 'school_class_id' => 1, 'class_code' => '10-A'],
        ['marks_obtained' => 90, 'max_marks' => 100, 'is_pass' => true, 'grade_letter' => 'A', 'school_class_id' => 2, 'class_code' => '10-B'],
    ]);

    expect($summary['by_class'])->toHaveCount(2)
        ->and($summary['by_class'][0]['code'])->toBe('10-A')
        ->and($summary['by_class'][0]['pass_rate'])->toBe(50.0)
        ->and($summary['by_class'][0]['average_percentage'])->toBe(50.0)
        ->and($summary['by_class'][1]['code'])->toBe('10-B')
        ->and($summary['by_class'][1]['pass_rate'])->toBe(100.0);
});
