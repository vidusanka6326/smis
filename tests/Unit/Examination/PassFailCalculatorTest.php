<?php

use App\Services\Examination\PassFailCalculator;

beforeEach(function () {
    $this->calculator = new PassFailCalculator;
});

test('marks at or above pass mark pass', function () {
    expect($this->calculator->passes(40, 40, 100))->toBeTrue()
        ->and($this->calculator->passes(40.01, 40, 100))->toBeTrue()
        ->and($this->calculator->passes(100, 40, 100))->toBeTrue();
});

test('marks below pass mark fail', function () {
    expect($this->calculator->passes(39.99, 40, 100))->toBeFalse()
        ->and($this->calculator->passes(0, 40, 100))->toBeFalse();
});

test('zero pass mark means any non-negative marks pass', function () {
    expect($this->calculator->passes(0, 0, 100))->toBeTrue();
});

test('invalid inputs throw', function (float $marks, float $pass, float $max) {
    $this->calculator->passes($marks, $pass, $max);
})->with([
    [10, 40, 0],
    [-1, 40, 100],
    [101, 40, 100],
    [10, -1, 100],
    [10, 101, 100],
])->throws(InvalidArgumentException::class);
