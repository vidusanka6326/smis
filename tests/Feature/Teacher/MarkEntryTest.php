<?php

use App\Enums\TeacherAssignmentRole;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\Grade;
use App\Models\Mark;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\User;

test('subject teacher can enter marks for assigned subject', function () {
    $user = User::factory()->teacher()->create();
    $teacher = Teacher::factory()->create(['user_id' => $user->id]);
    $year = AcademicYear::factory()->current()->create();
    $grade = Grade::factory()->number(10)->create();
    $subject = Subject::factory()->forGradeRange(1, 13)->create();
    $schoolClass = SchoolClass::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $grade->id,
    ]);
    $schoolClass->subjects()->sync([$subject->id]);
    $student = Student::factory()->create(['current_class_id' => $schoolClass->id]);

    TeacherAssignment::factory()->create([
        'teacher_id' => $teacher->id,
        'school_class_id' => $schoolClass->id,
        'subject_id' => $subject->id,
        'academic_year_id' => $year->id,
        'role_in_assignment' => TeacherAssignmentRole::SubjectTeacher,
    ]);

    $exam = Exam::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $grade->id,
        'school_class_id' => $schoolClass->id,
    ]);
    $examSubject = ExamSubject::factory()->create([
        'exam_id' => $exam->id,
        'subject_id' => $subject->id,
        'max_marks' => 100,
        'pass_mark' => 40,
    ]);

    $this->actingAs($user)
        ->put(route('teacher.marks.update', $examSubject), [
            'records' => [
                [
                    'student_id' => $student->id,
                    'marks_obtained' => 66,
                ],
            ],
        ])
        ->assertSessionHasNoErrors();

    expect(Mark::query()->where('entered_by_teacher_id', $teacher->id)->count())->toBe(1);
});

test('subject teacher cannot enter marks for another subject', function () {
    $user = User::factory()->teacher()->create();
    $teacher = Teacher::factory()->create(['user_id' => $user->id]);
    $year = AcademicYear::factory()->current()->create();
    $grade = Grade::factory()->number(10)->create();
    $assigned = Subject::factory()->forGradeRange(1, 13)->create();
    $other = Subject::factory()->forGradeRange(1, 13)->create();
    $schoolClass = SchoolClass::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $grade->id,
    ]);
    $schoolClass->subjects()->sync([$assigned->id, $other->id]);
    $student = Student::factory()->create(['current_class_id' => $schoolClass->id]);

    TeacherAssignment::factory()->create([
        'teacher_id' => $teacher->id,
        'school_class_id' => $schoolClass->id,
        'subject_id' => $assigned->id,
        'academic_year_id' => $year->id,
        'role_in_assignment' => TeacherAssignmentRole::SubjectTeacher,
    ]);

    $exam = Exam::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $grade->id,
        'school_class_id' => $schoolClass->id,
    ]);
    $examSubject = ExamSubject::factory()->create([
        'exam_id' => $exam->id,
        'subject_id' => $other->id,
    ]);

    $this->actingAs($user)
        ->put(route('teacher.marks.update', $examSubject), [
            'records' => [
                [
                    'student_id' => $student->id,
                    'marks_obtained' => 70,
                ],
            ],
        ])
        ->assertForbidden();
});

test('teacher marks form uses flux number inputs', function () {
    $user = User::factory()->teacher()->create();
    $teacher = Teacher::factory()->create(['user_id' => $user->id]);
    $year = AcademicYear::factory()->current()->create();
    $grade = Grade::factory()->number(10)->create();
    $subject = Subject::factory()->forGradeRange(1, 13)->create();
    $schoolClass = SchoolClass::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $grade->id,
    ]);
    $schoolClass->subjects()->sync([$subject->id]);
    $student = Student::factory()->create(['current_class_id' => $schoolClass->id]);

    TeacherAssignment::factory()->create([
        'teacher_id' => $teacher->id,
        'school_class_id' => $schoolClass->id,
        'subject_id' => $subject->id,
        'academic_year_id' => $year->id,
        'role_in_assignment' => TeacherAssignmentRole::SubjectTeacher,
    ]);

    $exam = Exam::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $grade->id,
        'school_class_id' => $schoolClass->id,
    ]);
    $examSubject = ExamSubject::factory()->create([
        'exam_id' => $exam->id,
        'subject_id' => $subject->id,
    ]);

    $this->actingAs($user)
        ->get(route('teacher.marks.edit', $examSubject))
        ->assertOk()
        ->assertSee($student->user->name)
        ->assertSee('name="records[0][marks_obtained]"', false)
        ->assertSee('data-flux-control', false)
        ->assertDontSee('rounded border border-border bg-transparent px-2 py-1', false);
});
