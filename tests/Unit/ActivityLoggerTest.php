<?php

use App\Enums\ActivityAction;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\Audit\ActivityLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('activity logger stores causer subject and properties', function () {
    $causer = User::factory()->create();
    $subject = User::factory()->create();

    $this->actingAs($causer);

    $log = app(ActivityLogger::class)->log(
        ActivityAction::UserCreated,
        'Created a user.',
        $subject,
        ['role' => 'student'],
    );

    expect($log)->toBeInstanceOf(ActivityLog::class)
        ->and($log->causer_id)->toBe($causer->id)
        ->and($log->subject_id)->toBe($subject->id)
        ->and($log->action)->toBe(ActivityAction::UserCreated)
        ->and($log->properties['role'])->toBe('student');
});
