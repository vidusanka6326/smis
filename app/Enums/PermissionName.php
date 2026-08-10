<?php

namespace App\Enums;

enum PermissionName: string
{
    case ManageAdmins = 'manage-admins';
    case ManageTeachers = 'manage-teachers';
    case ManageStudents = 'manage-students';
    case ManageUsers = 'manage-users';
    case ManageTimetable = 'manage-timetable';
    case ViewTimetable = 'view-timetable';
    case ManageAttendance = 'manage-attendance';
    case ViewAttendance = 'view-attendance';
    case EnterMarks = 'enter-marks';
    case ViewMarks = 'view-marks';
    case ManageExaminations = 'manage-examinations';
    case ViewReports = 'view-reports';
    case ManageSystemConfig = 'manage-system-config';
    case ViewActivityLog = 'view-activity-log';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Default permissions granted to each role.
     *
     * @return array<string, list<self>>
     */
    public static function forRoles(): array
    {
        return [
            RoleName::Admin->value => self::cases(),
            RoleName::Teacher->value => [
                self::ViewTimetable,
                self::ManageAttendance,
                self::ViewAttendance,
                self::EnterMarks,
                self::ViewMarks,
                self::ViewReports,
            ],
            RoleName::Student->value => [
                self::ViewTimetable,
                self::ViewAttendance,
                self::ViewMarks,
            ],
        ];
    }
}
