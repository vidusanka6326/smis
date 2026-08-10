<?php

use App\Enums\ExamType;
use App\Enums\GradeLetter;
use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\Mark;
use App\Models\User;

test('admin can create an exam and configure subjects', function () {
    $admin = User::factory()->admin()->create();
    [$year, $schoolClass, $subject] = examFixtures();

    $this->actingAs($admin)
        ->post(route('admin.exams.store'), [
            'name' => 'Term 1',
            'type' => ExamType::TermTest->value,
            'academic_year_id' => $year->id,
            'grade_id' => $schoolClass->grade_id,
            'school_class_id' => $schoolClass->id,
            'starts_on' => now()->toDateString(),
            'ends_on' => now()->addWeek()->toDateString(),
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $exam = Exam::query()->first();
    expect($exam)->not->toBeNull();

    $this->actingAs($admin)
        ->put(route('admin.exams.subjects.update', $exam), [
            'subjects' => [
                [
                    'subject_id' => $subject->id,
                    'max_marks' => 100,
                    'pass_mark' => 40,
                ],
            ],
        ])
        ->assertSessionHasNoErrors();

    expect(ExamSubject::query()->count())->toBe(1);
});

test('admin can enter marks and publish results', function () {
    $admin = User::factory()->admin()->create();
    [$year, $schoolClass, $subject, $student] = examFixtures(withStudent: true);

    $exam = Exam::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $schoolClass->grade_id,
        'school_class_id' => $schoolClass->id,
        'created_by' => $admin->id,
    ]);
    $examSubject = ExamSubject::factory()->create([
        'exam_id' => $exam->id,
        'subject_id' => $subject->id,
        'max_marks' => 100,
        'pass_mark' => 40,
    ]);

    $this->actingAs($admin)
        ->put(route('admin.marks.update', $examSubject), [
            'records' => [
                [
                    'student_id' => $student->id,
                    'marks_obtained' => 82,
                ],
            ],
        ])
        ->assertSessionHasNoErrors();

    $mark = Mark::query()->first();
    expect($mark->grade_letter)->toBe(GradeLetter::A)
        ->and($mark->is_pass)->toBeTrue();

    $this->actingAs($admin)
        ->post(route('admin.exams.publish', $exam))
        ->assertRedirect();

    expect($exam->refresh()->isPublished())->toBeTrue();
});

test('teacher cannot manage exams', function () {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->get(route('admin.exams.index'))
        ->assertForbidden();
});
