<?php

use App\Enums\TeacherAssignmentRole;

test('subject teacher role requires a subject', function (TeacherAssignmentRole $role, bool $requires) {
    expect($role->requiresSubject())->toBe($requires);
})->with([
    [TeacherAssignmentRole::ClassTeacher, false],
    [TeacherAssignmentRole::SubjectTeacher, true],
    [TeacherAssignmentRole::PtPdTeacher, false],
]);
