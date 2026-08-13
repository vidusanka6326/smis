<?php

use App\Enums\AttendanceStatus;
use App\Services\Attendance\AttendancePercentageCalculator;
use App\Services\Reporting\AttendanceAnalyticsReport;

beforeEach(function () {
    $this->report = new AttendanceAnalyticsReport(new AttendancePercentageCalculator);
});

test('at risk filters students below threshold and sorts worst first', function () {
    $rows = [
        ['student_id' => 1, 'name' => 'High', 'class' => '10-A', 'percentage' => 95.0, 'present' => 19, 'absent' => 1, 'late' => 0, 'excused' => 0],
        ['student_id' => 2, 'name' => 'Low', 'class' => '10-A', 'percentage' => 40.0, 'present' => 4, 'absent' => 6, 'late' => 0, 'excused' => 0],
        ['student_id' => 3, 'name' => 'Edge', 'class' => '10-B', 'percentage' => 79.9, 'present' => 8, 'absent' => 2, 'late' => 0, 'excused' => 0],
        ['student_id' => 4, 'name' => 'Safe', 'class' => '10-B', 'percentage' => 80.0, 'present' => 8, 'absent' => 2, 'late' => 0, 'excused' => 0],
    ];

    $atRisk = $this->report->atRiskFromRows($rows);

    expect($atRisk)->toHaveCount(2)
        ->and($atRisk[0]['student_id'])->toBe(2)
        ->and($atRisk[1]['student_id'])->toBe(3);
});

test('summarize rows counts at risk and class average', function () {
    $summary = $this->report->summarizeRows(
        [
            ['student_id' => 1, 'name' => 'A', 'class' => '10-A', 'percentage' => 100.0, 'present' => 10, 'absent' => 0, 'late' => 0, 'excused' => 0],
            ['student_id' => 2, 'name' => 'B', 'class' => '10-A', 'percentage' => 50.0, 'present' => 5, 'absent' => 5, 'late' => 0, 'excused' => 0],
        ],
        [
            ['percentage' => 90.0],
            ['percentage' => 70.0],
        ],
    );

    expect($summary['tracked_students'])->toBe(2)
        ->and($summary['at_risk_count'])->toBe(1)
        ->and($summary['class_average'])->toBe(80.0)
        ->and($summary['threshold'])->toBe(80.0);
});

test('count statuses tallies each attendance enum', function () {
    $counts = $this->report->countStatuses(collect([
        AttendanceStatus::Present,
        AttendanceStatus::Present,
        AttendanceStatus::Absent,
        AttendanceStatus::Late,
        AttendanceStatus::Excused,
    ]));

    expect($counts[AttendanceStatus::Present->value])->toBe(2)
        ->and($counts[AttendanceStatus::Absent->value])->toBe(1)
        ->and($counts[AttendanceStatus::Late->value])->toBe(1)
        ->and($counts[AttendanceStatus::Excused->value])->toBe(1);
});
