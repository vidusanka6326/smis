<?php

use App\Models\Stream;
use App\Models\User;

test('admin can manage streams', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.streams.store'), [
            'name' => 'Science',
            'code' => 'sci',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.streams.index'));

    $stream = Stream::query()->where('code', 'SCI')->firstOrFail();

    $this->actingAs($admin)
        ->put(route('admin.streams.update', $stream), [
            'name' => 'Science Stream',
            'code' => 'SCI',
        ])
        ->assertRedirect(route('admin.streams.index'));

    expect($stream->fresh()->name)->toBe('Science Stream');

    $this->actingAs($admin)
        ->delete(route('admin.streams.destroy', $stream))
        ->assertRedirect(route('admin.streams.index'));
});

test('teacher cannot manage streams', function () {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->post(route('admin.streams.store'), [
            'name' => 'Blocked',
            'code' => 'BLK',
        ])
        ->assertForbidden();
});
