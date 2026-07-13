<?php
//./admin/register.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// var_dump($_SESSION['csrf_token']);
// var_dump($_POST['csrf_token']);
// exit;

// include_once __DIR__ . '/../config/db.php';
// include_once __DIR__ . '/../core/DB.php';
// include_once __DIR__ . '/../../core/CRUD.php';
// include_once __DIR__ . '/../core/ORM.php';

include_once __DIR__ . '/../config/bootstrap.php';
include_once __DIR__ . '/../app/api/v1/auth.php';

// include_once __DIR__ . '/../data/dbSchemaData.php';
include_once __DIR__ . '/../data/functionData.php';
include_once __DIR__ . '/../components/Navbar.php';
include_once __DIR__ . '/../components/Avatar.php';
include_once __DIR__ . '/../components/RegisterStaff.php';
include_once __DIR__ . '/../components/RegisterStudent.php';



// include_once __DIR__ . '/api/dashboard.php';
// Generate token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}



$userId = checkAuth();

if (!$userId) {
    header("Location: " . BASE_URL . "/auth/signin.php");
    exit;
}

authorizeRole('admin');


// var_dump($_SESSION);
// var_dump($_COOKIE);
// exit;



include __DIR__ . '/../data/dataSchema.php';
// include_once "/config/db.php";
$routeAdmin[0]["active"] = false;
$routeAdmin[7]["active"] = true;



$type = $_GET['type'] ?? 'student';
$queryString = "?type={$type}";
$step = $_POST['step'] ?? null;


$db = new DB($conn);
$studentCRUD = new ORM($db, "tblStudents", "student_id");
$teacherCRUD = new ORM($db, "tblEmployees", "employee_id");
$employeeCRUD = new ORM($db, "tblEmployees", "employee_id");
$classCRUD = new ORM($db, "tblClasses", "class_id");
$courseCRUD = new ORM($db, "tblCourses", "course_id");
$subjectCRUD = new ORM($db, "tblSubjects", "subject_id");
$courseSubjectCRUD = new ORM($db, "tblCourseSubjects", "id");
$roomCRUD = new ORM($db, "tblRooms", "room_id");
$timeSlotCRUD = new ORM($db, "tblTimeSlots", "slot_id");
$timetablCRUD = new ORM($db, "tblTimetables tt", "timetable_id");
$paymentMethodsCRUD = new ORM($db, "tblPaymentMethods", "method_id");
$departmentCRUD = new ORM($db, "tblDepartments", "department_id");


$getClass = $classCRUD->get("*", "", [], "", 100, 0);
$paymentMethods = $paymentMethodsCRUD->get("*", "", [], "", 10, 0);
$departments = $departmentCRUD
    ->limit(25)
    ->offset(0)
    ->get();


$res = $studentCRUD
    ->select('student_id')
    ->orderBy('student_id', 'DESC')
    ->first();

$lastId = $res['student_id'] ?? 0;
$idCode = $lastId + 1;

$resS = $employeeCRUD
    ->select('employee_id')
    ->orderBy('employee_id', 'DESC')
    ->first();

$lastIdS = $resS['employee_id'] ?? 0;
$idCodeStaff = $lastIdS + 1;



$classes = [];
function formatDays($days)
{
    if (empty($days)) return '';

    $map = ['Mon' => 1, 'Tue' => 2, 'Wed' => 3, 'Thu' => 4, 'Fri' => 5, 'Sat' => 6, 'Sun' => 7];

    // sort days
    usort($days, function ($a, $b) use ($map) {
        return $map[$a] <=> $map[$b];
    });

    $ranges = [];
    $start = $days[0];
    $prev = $days[0];

    for ($i = 1; $i < count($days); $i++) {
        if ($map[$days[$i]] == $map[$prev] + 1) {
            $prev = $days[$i];
        } else {
            $ranges[] = ($start == $prev) ? $start : "$start-$prev";
            $start = $days[$i];
            $prev = $days[$i];
        }
    }

    $ranges[] = ($start == $prev) ? $start : "$start-$prev";

    return implode(', ', $ranges);
}


if (is_array($getClass)) {
    foreach ($getClass as $classRow) {

        $teacher = $teacherCRUD->find($classRow['teacher_id']);
        $room = $roomCRUD->find($classRow['room_id']);
        $timeSlot = $timeSlotCRUD->find($classRow['slot_id']);
        $courseSubject = $courseSubjectCRUD->find($classRow['course_subject_id']);

        $course = null;
        $subject = null;
        $level = null;

        if ($courseSubject) {

            $course = $courseCRUD->find($courseSubject['course_id']);

            $subject = $subjectCRUD->find($courseSubject['subject_id']);
        }

        // ✅ FIX timetable query (no subject join needed)
        $timetables = (new ORM($db, "tblTimetables tt"))
            ->select("d.day_code")
            ->join("tblDays d", "tt.day_id = d.day_id")
            ->where("tt.class_id", "=", $classRow['class_id'])
            ->orderBy("d.sort_order", "ASC")
            ->get();

        // ------------------------
        // Assign data
        // ------------------------

        $classRow['class_name'] = $classRow['class_name'] ?? '';
        $classRow['subject_code'] = $subject['subject_code'] ?? ''; // ✅ correct
        $classRow['course_name'] = $course['course_name'] ?? '';
        $classRow['price'] = $course['price'] ?? 0;

        $classRow['teacher_name'] = $teacher
            ? $teacher['first_name_en'] . ' ' . $teacher['last_name_en']
            : '';

        $classRow['time'] = $timeSlot
            ? $timeSlot['start_time'] . ' - ' . $timeSlot['end_time']
            : '';

        // ✅ Extract days
        $days = [];

        if (!empty($timetables)) {
            foreach ($timetables as $tt) {
                $days[] = $tt['day_code'];
            }
        }

        $classRow['schedule'] = formatDays($days);
        $classRow['room_name'] = $room['room_name'] ?? '';

        $classes[$classRow['class_id']] = $classRow;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCSRF();
    require_once __DIR__ . '/../app/api/v1/register_process.php';
    // header('Location: ' . BASE_URL . '/admin/register.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | <?= htmlspecialchars($infoSchemaData[1]["name_short"]) ?></title>
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
    <link rel="stylesheet" href="../src/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="../src/assets/js/user-profile.js" defer></script>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
        .progress-bar {
            transition: width 0.4s ease;
        }

        .page-title {
            font-weight: 700;
        }
    </style>

</head>

<body class="container-fluid p-0 overflow-x-hidden">
    <div class="row g-3">

        <?php Navbar($infoSchemaData, $routeAdmin); ?>


        <!-- Main area -->
        <main class="col-lg-10 bg-light">
            <div
                class="d-flex justify-content-between align-items-center px-2 py-2 bg-white position-sticky top-0 z-3">
                <div class="title">Welcome to <?php echo $infoSchemaData[0]["name"] ?></div>

                <?php Avatar($_SESSION['role']); ?>

            </div>

            <!-- form -->

            <div class="container my-3 pb-5">
                <div class="row">
                    <div class="w-100 ">

                        <div>
                            <button style="width: 90px;" type="button" id="studentBtn" class="btn btn-primary">Student</button>
                            <button style="width: 90px;" type="button" id="staffBtn" class="btn btn-secondary">Staff</button>
                        </div>
                        <!-- student register -->

                        <div id="studentSection">

                            <?php register_student($conn, $classes, $paymentMethods, $idCode); ?>

                        </div>

                        <div id="staffSection" style="display:none;">
                            <form id="staffForm" method="post" enctype="multipart/form-data">
                                <?= csrf_field(); ?>

                                <input type="hidden" name="created_by" value="<?= $_SESSION['reference_id'] ?? '' ?>">
                                <?php register_staff($conn, $departments, $idCodeStaff); ?>
                            </form>
                        </div>


                    </div>
                    <?php if (!empty($_SESSION['success']) || !empty($_SESSION['error'])): ?>
                        <div class="container mt-3">
                            <?php if (!empty($_SESSION['success'])): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <?= htmlspecialchars($_SESSION['success']) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                                <?php unset($_SESSION['success']); ?>
                            <?php endif; ?>

                            <?php if (!empty($_SESSION['error'])): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?= htmlspecialchars($_SESSION['error']) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                                <?php unset($_SESSION['error']); ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="../src/assets/js/navbar-toggle-action.js"></script>

    <script>
        document.getElementById("student_photo").addEventListener("change", function() {

            const file = this.files[0];

            if (file) {
                const preview = document.getElementById("studentPreviewPhoto");
                preview.src = URL.createObjectURL(file);
            }

        });
        document.getElementById("staff_photo").addEventListener("change", function() {

            const file = this.files[0];

            if (file) {
                const preview = document.getElementById("staffPreviewPhoto");
                preview.src = URL.createObjectURL(file);
            }

        });
    </script>

    <script>
        // const BASE_API = "/system-management/api/v1/address.php";
        const BASE_URL = "<?= BASE_URL ?>";

        function reset(select, label, disable = true) {
            select.innerHTML = `<option value="">-- ${label} --</option>`;
            select.disabled = disable;
            select.style.display = "block";
        }

        function addOptions(select, data, label, allowOther = true) {
            select.innerHTML = `<option value="">-- ${label} --</option>`;

            if (Array.isArray(data)) {
                data.forEach(item => {
                    select.add(new Option(item, item));
                });
            }

            if (allowOther) {
                select.add(new Option("-- Other --", "other"));
            }

            select.disabled = false;
            select.style.display = "block";
        }


        const studentBtn = document.getElementById("studentBtn");
        const staffBtn = document.getElementById("staffBtn");

        const studentSection = document.getElementById("studentSection");
        const staffSection = document.getElementById("staffSection");

        function toggleSection(show, hide, activeBtn, inactiveBtn) {
            show.style.display = "block";
            hide.style.display = "none";

            activeBtn.classList.add("btn-primary");
            activeBtn.classList.remove("btn-secondary");

            inactiveBtn.classList.add("btn-secondary");
            inactiveBtn.classList.remove("btn-primary");

            // enable inputs
            show.querySelectorAll("input, select, textarea")
                .forEach(el => el.disabled = false);

            // disable hidden inputs
            hide.querySelectorAll("input, select, textarea")
                .forEach(el => el.disabled = true);
        }

        // events
        studentBtn.addEventListener("click", () => {

            type = "student";

            console.log(type);

            const studentPrefixes = [
                "student_birth_addr",
                "student_curr_addr",
                "student_guardian_curr_addr"
            ];

            studentPrefixes.forEach(setupAddressAPI);

            toggleSection(studentSection, staffSection, studentBtn, staffBtn);

        });

        staffBtn.addEventListener("click", () => {

            type = "staff";


            console.log(type);

            const staffPrefixes = [
                "birth_addr",
                "curr_addr"
            ];

            staffPrefixes.forEach(setupAddressAPI);

            toggleSection(staffSection, studentSection, staffBtn, studentBtn);

        });

        function setupAddressAPI(prefix) {

            const province = document.getElementById(prefix + "_province");
            const district = document.getElementById(prefix + "_district");
            const commune = document.getElementById(prefix + "_commune");
            const village = document.getElementById(prefix + "_village");

            const otherDistrict = document.getElementById("other_" + prefix + "_district");
            const otherCommune = document.getElementById("other_" + prefix + "_commune");
            const otherVillage = document.getElementById("other_" + prefix + "_village");

            if (!province || !district || !commune || !village) return;

            // ---------------- HELPERS ----------------
            function reset(select, label, allowOther = false) {
                select.innerHTML = `<option value="">-- ${label} --</option>`;
                select.disabled = true;
                select.style.display = "block";

                if (allowOther) {
                    select.add(new Option("-- Other --", "other"));
                }
            }

            function addOptions(select, data, label, allowOther = true) {
                select.innerHTML = `<option value="">-- ${label} --</option>`;

                if (Array.isArray(data)) {
                    data.forEach(item => select.add(new Option(item, item)));
                }

                if (allowOther) {
                    select.add(new Option("-- Other --", "other"));
                }

                select.disabled = false;
                select.style.display = "block";
            }

            function showOther(selectEl, inputEl, name) {
                selectEl.style.display = "none";
                inputEl.style.display = "block";
                inputEl.name = name;
            }

            function hideOther(selectEl, inputEl) {
                selectEl.style.display = "block";
                inputEl.style.display = "none";
                inputEl.name = "";
            }

            // ---------------- LOAD PROVINCE ----------------
            fetch(`${BASE_URL}/api/v1/address?type=provinces`)
                .then(r => r.json())
                .then(data => {
                    addOptions(province, data, "Province", false);
                    reset(district, "District");
                    reset(commune, "Commune");
                    reset(village, "Village");
                });

            // ---------------- PROVINCE ----------------
            province.addEventListener("change", async () => {

                reset(district, "District");
                reset(commune, "Commune");
                reset(village, "Village");
                if (province.value === "other" || province.value !== "other") {
                    showOther(district, district, prefix + "_district");
                    showOther(commune, commune, prefix + "_commune");
                    showOther(village, village, prefix + "_village");
                    hideOther(district, otherDistrict);
                    hideOther(commune, otherCommune);
                    hideOther(village, otherVillage);
                }

                if (!province.value) return;

                const res = await fetch(`${BASE_URL}/api/v1/address?type=districts&province=${province.value}`);
                const data = await res.json();

                addOptions(district, data, "District");
            });

            // ---------------- DISTRICT ----------------
            district.addEventListener("change", async () => {

                reset(commune, "Commune");
                reset(village, "Village");

                if (district.value === "other") {

                    showOther(district, otherDistrict, prefix + "_district");
                    showOther(commune, otherCommune, prefix + "_commune");
                    showOther(village, otherVillage, prefix + "_village");
                    hideOther(district, district);
                    hideOther(commune, commune);
                    hideOther(village, village);

                    // ❌ HIDE village completely
                    // village.style.display = "none";
                    // village.disabled = true;

                    // otherVillage.style.display = "block";
                    // otherVillage.name = prefix;

                    return;
                }
                if (district.value !== "other" && district.value) {

                    showOther(commune, commune, prefix + "_district");
                    hideOther(commune, otherCommune, prefix + "_commune");

                    // ❌ HIDE village completely
                    village.style.display = "block";
                    village.disabled = true;

                    otherVillage.style.display = "none";
                    otherVillage.name = '';

                    // return;
                }


                if (!district.value) return;

                const res = await fetch(`${BASE_URL}/api/v1/address?type=communes&province=${province.value}&district=${district.value}`);
                const data = await res.json();

                addOptions(commune, data, "Commune");
            });

            // ---------------- COMMUNE ----------------
            commune.addEventListener("change", async () => {

                reset(village, "Village", true);

                // ---------------- COMMUNE = OTHER ----------------
                if (commune.value === "other") {

                    showOther(commune, otherCommune, prefix + "_commune");
                    hideOther(commune, commune, prefix + "__commune");

                    // ❌ HIDE village completely
                    village.style.display = "none";
                    village.disabled = true;

                    otherVillage.style.display = "block";
                    otherVillage.name = prefix;

                    return;
                }
                if (commune.value !== "other" && village.value !== "other") {

                    showOther(commune, commune, prefix + "_commune");
                    hideOther(commune, otherCommune, prefix + "__commune");

                    // ❌ HIDE village completely
                    village.style.display = "block";
                    village.disabled = false;

                    otherVillage.style.display = "none";
                    otherVillage.name = '';

                    // return;
                }


                // ---------------- NORMAL COMMUNE ----------------
                hideOther(commune, otherCommune, prefix + "_commune");

                // ✅ SHOW village again
                village.style.display = "block";
                village.disabled = false;

                if (!commune.value) return;

                const res = await fetch(
                    `${BASE_URL}/api/v1/address?type=villages&province=${province.value}&district=${district.value}&commune=${commune.value}`
                );

                const data = await res.json();

                addOptions(village, data, "Village", true);
            });
            // ---------------- VILLAGE ----------------
            village.addEventListener("change", () => {

                if (village.value === "other") {
                    showOther(village, otherVillage, prefix + "_village");
                } else {
                    hideOther(village, otherVillage);
                }
            });
        }
        let type = <?= json_encode($type) ?>;

        // INIT
        document.addEventListener("DOMContentLoaded", async function() {

            console.log(type);

            if (type === "staff") {
                staffBtn.click(); // auto switch to staff form
            } else {
                studentBtn.click(); // default student
            }


            flatpickr("#student_dob", {
                altFormat: "d-m-Y", // ✅ what user sees
                dateFormat: "Y-m-d", // ✅ value sent to backend
                altInput: true, // ✅ show separate input
                maxDate: "today",
                allowInput: true,
                monthSelectorType: "dropdown",
                yearSelectorType: "dropdown"
            });
            flatpickr("#student_register_at", {
                altInput: true,
                altFormat: "d-m-Y",
                dateFormat: "Y-m-d",
                maxDate: "today",
                allowInput: true,
                monthSelectorType: "dropdown",
                yearSelectorType: "dropdown",

                onReady: function(selectedDates, dateStr, instance) {
                    instance.altInput.setAttribute("placeholder", "Register Date");
                }
            });
            flatpickr("#dob", {
                altFormat: "d-m-Y",
                dateFormat: "Y-m-d",
                altInput: true,
                maxDate: "today",
                allowInput: true,
                monthSelectorType: "dropdown",
                yearSelectorType: "dropdown",
            });
            flatpickr("#hired_at", {
                altFormat: "d-m-Y",
                dateFormat: "Y-m-d",
                altInput: true,
                maxDate: "today",
                allowInput: true,
                monthSelectorType: "dropdown",
                yearSelectorType: "dropdown",
            });
        });
        document.addEventListener("DOMContentLoaded", function() {

            const studentPrefixes = [
                "student_birth_addr",
                "student_curr_addr",
                "student_guardian_curr_addr"
            ];

            const staffPrefixes = [
                "birth_addr",
                "curr_addr"
            ];

            studentPrefixes.forEach(setupAddressAPI);
            staffPrefixes.forEach(setupAddressAPI);

        });
    </script>


</body>

</html>
