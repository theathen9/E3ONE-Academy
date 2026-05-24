    <?php
    // echo "<pre>";
    // print_r($_SESSION);
    // print_r($_COOKIE);
    // exit;
    //./admin/examination/add.php
    session_start();
    date_default_timezone_set('Asia/Phnom_Penh');
    include_once __DIR__ . '/../../config/bootstrap.php';
    include_once __DIR__ . "/../../data/dataSchema.php";
    include_once __DIR__ . "/../../components/Navbar.php";




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

    // include_once "/config/db.php";
    $routeAdmin[0]["active"] = false;
    $routeAdmin[5]["active"] = true;
    $routeAdmin[5]['submenu'][0]['active'] = true;


    $db = new DB($conn);
    $teacherCRUD = new ORM($db, 'tblEmployees t', 'employee_id');
    $ClassesCRUD = new ORM($db, 'tblClasses', 'class_id');
    $StudentsCRUD = new ORM($db, 'tblStudents s', 'student_id');
    $ScoresCRUD = new ORM($db, 'tblScores sc', 'score_id');
    $scoreTypesCRUD = new ORM($db, 'tblScoreTypes', 'score_type_id');
    $resultStudentCRUD = new ORM($db, 'tblStudentResults', 'result_id');


    $selectedTeacher = $_GET['teacher'] ?? '';
    $selectedClass   = $_GET['class'] ?? '';
    $academicYear    = $_GET['academic_year'] ?? '';
    $selectedExamType = $_GET['exam_type'] ?? '';

    $years = $conn->query("
    SELECT DISTINCT academic_year
    FROM tblClasses
    ORDER BY academic_year DESC");

    $students = [];

    if ($selectedClass && $academicYear) {

        $students = $StudentsCRUD
            ->select("
        s.student_id,
        e.enrollment_id,
        CONCAT(s.first_name_kh, ' ', s.last_name_kh) AS name,
        c.class_name AS class
    ")
            ->join("tblEnrollments e", "e.student_id = s.student_id")
            ->join("tblClasses c", "c.class_id = e.class_id")
            ->where("e.class_id", "=", $selectedClass) // ✅ FIX HERE
            ->get();
    }

    $scoreTypes = $scoreTypesCRUD->select("*")->get();

    $studentsScores = $ScoresCRUD
        ->select("
        sc.enrollment_id,
        sc.score_type_id,
        sc.score
    ")
        ->join('tblEnrollments e', 'sc.enrollment_id = e.enrollment_id')
        ->join('tblClasses cl', 'e.class_id = cl.class_id');

    if (!empty($academicYear)) {
        $studentsScores->where("SUBSTRING_INDEX(cl.academic_year, '-', -1)", "=", $academicYear);
    }

    $studentsScores = $studentsScores->get();

    $scoreMap = [];

    foreach ($studentsScores as $score) {

        $scoreMap[$score['enrollment_id']][$score['score_type_id']] =
            (float) $score['score'];
    }



    $teachers = $teacherCRUD
        ->select("
        t.employee_id,
        CONCAT(t.first_name_kh, ' ', t.last_name_kh) AS name")
        ->join("tblDepartments d", "d.department_id = t.department_id")
        ->where("d.department_name", "=", "Teacher")
        ->get();

    $classQuery = $ClassesCRUD
        ->select("
        class_id,
        class_name,
        teacher_id,
        academic_year
    ");

    if (!empty($academicYear)) {
        $classQuery->where("SUBSTRING_INDEX(academic_year, '-', -1)", "=", $academicYear);
    }

    if (!empty($selectedTeacher)) {
        $classQuery->where("teacher_id", "=", $selectedTeacher);
    }

    $classes = $classQuery->get();


    // echo "<pre>";
    // print_r($scoreMap);
    // exit;


    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        verifyCSRF();

        $ScoresCRUD = new ORM($db, 'tblScores', 'score_id');
        $resultStudentCRUD = new ORM($db, 'tblStudentResults', 'result_id');

        // =========================
        // 1. UPSERT SCORES
        // =========================
        foreach ($_POST['scores'] as $scoreTypeId => $studentsScores) {

            foreach ($studentsScores as $enrollmentId => $scoreValue) {

                $data = [
                    'enrollment_id'   => $enrollmentId,
                    'score_type_id'   => $scoreTypeId,
                    'score'           => (float) $scoreValue
                ];

                $existing = $ScoresCRUD
                    ->select("score_id")
                    ->where("enrollment_id", "=", $enrollmentId)
                    ->where("score_type_id", "=", $scoreTypeId)
                    ->first();

                if ($existing) {
                    $ScoresCRUD
                        ->where("score_id", "=", $existing['score_id'])
                        ->update($data);
                } else {
                    $ScoresCRUD->insert($data);
                }
            }
        }

        // =========================
        // 2. GET ALL ENROLLMENTS (IMPORTANT FIX)
        // =========================
        $enrollments = $StudentsCRUD
            ->select("e.enrollment_id")
            ->join("tblEnrollments e", "e.student_id = s.student_id")
            ->where("e.class_id", "=", $selectedClass)
            ->get();

        // =========================
        // 3. BUILD RESULTS PER STUDENT
        // =========================
        foreach ($enrollments as $enroll) {

            $enrollmentId = $enroll['enrollment_id'];

            $total = 0;

            foreach ($scoreTypes as $type) {

                $row = $ScoresCRUD
                    ->select("score")
                    ->where("enrollment_id", "=", $enrollmentId)
                    ->where("score_type_id", "=", $type['score_type_id'])
                    ->first();

                $total += (float) ($row['score'] ?? 0);
            }

            $average = $total; // (you can divide later if needed)

            // =========================
            // GRADE LOGIC
            // =========================
            if ($average >= 90) $gradeId = 1;
            elseif ($average >= 80) $gradeId = 2;
            elseif ($average >= 70) $gradeId = 3;
            elseif ($average >= 60) $gradeId = 4;
            else $gradeId = 5;

            $resultData = [
                'enrollment_id' => $enrollmentId,
                'class_id'       => $selectedClass,
                'academic_year'  => $academicYear,
                'total_score'    => $total,
                'average_score'  => $average,
                'grade_id'       => $gradeId
            ];

            $existingResult = $resultStudentCRUD
                ->select("result_id")
                ->where("enrollment_id", "=", $enrollmentId)
                ->first();

            if ($existingResult) {
                $resultStudentCRUD
                    ->where("result_id", "=", $existingResult['result_id'])
                    ->update($resultData);
            } else {
                $resultStudentCRUD->insert($resultData);
            }
        }
        header("Location: add.php?class=$selectedClass&academic_year=$academicYear&teacher=$selectedTeacher&exam_type=$selectedExamType");
        exit;
    }

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
        <link rel="stylesheet" href="../../src/style.css">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>


        <style>
            .dataLoading.loading {
                opacity: 0.6;
                pointer-events: none;
            }

            .tag-box {
                display: flex;
                flex-wrap: wrap;
                border: 1px solid #ccc;
                padding: 5px;
                border-radius: 6px;
            }

            .tag {
                background: #e3f2fd;
                margin: 3px;
                padding: 5px 10px;
                border-radius: 20px;
                display: flex;
                align-items: center;
            }

            .tag span {
                margin-left: 8px;
                cursor: pointer;
                color: red;
            }

            .sidebar a {
                display: block;
                padding: 12px 20px;
                color: #333;
                text-decoration: none;
            }

            .sidebar a:hover {
                background: #0d6efd;
                color: white;
                border-radius: 6px;
            }

            .content {
                margin-left: 240px;
                padding: 20px;
            }

            .card-box {
                border-radius: 12px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            }

            .stat-icon {
                font-size: 28px;
                padding: 15px;
                border-radius: 10px;
                color: white;
            }

            .bg-blue {
                background: #0d6efd;
            }

            .bg-green {
                background: #28a745;
            }

            .bg-orange {
                background: #f39c12;
            }

            .bg-red {
                background: #e74c3c;
            }
        </style>

    </head>

    <body class="container-fluid p-0 overflow-x-hidden">
        <div class="row g-3">

            <?php Navbar($infoSchemaData, $routeAdmin) ?>

            <!-- Main area -->
            <main class="col-10 bg-light">
                <div
                    class="d-flex justify-content-between align-items-center px-2 py-2 bg-white position-sticky top-0 z-3">
                    <div class="title">Welcome to <?php echo $infoSchemaData[0]["name"] ?></div>

                    <div class="dropdown">
                        <!-- <button class="d-flex align-items-center border-0 bg-white gap-2" data-bs-toggle="dropdown">
                            <img src="../src/assets/logo.jpg" width="60" height="60" style="border-radius:50%">
                            <div>Username</div>
                        </button> -->

                        <button id="account" class="d-flex align-items-center border-0 bg-white gap-2" data-bs-toggle="dropdown">
                            <img id="profileImg" width="60" height="60" style="border-radius:50%">
                            <div id="username"></div>
                        </button>

                        <ul class="dropdown-menu bg-white ">
                            <a href="../auth/signout.php" class="text-decoration-none">
                                <li><button class="dropdown-item">Sign Out</button></li>
                                <li><button class="dropdown-item">Account</button></li>
                            </a>
                        </ul>
                    </div>
                </div>



                <div class="w-100 d-flex mt-3 ms-3 justify-content-between gap-3 flex-wrap dataLoading">
                    <div class="w-100 bg-white shadow px-4 py-3 rounded">
                        <div class="row g-4 pb-5">
                            <div class="">
                                <div class="container-fluid">

                                    <!-- Header -->
                                    <div class="card shadow-sm border-0 mb-4">
                                        <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
                                            <div>
                                                <h3 class="mb-1 fw-bold">Student Score Management</h3>
                                                <p class="text-muted mb-0">
                                                    Input and manage student scores
                                                </p>
                                            </div>

                                            <button class="btn btn-primary px-4">
                                                <i class="bi bi-save me-2"></i>
                                                Save Scores
                                            </button>
                                        </div>
                                    </div>
                                    <form enctype="multipart/form-data" method="GET">

                                        <!-- Filter -->
                                        <div class="card shadow-sm border-0 mb-4">
                                            <div class="card-body">

                                                <div class="row g-3">

                                                    <div class="col-md-3">
                                                        <label class="form-label">Teacher</label>

                                                        <select name="teacher" class="form-select" onchange="this.form.submit()" required>
                                                            <option value="">Select Teacher</option>

                                                            <?php foreach ($teachers as $teacher): ?>
                                                                <option
                                                                    value="<?= $teacher['employee_id'] ?>"
                                                                    <?= $selectedTeacher == $teacher['employee_id'] ? 'selected' : '' ?>>
                                                                    <?= $teacher['name'] ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <label class="form-label">Academic Year</label>

                                                        <input
                                                            type="text"
                                                            name="academic_year"
                                                            class="form-control"
                                                            placeholder="e.g. 2023-2024 or 2024"
                                                            value="<?= htmlspecialchars($academicYear ?? '') ?>"
                                                            required
                                                            onchange="this.form.submit()">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label">Class</label>

                                                        <select name="class" class="form-select" required>
                                                            <option value="">Select Class</option>

                                                            <?php foreach ($classes as $class): ?>
                                                                <option
                                                                    value="<?= $class['class_id'] ?>"
                                                                    <?= $selectedClass == $class['class_id'] ? 'selected' : '' ?>>
                                                                    <?= $class['class_name'] ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <label class="form-label">Exam Type</label>

                                                        <select name="exam_type" class="form-select" required>
                                                            <option value="">Select Exam Type</option>
                                                            <option value="midterm" <?= $selectedExamType == 'midterm' ? 'selected' : '' ?>>Midterm</option>
                                                            <option value="final" <?= $selectedExamType == 'final' ? 'selected' : '' ?>>Final</option>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-2 d-flex align-items-end">
                                                        <button type="submit" class="btn btn-primary w-100">
                                                            <i class="bi bi-search me-1"></i>
                                                            Load
                                                        </button>
                                                    </div>

                                                </div>

                                            </div>
                                        </div>
                                    </form>



                                    <!-- Table -->
                                    <div class="card shadow-sm border-0">

                                        <div class="card-body p-0">

                                            <form method="POST" enctype="multipart/form-data">



                                                <?= csrf_field() ?>
                                                <input type="hidden" name="created_by" value="<?= $_SESSION['reference_id'] ?? '' ?>">


                                                <div class="table-responsive">

                                                    <table class="table table-hover align-middle mb-0">

                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Student</th>
                                                                <th>Class</th>
                                                                <th>Speaking</th>
                                                                <th>Listening</th>
                                                                <th>Reading</th>
                                                                <th>Grammar</th>
                                                                <th>Writing</th>
                                                                <th width="150">Score</th>
                                                            </tr>
                                                        </thead>

                                                        <tbody>

                                                            <?php foreach ($students as $index => $student): ?>

                                                                <tr>

                                                                    <td><?= $index + 1 ?></td>

                                                                    <td class="fw-semibold">
                                                                        <?= htmlspecialchars($student['name']) ?>
                                                                    </td>

                                                                    <td>
                                                                        <?= htmlspecialchars($student['class']) ?>
                                                                    </td>

                                                                    <?php
                                                                    $total = 0;
                                                                    ?>



                                                                    <?php foreach ($scoreTypes as $type): ?>

                                                                        <td>

                                                                            <input
                                                                                type="number"
                                                                                name="scores[<?= $type['score_type_id'] ?>][<?= $student['enrollment_id'] ?>]"
                                                                                class="form-control score-input"
                                                                                min="0"
                                                                                max="100"

                                                                                value="<?= htmlspecialchars(
                                                                                            $scoreMap[$student['enrollment_id']][$type['score_type_id']] ?? ''
                                                                                        ) ?>">

                                                                        </td>

                                                                    <?php endforeach; ?>

                                                                    <td>
                                                                        <input
                                                                            type="number"
                                                                            class="form-control total-score"
                                                                            value="<?= $total ?>"
                                                                            readonly>
                                                                    </td>

                                                                </tr>

                                                            <?php endforeach; ?>

                                                        </tbody>

                                                    </table>

                                                </div>

                                                <div class="p-3 border-top text-end">

                                                    <button type="submit" class="btn btn-success px-4">
                                                        <i class="bi bi-check-circle me-2"></i>
                                                        Save All Scores
                                                    </button>

                                                </div>

                                            </form>

                                        </div>

                                    </div>

                                </div>

                            </div>
                        </div>
                    </div>
            </main>
        </div>
        <script src="../../script.js"></script>

        <script>
            document.querySelectorAll("tbody tr").forEach(row => {

                // dynamic score inputs
                const scoreInputs = row.querySelectorAll(".score-input");

                // total field
                const totalInput = row.querySelector(".total-score");

                function calculateTotal() {

                    let total = 0;

                    scoreInputs.forEach(input => {

                        total += Number(input.value || 0);

                    });

                    if (totalInput) {
                        totalInput.value = total;
                    }
                }

                // auto calculate on typing
                scoreInputs.forEach(input => {

                    input.addEventListener("input", calculateTotal);

                });

                // first load
                calculateTotal();
            });
        </script>

        <script>
            async function loadClasses() {
                const teacher = document.querySelector('[name="teacher"]').value;
                const year = document.querySelector('[name="academic_year"]').value;
                const classId = document.querySelector('[name="class"]').value;
                const examType = document.querySelector('[name="exam_type"]').value;

                const res = await fetch(`/system-management/app/api/v1/classes_by_teacher.php?teacher=${teacher}&academic_year=${year}&class=${classId}&exam_type=${examType}`);

                const json = await res.json();
                console.log(json);
            }
        </script>

    </body>

    </html>