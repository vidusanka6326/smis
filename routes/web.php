<?php

use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\GradeController;
use App\Http\Controllers\Admin\SchoolClassController;
use App\Http\Controllers\Admin\StreamController;
use App\Http\Controllers\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TeacherAssignmentController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\StudentController as TeacherStudentController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified', 'active'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::view('dashboard', 'admin.dashboard')->name('dashboard');
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');

        Route::resource('academic-years', AcademicYearController::class)->except(['show']);
        Route::resource('grades', GradeController::class)->except(['show']);
        Route::resource('streams', StreamController::class)->except(['show']);
        Route::resource('subjects', SubjectController::class)->except(['show']);
        Route::resource('classes', SchoolClassController::class)
            ->except(['show'])
            ->parameters(['classes' => 'school_class']);

        Route::resource('teachers', TeacherController::class);
        Route::get('teachers/{teacher}/assignments', [TeacherAssignmentController::class, 'edit'])
            ->name('teachers.assignments.edit');
        Route::put('teachers/{teacher}/assignments', [TeacherAssignmentController::class, 'update'])
            ->name('teachers.assignments.update');

        Route::resource('students', AdminStudentController::class);
    });

    Route::middleware('role:teacher')->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('dashboard', TeacherDashboardController::class)->name('dashboard');
        Route::resource('students', TeacherStudentController::class)->except(['show', 'destroy']);
    });

    Route::middleware('role:student')->prefix('student')->name('student.')->group(function () {
        Route::get('dashboard', StudentDashboardController::class)->name('dashboard');
    });
});

require __DIR__.'/settings.php';
