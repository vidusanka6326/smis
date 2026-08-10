<?php

use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\AttendanceMonthlySummaryController as AdminAttendanceMonthlySummaryController;
use App\Http\Controllers\Admin\AttendanceSessionController as AdminAttendanceSessionController;
use App\Http\Controllers\Admin\GradeController;
use App\Http\Controllers\Admin\ReliefTeacherAssignmentController;
use App\Http\Controllers\Admin\SchoolClassController;
use App\Http\Controllers\Admin\StreamController;
use App\Http\Controllers\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TeacherAssignmentController;
use App\Http\Controllers\Admin\TeacherAttendanceController as AdminTeacherAttendanceController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\TimetableController as AdminTimetableController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Student\AttendanceController as StudentAttendanceController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\TimetableController as StudentTimetableController;
use App\Http\Controllers\Teacher\AttendanceMonthlySummaryController as TeacherAttendanceMonthlySummaryController;
use App\Http\Controllers\Teacher\AttendanceSessionController as TeacherAttendanceSessionController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\StudentController as TeacherStudentController;
use App\Http\Controllers\Teacher\TeacherAttendanceController as TeacherSelfAttendanceController;
use App\Http\Controllers\Teacher\TimetableController as TeacherTimetableController;
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

        Route::get('timetables', [AdminTimetableController::class, 'index'])->name('timetables.index');
        Route::post('timetables', [AdminTimetableController::class, 'store'])->name('timetables.store');
        Route::get('timetables/{timetable_entry}/edit', [AdminTimetableController::class, 'edit'])->name('timetables.edit');
        Route::put('timetables/{timetable_entry}', [AdminTimetableController::class, 'update'])->name('timetables.update');
        Route::delete('timetables/{timetable_entry}', [AdminTimetableController::class, 'destroy'])->name('timetables.destroy');

        Route::get('relief-assignments', [ReliefTeacherAssignmentController::class, 'index'])->name('relief-assignments.index');
        Route::get('relief-assignments/create', [ReliefTeacherAssignmentController::class, 'create'])->name('relief-assignments.create');
        Route::post('relief-assignments', [ReliefTeacherAssignmentController::class, 'store'])->name('relief-assignments.store');
        Route::delete('relief-assignments/{relief_teacher_assignment}', [ReliefTeacherAssignmentController::class, 'destroy'])
            ->name('relief-assignments.destroy');

        Route::get('attendance/sessions', [AdminAttendanceSessionController::class, 'index'])->name('attendance.sessions.index');
        Route::get('attendance/sessions/create', [AdminAttendanceSessionController::class, 'create'])->name('attendance.sessions.create');
        Route::post('attendance/sessions', [AdminAttendanceSessionController::class, 'store'])->name('attendance.sessions.store');
        Route::get('attendance/sessions/{attendance_session}/edit', [AdminAttendanceSessionController::class, 'edit'])->name('attendance.sessions.edit');
        Route::put('attendance/sessions/{attendance_session}', [AdminAttendanceSessionController::class, 'update'])->name('attendance.sessions.update');
        Route::delete('attendance/sessions/{attendance_session}', [AdminAttendanceSessionController::class, 'destroy'])->name('attendance.sessions.destroy');
        Route::post('attendance/sessions/{attendance_session}/finalize', [AdminAttendanceSessionController::class, 'finalize'])->name('attendance.sessions.finalize');
        Route::get('attendance/monthly', AdminAttendanceMonthlySummaryController::class)->name('attendance.monthly');
        Route::get('attendance/teachers', [AdminTeacherAttendanceController::class, 'index'])->name('attendance.teachers.index');
        Route::post('attendance/teachers', [AdminTeacherAttendanceController::class, 'store'])->name('attendance.teachers.store');
        Route::delete('attendance/teachers/{teacher_attendance}', [AdminTeacherAttendanceController::class, 'destroy'])->name('attendance.teachers.destroy');
    });

    Route::middleware('role:teacher')->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('dashboard', TeacherDashboardController::class)->name('dashboard');
        Route::resource('students', TeacherStudentController::class)->except(['show', 'destroy']);
        Route::get('timetable', TeacherTimetableController::class)->name('timetable');

        Route::get('attendance/sessions', [TeacherAttendanceSessionController::class, 'index'])->name('attendance.sessions.index');
        Route::get('attendance/sessions/create', [TeacherAttendanceSessionController::class, 'create'])->name('attendance.sessions.create');
        Route::post('attendance/sessions', [TeacherAttendanceSessionController::class, 'store'])->name('attendance.sessions.store');
        Route::get('attendance/sessions/{attendance_session}/edit', [TeacherAttendanceSessionController::class, 'edit'])->name('attendance.sessions.edit');
        Route::put('attendance/sessions/{attendance_session}', [TeacherAttendanceSessionController::class, 'update'])->name('attendance.sessions.update');
        Route::post('attendance/sessions/{attendance_session}/finalize', [TeacherAttendanceSessionController::class, 'finalize'])->name('attendance.sessions.finalize');
        Route::get('attendance/monthly', TeacherAttendanceMonthlySummaryController::class)->name('attendance.monthly');
        Route::get('attendance/self', [TeacherSelfAttendanceController::class, 'index'])->name('attendance.self.index');
        Route::post('attendance/self', [TeacherSelfAttendanceController::class, 'store'])->name('attendance.self.store');
    });

    Route::middleware('role:student')->prefix('student')->name('student.')->group(function () {
        Route::get('dashboard', StudentDashboardController::class)->name('dashboard');
        Route::get('timetable', StudentTimetableController::class)->name('timetable');
        Route::get('attendance', StudentAttendanceController::class)->name('attendance');
    });
});

require __DIR__.'/settings.php';
