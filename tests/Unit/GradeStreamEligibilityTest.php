<?php

use App\Models\Grade;

test('only grades 12 and 13 allow streams', function (int $number, bool $allowed) {
    $grade = new Grade(['number' => $number]);

    expect($grade->allowsStream())->toBe($allowed);
})->with([
    [1, false],
    [11, false],
    [12, true],
    [13, true],
]);
