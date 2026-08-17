<?php

use App\Enums\AppLocale;

test('supported locales are english sinhala and tamil', function () {
    expect(AppLocale::values())->toBe(['en', 'si', 'ta'])
        ->and(AppLocale::Sinhala->nativeName())->toBe('සිංහල')
        ->and(AppLocale::Tamil->nativeName())->toBe('தமிழ்')
        ->and(AppLocale::English->englishName())->toBe('English');
});
