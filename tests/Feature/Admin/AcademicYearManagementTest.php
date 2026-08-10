<?php

use App\Models\AcademicYear;
use App\Models\User;

test('admin can list and create academic years', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.academic-years.index'))
        ->assertOk()
        ->assertSee(__('Academic years'));

    $response = $this->actingAs($admin)->post(route('admin.academic-years.store'), [
        'name' => '2026/2027',
        'starts_on' => '2026-01-01',
        'ends_on' => '2026-12-31',
        'is_current' => '1',
    ]);

    $response->assertSessionHasNoErrors()->assertRedirect(route('admin.academic-years.index'));

    $year = AcademicYear::query()->where('name', '2026/2027')->first();

    expect($year)->not->toBeNull()
        ->and($year->is_current)->toBeTrue();
});

test('setting a current academic year clears the previous current year', function () {
    $admin = User::factory()->admin()->create();
    $previous = AcademicYear::factory()->current()->create(['name' => '2024/2025']);

    $this->actingAs($admin)->post(route('admin.academic-years.store'), [
        'name' => '2025/2026',
        'starts_on' => '2025-01-01',
        'ends_on' => '2025-12-31',
        'is_current' => '1',
    ])->assertSessionHasNoErrors();

    expect($previous->fresh()->is_current)->toBeFalse()
        ->and(AcademicYear::query()->where('name', '2025/2026')->first()->is_current)->toBeTrue();
});

test('admin can update and delete academic years without classes', function () {
    $admin = User::factory()->admin()->create();
    $year = AcademicYear::factory()->create(['name' => '2023/2024']);

    $this->actingAs($admin)->put(route('admin.academic-years.update', $year), [
        'name' => '2023/2024-rev',
        'starts_on' => '2023-01-01',
        'ends_on' => '2023-12-31',
        'is_current' => '0',
    ])->assertRedirect(route('admin.academic-years.index'));

    expect($year->fresh()->name)->toBe('2023/2024-rev');

    $this->actingAs($admin)
        ->delete(route('admin.academic-years.destroy', $year))
        ->assertRedirect(route('admin.academic-years.index'));

    expect(AcademicYear::query()->whereKey($year->id)->exists())->toBeFalse();
});

test('teacher cannot manage academic years', function () {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->get(route('admin.academic-years.index'))
        ->assertForbidden();

    $this->actingAs($teacher)
        ->post(route('admin.academic-years.store'), [
            'name' => 'Blocked',
            'starts_on' => '2026-01-01',
            'ends_on' => '2026-12-31',
        ])
        ->assertForbidden();
});

test('academic year validation requires ends_on after starts_on', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.academic-years.store'), [
            'name' => 'Invalid',
            'starts_on' => '2026-12-31',
            'ends_on' => '2026-01-01',
        ])
        ->assertSessionHasErrors(['ends_on']);
});
