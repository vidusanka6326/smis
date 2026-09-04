<?php

use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AtRiskAttendanceReportController as AdminAtRiskAttendanceReportController;
use App\Http\Controllers\Admin\AttendanceMonthlySummaryController as AdminAttendanceMonthlySummaryController;
use App\Http\Controllers\Admin\AttendanceReportController as AdminAttendanceReportController;
use App\Http\Controllers\Admin\AttendanceSessionController as AdminAttendanceSessionController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DemographicsReportController;
use App\Http\Controllers\Admin\EnrollmentReportController as AdminEnrollmentReportController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\ExaminationReportController as AdminExaminationReportController;
use App\Http\Controllers\Admin\ExamResultsReportController as AdminExamResultsReportController;
use App\Http\Controllers\Admin\ExamSubjectController;
use App\Http\Controllers\Admin\GradeController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\MarkEntryController as AdminMarkEntryController;
use App\Http\Controllers\Admin\OfficerController;
use App\Http\Controllers\Admin\PerformanceReportController as AdminPerformanceReportController;
use App\Http\Controllers\Admin\ReliefTeacherAssignmentController;
use App\Http\Controllers\Admin\ReportDashboardController as AdminReportDashboardController;
use App\Http\Controllers\Admin\SchoolClassController;
use App\Http\Controllers\Admin\StaffAttendanceReportController as AdminStaffAttendanceReportController;
use App\Http\Controllers\Admin\StreamController;
use App\Http\Controllers\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TeacherAssignmentController;
use App\Http\Controllers\Admin\TeacherAssignmentReportController as AdminTeacherAssignmentReportController;
use App\Http\Controllers\Admin\TeacherAttendanceController as AdminTeacherAttendanceController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\TimetableController as AdminTimetableController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Student\AttendanceController as StudentAttendanceController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\ExamScheduleController as StudentExamScheduleController;
use App\Http\Controllers\Student\OwnAttendanceReportController as StudentOwnAttendanceReportController;
use App\Http\Controllers\Student\OwnReportController as StudentOwnReportController;
use App\Http\Controllers\Student\OwnResultsReportController as StudentOwnResultsReportController;
use App\Http\Controllers\Student\ReportCatalogController as StudentReportCatalogController;
use App\Http\Controllers\Student\ResultController as StudentResultController;
use App\Http\Controllers\Student\TimetableController as StudentTimetableController;
use App\Http\Controllers\Teacher\AtRiskAttendanceReportController as TeacherAtRiskAttendanceReportController;
use App\Http\Controllers\Teacher\AttendanceMonthlySummaryController as TeacherAttendanceMonthlySummaryController;
use App\Http\Controllers\Teacher\AttendanceReportController as TeacherAttendanceReportController;
use App\Http\Controllers\Teacher\AttendanceSessionController as TeacherAttendanceSessionController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\EnrollmentReportController as TeacherEnrollmentReportController;
use App\Http\Controllers\Teacher\ExaminationReportController as TeacherExaminationReportController;
use App\Http\Controllers\Teacher\ExamResultsReportController as TeacherExamResultsReportController;
use App\Http\Controllers\Teacher\MarkEntryController as TeacherMarkEntryController;
use App\Http\Controllers\Teacher\PerformanceReportController as TeacherPerformanceReportController;
use App\Http\Controllers\Teacher\ReliefAssignmentController as TeacherReliefAssignmentController;
use App\Http\Controllers\Teacher\ReportDashboardController as TeacherReportDashboardController;
use App\Http\Controllers\Teacher\StudentController as TeacherStudentController;
use App\Http\Controllers\Teacher\TeacherAttendanceController as TeacherSelfAttendanceController;
use App\Http\Controllers\Teacher\TeacherDataSheetController;
use App\Http\Controllers\Teacher\TimetableController as TeacherTimetableController;
use App\Livewire\Agent\Chat as AgentChat;
use App\Livewire\Teacher\DataSheet\Form;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::post('locale', LocaleController::class)
    ->middleware('throttle:30,1')
    ->name('locale.update');

Route::middleware(['auth', 'verified', 'active'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::middleware('role:admin|officer|teacher')->group(function () {
        Route::livewire('agent', AgentChat::class)->name('agent.chat');
    });

    Route::middleware('role:admin|officer')->prefix('admin')->name('admin.')->group(function () {
        Route::get('dashboard', AdminDashboardController::class)->name('dashboard');
        Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

        Route::middleware('role:admin')->group(function () {
            Route::resource('officers', OfficerController::class)->except(['show']);
        });

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
        Route::resource('lessons', LessonController::class)->only(['index', 'show', 'destroy']);

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

        Route::get('exams', [ExamController::class, 'index'])->name('exams.index');
        Route::get('exams/create', [ExamController::class, 'create'])->name('exams.create');
        Route::post('exams', [ExamController::class, 'store'])->name('exams.store');
        Route::get('exams/{exam}/edit', [ExamController::class, 'edit'])->name('exams.edit');
        Route::put('exams/{exam}', [ExamController::class, 'update'])->name('exams.update');
        Route::delete('exams/{exam}', [ExamController::class, 'destroy'])->name('exams.destroy');
        Route::post('exams/{exam}/publish', [ExamController::class, 'publish'])->name('exams.publish');
        Route::post('exams/{exam}/unpublish', [ExamController::class, 'unpublish'])->name('exams.unpublish');
        Route::get('exams/{exam}/subjects', [ExamSubjectController::class, 'edit'])->name('exams.subjects.edit');
        Route::put('exams/{exam}/subjects', [ExamSubjectController::class, 'update'])->name('exams.subjects.update');
        Route::get('marks', [AdminMarkEntryController::class, 'index'])->name('marks.index');
        Route::get('marks/{exam_subject}/edit', [AdminMarkEntryController::class, 'edit'])->name('marks.edit');
        Route::put('marks/{exam_subject}', [AdminMarkEntryController::class, 'update'])->name('marks.update');

        Route::get('reports', AdminReportDashboardController::class)->name('reports.dashboard');
        Route::get('reports/demographics', DemographicsReportController::class)->name('reports.demographics');
        Route::get('reports/attendance', AdminAttendanceReportController::class)->name('reports.attendance');
        Route::get('reports/at-risk', AdminAtRiskAttendanceReportController::class)->name('reports.at-risk');
        Route::get('reports/staff-attendance', AdminStaffAttendanceReportController::class)->name('reports.staff-attendance');
        Route::get('reports/enrollment', AdminEnrollmentReportController::class)->name('reports.enrollment');
        Route::get('reports/examination', AdminExaminationReportController::class)->name('reports.examination');
        Route::get('reports/exam-results', AdminExamResultsReportController::class)->name('reports.exam-results');
        Route::get('reports/performance', AdminPerformanceReportController::class)->name('reports.performance');
        Route::get('reports/assignments', AdminTeacherAssignmentReportController::class)->name('reports.assignments');
    });

    Route::middleware('role:teacher')->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('dashboard', TeacherDashboardController::class)->name('dashboard');
        Route::resource('students', TeacherStudentController::class)->except(['show', 'destroy']);
        Route::resource('lessons', App\Http\Controllers\Teacher\LessonController::class);
        Route::get('timetable', TeacherTimetableController::class)->name('timetable');
        Route::get('relief-assignments', [TeacherReliefAssignmentController::class, 'index'])->name('relief-assignments.index');

        Route::get('data-sheet', Form::class)->name('data-sheet.index');
        Route::get('data-sheet/pdf', [TeacherDataSheetController::class, 'pdf'])->name('data-sheet.pdf');

        Route::get('attendance/sessions', [TeacherAttendanceSessionController::class, 'index'])->name('attendance.sessions.index');
        Route::get('attendance/sessions/create', [TeacherAttendanceSessionController::class, 'create'])->name('attendance.sessions.create');
        Route::post('attendance/sessions', [TeacherAttendanceSessionController::class, 'store'])->name('attendance.sessions.store');
        Route::get('attendance/sessions/{attendance_session}/edit', [TeacherAttendanceSessionController::class, 'edit'])->name('attendance.sessions.edit');
        Route::put('attendance/sessions/{attendance_session}', [TeacherAttendanceSessionController::class, 'update'])->name('attendance.sessions.update');
        Route::post('attendance/sessions/{attendance_session}/finalize', [TeacherAttendanceSessionController::class, 'finalize'])->name('attendance.sessions.finalize');
        Route::get('attendance/monthly', TeacherAttendanceMonthlySummaryController::class)->name('attendance.monthly');
        Route::get('attendance/self', [TeacherSelfAttendanceController::class, 'index'])->name('attendance.self.index');
        Route::post('attendance/self', [TeacherSelfAttendanceController::class, 'store'])->name('attendance.self.store');

        Route::get('marks', [TeacherMarkEntryController::class, 'index'])->name('marks.index');
        Route::get('marks/{exam_subject}/edit', [TeacherMarkEntryController::class, 'edit'])->name('marks.edit');
        Route::put('marks/{exam_subject}', [TeacherMarkEntryController::class, 'update'])->name('marks.update');

        Route::get('reports', TeacherReportDashboardController::class)->name('reports.dashboard');
        Route::get('reports/attendance', TeacherAttendanceReportController::class)->name('reports.attendance');
        Route::get('reports/at-risk', TeacherAtRiskAttendanceReportController::class)->name('reports.at-risk');
        Route::get('reports/enrollment', TeacherEnrollmentReportController::class)->name('reports.enrollment');
        Route::get('reports/examination', TeacherExaminationReportController::class)->name('reports.examination');
        Route::get('reports/exam-results', TeacherExamResultsReportController::class)->name('reports.exam-results');
        Route::get('reports/performance', TeacherPerformanceReportController::class)->name('reports.performance');
    });

    Route::middleware('role:student')->prefix('student')->name('student.')->group(function () {
        Route::get('dashboard', StudentDashboardController::class)->name('dashboard');
        Route::get('timetable', StudentTimetableController::class)->name('timetable');
        Route::get('exam-schedule', StudentExamScheduleController::class)->name('exam-schedule');
        Route::get('attendance', StudentAttendanceController::class)->name('attendance');
        Route::get('results', StudentResultController::class)->name('results');
        Route::get('lessons', [App\Http\Controllers\Student\LessonController::class, 'index'])->name('lessons.index');
        Route::get('lessons/{lesson}', [App\Http\Controllers\Student\LessonController::class, 'show'])->name('lessons.show');
        Route::get('reports', StudentReportCatalogController::class)->name('reports');
        Route::get('reports/attendance', StudentOwnAttendanceReportController::class)->name('reports.attendance');
        Route::get('reports/results', StudentOwnResultsReportController::class)->name('reports.results');
        Route::get('report', StudentOwnReportController::class)->name('report');
    });
});

require __DIR__.'/settings.php';
