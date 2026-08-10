<?php

use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Stream;

test('builds class codes with and without streams', function () {
    $grade10 = new Grade(['number' => 10]);
    $grade12 = new Grade(['number' => 12]);
    $stream = new Stream(['code' => 'sci']);

    expect(SchoolClass::buildCode($grade10, 'a'))->toBe('10-A')
        ->and(SchoolClass::buildCode($grade12, 'b', $stream))->toBe('12-SCI-B');
});
