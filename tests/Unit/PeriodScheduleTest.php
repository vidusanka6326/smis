<?php

use App\Services\Timetable\PeriodSchedule;

test('period schedule returns eight labeled ranges with break after period 4', function () {
    $schedule = new PeriodSchedule;

    expect($schedule->forPeriod(1))->toMatchArray([
        'start' => '07:30',
        'end' => '08:10',
    ])
        ->and($schedule->forPeriod(5)['start'])->toBe('10:30')
        ->and($schedule->all())->toHaveCount(8);
});

test('period schedule rejects out of range periods', function () {
    $schedule = new PeriodSchedule;

    $schedule->forPeriod(0);
})->throws(InvalidArgumentException::class);
