<?php

use App\Services\Reporting\PerformanceRankingService;

beforeEach(function () {
    $this->service = new PerformanceRankingService;
});

test('ranks best and poor by percentage then average marks', function () {
    $result = $this->service->rank([
        ['student_id' => 1, 'name' => 'A', 'percentage' => 90.0, 'average_marks' => 90],
        ['student_id' => 2, 'name' => 'B', 'percentage' => 40.0, 'average_marks' => 40],
        ['student_id' => 3, 'name' => 'C', 'percentage' => 70.0, 'average_marks' => 70],
        ['student_id' => 4, 'name' => 'D', 'percentage' => 90.0, 'average_marks' => 95],
    ], 2);

    expect($result['best'][0]['student_id'])->toBe(4)
        ->and($result['best'][1]['student_id'])->toBe(1)
        ->and($result['poor'][0]['student_id'])->toBe(2)
        ->and($result['poor'][1]['student_id'])->toBe(3)
        ->and($result['best'][0]['rank'])->toBe(1);
});

test('empty rows return empty bands', function () {
    expect($this->service->rank([], 5))->toBe([
        'best' => [],
        'poor' => [],
    ]);
});

test('invalid limit throws', function () {
    $this->service->rank([], 0);
})->throws(InvalidArgumentException::class);

test('limit larger than dataset returns all', function () {
    $result = $this->service->rank([
        ['student_id' => 1, 'name' => 'A', 'percentage' => 50.0, 'average_marks' => 50],
    ], 5);

    expect($result['best'])->toHaveCount(1)
        ->and($result['poor'])->toHaveCount(1);
});
