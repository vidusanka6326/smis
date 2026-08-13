<?php

use App\Enums\GradeLetter;
use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\Mark;
use App\Models\User;

test('marks cannot be edited after publish', function () {
    $admin = User::factory()->admin()->create();
    [$year, $schoolClass, $subject, $student] = examFixtures(withStudent: true);

    $exam = Exam::factory()->published()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $schoolClass->grade_id,
        'school_class_id' => $schoolClass->id,
    ]);
    $examSubject = ExamSubject::factory()->create([
        'exam_id' => $exam->id,
        'subject_id' => $subject->id,
    ]);

    $this->actingAs($admin)
        ->put(route('admin.marks.update', $examSubject), [
            'records' => [
                [
                    'student_id' => $student->id,
                    'marks_obtained' => 50,
                ],
            ],
        ])
        ->assertForbidden();
});

test('admin mark entry computes fail below pass mark', function () {
    $admin = User::factory()->admin()->create();
    [$year, $schoolClass, $subject, $student] = examFixtures(withStudent: true);

    $exam = Exam::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $schoolClass->grade_id,
        'school_class_id' => $schoolClass->id,
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
                    'marks_obtained' => 35,
                ],
            ],
        ])
        ->assertSessionHasNoErrors();

    $mark = Mark::query()->first();
    expect($mark->is_pass)->toBeFalse()
        ->and($mark->grade_letter)->toBe(GradeLetter::F);
});

test('admin marks form uses flux number inputs', function () {
    $admin = User::factory()->admin()->create();
    [$year, $schoolClass, $subject, $student] = examFixtures(withStudent: true);

    $exam = Exam::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $schoolClass->grade_id,
        'school_class_id' => $schoolClass->id,
    ]);
    $examSubject = ExamSubject::factory()->create([
        'exam_id' => $exam->id,
        'subject_id' => $subject->id,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.marks.edit', $examSubject))
        ->assertOk()
        ->assertSee($student->user->name)
        ->assertSee('name="records[0][marks_obtained]"', false)
        ->assertSee('data-flux-control', false)
        ->assertDontSee('rounded border border-border bg-transparent px-2 py-1', false);
});
