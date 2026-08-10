<?php

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Stream;
use App\Models\Subject;
use App\Models\User;

test('admin can create a class for grades without streams', function () {
    $admin = User::factory()->admin()->create();
    $year = AcademicYear::factory()->create();
    $grade = Grade::factory()->number(10)->create();
    $subject = Subject::factory()->forGradeRange(1, 13)->create();

    $response = $this->actingAs($admin)->post(route('admin.classes.store'), [
        'name' => 'A',
        'academic_year_id' => $year->id,
        'grade_id' => $grade->id,
        'stream_id' => null,
        'subject_ids' => [$subject->id],
    ]);

    $response->assertSessionHasNoErrors()->assertRedirect(route('admin.classes.index'));

    $schoolClass = SchoolClass::query()->where('code', '10-A')->first();

    expect($schoolClass)->not->toBeNull()
        ->and($schoolClass->subjects()->pluck('subjects.id')->all())->toContain($subject->id);
});

test('grades 12 and 13 require a stream', function () {
    $admin = User::factory()->admin()->create();
    $year = AcademicYear::factory()->create();
    $grade = Grade::factory()->number(12)->create();

    $this->actingAs($admin)
        ->post(route('admin.classes.store'), [
            'name' => 'A',
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
        ])
        ->assertSessionHasErrors(['stream_id']);
});

test('grades below 12 cannot have a stream', function () {
    $admin = User::factory()->admin()->create();
    $year = AcademicYear::factory()->create();
    $grade = Grade::factory()->number(9)->create();
    $stream = Stream::factory()->create(['code' => 'SCI']);

    $this->actingAs($admin)
        ->post(route('admin.classes.store'), [
            'name' => 'B',
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'stream_id' => $stream->id,
        ])
        ->assertSessionHasErrors(['stream_id']);
});

test('admin can create a streamed a-level class and assign a teacher', function () {
    $admin = User::factory()->admin()->create();
    $teacher = User::factory()->teacher()->create();
    $year = AcademicYear::factory()->create();
    $grade = Grade::factory()->number(13)->create();
    $stream = Stream::factory()->create(['code' => 'COM']);
    $subject = Subject::factory()->forGradeRange(12, 13)->create();

    $this->actingAs($admin)
        ->post(route('admin.classes.store'), [
            'name' => 'A',
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'stream_id' => $stream->id,
            'class_teacher_id' => $teacher->id,
            'subject_ids' => [$subject->id],
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.classes.index'));

    $schoolClass = SchoolClass::query()->where('code', '13-COM-A')->firstOrFail();

    expect($schoolClass->class_teacher_id)->toBe($teacher->id)
        ->and($schoolClass->stream_id)->toBe($stream->id);
});

test('subjects outside the class grade cannot be attached', function () {
    $admin = User::factory()->admin()->create();
    $year = AcademicYear::factory()->create();
    $grade = Grade::factory()->number(8)->create();
    $subject = Subject::factory()->forGradeRange(12, 13)->create();

    $this->actingAs($admin)
        ->post(route('admin.classes.store'), [
            'name' => 'C',
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'subject_ids' => [$subject->id],
        ])
        ->assertSessionHasErrors(['subject_ids']);
});

test('class teacher must have the teacher role', function () {
    $admin = User::factory()->admin()->create();
    $student = User::factory()->student()->create();
    $year = AcademicYear::factory()->create();
    $grade = Grade::factory()->number(7)->create();

    $this->actingAs($admin)
        ->post(route('admin.classes.store'), [
            'name' => 'D',
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'class_teacher_id' => $student->id,
        ])
        ->assertSessionHasErrors(['class_teacher_id']);
});

test('teacher cannot manage classes', function () {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->get(route('admin.classes.index'))
        ->assertForbidden();
});

test('admin can update and delete classes', function () {
    $admin = User::factory()->admin()->create();
    $year = AcademicYear::factory()->create();
    $grade = Grade::factory()->number(6)->create();
    $schoolClass = SchoolClass::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $grade->id,
        'name' => 'A',
        'code' => '6-A',
    ]);

    $this->actingAs($admin)
        ->put(route('admin.classes.update', $schoolClass), [
            'name' => 'B',
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'subject_ids' => [],
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.classes.index'));

    expect($schoolClass->fresh()->code)->toBe('6-B');

    $this->actingAs($admin)
        ->delete(route('admin.classes.destroy', $schoolClass))
        ->assertRedirect(route('admin.classes.index'));

    expect(SchoolClass::query()->whereKey($schoolClass->id)->exists())->toBeFalse();
});
