<?php

use App\Enums\AppLocale;

test('supported locales are english sinhala and tamil', function () {
    expect(AppLocale::values())->toBe(['en', 'si', 'ta'])
        ->and(AppLocale::Sinhala->nativeName())->toBe('සිංහල')
        ->and(AppLocale::Tamil->nativeName())->toBe('தமிழ்')
        ->and(AppLocale::English->shortCode())->toBe('ENG')
        ->and(AppLocale::Sinhala->shortCode())->toBe('SIN')
        ->and(AppLocale::Tamil->shortCode())->toBe('TAM')
        ->and(AppLocale::English->englishName())->toBe('English');
});
