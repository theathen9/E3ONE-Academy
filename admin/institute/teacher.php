<?php
//./admin/institute/teacher.php

// require_once( __DIR__ . "/../../config/db.php");
include_once __DIR__ . '/../../config/db.php';
include_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../data/dataSchema.php';
require_once __DIR__ . '/../../components/Navbar.php';


// include_once "/config/db.php";
$routeAdmin[0]["active"] = false;
$routeAdmin[1]["active"] = true;
$routeAdmin[1]['submenu'][2]['active'] = true;

$userId = checkAuth();

if (!$userId) {
    header("Location: ../auth/signin.php");
    exit;
}

authorizeRole('admin');

$limit = 18;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page  = max($page, 1);
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$db = new DB($conn);
$teacherCRUD = new ORM($db, 'tblEmployees emp', 'employee_id');
$cache = new Cache();

$cacheKey = "teachers_list_{$search}_{$page}_{$limit}";

$data = $cache->get($cacheKey, 120);

if ($data === null) {

    $data = $teacherCRUD
        ->select("emp.*, IFNULL(GROUP_CONCAT(s.subject_name SEPARATOR ', '),'') as subjects, d.department_name")
        ->join("tblEmployeeSubjects empsub", "emp.employee_id = empsub.employee_id", "LEFT")
        ->join("tblDepartments d", "emp.department_id = d.department_id", "LEFT")
        ->join("tblSubjects s", "empsub.subject_id = s.subject_id", "LEFT")
        ->where('d.department_name', '=', 'Teacher')
        ->search($search, [
            "emp.employee_id",
            "emp.first_name_kh",
            "emp.last_name_kh",
            "emp.first_name_en",
            "emp.last_name_en",
            "emp.email",
            "emp.phone1",
            "emp.phone2",
            "s.subject_name",
            "d.department_name"
        ])
        ->groupBy("emp.employee_id")
        ->limit($limit, $offset)
        ->get();

    $cache->set($cacheKey, $data, 120);
}

// =========================
// 📊 COUNT QUERY (NEW OBJECT)
// =========================
$countORM = new ORM($db, "tblEmployees epm", "employee_id");

$countData = $countORM
    ->select("COUNT(*) as total")
    ->join("tblEmployeeSubjects empsub", "epm.employee_id = empsub.employee_id")
    ->join("tblDepartments d", "epm.department_id = d.department_id")
    ->join("tblSubjects s", "empsub.subject_id = s.subject_id")
    ->where('d.department_name', '=', 'Teacher')
    ->search($search, [
        "epm.employee_id",
        "epm.first_name_kh",
        "epm.last_name_kh",
        "epm.first_name_en",
        "epm.last_name_en",
        "epm.email",
        "epm.phone1",
        "epm.phone2",
        "s.subject_name",
        "d.department_name"
    ])
    ->first();


$totalTeachers = $countData['total'] ?? 0;
$totalPages = ceil($totalTeachers / $limit);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher | <?php echo $infoSchemaData[1]["name_short"] ?></title>
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

</head>

<body class="container-fluid p-0 overflow-x-hidden">
    <div class="row g-3">

        <?php Navbar($infoSchemaData, $routeAdmin); ?>


        <!-- Main area -->
        <main class="col-lg-10 bg-light">
            <div
                class="d-flex justify-content-between align-items-center px-2 py-2 bg-white position-sticky top-0 z-3">
                <div class="title">Welcome to <?php echo $infoSchemaData[0]["name"] ?></div>

                <div class="dropdown">
                    <button id="account" class="d-flex align-items-center border-0 bg-white gap-2" data-bs-toggle="dropdown">
                        <img id="profileImg" width="60" height="60" style="border-radius:50%">
                        <div id="username"></div>
                    </button>


                    <ul class="dropdown-menu bg-white">
                        <a href="../auth/signout.php" class="text-decoration-none">
                            <li><button class="dropdown-item">Sign Out</button></li>
                            <li><button class="dropdown-item">Account</button></li>
                        </a>
                    </ul>
                </div>
            </div>

            <div class="p-3">
                <div hidden style="background: #006d9c;
background: linear-gradient(139deg, rgba(0, 109, 156, 1) 32%, rgba(0, 109, 156, 1) 50%, rgba(101, 155, 90, 1) 61%, rgba(128, 164, 74, 1) 63%, rgba(154, 173, 59, 1) 66%, rgba(255, 208, 0, 1) 70%, rgba(255, 208, 0, 1) 100%);"
                    class="w-100 bg-gradient-custom py-3 px-4 rounded">
                    <div class="d-flex justify-content-between text-white">
                        <div class="">
                            <h3 class="mb-4"><?php echo $infoSchemaData[0]["name"] ?></h3>
                            <div>
                                <div>
                                    <i
                                        class="bi bi-envelope-fill me-1 mb-0"></i><?php echo $infoSchemaData[1]["email"] ?>
                                </div>
                                <div>
                                    <i
                                        class="bi bi-geo-alt-fill me-1 mb-0"></i><?php echo $infoSchemaData[2]["address"] ?>
                                </div>
                                <div>
                                    <i
                                        class="bi bi-telephone-fill me-1 mb-0"></i><?php echo $infoSchemaData[3]["phone"] ?>
                                </div>
                            </div>
                        </div>
                        <img style="width: 135px; border-radius: 50%;" src="<?php echo $infoSchemaData[5]["image"] ?>"
                            alt="" srcset="" class="h-100">
                    </div>
                </div>

                <div class="container-lg container-md container-sm p-0 mt-3">
                    <div class="w-100 bg-white shadow px-4 py-3 rounded">

                        <div class="d-flex justify-content-between align-items-center fw-semibold mb-3">

                            <!-- Left -->
                            <div class="d-flex align-items-center gap-3 w-50">
                                <div class="fw-semibold w-25">
                                    <i class="bi bi-credit-card-fill me-1"></i>Teachers List
                                </div>

                                <form method="GET" class="d-flex mb-0 w-75" autocomplete="off">
                                    <input type="text" name="search"
                                        class="form-control me-2"
                                        placeholder="Search teacher..."
                                        value="<?= htmlspecialchars($search) ?>">
                                    <!-- <button type="submit" class="btn btn-primary">Search</button> -->
                                </form>
                            </div>

                            <!-- Right -->
                            <div class="d-flex gap-2">
                                <button id="editBtn" class="btn btn-primary " disabled>Edit</button>
                                <button id="detailBtn" class="btn btn-primary " disabled>Detail</button>
                                <button class="btn btn-primary "
                                    onclick="window.location.href='../register?type=staff'">
                                    Add
                                </button>
                            </div>

                        </div>

                        <!-- Employee Table -->
                        <div style="min-height: 600px;" class="table-scroll modelBox ps-3">
                            <table class="table table-hover table-custom mb-0">
                                <thead class="head-custom">
                                    <tr class="headLabel">
                                        <th class="col-1">ID</th>
                                        <th class="col-2">Name Kh</th>
                                        <th class="col-2">Name Eng</th>
                                        <th style="min-width: 100px;">Birth</th>
                                        <th class="col-1">Gender</th>
                                        <th class="col-2">Email</th>
                                        <th class="col-2">Position</th>
                                        <th class="col-3">Subjects</th>
                                        <th class="col-3">Phone</th>
                                    </tr>
                                </thead>
                                <tbody id="teacherTable" class="text-lg-start fs-08">
                                    <?php if (!empty($data)): ?>
                                        <?php foreach ($data as $row): ?>
                                            <tr data-id="<?= $row['employee_id'] ?>">
                                                <td><?= htmlspecialchars($row['employee_id'] ?? 'N/A') ?></td>

                                                <td>
                                                    <?= !empty($row['first_name_kh'] && $row['last_name_kh'])
                                                        ? htmlspecialchars($row['first_name_kh']) . ' ' . htmlspecialchars($row['last_name_kh'])
                                                        : 'N/A' ?>
                                                </td>

                                                <td>
                                                    <?= !empty($row['first_name_en'] && $row['last_name_en'])
                                                        ? htmlspecialchars($row['first_name_en']) . ' ' . htmlspecialchars($row['last_name_en'])
                                                        : 'N/A' ?>
                                                </td>

                                                <td><?= htmlspecialchars($row['dob'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($row['gender'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($row['email'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($row['department_name'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($row['subjects'] ?? 'N/A') ?></td>
                                                <td>
                                                    <?= !empty($row['phone1'])
                                                        ? htmlspecialchars($row['phone1']) .
                                                        (!empty($row['phone2']) ? ' ' . htmlspecialchars($row['phone2']) : '')
                                                        : 'N/A' ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">No records found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center align-items-center mt-3 gap-2">
                            <form method="GET" class="d-flex gap-2">
                                <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">

                                <!-- Prev -->
                                <?php if ($page > 1): ?>
                                    <button type="submit" name="page" value="<?= $page - 1 ?>" class="btn btn-outline-primary">
                                        ⬅ Prev
                                    </button>
                                <?php endif; ?>

                                <!-- Page Numbers (limit display) -->
                                <?php
                                $start = max(1, $page - 2);
                                $end = min($totalPages, $page + 2);
                                ?>

                                <?php for ($i = $start; $i <= $end; $i++): ?>
                                    <button type="submit" name="page" value="<?= $i ?>"
                                        class="btn <?= $i == $page ? 'btn-primary' : 'btn-outline-primary' ?>">
                                        <?= $i ?>
                                    </button>
                                <?php endfor; ?>

                                <!-- Next -->
                                <?php if ($page < $totalPages): ?>
                                    <button type="submit" name="page" value="<?= $page + 1 ?>" class="btn btn-outline-primary">
                                        Next ➡
                                    </button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>


            <script>
                 fetch("http://localhost/system-management/api/v1/users.php", {
                    credentials: "include"
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.querySelector("#username").innerText = data.data.username;

                        const profileImg = data.data.profile_image ?
                            "/system-management/uploads/photos/" + data.data.profile_image :
                            "/system-management/src/assets/default-user.png";

                        document.querySelector("#profileImg").src = profileImg;
                    } else {
                        console.log("Failed:", data);
                    }
                });

                document.getElementById("searchTeacher").addEventListener("keyup", function() {
                    let search = this.value;

                    fetch("/System-Management/ajax/search_teachers.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: "search=" + encodeURIComponent(search)
                        })
                        .then(res => res.text())
                        .then(data => {
                            document.getElementById("teacherTable").innerHTML = data;

                            selectedId = null;
                            editBtn.disabled = true;
                            detailBtn.disabled = true;
                        });
                });
            </script>
            <script>
                let selectedId = null;

                const tableBody = document.getElementById("teacherTable");
                const editBtn = document.getElementById("editBtn");
                const detailBtn = document.getElementById("detailBtn");

                tableBody.addEventListener("click", function(e) {
                    const row = e.target.closest("tr");
                    if (!row) return;
                    console.dir(e.target);
                    console.dir(e.target.closest);

                    // Remove highlight from all rows
                    document.querySelectorAll("#teacherTable tr")
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
                    window.location.href = "edit?type=teacher&id=" + selectedId;
                });

                detailBtn.addEventListener("click", () => {
                    if (!selectedId) return;
                    window.location.href = "detail?type=teacher&id=" + selectedId;
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
</body>

</html>