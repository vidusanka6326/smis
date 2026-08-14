<?php

use App\Enums\AttendanceStatus;
use App\Enums\Gender;
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
use App\Services\Agent\AgentToolRegistry;
use App\Services\Agent\Tools\EnterMarksTool;
use App\Services\Agent\Tools\GetDashboardSummaryTool;
use App\Services\Agent\Tools\ListCapabilitiesTool;
use App\Services\Agent\Tools\ManageGradeTool;
use App\Services\Agent\Tools\ManageOfficerTool;
use App\Services\Agent\Tools\ManageStudentTool;
use App\Services\Agent\Tools\ManageTeacherTool;
use App\Services\Agent\Tools\SaveAttendanceSessionTool;

test('capabilities lists the signed-in user’s permissions', function () {
    $admin = User::factory()->admin()->create();

    $result = app(ListCapabilitiesTool::class)->handle($admin, []);

    expect($result['ok'])->toBeTrue()
        ->and($result['roles'])->toContain('admin')
        ->and($result['permissions'])->toContain('manage-students')
        ->and($result['permissions'])->toContain('use-smis-agent');
});

test('admin can create a grade through the agent', function () {
    $admin = User::factory()->admin()->create();

    $result = app(ManageGradeTool::class)->handle($admin, [
        'action' => 'create',
        'number' => 5,
        'name' => 'Grade 5',
    ]);

    expect($result['ok'])->toBeTrue()
        ->and($result['grade']['number'])->toBe(5);

    $this->assertDatabaseHas('grades', ['number' => 5, 'name' => 'Grade 5']);
});

test('teacher cannot create a grade through the registry', function () {
    $teacherUser = User::factory()->teacher()->create();

    expect(app(ManageGradeTool::class)->authorized($teacherUser))->toBeFalse();

    $result = app(AgentToolRegistry::class)->execute($teacherUser, 'manage_grade', [
        'action' => 'create',
        'number' => 4,
        'name' => 'Grade 4',
    ]);

    expect($result['ok'])->toBeFalse();
});

test('officer can create a student through the agent', function () {
    $year = AcademicYear::factory()->current()->create();
    $grade = Grade::factory()->number(8)->create();
    $schoolClass = SchoolClass::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $grade->id,
        'code' => '8-A',
        'name' => 'A',
    ]);
    $officer = User::factory()->officer()->create();

    $result = app(ManageStudentTool::class)->handle($officer, [
        'action' => 'create',
        'name' => 'Kasun Perera',
        'email' => 'kasun.agent@example.com',
        'password' => 'password',
        'admission_no' => 'ADM-AGENT-1',
        'gender' => 'B',
        'class_code' => '8-A',
    ]);

    expect($result['ok'])->toBeTrue()
        ->and($result['student']['admission_no'])->toBe('ADM-AGENT-1');

    $this->assertDatabaseHas('students', [
        'admission_no' => 'ADM-AGENT-1',
        'current_class_id' => $schoolClass->id,
    ]);
});

test('class teacher can create a student in their homeroom through the agent', function () {
    $teacherUser = User::factory()->teacher()->create();
    $teacher = Teacher::factory()->create(['user_id' => $teacherUser->id]);
    $year = AcademicYear::factory()->current()->create();
    $grade = Grade::factory()->number(7)->create();
    $schoolClass = SchoolClass::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $grade->id,
        'class_teacher_id' => $teacher->id,
        'code' => '7-A',
        'name' => 'A',
    ]);

    TeacherAssignment::factory()->create([
        'teacher_id' => $teacher->id,
        'school_class_id' => $schoolClass->id,
        'academic_year_id' => $year->id,
        'role_in_assignment' => TeacherAssignmentRole::ClassTeacher,
        'subject_id' => null,
    ]);

    $result = app(ManageStudentTool::class)->handle($teacherUser, [
        'action' => 'create',
        'name' => 'Class Kid',
        'email' => 'class.kid.agent@example.com',
        'password' => 'password',
        'admission_no' => 'ADM-AGENT-2',
        'gender' => 'girl',
        'class_code' => '7-A',
    ]);

    expect($result['ok'])->toBeTrue();
    expect(Student::query()->where('admission_no', 'ADM-AGENT-2')->exists())->toBeTrue();
});

test('class teacher cannot create a student in another class through the agent', function () {
    $teacherUser = User::factory()->teacher()->create();
    $teacher = Teacher::factory()->create(['user_id' => $teacherUser->id]);
    $year = AcademicYear::factory()->current()->create();
    $ownClass = SchoolClass::factory()->create([
        'academic_year_id' => $year->id,
        'class_teacher_id' => $teacher->id,
        'code' => '7-A',
        'name' => 'A',
    ]);
    SchoolClass::factory()->create([
        'academic_year_id' => $year->id,
        'code' => '7-B',
        'name' => 'B',
    ]);

    TeacherAssignment::factory()->create([
        'teacher_id' => $teacher->id,
        'school_class_id' => $ownClass->id,
        'academic_year_id' => $year->id,
        'role_in_assignment' => TeacherAssignmentRole::ClassTeacher,
        'subject_id' => null,
    ]);

    $result = app(AgentToolRegistry::class)->execute($teacherUser, 'manage_student', [
        'action' => 'create',
        'name' => 'Other Kid',
        'email' => 'other.kid.agent@example.com',
        'password' => 'password',
        'admission_no' => 'ADM-AGENT-3',
        'gender' => Gender::Boy->value,
        'class_code' => '7-B',
    ]);

    expect($result['ok'])->toBeFalse();
});

test('teacher cannot create a teacher through the registry', function () {
    $teacherUser = User::factory()->teacher()->create();

    expect(app(ManageTeacherTool::class)->authorized($teacherUser))->toBeFalse();

    $result = app(AgentToolRegistry::class)->execute($teacherUser, 'manage_teacher', [
        'action' => 'create',
        'name' => 'New Teacher',
        'email' => 'new.teacher@example.com',
        'password' => 'password',
        'employee_no' => 'TCH-AGENT',
    ]);

    expect($result['ok'])->toBeFalse();
});

test('officer cannot manage officers through the registry', function () {
    $officer = User::factory()->officer()->create();

    expect(app(ManageOfficerTool::class)->authorized($officer))->toBeFalse();

    $result = app(AgentToolRegistry::class)->execute($officer, 'manage_officer', [
        'action' => 'create',
        'name' => 'Extra Officer',
        'email' => 'extra.officer@example.com',
        'password' => 'password',
    ]);

    expect($result['ok'])->toBeFalse();
});

test('admin can save a class attendance session through the agent', function () {
    $year = AcademicYear::factory()->current()->create();
    $grade = Grade::factory()->number(9)->create();
    $schoolClass = SchoolClass::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $grade->id,
        'code' => '9-A',
        'name' => 'A',
    ]);
    $student = Student::factory()->create(['current_class_id' => $schoolClass->id]);
    $student->user->forceFill(['name' => 'Nimali Fernando'])->save();
    $admin = User::factory()->admin()->create();

    $result = app(SaveAttendanceSessionTool::class)->handle($admin, [
        'action' => 'save',
        'class_code' => '9-A',
        'date' => now()->toDateString(),
        'records' => [
            ['student' => 'Nimali Fernando', 'status' => AttendanceStatus::Present->value],
        ],
    ]);

    expect($result['ok'])->toBeTrue()
        ->and($result['record_count'])->toBe(1);
});

test('subject teacher can enter marks through the agent', function () {
    $user = User::factory()->teacher()->create();
    $teacher = Teacher::factory()->create(['user_id' => $user->id]);
    $year = AcademicYear::factory()->current()->create();
    $grade = Grade::factory()->number(10)->create();
    $subject = Subject::factory()->forGradeRange(1, 13)->create(['name' => 'Science']);
    $schoolClass = SchoolClass::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $grade->id,
        'code' => '10-A',
        'name' => 'A',
    ]);
    $schoolClass->subjects()->sync([$subject->id]);
    $student = Student::factory()->create(['current_class_id' => $schoolClass->id]);
    $student->user->forceFill(['name' => 'Kasun Marks'])->save();

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
        'name' => 'First Term Test',
    ]);
    ExamSubject::factory()->create([
        'exam_id' => $exam->id,
        'subject_id' => $subject->id,
        'max_marks' => 100,
        'pass_mark' => 40,
    ]);

    $result = app(EnterMarksTool::class)->handle($user, [
        'exam_name' => 'First Term Test',
        'subject_name' => 'Science',
        'records' => [
            ['student' => 'Kasun Marks', 'marks_obtained' => 72],
        ],
    ]);

    expect($result['ok'])->toBeTrue()
        ->and($result['saved'])->toBe(1);

    expect(Mark::query()->where('student_id', $student->id)->where('marks_obtained', 72)->exists())->toBeTrue();
});

test('admin dashboard summary is available through the agent', function () {
    AcademicYear::factory()->current()->create();
    $admin = User::factory()->admin()->create();

    $result = app(GetDashboardSummaryTool::class)->handle($admin, []);

    expect($result['ok'])->toBeTrue()
        ->and($result['role'])->toBe('admin')
        ->and($result['stats'])->toHaveKey('students');
});
