<?php

use App\Models\Lesson;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()->make(RolesAndPermissionsSeeder::class)->run();
});

it('allows teachers to manage their lessons', function () {
    $teacher = Teacher::factory()->create();
    $class = SchoolClass::factory()->create();
    $subject = Subject::factory()->create();

    TeacherAssignment::factory()->create([
        'teacher_id' => $teacher->id,
        'school_class_id' => $class->id,
        'subject_id' => $subject->id,
    ]);

    $this->actingAs($teacher->user)
        ->post(route('teacher.lessons.store'), [
            'title' => 'Math Lesson 1',
            'description' => 'Complete chapter 1',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'school_class_ids' => [$class->id],
            'subject_id' => $subject->id,
        ])
        ->assertRedirect(route('teacher.lessons.index'));

    $this->assertDatabaseHas('lessons', [
        'title' => 'Math Lesson 1',
        'teacher_id' => $teacher->id,
    ]);

    $lesson = Lesson::first();

    $this->assertDatabaseHas('lesson_school_class', [
        'lesson_id' => $lesson->id,
        'school_class_id' => $class->id,
    ]);

    $this->actingAs($teacher->user)
        ->get(route('teacher.lessons.show', $lesson))
        ->assertOk()
        ->assertSee('Math Lesson 1')
        ->assertSee('youtube.com/embed/dQw4w9WgXcQ');
});

it('allows students to view lessons for their class', function () {
    $class = SchoolClass::factory()->create();
    $student = Student::factory()->create(['current_class_id' => $class->id]);

    $lesson = Lesson::factory()->create([
        'title' => 'Assigned Lesson',
    ]);
    $lesson->schoolClasses()->sync([$class->id]);

    $this->actingAs($student->user)
        ->get(route('student.lessons.index'))
        ->assertOk()
        ->assertSee('Assigned Lesson');

    $this->actingAs($student->user)
        ->get(route('student.lessons.show', $lesson))
        ->assertOk()
        ->assertSee('Assigned Lesson');
});

it('prevents students from viewing lessons of other classes', function () {
    $class1 = SchoolClass::factory()->create();
    $class2 = SchoolClass::factory()->create();

    $student = Student::factory()->create(['current_class_id' => $class1->id]);

    $lesson = Lesson::factory()->create([
        'title' => 'Other Class Lesson',
    ]);
    $lesson->schoolClasses()->sync([$class2->id]);

    $this->actingAs($student->user)
        ->get(route('student.lessons.index'))
        ->assertOk()
        ->assertDontSee('Other Class Lesson');

    $this->actingAs($student->user)
        ->get(route('student.lessons.show', $lesson))
        ->assertForbidden();
});
