<?php
include_once __DIR__ . '/../config/bootstrap.php';
include_once __DIR__ . '/../app/api/v1/auth.php';
include_once __DIR__ . '/../data/dataSchema.php';
include_once __DIR__ . '/../components/Navbar.php';
include_once __DIR__ . '/../components/Avatar.php';


if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


$userId = checkAuth();

if (!$userId) {
    header("Location: " . BASE_URL . "/auth/signin.php");
    exit;
}

authorizeRole('admin');
//     var_dump($_SESSION);
// var_dump($_COOKIE);
// exit;

$routeAdmin[0]["active"] = true;


$db = new DB($conn);
$teacherCRUD = new ORM($db, 'tblEmployees', 'employee_id');

// Fetch DISTINCT years (clean)
$stmt = $conn->query("
    SELECT DISTINCT SPLIT_PART(academic_year, '-', 1) AS year
    FROM tblClasses
    ORDER BY year ASC
");

$years = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $years[] = $row['year'];
}

// Get filters
$selectTeacher = isset($_GET['teacher']) && $_GET['teacher'] !== ''
    ? $_GET['teacher']
    : 'allTeachers';

$selectedYear = isset($_GET['year']) && $_GET['year'] !== ''
    ? $_GET['year']
    : 'allYear';

$selectedClass = isset($_GET['selectClass']) && $_GET['selectClass'] !== ''
    ? $_GET['selectClass']
    : 'allClasses';

$statusFilters = is_array($_GET['status'] ?? null)
    ? $_GET['status']
    : explode(',', $_GET['status'] ?? 'all');


$teacherSelected = $teacherCRUD
    ->select("employee_id, first_name_kh, last_name_kh")
    ->join("tblDepartments", "tblEmployees.department_id = tblDepartments.department_id")
    ->where("department_name", '=', "Teacher")
    ->get();




$startDate = $_GET['startDate'] ?? date('Y-m-d');
$endDate   = $_GET['endDate'] ?? date('Y-m-d');

$year = $startDate;
$firstYear = explode('-', $year)[0];


// Format for display
$datePicker = date('M d, Y', strtotime($startDate)) . ' — ' . date('M d, Y', strtotime($endDate));


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | <?php echo $infoSchemaData[1]["name_short"] ?></title>
    <link rel="icon" type="image/png" href="<?php echo $infoSchemaData[5]["image"] ?>">
    <link rel="icon" type="image/png" href="<?php echo $infoSchemaData[5]["image"] ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.13.1/font/bootstrap-icons.min.css"
        integrity="sha512-t7Few9xlddEmgd3oKZQahkNI4dS6l80+eGEzFQiqtyVYdvcSG2D3Iub77R20BdotfRPA9caaRkg1tyaJiPmO0g=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <link rel="stylesheet" href="../src/style.css">
    <script src="../src/assets/js/user-profile.js" defer></script>


    <style>
        .page-title {
            font-weight: 700;
        }

        /* .title {
                font-weight: 600;
            } */

        .card-report {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, .05);
            transition: .3s;
        }

        .card-report:hover {
            transform: translateY(-5px);
        }

        .card-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }

        .students {
            background: #3B82F6;
        }

        .enrollments {
            background: #8B5CF6;
        }

        .revenue {
            background: #10B981;
        }

        .attendance {
            background: #F59E0B;
        }

        .quick-report {
            cursor: pointer;
            transition: .3s;
        }

        .quick-report:hover {
            background: #f8f9fa;
        }

        .chart-container {
            height: 350px;
        }

        .card-report {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, .05);
            transition: .3s;
        }

        .card-report:hover {
            transform: translateY(-5px);
        }

        .card-icon {
            width: 55px;
            height: 55px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: white;
        }

        /* FIX colors (you were using bg-green etc but not defined properly) */
        .bg-blue {
            background: #3B82F6;
        }

        .bg-green {
            background: #10B981;
        }

        .bg-red {
            background: #EF4444;
        }

        .bg-purple {
            background: #8B5CF6;
        }

        .page-title {
            font-weight: 700;
        }
    </style>
    <?php VercelAnalytics(); ?>

</head>

<body class="container-fluid p-0 overflow-x-hidden">
    <div class="row g-3">

        <?php Navbar($infoSchemaData, $routeAdmin); ?>

        <!-- Main area -->
        <main class="col-10 bg-light">
            <div
                class="d-flex justify-content-between align-items-center px-2 py-2 bg-white position-sticky top-0 z-3">
                <div class="title">Welcome to <?php echo $infoSchemaData[0]["name"] ?></div>

                <?php Avatar($_SESSION['role']); ?>

            </div>

            <div class="container-lg container-md container-sm p-3 ">
                <div class="w-100 d-flex justify-content-between flex-wrap h-25">
                    <div class="d-flex gap-2 w-100">
                        <form method="GET" id="filterForm" class="d-flex justify-content-around w-100" autocomplete="off">
                            <div class="d-flex w-100">

                                <div class="me-2">
                                    <h3 class="text-break page-title">Analytics</h3>
                                </div>
                                <div class="d-flex w-75">

                                    <select name="teacher" id="selectTeacher" class="form-select me-2 w-50">
                                        <option value="allTeachers">All Teachers</option>
                                        <?php foreach ($teacherSelected as $t): ?>
                                            <option value="<?= $t['employee_id'] ?>"
                                                <?= $selectTeacher == $t['employee_id'] ? 'selected' : '' ?>>
                                                <?= $t['first_name_kh'] ?> <?= $t['last_name_kh'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                    <select name="selectClass"
                                        id="selectClass"
                                        class="form-select me-2">
                                        <option value="allClasses">All Classes</option>

                                    </select>
                                </div>
                            </div>
                            <div class="d-flex">

                                <!-- Date Picker -->
                                <div class="d-flex me-3">

                                    <!-- Display -->
                                    <input type="text"
                                        id="dateDisplay"
                                        class="form-control"
                                        value="<?= htmlspecialchars($datePicker) ?>"
                                        readonly
                                        style="width: 260px;">

                                    <!-- Hidden Start -->
                                    <input type="hidden" name="startDate" id="startDate"
                                        value="<?= $startDate ?>">

                                    <!-- Hidden End -->
                                    <input type="hidden" name="endDate" id="endDate"
                                        value="<?= $endDate ?>">

                                </div>

                                <!-- Filter Dropdown -->
                                <div class="dropdown">
                                    <button style="width: 117px;"
                                        type="button"
                                        class="btn btn-primary d-flex align-items-center justify-content-center"
                                        data-bs-toggle="dropdown">

                                        <i class="fa fa-filter me-2"></i> Filter
                                    </button>

                                    <div class="dropdown-menu p-3 bg-white">

                                        <div class="form-check">
                                            <input id="filterAll"
                                                class="form-check-input status-filter"
                                                type="checkbox"
                                                value="all"
                                                <?= in_array('all', $statusFilters) ? 'checked' : '' ?>>
                                            <label class="form-check-label">All</label>
                                        </div>

                                        <div class="form-check">
                                            <input id="filterAttendences"
                                                class="form-check-input status-filter"
                                                type="checkbox"
                                                value="attendences"
                                                <?= in_array('all', $statusFilters) || empty($_GET['status']) ? 'checked' : '' ?>>

                                            <label class="form-check-label">
                                                Attendences
                                            </label>
                                        </div>

                                        <div class="form-check">
                                            <input id="filterPayments"
                                                class="form-check-input status-filter"
                                                type="checkbox"
                                                value="payment"
                                                <?= in_array('all', $statusFilters) || empty($_GET['status']) ? 'checked' : '' ?>>

                                            <label class="form-check-label">
                                                Payment
                                            </label>
                                        </div>

                                        <div class="form-check">
                                            <input id="filterEnrollments"
                                                class="form-check-input status-filter"
                                                type="checkbox"
                                                value="enrollments"
                                                <?= in_array('all', $statusFilters) || empty($_GET['status']) ? 'checked' : '' ?>>

                                            <label class="form-check-label">
                                                Enrollments
                                            </label>
                                        </div>
                                    </div>

                                </div>
                                <div>
                                    <button style="width: 135px;" type="button" class="btn btn-success ms-3 h-auto">
                                        Export Data
                                    </button>
                                </div>
                            </div>

                        </form>


                    </div>

                </div>


            </div>

            <div class="w-100 d-flex mt-3 ms-3 justify-content-between gap-3 flex-wrap dataLoading">
                <div class="w-100 rounded">
                    <div class="row g-4">
                        <div class="row g-4">
                            <?php

                           // Students all
$getStudentCard = "SELECT COUNT(*) AS total FROM tblStudents";
$studentCard = $conn->query($getStudentCard)->fetch(PDO::FETCH_ASSOC)['total'];

// Teachers all
$getTeacherCard = "
    SELECT COUNT(*) AS total
    FROM tblEmployees e
    JOIN tblDepartments d
        ON e.department_id = d.department_id
    WHERE d.department_name = 'Teacher'
";
$teacherCard = $conn->query($getTeacherCard)->fetch(PDO::FETCH_ASSOC)['total'];

// Classes all
$getClassCard = "SELECT COUNT(*) AS total FROM tblClasses";
$classCard = $conn->query($getClassCard)->fetch(PDO::FETCH_ASSOC)['total'];

// Revenue all
$getRevenueCard = "SELECT COALESCE(SUM(amount), 0) AS total FROM tblPayments";
$revenueCard = $conn->query($getRevenueCard)->fetch(PDO::FETCH_ASSOC)['total'];

                            $cards = [
                                ["Total Revenue", "totalRevenueCard", "fa-dollar-sign", "bg-green", "$" . number_format($revenueCard, 2)],
                                ["Total Students", "totalStudentCard", "fa-graduation-cap", "bg-blue", $studentCard],
                                ["Total Teachers", "totalTeacherCard", "fa-users", "bg-green", $teacherCard],
                                ["Total Classes", "classCard", "fa-school", "bg-red", $classCard], // hardcoded example
                            ];
                            ?>



                            <?php foreach ($cards as $c): ?>
                                <div class="col-md-3">
                                    <div class="card card-report">
                                        <div class="card-body d-flex justify-content-between align-items-center">

                                            <div>
                                                <h6 class="mb-1"><?= htmlspecialchars($c[0]) ?></h6>
                                                <h3 class="mb-0" id="<?= htmlspecialchars($c[1]) ?>">
                                                    <?= htmlspecialchars($c[4]) ?>
                                                </h3>
                                            </div>

                                            <div class="card-icon <?= htmlspecialchars($c[3]) ?>">
                                                <i class="fa <?= htmlspecialchars($c[2]) ?>"></i>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="row g-4 mt-2">


                        <div id="attendanceSection" class="row g-4 mt-2">

                            <!-- Attendance -->
                            <div class="col-md-8">
                                <div class="card card-box">
                                    <div class="card-body">
                                        <h5>Attendance Statistics</h5>
                                        <canvas id="attenChart"></canvas>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card card-box">
                                    <div class="card-body">
                                        <h5>Attendance Overview</h5>
                                        <canvas id="attenOverviewChart"></canvas>
                                    </div>
                                </div>
                            </div>

                        </div>


                        <div id="enrollmentSection" class="row g-4 mt-2">

                            <!-- Enrollment -->
                            <div class="col-md-8">
                                <div class="card card-box">
                                    <div class="card-body">
                                        <h5>Enrollment Statistics</h5>
                                        <canvas id="enrollChart"></canvas>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card card-box">
                                    <div class="card-body">
                                        <h5>Enrollment Overview</h5>
                                        <canvas id="enrollOverviewChart"></canvas>
                                    </div>
                                </div>
                            </div>

                        </div>


                        <div id="paymentSection" class="row g-4 mt-2">

                            <!-- Payment -->
                            <div class="col-md-8">
                                <div class="card card-box">
                                    <div class="card-body">
                                        <h5>Payment Statistics</h5>
                                        <canvas id="paymentChart"></canvas>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card card-box">
                                    <div class="card-body">
                                        <h5>Payment Overview</h5>
                                        <canvas id="paymentOverviewChart"></canvas>
                                    </div>
                                </div>
                            </div>

                        </div>


                        <!-- Recent Activity -->

                        <div class="card card-box">
                            <div class="card-body">

                                <h5>Recent Activity</h5>

                                <ul class="list-group">

                                    <li class="list-group-item">
                                        New student enrolled — Grade 3B
                                    </li>

                                    <li class="list-group-item">
                                        Scheduled examination next Monday
                                    </li>

                                    <li class="list-group-item">
                                        Attendance marked for Grade 6A
                                    </li>

                                    <li class="list-group-item">
                                        Tuition fee payment completed
                                    </li>

                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
        </main>
    </div>
    <script src="../src/assets/js/script.js"></script>
    <script src="../src/assets/js/navbar-toggle-action.js"></script>
    <script>
        let startDate = "<?= $startDate ?>";
        let endDate = "<?= $endDate ?>";
        let selectYear = "<?= $selectedYear ?>";
        let selectClass = "<?= $selectedClass ?>";
        let selectTeacher = "<?= $selectTeacher  ?>";
        const BASE_URL = "<?= BASE_URL ?>";
        

        // ----------------------
        // Flatpickr Setup
        // ----------------------
        const fp = flatpickr("#dateDisplay", {
            mode: "range",
            maxDate: "today",
            defaultDate: [startDate, endDate],
            onReady: function(selectedDates) {
                if (selectedDates.length === 2) formatDisplay(selectedDates);
            },
            onChange: function(selectedDates) {
                if (selectedDates.length === 2) {
                    startDate = formatDateLocal(selectedDates[0]);
                    endDate = formatDateLocal(selectedDates[1]);
                    formatDisplay(selectedDates);

                    // Clear year selection when using date range
                    // document.getElementById('selectYear').value = null;
                    // selectYear = null;

                    loadDashboard();
                }
            }
        });

        function formatDateLocal(date) {
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const d = String(date.getDate()).padStart(2, '0');
            return `${y}-${m}-${d}`;
        }

        function formatDisplay(dates) {
            const options = {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            };
            document.getElementById("dateDisplay").value =
                `${dates[0].toLocaleDateString('en-US', options)} — ${dates[1].toLocaleDateString('en-US', options)}`;
        }

        // ----------------------
        // Detect Filter Type
        // ----------------------
        function detectFilterType(start, end) {
            if (!start || !end) return 'custom';

            const s = new Date(start),
                e = new Date(end),
                today = new Date();
            [s, e, today].forEach(d => d.setHours(0, 0, 0, 0));

            const format = d => d.toISOString().split('T')[0];

            const todayStr = format(today);
            const yesterday = new Date(today);
            yesterday.setDate(today.getDate() - 1);
            const last7Start = new Date(today);
            last7Start.setDate(today.getDate() - 6);

            const firstDayMonth = new Date(today.getFullYear(), today.getMonth(), 1);
            const firstDayLastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            const lastDayLastMonth = new Date(today.getFullYear(), today.getMonth(), 0);

            const firstDayYear = new Date(today.getFullYear(), 0, 1);
            const firstDayLastYear = new Date(today.getFullYear() - 1, 0, 1);
            const lastDayLastYear = new Date(today.getFullYear() - 1, 11, 31);

            const sStr = format(s),
                eStr = format(e);

            if (sStr === todayStr && eStr === todayStr) return 'today';
            if (sStr === format(yesterday) && eStr === format(yesterday)) return 'yesterday';
            if (sStr === format(last7Start) && eStr === todayStr) return 'last7days';
            if (sStr === format(firstDayMonth) && eStr === todayStr) return 'thisMonth';
            if (sStr === format(firstDayLastMonth) && eStr === format(lastDayLastMonth)) return 'lastMonth';
            if (sStr === format(firstDayYear) && eStr === todayStr) return 'thisYear';
            if (sStr === format(firstDayLastYear) && eStr === format(lastDayLastYear)) return 'lastYear';

            return 'custom';
        }

        // ----------------------
        // Charts Setup
        // ----------------------
        let enrollChart, attendanceChart, attenOverviewChart, enrollOverviewChart;

        function initCharts() {
            enrollChart = new Chart(document.getElementById('enrollChart'), {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Enrollments',
                        data: []
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top'
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        intersect: false
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
            attendanceChart = new Chart(document.getElementById('attenChart'), {
                type: 'bar', // changed from 'bar' to 'line'
                data: {
                    labels: [], // x-axis labels (dates)
                    datasets: [{
                            label: 'Present',
                            data: [],
                            borderColor: '#28a745',
                            backgroundColor: 'rgba(40, 167, 70, 0.9)',
                            tension: 0.4
                        },
                        {
                            label: 'Absent',
                            data: [],
                            borderColor: '#e74c3c',
                            backgroundColor: 'rgba(231,76,60,0.9)',
                            tension: 0.4
                        },
                        {
                            label: 'Late',
                            data: [],
                            borderColor: '#f1c40f',
                            backgroundColor: 'rgba(241,196,15,0.9)',
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top'
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        intersect: false
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });




            attenOverviewChart = new Chart(document.getElementById('attenOverviewChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Present', 'Absent', 'Late'],
                    datasets: [{
                        data: [],
                        backgroundColor: ['#28a745', '#e74c3c', '#f1c40f'],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    cutout: '65%', // 👈 makes space for center text
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        datalabels: {
                            color: '#fff',
                            font: {
                                weight: 'bold',
                                size: 12
                            },
                            formatter: (value, ctx) => {
                                const data = ctx.chart.data.datasets[0].data;
                                const total = data.reduce((a, b) => a + b, 0);

                                if (!total) return '0%';

                                return ((value / total) * 100).toFixed(1) + '%';
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const data = context.dataset.data;
                                    const total = data.reduce((a, b) => a + b, 0);
                                    const value = context.raw;

                                    const percent = total ?
                                        ((value / total) * 100).toFixed(1) :
                                        0;

                                    return `${context.label}: ${value} (${percent}%)`;
                                }
                            }
                        }
                    }
                },
                plugins: [ChartDataLabels] // keep this
            });

            enrollOverviewChart = new Chart(document.getElementById('enrollOverviewChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Paid', 'Unpaid'],
                    datasets: [{
                        data: [],
                        backgroundColor: ['#28a745', '#e74c3c']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        datalabels: {
                            color: '#fff',
                            formatter: (v, ctx) => {
                                const d = ctx.chart.data.datasets[0].data;
                                const total = d.reduce((a, b) => a + b, 0);
                                return total ? ((v / total) * 100).toFixed(1) + '%' : '0%';
                            }
                        }
                    }
                },
                plugins: [ChartDataLabels]
            });
        }

        async function loadClasses(teacherId = 'allTeachers') {

            try {
                const response = await fetch(
                    `${BASE_URL}/api/v1/classes_by_teacher.php?teacher_id=${teacherId}`
                );

                const data = await response.json();

                const selectClass =
                    document.getElementById('selectClass');

                let html = `
            <option value="">Select Class</option>
            <option value="allClasses">All Classes</option>
        `;

                data.data.forEach(cls => {

                    html += `
                <option value="${cls.class_id}">
                    ${cls.class_name}
                </option>
            `;
                });

                selectClass.innerHTML = html;

            } catch (err) {

                console.error(err);

            }
        }

        function updateUI(data) {
            document.getElementById('totalStudentCard').innerText = data.studentCard ?? 0;
            document.getElementById('totalTeacherCard').innerText = data.teacherCard ?? 0;
            document.getElementById('totalRevenueCard').innerText = '$' + Number(data.revenueCard ?? 0).toFixed(2);

            // Enrollment chart
            enrollChart.data.labels = data.enrollChart?.labels ?? [];
            enrollChart.data.datasets[0].data = data.enrollChart?.data ?? [];

            const values = data.enrollChart?.data ?? [];

            const maxValue1 = values.length > 0 ? Math.max(...values) : 0;

            enrollChart.options.scales.y.max = maxValue1 > 0 ? maxValue1 + 1 : 5;

            enrollChart.update();

            attendanceChart.data.labels = data.attendanceChart?.labels ?? [];
            attendanceChart.data.datasets[0].data = data.attendanceChart?.present ?? [];
            attendanceChart.data.datasets[1].data = data.attendanceChart?.absent ?? [];
            attendanceChart.data.datasets[2].data = data.attendanceChart?.late ?? [];

            const maxValue = Math.max(
                ...(data.attendanceChart?.present ?? [0]),
                ...(data.attendanceChart?.absent ?? [0]),
                ...(data.attendanceChart?.late ?? [0])
            );

            attendanceChart.options.scales.y.max = maxValue > 0 ? maxValue + 1 : 5;

            // ✅ DO NOT TOUCH X AXIS
            // attendanceChart.options.scales.x.max = ...

            attendanceChart.update();


            attenOverviewChart.data.datasets[0].data = [
                data.attendanceOverview?.present ?? 0,
                data.attendanceOverview?.absent ?? 0,
                data.attendanceOverview?.late ?? 0
            ];
            attenOverviewChart.update();

            enrollOverviewChart.data.datasets[0].data = [
                data.enrollmentOverview?.paid ?? 0,
                data.enrollmentOverview?.unpaid ?? 0
            ];
            enrollOverviewChart.update();

        }

        // ----------------------
        // Load Dashboard
        // ----------------------
        async function loadDashboard() {
            // const loader = document.querySelector(".dataLoading");
            // loader.classList.add('loading');

            try {
                // selectYear = document.getElementById('selectYear').value || 'allYear';
                selectTeacher = document.getElementById('selectTeacher').value || 'allTeachers';
                selectClass = document.getElementById('selectClass').value || '';

                const filterAll = document.getElementById('filterAll');
                const otherCheckboxes = document.querySelectorAll('.status-filter:not(#filterAll)');

                const anyChecked = Array.from(otherCheckboxes).some(cb => cb.checked);
                if (!anyChecked) {
                    filterAll.checked = true;
                    otherCheckboxes.forEach(cb => cb.checked = true);
                }

                let statuses = Array.from(document.querySelectorAll('.status-filter:checked')).map(cb => cb.value);
                if (statuses.includes('all')) statuses = ['all'];

                const params = new URLSearchParams();
                if (selectYear !== 'allYear') {
                    params.set('selectYear', selectYear);
                } else {
                    const filterType = detectFilterType(startDate, endDate);
                    params.set('filterType', filterType);
                    if (filterType === 'custom') {
                        params.set('startDate', startDate);
                        params.set('endDate', endDate);
                    }
                }

                if (selectTeacher && selectTeacher !== 'allTeachers') {
                    params.set('teacher', selectTeacher);
                }

                if (selectClass && selectClass !== 'allClasses') {
                    params.set('selectClass', selectClass);
                }


                params.set('status', statuses.join(','));

                const url = `${BASE_URL}/api/v1/dashboard.php?${params.toString()}`;
                console.log("Final URL:", url);

                const res = await fetch(url);

                // const res = await fetch(
                //     `${url}&t=${Date.now()}`, {
                //         cache: "no-store"
                //     }
                // );

                if (!res.ok) throw new Error("API failed");
                const data = await res.json();
                updateUI(data);

                console.log(data);

                window.history.replaceState({}, '', window.location.pathname + '?' + params.toString());

            } catch (err) {
                console.error("Dashboard Load Error:", err);
            } finally {
                // loader.classList.remove('loading');
            }
        }

        function toggleCharts() {

            const attendanceChecked =
                document.getElementById('filterAttendences').checked;

            const enrollmentChecked =
                document.getElementById('filterEnrollments').checked;

            const paymentChecked =
                document.getElementById('filterPayments').checked;

            document.getElementById('attendanceSection').style.display =
                attendanceChecked ? 'flex' : 'none';

            document.getElementById('enrollmentSection').style.display =
                enrollmentChecked ? 'flex' : 'none';

            document.getElementById('paymentSection').style.display =
                paymentChecked ? 'flex' : 'none';
        }


        // ----------------------
        // Checkbox logic
        // ----------------------
        const filterAll = document.getElementById('filterAll');
        const otherCheckboxes = document.querySelectorAll('.status-filter:not(#filterAll)');

        filterAll.addEventListener('change', () => {

            otherCheckboxes.forEach(cb => {
                cb.checked = filterAll.checked;
            });

            toggleCharts();
            loadDashboard();
        });

        otherCheckboxes.forEach(cb => {

            cb.addEventListener('change', () => {

                filterAll.checked =
                    Array.from(otherCheckboxes).every(c => c.checked);

                toggleCharts();
                loadDashboard();
            });

        });

        // ----------------------
        // Debounced reload for filters
        // ----------------------
        let timer;

        function debounceLoad() {
            clearTimeout(timer);
            timer = setTimeout(loadDashboard, 300);
        }

        document.querySelectorAll('#startDate, #endDate, #selectTeacher, #selectClass, .status-filter')
            .forEach(el => el.addEventListener('change', debounceLoad));

        // ----------------------
        // Year/Class Change
        // ----------------------
        document.getElementById('selectTeacher').addEventListener('change', async () => {

            selectTeacher = document.getElementById('selectTeacher').value;

            await loadClasses(selectTeacher);

            loadDashboard();
        });

        // document
        //     .getElementById('selectTeacher')
        //     .addEventListener('change', async function() {

        //         const teacherId = this.value;

        //         await loadClasses(teacherId);

        //         loadDashboard();
        //     });

        document.getElementById('selectClass').addEventListener('change', loadDashboard);


        document.querySelectorAll('.status-filter').forEach(cb => {
            cb.addEventListener('change', toggleCharts);
        });



        // ----------------------
        // Init
        // ----------------------
        document.addEventListener('DOMContentLoaded', async () => {

            initCharts();
            toggleCharts();

            await loadClasses(selectTeacher);
            loadDashboard();

            // Auto refresh every 15 seconds
            setInterval(async () => {
                console.log("Refreshing dashboard...", new Date());
                await loadDashboard();
            }, 15000);
        });
    </script>

</body>

</html>
