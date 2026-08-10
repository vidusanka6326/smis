<?php

use App\Models\Subject;
use App\Models\User;

test('admin can manage subjects with grade ranges', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.subjects.store'), [
            'name' => 'Mathematics',
            'code' => 'math',
            'min_grade' => 1,
            'max_grade' => 13,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.subjects.index'));

    $subject = Subject::query()->where('code', 'MATH')->firstOrFail();

    $this->actingAs($admin)
        ->put(route('admin.subjects.update', $subject), [
            'name' => 'Maths',
            'code' => 'MATH',
            'min_grade' => 6,
            'max_grade' => 11,
        ])
        ->assertRedirect(route('admin.subjects.index'));

    expect($subject->fresh()->min_grade)->toBe(6);

    $this->actingAs($admin)
        ->delete(route('admin.subjects.destroy', $subject))
        ->assertRedirect(route('admin.subjects.index'));
});

test('subject max grade must be greater than or equal to min grade', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.subjects.store'), [
            'name' => 'Invalid',
            'code' => 'INV',
            'min_grade' => 10,
            'max_grade' => 5,
        ])
        ->assertSessionHasErrors(['max_grade']);
});

test('teacher cannot manage subjects', function () {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->get(route('admin.subjects.create'))
        ->assertForbidden();
});
