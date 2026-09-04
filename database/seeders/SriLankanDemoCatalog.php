<?php

namespace Database\Seeders;

/**
 * Sri Lankan national-curriculum demo dataset (English labels, Sinhala Roman names).
 *
 * Assumption: Type 1AB Maha Vidyalaya (grades 6–13). Grades 1–5 exist as records only.
 */
class SriLankanDemoCatalog
{
    public const ADMIN_EMAIL = 'admin@smis.test';

    public const CLASS_TEACHER_EMAIL = 'class.teacher@smis.test';

    public const SUBJECT_TEACHER_EMAIL = 'subject.teacher@smis.test';

    public const STUDENT_EMAIL = 'student@smis.test';

    public const DEMO_ADMISSION_NO = 'ADM-10001';

    public const DEMO_CLASS_TEACHER_NO = 'TCH-1001';

    public const DEMO_SUBJECT_TEACHER_NO = 'TCH-1002';

    public const PASSWORD = 'password';

    public const ACADEMIC_YEAR_NAME = '2026';

    /**
     * @var list<string>
     */
    public const JUNIOR_SUBJECT_CODES = [
        'BUD', 'SIN', 'ENG', 'MATH', 'SCI', 'HIS', 'GEO', 'CIV', 'HPE', 'PTS', 'ART', 'TSL',
    ];

    /**
     * @var list<string>
     */
    public const OL_SUBJECT_CODES = [
        'BUD', 'SIN', 'ENG', 'MATH', 'SCI', 'HIS', 'CIV', 'ICT', 'BAS',
    ];

    /**
     * @return list<array{name: string, email: string}>
     */
    public static function officers(): array
    {
        return [
            ['name' => 'Tharushi Fernando', 'email' => 'officer@smis.test'],
            ['name' => 'Ruwan Bandara', 'email' => 'officer.ruwan@smis.test'],
            ['name' => 'Nadeesha Perera', 'email' => 'officer.nadeesha@smis.test'],
            ['name' => 'Lasith Gunasekara', 'email' => 'officer.lasith@smis.test'],
            ['name' => 'Thilini Rathnayake', 'email' => 'officer.thilini@smis.test'],
        ];
    }

    /**
     * @return list<array{name: string, code: string}>
     */
    public static function streams(): array
    {
        return [
            ['name' => 'Science', 'code' => 'SCI'],
            ['name' => 'Commerce', 'code' => 'COM'],
            ['name' => 'Arts', 'code' => 'ART'],
            ['name' => 'Technology', 'code' => 'TEC'],
        ];
    }

    /**
     * @return list<array{name: string, code: string, min_grade: int, max_grade: int}>
     */
    public static function subjects(): array
    {
        return [
            ['name' => 'Buddhism', 'code' => 'BUD', 'min_grade' => 6, 'max_grade' => 11],
            ['name' => 'Sinhala Language', 'code' => 'SIN', 'min_grade' => 6, 'max_grade' => 11],
            ['name' => 'English', 'code' => 'ENG', 'min_grade' => 6, 'max_grade' => 11],
            ['name' => 'Mathematics', 'code' => 'MATH', 'min_grade' => 6, 'max_grade' => 11],
            ['name' => 'Science', 'code' => 'SCI', 'min_grade' => 6, 'max_grade' => 11],
            ['name' => 'History', 'code' => 'HIS', 'min_grade' => 6, 'max_grade' => 11],
            ['name' => 'Geography', 'code' => 'GEO', 'min_grade' => 6, 'max_grade' => 13],
            ['name' => 'Civic Education', 'code' => 'CIV', 'min_grade' => 6, 'max_grade' => 11],
            ['name' => 'Health and Physical Education', 'code' => 'HPE', 'min_grade' => 6, 'max_grade' => 9],
            ['name' => 'Practical and Technical Skills', 'code' => 'PTS', 'min_grade' => 6, 'max_grade' => 9],
            ['name' => 'Art', 'code' => 'ART', 'min_grade' => 6, 'max_grade' => 9],
            ['name' => 'Tamil as a Second Language', 'code' => 'TSL', 'min_grade' => 6, 'max_grade' => 9],
            ['name' => 'Information and Communication Technology', 'code' => 'ICT', 'min_grade' => 10, 'max_grade' => 13],
            ['name' => 'Business and Accounting Studies', 'code' => 'BAS', 'min_grade' => 10, 'max_grade' => 11],
            ['name' => 'Combined Mathematics', 'code' => 'CMAT', 'min_grade' => 12, 'max_grade' => 13],
            ['name' => 'Physics', 'code' => 'PHY', 'min_grade' => 12, 'max_grade' => 13],
            ['name' => 'Chemistry', 'code' => 'CHE', 'min_grade' => 12, 'max_grade' => 13],
            ['name' => 'Biology', 'code' => 'BIO', 'min_grade' => 12, 'max_grade' => 13],
            ['name' => 'Accounting', 'code' => 'ACC', 'min_grade' => 12, 'max_grade' => 13],
            ['name' => 'Business Studies', 'code' => 'BST', 'min_grade' => 12, 'max_grade' => 13],
            ['name' => 'Economics', 'code' => 'ECO', 'min_grade' => 12, 'max_grade' => 13],
            ['name' => 'Political Science', 'code' => 'POL', 'min_grade' => 12, 'max_grade' => 13],
            ['name' => 'Logic and Scientific Method', 'code' => 'LOG', 'min_grade' => 12, 'max_grade' => 13],
            ['name' => 'Engineering Technology', 'code' => 'ETEC', 'min_grade' => 12, 'max_grade' => 13],
            ['name' => 'Science for Technology', 'code' => 'SFT', 'min_grade' => 12, 'max_grade' => 13],
        ];
    }

    /**
     * @return list<array{grade: int, section: string, stream: ?string, size: int}>
     */
    public static function classPlans(): array
    {
        return [
            ['grade' => 6, 'section' => 'A', 'stream' => null, 'size' => 30],
            ['grade' => 6, 'section' => 'B', 'stream' => null, 'size' => 30],
            ['grade' => 6, 'section' => 'C', 'stream' => null, 'size' => 30],
            ['grade' => 7, 'section' => 'A', 'stream' => null, 'size' => 30],
            ['grade' => 7, 'section' => 'B', 'stream' => null, 'size' => 30],
            ['grade' => 7, 'section' => 'C', 'stream' => null, 'size' => 30],
            ['grade' => 8, 'section' => 'A', 'stream' => null, 'size' => 28],
            ['grade' => 8, 'section' => 'B', 'stream' => null, 'size' => 28],
            ['grade' => 8, 'section' => 'C', 'stream' => null, 'size' => 28],
            ['grade' => 9, 'section' => 'A', 'stream' => null, 'size' => 28],
            ['grade' => 9, 'section' => 'B', 'stream' => null, 'size' => 28],
            ['grade' => 9, 'section' => 'C', 'stream' => null, 'size' => 28],
            ['grade' => 10, 'section' => 'A', 'stream' => null, 'size' => 28],
            ['grade' => 10, 'section' => 'B', 'stream' => null, 'size' => 28],
            ['grade' => 10, 'section' => 'C', 'stream' => null, 'size' => 28],
            ['grade' => 11, 'section' => 'A', 'stream' => null, 'size' => 28],
            ['grade' => 11, 'section' => 'B', 'stream' => null, 'size' => 28],
            ['grade' => 11, 'section' => 'C', 'stream' => null, 'size' => 28],
            ['grade' => 12, 'section' => 'A', 'stream' => 'SCI', 'size' => 12],
            ['grade' => 12, 'section' => 'B', 'stream' => 'SCI', 'size' => 12],
            ['grade' => 12, 'section' => 'A', 'stream' => 'COM', 'size' => 8],
            ['grade' => 12, 'section' => 'A', 'stream' => 'ART', 'size' => 6],
            ['grade' => 12, 'section' => 'A', 'stream' => 'TEC', 'size' => 4],
            ['grade' => 13, 'section' => 'A', 'stream' => 'SCI', 'size' => 12],
            ['grade' => 13, 'section' => 'B', 'stream' => 'SCI', 'size' => 12],
            ['grade' => 13, 'section' => 'A', 'stream' => 'COM', 'size' => 8],
            ['grade' => 13, 'section' => 'A', 'stream' => 'ART', 'size' => 6],
            ['grade' => 13, 'section' => 'A', 'stream' => 'TEC', 'size' => 4],
        ];
    }

    /**
     * @return list<string>
     */
    public static function subjectCodesFor(int $grade, ?string $stream, string $section): array
    {
        if ($grade >= 6 && $grade <= 9) {
            return self::JUNIOR_SUBJECT_CODES;
        }

        if ($grade >= 10 && $grade <= 11) {
            return self::OL_SUBJECT_CODES;
        }

        return match ($stream) {
            'SCI' => $section === 'A'
                ? ['CMAT', 'PHY', 'CHE']
                : ['BIO', 'PHY', 'CHE'],
            'COM' => ['ACC', 'BST', 'ECO'],
            'ART' => ['POL', 'LOG', 'GEO'],
            'TEC' => ['ETEC', 'SFT', 'ICT'],
            default => [],
        };
    }

    /**
     * Weekly period counts keyed by subject code (8 periods × 5 days = 40).
     *
     * @return array<string, int>
     */
    public static function weeklyPeriodCounts(int $grade): array
    {
        if ($grade >= 6 && $grade <= 9) {
            return [
                'MATH' => 6, 'SCI' => 5, 'ENG' => 5, 'SIN' => 5,
                'BUD' => 3, 'HIS' => 3, 'GEO' => 3, 'CIV' => 2,
                'HPE' => 2, 'PTS' => 2, 'ART' => 2, 'TSL' => 2,
            ];
        }

        if ($grade >= 10 && $grade <= 11) {
            return [
                'MATH' => 6, 'SCI' => 6, 'ENG' => 5, 'SIN' => 5,
                'HIS' => 4, 'BUD' => 3, 'CIV' => 3, 'ICT' => 4, 'BAS' => 4,
            ];
        }

        return [];
    }

    /**
     * A/L classes use 10 periods per main subject (30 taught + 10 free).
     */
    public static function alPeriodsPerSubject(): int
    {
        return 10;
    }

    /**
     * @return list<array{
     *     employee_no: string,
     *     name: string,
     *     email: string,
     *     phone: string,
     *     subjects: list<string>,
     *     homeroom: ?string,
     *     pt_pd: bool,
     *     grades: list<int>
     * }>
     */
    public static function teachers(): array
    {
        return [
            ['employee_no' => 'TCH-1001', 'name' => 'Nimal Perera', 'email' => self::CLASS_TEACHER_EMAIL, 'phone' => '0712341001', 'subjects' => ['ENG'], 'homeroom' => '10-A', 'pt_pd' => false, 'grades' => [10, 11]],
            ['employee_no' => 'TCH-1002', 'name' => 'Chaminda Jayasinghe', 'email' => self::SUBJECT_TEACHER_EMAIL, 'phone' => '0712341002', 'subjects' => ['MATH'], 'homeroom' => null, 'pt_pd' => false, 'grades' => [10, 11]],
            ['employee_no' => 'TCH-1003', 'name' => 'Tharushi Silva', 'email' => 'tharushi.silva@smis.test', 'phone' => '0712341003', 'subjects' => ['ENG'], 'homeroom' => '6-A', 'pt_pd' => false, 'grades' => [6, 7, 8, 9]],
            ['employee_no' => 'TCH-1004', 'name' => 'Lahiru Weerasinghe', 'email' => 'lahiru.weerasinghe@smis.test', 'phone' => '0712341004', 'subjects' => ['ENG'], 'homeroom' => '7-A', 'pt_pd' => false, 'grades' => [6, 7, 8, 9]],
            ['employee_no' => 'TCH-1005', 'name' => 'Kasun Wickramasinghe', 'email' => 'kasun.wickramasinghe@smis.test', 'phone' => '0712341005', 'subjects' => ['MATH'], 'homeroom' => '6-B', 'pt_pd' => false, 'grades' => [6, 7, 8, 9]],
            ['employee_no' => 'TCH-1006', 'name' => 'Dilshan Rathnayake', 'email' => 'dilshan.rathnayake@smis.test', 'phone' => '0712341006', 'subjects' => ['MATH'], 'homeroom' => '7-B', 'pt_pd' => false, 'grades' => [6, 7, 8, 9]],
            ['employee_no' => 'TCH-1007', 'name' => 'Sanduni Gamage', 'email' => 'sanduni.gamage@smis.test', 'phone' => '0712341007', 'subjects' => ['SCI'], 'homeroom' => '6-C', 'pt_pd' => false, 'grades' => [6, 7, 8, 9]],
            ['employee_no' => 'TCH-1008', 'name' => 'Hashini Gunasekara', 'email' => 'hashini.gunasekara@smis.test', 'phone' => '0712341008', 'subjects' => ['SCI'], 'homeroom' => '7-C', 'pt_pd' => false, 'grades' => [6, 7, 8, 9]],
            ['employee_no' => 'TCH-1009', 'name' => 'Ruwan Dissanayake', 'email' => 'ruwan.dissanayake@smis.test', 'phone' => '0712341009', 'subjects' => ['SCI'], 'homeroom' => '10-B', 'pt_pd' => false, 'grades' => [10, 11]],
            ['employee_no' => 'TCH-1010', 'name' => 'Nadeesha Herath', 'email' => 'nadeesha.herath@smis.test', 'phone' => '0712341010', 'subjects' => ['SIN'], 'homeroom' => '8-A', 'pt_pd' => false, 'grades' => [6, 7, 8, 9]],
            ['employee_no' => 'TCH-1011', 'name' => 'Pradeep Karunaratne', 'email' => 'pradeep.karunaratne@smis.test', 'phone' => '0712341011', 'subjects' => ['SIN'], 'homeroom' => '8-B', 'pt_pd' => false, 'grades' => [6, 7, 8, 9]],
            ['employee_no' => 'TCH-1012', 'name' => 'Menaka Liyanage', 'email' => 'menaka.liyanage@smis.test', 'phone' => '0712341012', 'subjects' => ['SIN'], 'homeroom' => '11-A', 'pt_pd' => false, 'grades' => [10, 11]],
            ['employee_no' => 'TCH-1013', 'name' => 'Saman Ekanayake', 'email' => 'saman.ekanayake@smis.test', 'phone' => '0712341013', 'subjects' => ['BUD', 'CIV'], 'homeroom' => '8-C', 'pt_pd' => false, 'grades' => [6, 7, 8, 9]],
            ['employee_no' => 'TCH-1014', 'name' => 'Gayani Senanayake', 'email' => 'gayani.senanayake@smis.test', 'phone' => '0712341014', 'subjects' => ['BUD', 'CIV'], 'homeroom' => '11-B', 'pt_pd' => false, 'grades' => [10, 11]],
            ['employee_no' => 'TCH-1015', 'name' => 'Janaka Pathirana', 'email' => 'janaka.pathirana@smis.test', 'phone' => '0712341015', 'subjects' => ['HIS'], 'homeroom' => '9-A', 'pt_pd' => false, 'grades' => [6, 7, 8, 9]],
            ['employee_no' => 'TCH-1016', 'name' => 'Dinusha Amarasinghe', 'email' => 'dinusha.amarasinghe@smis.test', 'phone' => '0712341016', 'subjects' => ['HIS'], 'homeroom' => null, 'pt_pd' => false, 'grades' => [10, 11]],
            ['employee_no' => 'TCH-1017', 'name' => 'Kavindi Wijesinghe', 'email' => 'kavindi.wijesinghe@smis.test', 'phone' => '0712341017', 'subjects' => ['GEO', 'TSL'], 'homeroom' => '9-B', 'pt_pd' => false, 'grades' => [6, 7, 8, 9]],
            ['employee_no' => 'TCH-1018', 'name' => 'Chathura Alwis', 'email' => 'chathura.alwis@smis.test', 'phone' => '0712341018', 'subjects' => ['PTS', 'ART'], 'homeroom' => null, 'pt_pd' => false, 'grades' => [6, 7, 8, 9]],
            ['employee_no' => 'TCH-1019', 'name' => 'Mahesh Samarakoon', 'email' => 'mahesh.samarakoon@smis.test', 'phone' => '0712341019', 'subjects' => ['HPE'], 'homeroom' => null, 'pt_pd' => true, 'grades' => [6, 7, 8, 9]],
            ['employee_no' => 'TCH-1020', 'name' => 'Nuwan Jayawardena', 'email' => 'nuwan.jayawardena@smis.test', 'phone' => '0712341020', 'subjects' => ['ICT', 'BAS'], 'homeroom' => '10-C', 'pt_pd' => false, 'grades' => [10, 11]],
            ['employee_no' => 'TCH-1021', 'name' => 'Isuru Bandara', 'email' => 'isuru.bandara@smis.test', 'phone' => '0712341021', 'subjects' => ['CMAT'], 'homeroom' => '12-SCI-A', 'pt_pd' => false, 'grades' => [12, 13]],
            ['employee_no' => 'TCH-1022', 'name' => 'Buddhika Ranasinghe', 'email' => 'buddhika.ranasinghe@smis.test', 'phone' => '0712341022', 'subjects' => ['PHY'], 'homeroom' => '12-SCI-B', 'pt_pd' => false, 'grades' => [12, 13]],
            ['employee_no' => 'TCH-1023', 'name' => 'Hiruni Seneviratne', 'email' => 'hiruni.seneviratne@smis.test', 'phone' => '0712341023', 'subjects' => ['CHE'], 'homeroom' => '13-SCI-A', 'pt_pd' => false, 'grades' => [12, 13]],
            ['employee_no' => 'TCH-1024', 'name' => 'Pasindu Goonewardena', 'email' => 'pasindu.goonewardena@smis.test', 'phone' => '0712341024', 'subjects' => ['BIO'], 'homeroom' => '13-SCI-B', 'pt_pd' => false, 'grades' => [12, 13]],
            ['employee_no' => 'TCH-1025', 'name' => 'Roshan Dias', 'email' => 'roshan.dias@smis.test', 'phone' => '0712341025', 'subjects' => ['ACC', 'ECO'], 'homeroom' => '12-COM-A', 'pt_pd' => false, 'grades' => [12, 13]],
            ['employee_no' => 'TCH-1026', 'name' => 'Amal Abeysekara', 'email' => 'amal.abeysekara@smis.test', 'phone' => '0712341026', 'subjects' => ['BST', 'ECO'], 'homeroom' => '13-COM-A', 'pt_pd' => false, 'grades' => [12, 13]],
            ['employee_no' => 'TCH-1027', 'name' => 'Thilini Wijeratne', 'email' => 'thilini.wijeratne@smis.test', 'phone' => '0712341027', 'subjects' => ['POL', 'GEO'], 'homeroom' => '12-ART-A', 'pt_pd' => false, 'grades' => [12, 13]],
            ['employee_no' => 'TCH-1028', 'name' => 'Gayan Mendis', 'email' => 'gayan.mendis@smis.test', 'phone' => '0712341028', 'subjects' => ['LOG', 'GEO'], 'homeroom' => null, 'pt_pd' => false, 'grades' => [12, 13]],
            ['employee_no' => 'TCH-1029', 'name' => 'Oshada Pathirana', 'email' => 'oshada.pathirana@smis.test', 'phone' => '0712341029', 'subjects' => ['ETEC', 'ICT'], 'homeroom' => '12-TEC-A', 'pt_pd' => false, 'grades' => [12, 13]],
            ['employee_no' => 'TCH-1030', 'name' => 'Sewwandi Peiris', 'email' => 'sewwandi.peiris@smis.test', 'phone' => '0712341030', 'subjects' => ['SFT', 'ICT'], 'homeroom' => '13-TEC-A', 'pt_pd' => false, 'grades' => [12, 13]],
        ];
    }

    public static function expectedTeacherCount(): int
    {
        return count(self::teachers());
    }

    public static function expectedOfficerCount(): int
    {
        return count(self::officers());
    }

    public static function expectedStudentCount(): int
    {
        return collect(self::classPlans())->sum('size');
    }

    public static function expectedClassCount(): int
    {
        return count(self::classPlans());
    }

    public static function birthYearForGrade(int $grade): int
    {
        return 2026 - (5 + $grade);
    }

    /**
     * @return array{first: string, last: string, full: string, female: bool}
     */
    public static function person(int $index, bool $female): array
    {
        $firstNames = $female ? self::femaleFirstNames() : self::maleFirstNames();
        $first = $firstNames[$index % count($firstNames)];
        $surnames = self::surnames();
        $last = $surnames[(int) floor($index / 3) % count($surnames)];

        return [
            'first' => $first,
            'last' => $last,
            'full' => $first.' '.$last,
            'female' => $female,
        ];
    }

    /**
     * @return array{first: string, last: string, full: string, female: bool}
     */
    public static function guardianFor(string $surname, int $index, bool $mother): array
    {
        $firstNames = $mother ? self::femaleFirstNames() : self::maleFirstNames();
        $first = $firstNames[($index + 11) % count($firstNames)];

        return [
            'first' => $first,
            'last' => $surname,
            'full' => $first.' '.$surname,
            'female' => $mother,
        ];
    }

    /**
     * @return list<string>
     */
    public static function maleFirstNames(): array
    {
        return [
            'Amal', 'Anura', 'Asanka', 'Bandula', 'Buddhika', 'Chamara', 'Chaminda', 'Charith',
            'Chathura', 'Damith', 'Dhanushka', 'Dilan', 'Dilshan', 'Duminda', 'Eranga', 'Gayan',
            'Gihan', 'Harsha', 'Hasitha', 'Heshan', 'Indika', 'Ishan', 'Isuru', 'Jagath',
            'Janaka', 'Janith', 'Kamal', 'Kasun', 'Kavindu', 'Kusal', 'Lahiru', 'Lakmal',
            'Lasith', 'Madhawa', 'Mahesh', 'Nalaka', 'Naveen', 'Nimal', 'Nishan', 'Nuwan',
            'Oshada', 'Pasindu', 'Pradeep', 'Prasanna', 'Rajitha', 'Roshan', 'Ruwan', 'Sajith',
            'Saman', 'Sandun', 'Sunil', 'Tharindu', 'Thilina', 'Thusitha', 'Udara', 'Upul',
            'Vimukthi', 'Waruna', 'Yasith', 'Yohan',
        ];
    }

    /**
     * @return list<string>
     */
    public static function femaleFirstNames(): array
    {
        return [
            'Achini', 'Amaya', 'Anjali', 'Bhagya', 'Bimali', 'Chami', 'Chamari', 'Chathurika',
            'Darshani', 'Dilhani', 'Dilini', 'Dinusha', 'Eshani', 'Ganga', 'Gayani', 'Hansika',
            'Hashini', 'Hiruni', 'Imesha', 'Ishara', 'Jayani', 'Kalpani', 'Kaushalya', 'Kavindi',
            'Lakshika', 'Madhavi', 'Malsha', 'Menaka', 'Nadeesha', 'Nethmi', 'Nilmini', 'Nirosha',
            'Oshadi', 'Pavithra', 'Piyumi', 'Rashmi', 'Ridma', 'Ruwani', 'Samanthi', 'Sanduni',
            'Sewwandi', 'Shalika', 'Tharushi', 'Thilini', 'Upeksha', 'Uthpala', 'Vindya', 'Wasana',
            'Yamuna', 'Yashodha',
        ];
    }

    /**
     * @return list<string>
     */
    public static function surnames(): array
    {
        return [
            'Abeysekara', 'Alwis', 'Amarasinghe', 'Athukorala', 'Bandara', 'Basnayake', 'Cooray', 'Dharmasena',
            'Dias', 'Dissanayake', 'Ekanayake', 'Fernando', 'Fonseka', 'Gamage', 'Goonewardena', 'Gunasekara',
            'Herath', 'Hewage', 'Jayasinghe', 'Jayawardena', 'Karunaratne', 'Kodikara', 'Liyanage', 'Mendis',
            'Nanayakkara', 'Pathirana', 'Peiris', 'Perera', 'Rajapaksa', 'Ranasinghe', 'Rathnayake', 'Samarakoon',
            'Senanayake', 'Seneviratne', 'Silva', 'Vithanage', 'Weerasinghe', 'Wickramasinghe', 'Wijeratne', 'Wijesinghe',
        ];
    }
}
