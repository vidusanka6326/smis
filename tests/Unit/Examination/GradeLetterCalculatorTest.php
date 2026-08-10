<?php

use App\Enums\GradeLetter;
use App\Services\Examination\GradeLetterCalculator;

beforeEach(function () {
    $this->calculator = new GradeLetterCalculator;
});

test('percentage boundaries map to correct letters', function (float $percentage, GradeLetter $expected) {
    expect($this->calculator->fromPercentage($percentage))->toBe($expected);
})->with([
    [100.0, GradeLetter::A],
    [75.0, GradeLetter::A],
    [74.99, GradeLetter::B],
    [65.0, GradeLetter::B],
    [64.99, GradeLetter::C],
    [55.0, GradeLetter::C],
    [54.99, GradeLetter::S],
    [40.0, GradeLetter::S],
    [39.99, GradeLetter::F],
    [0.0, GradeLetter::F],
]);

test('from marks derives percentage then letter', function () {
    expect($this->calculator->fromMarks(75, 100))->toBe(GradeLetter::A)
        ->and($this->calculator->fromMarks(40, 100))->toBe(GradeLetter::S)
        ->and($this->calculator->fromMarks(20, 50))->toBe(GradeLetter::S);
});

test('invalid percentage throws', function () {
    $this->calculator->fromPercentage(101);
})->throws(InvalidArgumentException::class);

test('invalid marks throw', function (float $marks, float $max) {
    $this->calculator->fromMarks($marks, $max);
})->with([
    [-1, 100],
    [101, 100],
    [10, 0],
])->throws(InvalidArgumentException::class);
