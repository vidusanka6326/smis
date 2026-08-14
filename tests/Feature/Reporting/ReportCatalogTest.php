<?php

use App\Services\Reporting\ReportCatalog;

test('admin catalog includes operational download reports', function () {
    $items = collect((new ReportCatalog)->forAdmin())->pluck('key');

    expect($items)->toContain('attendance', 'at-risk', 'enrollment', 'exam-results', 'assignments');
});

test('teacher catalog is scoped and omits school-wide staff reports', function () {
    $items = collect((new ReportCatalog)->forTeacher())->pluck('key');

    expect($items)->toContain('attendance', 'enrollment', 'exam-results')
        ->and($items)->not->toContain('staff-attendance', 'assignments', 'demographics');
});

test('student catalog covers card attendance and results', function () {
    $items = collect((new ReportCatalog)->forStudent())->pluck('key');

    expect($items)->toContain('card', 'attendance', 'results');
});
