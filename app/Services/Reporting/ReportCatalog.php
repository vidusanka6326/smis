<?php

namespace App\Services\Reporting;

class ReportCatalog
{
    /**
     * @return list<array{key: string, title: string, description: string, icon: string, route: string}>
     */
    public function forAdmin(): array
    {
        return [
            $this->item('attendance', __('Student attendance'), __('Monthly attendance by student and class.'), 'clipboard-document-check', 'admin.reports.attendance'),
            $this->item('at-risk', __('Attendance at risk'), __('Students below the 80% monthly attendance threshold.'), 'clipboard-document-list', 'admin.reports.at-risk'),
            $this->item('staff-attendance', __('Teacher attendance'), __('Staff attendance totals for a selected month.'), 'users', 'admin.reports.staff-attendance'),
            $this->item('demographics', __('Student demographics'), __('Headcount by gender, grade, class, and subject.'), 'chart-bar', 'admin.reports.demographics'),
            $this->item('enrollment', __('Class enrollment'), __('Student register with class, gender, and guardian details.'), 'building-library', 'admin.reports.enrollment'),
            $this->item('examination', __('Examination statistics'), __('Pass rates and averages by subject and class.'), 'academic-cap', 'admin.reports.examination'),
            $this->item('exam-results', __('Exam results'), __('Student-level marks, grades, and pass/fail for an exam.'), 'document-text', 'admin.reports.exam-results'),
            $this->item('performance', __('Best & poor performers'), __('Top and bottom students by exam average.'), 'book-open', 'admin.reports.performance'),
            $this->item('assignments', __('Teacher assignments'), __('Who teaches which class and subject.'), 'briefcase', 'admin.reports.assignments'),
        ];
    }

    /**
     * @return list<array{key: string, title: string, description: string, icon: string, route: string}>
     */
    public function forTeacher(): array
    {
        return [
            $this->item('attendance', __('Student attendance'), __('Monthly attendance for your classes.'), 'clipboard-document-check', 'teacher.reports.attendance'),
            $this->item('at-risk', __('Attendance at risk'), __('Your students below the 80% attendance threshold.'), 'clipboard-document-list', 'teacher.reports.at-risk'),
            $this->item('enrollment', __('Class roster'), __('Students in your assigned classes.'), 'users', 'teacher.reports.enrollment'),
            $this->item('examination', __('Examination statistics'), __('Pass rates for students in your scope.'), 'academic-cap', 'teacher.reports.examination'),
            $this->item('exam-results', __('Exam results'), __('Marks and grades for your students.'), 'document-text', 'teacher.reports.exam-results'),
            $this->item('performance', __('Best & poor performers'), __('Ranked students in your classes.'), 'book-open', 'teacher.reports.performance'),
        ];
    }

    /**
     * @return list<array{key: string, title: string, description: string, icon: string, route: string}>
     */
    public function forStudent(): array
    {
        return [
            $this->item('card', __('Report card'), __('Attendance summary and published exam results together.'), 'document-text', 'student.report'),
            $this->item('attendance', __('My attendance'), __('Monthly attendance records you can filter and download.'), 'clipboard-document-check', 'student.reports.attendance'),
            $this->item('results', __('My exam results'), __('Published marks, grades, and pass/fail by exam.'), 'academic-cap', 'student.reports.results'),
        ];
    }

    /**
     * @return array{key: string, title: string, description: string, icon: string, route: string}
     */
    private function item(string $key, string $title, string $description, string $icon, string $route): array
    {
        return [
            'key' => $key,
            'title' => $title,
            'description' => $description,
            'icon' => $icon,
            'route' => $route,
        ];
    }
}
