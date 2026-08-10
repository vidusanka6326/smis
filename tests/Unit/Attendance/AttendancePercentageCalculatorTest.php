<?php

use App\Enums\AttendanceStatus;
use App\Services\Attendance\AttendancePercentageCalculator;

beforeEach(function () {
    $this->calculator = new AttendancePercentageCalculator;
});

test('present and late count as attended', function () {
    expect($this->calculator->percentage([
        AttendanceStatus::Present,
        AttendanceStatus::Late,
        AttendanceStatus::Absent,
    ]))->toBe(66.67);
});

test('excused days are excluded from denominator', function () {
    expect($this->calculator->percentage([
        AttendanceStatus::Present,
        AttendanceStatus::Excused,
        AttendanceStatus::Absent,
    ]))->toBe(50.0);
});

test('all excused returns zero percent', function () {
    expect($this->calculator->percentage([
        AttendanceStatus::Excused,
        AttendanceStatus::Excused,
    ]))->toBe(0.0);
});

test('empty statuses return zero percent', function () {
    expect($this->calculator->percentage([]))->toBe(0.0);
});

test('percentage from counts matches status list', function () {
    expect($this->calculator->percentageFromCounts([
        'present' => 2,
        'absent' => 1,
        'late' => 1,
        'excused' => 3,
    ]))->toBe(75.0);
});

test('perfect attendance is one hundred', function () {
    expect($this->calculator->percentage([
        AttendanceStatus::Present,
        AttendanceStatus::Present,
        AttendanceStatus::Late,
    ]))->toBe(100.0);
});

test('string statuses are accepted', function () {
    expect($this->calculator->percentage(['present', 'absent']))->toBe(50.0);
});
