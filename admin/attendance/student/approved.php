<?php
date_default_timezone_set('Asia/Phnom_Penh');
include_once __DIR__ . '/../../../data/dataSchema.php';
include_once __DIR__ . '/../../../config/bootstrap.php';
include_once __DIR__ . '/../../../components/Navbar.php';
$userId = checkAuth();
if (!$userId) {
    header("Location: " . BASE_URL . "/auth/signin.php");
    exit;
}
authorizeRole('admin');
$routeAdmin[0]["active"] = false;
$routeAdmin[4]["active"] = true;
$routeAdmin[4]['submenu'][1]['active'] = true;
$db = new DB($conn);
$cache = new Cache();
$studentCRUD     = new ORM($db, "tblStudents s");
$classCRUD       = new ORM($db, "tblClasses cl");
$attendanceCRUD  = new ORM($db, "tblAttendances");
$enrollmentCRUD  = new ORM($db, "tblEnrollments e");
/*
|--------------------------------------------------------------------------
| PAGINATION
|--------------------------------------------------------------------------
*/
$limit  = 10;
$page   = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$classId = isset($_GET['class_id'])
    ? (int) $_GET['class_id']
    : 0;
$teacherId = isset($_GET['teacher_id']) ? (int) $_GET['teacher_id'] : 0;
$getTeacher = $_SESSION['reference_id'] ?? 0;

// echo 'Teacher ID from session: ' . $getTeacher;

$teacherId = $getTeacher;
$page   = max($page, 1);
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
/*
|--------------------------------------------------------------------------
| CACHE KEY
|--------------------------------------------------------------------------
*/
$cacheKey = "attendance_list_{$search}_{$page}_{$limit}";

$students = $cache->get($cacheKey, 120);
if ($students === null) {

    $studentsORM = $enrollmentCRUD
        ->select("e.enrollment_id, s.student_id,s.first_name_kh,s.last_name_kh,cl.class_id,cl.class_name,cl.class_code,a.attendance_id,
    a.status AS attendance_status,a.remarks AS attendance_remarks")
        ->join("tblStudents s", "e.student_id = s.student_id")->join("tblClasses cl", "e.class_id = cl.class_id")
        ->join("tblAttendances a", "a.enrollment_id = e.enrollment_id AND a.attendance_date = '" . date('Y-m-d') . "'", "LEFT");

    if ($classId > 0) {
        $studentsORM->where("cl.class_id", "=", $classId);
    }
    $students = $studentsORM->orderBy("s.student_id", "ASC")->get();
    $cache->set($cacheKey, $students, 120);
    $cache->clearByPrefix("attendance_list_");
    $cache->clearByPrefix("students_class_");
}
/*
|--------------------------------------------------------------------------
| COUNT TOTAL
|--------------------------------------------------------------------------
*/

$countORM = new ORM($db, "tblAttendances a");

$countData = $countORM

    ->join("tblEnrollments e", "a.enrollment_id = e.enrollment_id")

    ->join("tblStudents s", "e.student_id = s.student_id")

    ->join("tblClasses cl", "e.class_id = cl.class_id")

    ->join("tblEmployees t", "e.created_by = t.employee_id", "LEFT")

    ->search($search, [
        "s.student_id",
        "s.first_name_kh",
        "s.last_name_kh",
        "cl.class_name",
        "cl.class_code",
        "a.status"
    ])

    ->select("COUNT(*) AS total")
    ->first();
$cache->clearByPrefix("attendance_list_");


$totalAttendances = $countData['total'] ?? 0;

$totalPages = ceil($totalAttendances / $limit);

/*
|--------------------------------------------------------------------------
| ACTIVE CLASSES
|--------------------------------------------------------------------------
*/

$activeClasses = $classCRUD
    ->select("cl.class_id,cl.class_name,cl.class_code,ts.slot_name as time")
    ->join("tblTimeSlots ts", "cl.slot_id = ts.slot_id")
    ->get();

$error = false;
$success = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    verifyCSRF();

    $statusList = $_POST['status'] ?? [];
    $remarksList = $_POST['remarks'] ?? [];

    $attendanceDate = post("attendance_date", date("Y-m-d"));

    try {

        $db->beginTransaction();

        foreach ($statusList as $enrollmentId => $status) {

            // check duplicate
            $existingAttendance = $attendanceCRUD
                ->where("enrollment_id", "=", $enrollmentId)
                ->where("attendance_date", "=", $attendanceDate)
                ->first();

            if ($existingAttendance) {
                $db->rollback();
                $error = true;
                break;
            }

            $attendanceCRUD->insert([
                "enrollment_id" => $enrollmentId,
                "status" => $status,
                "attendance_date" => $attendanceDate,
                "remarks" => $remarksList[$enrollmentId] ?? null,
                "created_by" => $userId
            ]);
            $cache->clearByPrefix("attendance_list_");
        }

        if (!$error) {

            $db->commit();
            $success = true;

            header("Location: " . BASE_URL . "/admin/attendance/student/approved.php?class_id={$classId}&attendance_date={$attendanceDate}");
            exit;
        }
    } catch (Exception $e) {
        $db->rollback();
        $error = true;
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approved | <?php echo $infoSchemaData[1]["name_short"] ?></title>
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
    <link rel="stylesheet" href="../../../src/style.css">

</head>

<body class="container-fluid p-0 overflow-x-hidden ">
    <div class="row g-3">

        <?php Navbar($infoSchemaData, $routeAdmin); ?>

        <!-- Main area -->
        <main class="col-10 bg-light ">
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

            <div class="container-lg container-md container-sm p-3 vh-100">

                <div class="w-100 d-flex mt-3 justify-content-between gap-3 flex-wrap">
                    <div class="w-100  bg-white shadow px-4 py-3 rounded">

                        <!-- Header -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="fw-bold mb-1">
                                    <i class="bi bi-calendar-check me-2"></i>
                                    Submit Attendance
                                </h4>
                                <p class="text-muted mb-0">
                                    Manage daily student attendance
                                </p>
                            </div>

                            <div>
                                <button class="btn btn-success">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Save Attendance
                                </button>
                            </div>
                        </div>

                        <!-- Filters -->
                        <form method="GET" class="mb-4">
                            <div class="row g-3 mb-4">

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        Select Class
                                    </label>

                                    <select name="class_id" class="form-select" id="classSelect">
                                        <option value="">Choose Class</option>

                                        <?php foreach ($activeClasses as $class): ?>

                                            <option value="<?= $class['class_id']; ?>" <?= ($classId == $class['class_id']) ? 'selected' : ''; ?>>
                                                <?= $class['class_name']; ?>
                                                (<?= $class['class_code']; ?>)
                                                (<?= $class['time']; ?>)
                                            <?php endforeach; ?>
                                            </option>

                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">
                                        Attendance Date
                                    </label>

                                    <input
                                        type="date"
                                        name="attendance_date"
                                        class="form-control"
                                        value="<?= date('Y-m-d'); ?>">
                                </div>



                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-search me-1"></i>
                                        Load
                                    </button>
                                </div>

                            </div>
                        </form>

                        <!-- Attendance Table -->
                        <form method="POST">

                            <?= csrf_field() ?>
                            <div class="table-responsive">

                                <table class="table align-middle table-hover">

                                    <thead class="table-light">
                                        <tr>
                                            <th width="80">ID</th>
                                            <th>Student Name</th>
                                            <th>Class</th>
                                            <th width="180">Status</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>

                                    <tbody class="bg-white" id="attendTable">

                                        <?php if ($classId > 0): ?>

                                            <?php foreach ($students as $enrollmentId => $student): ?>
                                                <input type="hidden" name="enrollment_id[]" value="<?= $student['enrollment_id']; ?>">

                                                <tr>

                                                    <td>
                                                        <?= $enrollmentId + 1; ?>
                                                    </td>

                                                    <td>
                                                        <div class="fw-semibold">
                                                            <?= $student['first_name_kh']; ?>
                                                            <?= $student['last_name_kh']; ?>
                                                        </div>
                                                    </td>

                                                    <td>
                                                        <?= $student['class_name']; ?>
                                                    </td>

                                                    <td>

                                                        <select
                                                            name="status[<?= $student['enrollment_id']; ?>]"
                                                            class="form-select">

                                                            <option
                                                                value="absent"
                                                                <?= ($student['attendance_status'] ?? '') === 'absent' ? 'selected' : ''; ?>>
                                                                ❌ Absent
                                                            </option>

                                                            <option
                                                                value="present"
                                                                <?= ($student['attendance_status'] ?? '') === 'present' ? 'selected' : ''; ?>>
                                                                ✅ Present
                                                            </option>

                                                            <option
                                                                value="late"
                                                                <?= ($student['attendance_status'] ?? '') === 'late' ? 'selected' : ''; ?>>
                                                                ⏰ Late
                                                            </option>

                                                        </select>

                                                    </td>

                                                    <td>

                                                        <input
                                                            type="text"
                                                            name="remarks[<?= $student['enrollment_id']; ?>]"
                                                            class="form-control"
                                                            placeholder="Optional remarks..."
                                                            value="<?= htmlspecialchars($student['attendance_remarks'] ?? ''); ?>">

                                                    </td>

                                                </tr>

                                            <?php endforeach; ?>

                                        <?php else: ?>

                                            <tr class="text-center py-5">

                                                <td colspan="5">

                                                    <div class="alert alert-info mb-0">

                                                        Please select a class and click Load.

                                                    </div>

                                                </td>

                                            </tr>

                                        <?php endif; ?>

                                    </tbody>

                                </table>

                            </div>

                            <!-- Footer Actions -->
                            <div class="alert text-center alert-danger <?= !$error ? 'd-none' : ''; ?>">
                                Cannot Resubmit Attendance
                            </div>

                            <div class="alert text-center alert-success <?= !$success ? 'd-none' : ''; ?>">
                                Attendance submitted successfully!
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4 mb-5">

                                <button
                                    type="button"
                                    class="btn btn-outline-secondary"
                                    id="cancelBtn">
                                    Cancel
                                </button>

                                <button type="submit" class="btn btn-success px-4">
                                    <i class="bi bi-save me-1"></i>
                                    Submit Attendance
                                </button>

                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>


    </div>

    <script>
        document.getElementById("searchStudent").addEventListener("keyup", function() {
            let search = this.value;

            fetch("/System-Management/ajax/search_student.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: "search=" + encodeURIComponent(search)
                })
                .then(res => res.text())
                .then(data => {
                    document.getElementById("attendTable").innerHTML = data;

                    selectedId = null;
                    editBtn.disabled = true;
                    detailBtn.disabled = true;
                });
        });
    </script>

    <script>
        let selectedId = null;

        const tableBody = document.getElementById("attendTable");
        const editBtn = document.getElementById("editBtn");
        const detailBtn = document.getElementById("detailBtn");

        tableBody.addEventListener("click", function(e) {
            const row = e.target.closest("tr");
            if (!row) return;
            console.dir(e.target);
            console.dir(e.target.closest);

            // Remove highlight from all rows
            document.querySelectorAll("#attendTable tr")
                .forEach(r => r.classList.remove("table-active"));

            // Highlight clicked row
            row.classList.add("table-active");

            // Store ID
            selectedId = row.dataset.id;

            // Enable buttons
            editBtn.disabled = false;
            detailBtn.disabled = false;

            console.log("Selected ID:", selectedId);
        });

        editBtn.addEventListener("click", () => {
            if (!selectedId) return;
            window.location.href = "edit?type=student&id=" + selectedId;
        });

        detailBtn.addEventListener("click", () => {
            if (!selectedId) return;
            window.location.href = "detail?type=student&id=" + selectedId;
        });
    </script>

    <script>
        document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(function(menu) {

            const icon = menu.querySelector(".submenu-icon");
            const target = document.querySelector(menu.getAttribute("href"));

            if (!icon || !target) return;

            target.addEventListener("show.bs.collapse", function() {
                icon.classList.remove("bi-chevron-left");
                icon.classList.add("bi-chevron-down");
            });

            target.addEventListener("hide.bs.collapse", function() {
                icon.classList.remove("bi-chevron-down");
                icon.classList.add("bi-chevron-left");
            });

        });
    </script>

    <script>
        document.getElementById("cancelBtn").addEventListener("click", function() {

            // Reset form
            document.querySelector("form").reset();

            // Hide error message
            document.querySelector(".alert-danger").classList.add("d-none");

            // Clear attendance table
            document.getElementById("attendTable").innerHTML = `
        <tr class="text-center py-5">
            <td colspan="5">
                <div class="alert alert-info mb-0">
                    Please select a class and click Load.
                </div>
            </td>
        </tr>
    `;
        });
    </script>

</body>

</html>