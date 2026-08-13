<?php

use App\Models\User;

test('admin create forms use sectioned multi-column layouts', function (string $route, array $see) {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get(route($route));

    $response->assertOk();

    foreach ($see as $text) {
        $response->assertSee($text, false);
    }

    $response->assertSee('sm:grid-cols-2', false);
})->with([
    'students' => ['admin.students.create', ['Account', 'Student profile', 'Guardian', 'Enrollment']],
    'teachers' => ['admin.teachers.create', ['Account', 'Employment']],
    'users' => ['admin.users.create', ['Account details', 'Access']],
    'classes' => ['admin.classes.create', ['Class identity', 'Subjects']],
    'subjects' => ['admin.subjects.create', ['Subject details', 'Grade range']],
    'exams' => ['admin.exams.create', ['Exam details', 'Scope', 'Schedule']],
    'academic years' => ['admin.academic-years.create', ['Year details']],
]);
