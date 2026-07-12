<?php
// ./api/v1/dashboard.php
// header("Cache-Control: no-cache, no-store, must-revalidate");
// header("Pragma: no-cache");
// header("Expires: 0");
include_once __DIR__ . '/../../../config/bootstrap.php';
header('Content-Type: application/json');


/* =========================
   AUTH
========================= */

if (!isset($_COOKIE['c_user']) || empty($_COOKIE['c_user'])) {

    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Unauthorized"
    ]);

    exit;
}

/* =========================
   ORM MODELS
========================= */

$db = new DB($conn);

$teacherCRUD    = new ORM($db, "tblEmployees t", "employee_id");
$studentCRUD    = new ORM($db, "tblStudents s", "student_id");
$enrollmentCRUD = new ORM($db, "tblEnrollments e", "enrollment_id");
$paymentCRUD    = new ORM($db, "tblPayments p", "payment_id");
$attendanceCRUD = new ORM($db, "tblAttendances a", "attendance_id");
$invoiceCRUD    = new ORM($db, "tblInvoices inv", "invoice_id");
$classCRUD      = new ORM($db, "tblClasses c", "class_id");

/* =========================
   INPUTS
========================= */

$filterType    = $_GET['filterType'] ?? 'today';
$selectedYear  = $_GET['selectYear'] ?? null;
$selectedClass = $_GET['selectClass'] ?? 'allClasses';
$teacher       = $_GET['teacher'] ?? 'allTeachers';

/* =========================
   HELPERS
========================= */

function safeDate($date)
{
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)
        ? $date
        : date('Y-m-d');
}

function resolveDateRange($filterType, $selectedYear)
{
    $today = date('Y-m-d');

    if ($selectedYear && $selectedYear !== 'allYear') {

        return [
            $selectedYear . '-01-01',
            $selectedYear . '-12-31'
        ];
    }

    switch ($filterType) {

        case 'today':
            return [$today, $today];

        case 'yesterday':

            $yesterday = date(
                'Y-m-d',
                strtotime('-1 day')
            );

            return [$yesterday, $yesterday];

        case 'last7days':

            return [
                date('Y-m-d', strtotime('-6 days')),
                $today
            ];

        case 'thisMonth':

            return [
                date('Y-m-01'),
                $today
            ];

        case 'lastMonth':

            return [
                date(
                    'Y-m-01',
                    strtotime('first day of last month')
                ),

                date(
                    'Y-m-t',
                    strtotime('last day of last month')
                )
            ];

        case 'thisYear':

            return [
                date('Y-01-01'),
                $today
            ];

        case 'lastYear':

            return [
                date(
                    'Y-01-01',
                    strtotime('-1 year')
                ),

                date(
                    'Y-12-31',
                    strtotime('-1 year')
                )
            ];

        case 'all':

            return [
                '2000-01-01',
                $today
            ];

        case 'custom':
        default:

            $start = safeDate(
                $_GET['startDate'] ?? $today
            );

            $end = safeDate(
                $_GET['endDate'] ?? $today
            );

            if ($start > $end) {
                [$start, $end] = [$end, $start];
            }

            return [$start, $end];
    }
}

function fillMissingDates(
    $start,
    $end,
    $rows
) {

    $map = [];

    foreach ($rows as $row) {
        $map[$row['d']] = (int)$row['total'];
    }

    $labels = [];
    $data   = [];

    $current = strtotime($start);
    $endTime = strtotime($end);

    while ($current <= $endTime) {

        $key = date('Y-m-d', $current);

        $labels[] = date('M d', $current);

        $data[] = $map[$key] ?? 0;

        $current = strtotime('+1 day', $current);
    }

    return [$labels, $data];
}

function fillAttendanceDates(
    $start,
    $end,
    $rows
) {

    $map = [];

    foreach ($rows as $row) {

        $map[$row['d']] = [

            'present' => (int)$row['present'],
            'absent'  => (int)$row['absent'],
            'late'    => (int)$row['late']
        ];
    }

    $labels  = [];
    $present = [];
    $absent  = [];
    $late    = [];

    $current = strtotime($start);
    $endTime = strtotime($end);

    while ($current <= $endTime) {

        $key = date('Y-m-d', $current);

        $labels[] = date('M d', $current);

        $present[] = $map[$key]['present'] ?? 0;
        $absent[]  = $map[$key]['absent'] ?? 0;
        $late[]    = $map[$key]['late'] ?? 0;

        $current = strtotime('+1 day', $current);
    }

    return [
        $labels,
        $present,
        $absent,
        $late
    ];
}

/* =========================
   DATE RANGE
========================= */

[$startDate, $endDate] = resolveDateRange(
    $filterType,
    $selectedYear
);

/* =========================
   TOTAL STUDENTS
========================= */

$totalStudent = $studentCRUD
    ->select("COUNT(*) as total")
    ->first();

$totalStudentCard =
    (int)($totalStudent['total'] ?? 0);

/* =========================
   TOTAL CLASSES
========================= */

$totalClass = $classCRUD
    ->select("COUNT(*) as total")
    ->first();

$totalClassCard =
    (int)($totalClass['total'] ?? 0);

/* =========================
   TOTAL TEACHERS
========================= */

$totalTeacher = $teacherCRUD
    ->select("COUNT(*) as total")
    ->join(
        "tblDepartments d",
        "t.department_id = d.department_id"
    )
    ->where(
        "d.department_name",
        "=",
        "Teacher"
    )
    ->first();

$totalTeacherCard =
    (int)($totalTeacher['total'] ?? 0);

/* =========================
   TOTAL TRAINERS
========================= */

$totalTrainer = $teacherCRUD
    ->select("COUNT(*) as total")
    ->join(
        "tblDepartments d",
        "t.department_id = d.department_id"
    )
    ->where(
        "d.department_name",
        "=",
        "Trainer"
    )
    ->first();

$totalTrainerCard =
    (int)($totalTrainer['total'] ?? 0);

/* =========================
   TOTAL ASSISTANTS
========================= */

$totalAssistant = $teacherCRUD
    ->select("COUNT(*) as total")
    ->join(
        "tblDepartments d",
        "t.department_id = d.department_id"
    )
    ->where(
        "d.department_name",
        "=",
        "Assistant"
    )
    ->first();

$totalAssistantCard =
    (int)($totalAssistant['total'] ?? 0);

/* =========================
   TOTAL REVENUE
========================= */

$totalRevenueCard = $paymentCRUD
->select('sum(amount) as total')
->where('status','=','Completed')
->first();

$totalRevenueCard = (int)($totalRevenueCard['total'] ?? 0);

$revenue = $paymentCRUD
    ->select("
        IFNULL(
            SUM(p.amount),
            0
        ) as revenue
    ")
    ->where(
        "p.payment_date",
        ">=",
        $startDate
    )
    ->where(
        "p.payment_date",
        "<",
        date(
            'Y-m-d',
            strtotime($endDate . ' +1 day')
        )
    );

if ($selectedClass !== 'allClasses') {

    $revenue->join(
        "tblInvoices inv",
        "p.invoice_id = inv.invoice_id"
    );

    $revenue->join(
        "tblEnrollments e",
        "inv.student_id = e.student_id"
    );

    $revenue->where(
        "e.class_id",
        "=",
        (int)$selectedClass
    );
}

$revenue = $revenue->first();

$revenue =
    round((float)($revenue['revenue'] ?? 0), 2);

/* =========================
   ATTENDANCE OVERVIEW
========================= */

$attendanceOverview = $attendanceCRUD
    ->select("
        SUM(
            CASE
                WHEN a.status='Present'
                THEN 1
                ELSE 0
            END
        ) as present,

        SUM(
            CASE
                WHEN a.status='Absent'
                THEN 1
                ELSE 0
            END
        ) as absent,

        SUM(
            CASE
                WHEN a.status='Late'
                THEN 1
                ELSE 0
            END
        ) as late
    ")
    ->join(
        "tblEnrollments e",
        "a.enrollment_id = e.enrollment_id"
    )
    ->where(
        "a.attendance_date",
        ">=",
        $startDate
    )
    ->where(
        "a.attendance_date",
        "<",
        date(
            'Y-m-d',
            strtotime($endDate . ' +1 day')
        )
    );

if ($selectedClass !== 'allClasses') {

    $attendanceOverview->where(
        "e.class_id",
        "=",
        (int)$selectedClass
    );
}

if ($teacher !== 'allTeachers') {

    $attendanceOverview->join(
        "tblClasses c",
        "e.class_id = c.class_id"
    );

    $attendanceOverview->where(
        "c.teacher_id",
        "=",
        (int)$teacher
    );
}

$attendanceOverview =
    $attendanceOverview->first();

/* =========================
   ENROLLMENT OVERVIEW
========================= */

$enrollmentOverview = $invoiceCRUD
    ->select("
        SUM(
            CASE
                WHEN inv.status='Paid'
                THEN 1
                ELSE 0
            END
        ) as paid,

        SUM(
            CASE
                WHEN inv.status='Unpaid'
                THEN 1
                ELSE 0
            END
        ) as unpaid
    ")
    ->join(
        "tblEnrollments e",
        "inv.student_id = e.student_id"
    )
    ->where(
        "inv.created_at",
        ">=",
        $startDate
    )
    ->where(
        "inv.created_at",
        "<",
        date(
            'Y-m-d',
            strtotime($endDate . ' +1 day')
        )
    );

if ($selectedClass !== 'allClasses') {

    $enrollmentOverview->where(
        "e.class_id",
        "=",
        (int)$selectedClass
    );
}

if ($teacher !== 'allTeachers') {

    $enrollmentOverview->join(
        "tblClasses c",
        "e.class_id = c.class_id"
    );

    $enrollmentOverview->where(
        "c.teacher_id",
        "=",
        (int)$teacher
    );
}

$enrollmentOverview =
    $enrollmentOverview->first();

/* =========================
   ENROLLMENT CHART
========================= */

$enrollChart = $enrollmentCRUD
    ->select("
        DATE(e.created_at) as d,
        COUNT(*) as total
    ")
    ->where(
        "e.created_at",
        ">=",
        $startDate
    )
    ->where(
        "e.created_at",
        "<",
        date(
            'Y-m-d',
            strtotime($endDate . ' +1 day')
        )
    )
    ->groupBy("d")
    ->orderBy("d", "ASC");

if ($selectedClass !== 'allClasses') {

    $enrollChart->where(
        "e.class_id",
        "=",
        (int)$selectedClass
    );
}

if ($teacher !== 'allTeachers') {

    $enrollChart->join(
        "tblClasses c",
        "e.class_id = c.class_id"
    );

    $enrollChart->where(
        "c.teacher_id",
        "=",
        (int)$teacher
    );
}

$enrollRows = $enrollChart->get();

list(
    $enrollLabels,
    $enrollData
) = fillMissingDates(
    $startDate,
    $endDate,
    $enrollRows
);

/* =========================
   ATTENDANCE CHART
========================= */

$attendanceChart = $attendanceCRUD
    ->select("
        DATE(a.attendance_date) as d,

        SUM(
            CASE
                WHEN a.status='Present'
                THEN 1
                ELSE 0
            END
        ) as present,

        SUM(
            CASE
                WHEN a.status='Absent'
                THEN 1
                ELSE 0
            END
        ) as absent,

        SUM(
            CASE
                WHEN a.status='Late'
                THEN 1
                ELSE 0
            END
        ) as late
    ")
    ->join(
        "tblEnrollments e",
        "a.enrollment_id = e.enrollment_id"
    )
    ->where(
        "a.attendance_date",
        ">=",
        $startDate
    )
    ->where(
        "a.attendance_date",
        "<",
        date(
            'Y-m-d',
            strtotime($endDate . ' +1 day')
        )
    )
    ->groupBy("d")
    ->orderBy("d", "ASC");

if ($selectedClass !== 'allClasses') {

    $attendanceChart->where(
        "e.class_id",
        "=",
        (int)$selectedClass
    );
}

if ($teacher !== 'allTeachers') {

    $attendanceChart->join(
        "tblClasses c",
        "e.class_id = c.class_id"
    );

    $attendanceChart->where(
        "c.teacher_id",
        "=",
        (int)$teacher
    );
}

$attendanceRows =
    $attendanceChart->get();

list(
    $attendanceLabels,
    $presentData,
    $absentData,
    $lateData
) = fillAttendanceDates(
    $startDate,
    $endDate,
    $attendanceRows
);

/* =========================
   RESPONSE
========================= */

echo json_encode([

    "success" => true,

    "filters" => [

        "startDate" => $startDate,
        "endDate"   => $endDate,
        "teacher"   => $teacher,
        "class"     => $selectedClass
    ],

    "studentCard"   => $totalStudentCard,
    "classCard"     => $totalClassCard,
    "teacherCard"   => $totalTeacherCard,
    "trainerCard"   => $totalTrainerCard,
    "assistantCard" => $totalAssistantCard,
    "revenueCard" => $totalRevenueCard,



    "attendanceOverview" => [

        "present" =>
            (int)($attendanceOverview['present'] ?? 0),

        "absent" =>
            (int)($attendanceOverview['absent'] ?? 0),

        "late" =>
            (int)($attendanceOverview['late'] ?? 0),
    ],

    "enrollmentOverview" => [

        "paid" =>
            (int)($enrollmentOverview['paid'] ?? 0),

        "unpaid" =>
            (int)($enrollmentOverview['unpaid'] ?? 0)
    ],

    "enrollChart" => [

        "labels" => $enrollLabels,
        "data"   => $enrollData
    ],

    "attendanceChart" => [

        "labels"  => $attendanceLabels,
        "present" => $presentData,
        "absent"  => $absentData,
        "late"    => $lateData
    ]

], JSON_PRETTY_PRINT);
?>
