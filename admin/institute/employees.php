<?php
// ./admin/institute/employees.php
date_default_timezone_set('Asia/Phnom_Penh');

include_once __DIR__ . '/../../config/db.php';
include_once __DIR__ . '/../../core/DB.php';
include_once __DIR__ . '/../../core/CRUD.php';
include_once __DIR__ . '/../../core/ORM.php';
include_once __DIR__ . '/../../core/Cache.php';
include_once __DIR__ . '/../../core/Logger.php';
require_once __DIR__ . '/../../auth/auth.php';
include_once __DIR__ . '/../../data/dbSchemaData.php';
require_once __DIR__ . '/../../data/dataSchema.php';
include_once __DIR__ . '/../../components/Navbar.php';





$userId = checkAuth();

if (!$userId) {
    header("Location: ../auth/signin.php");
    exit;
}

authorizeRole('admin');

// Set active menu
$routeAdmin[0]["active"] = false;
$routeAdmin[1]["active"] = true;
$routeAdmin[1]['submenu'][0]['active'] = true;

// Pagination & search
$limit = 18;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page  = max($page, 1);
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
// work with ORM.php

$db = new DB($conn);
$cache = new Cache();
$log = new Logger();

// =========================
// 📄 DATA QUERY
// =========================
$employees = new ORM($db, "tblEmployees e");

$cacheKey = "employees_list_{$search}_{$page}_{$limit}";

$data = $cache->get($cacheKey, 120);

if ($data === null) {

    $data = $employees
        // ->cache(120)
        ->select("e.*, d.department_name")
        ->join("tblDepartments d", "e.department_id = d.department_id")
        ->search($search, [
            "e.employee_id",
            "e.first_name_kh",
            "e.last_name_kh",
            "e.first_name_en",
            "e.last_name_en",
            "e.email",
            "e.phone1",
            "e.phone2",
            "d.department_name"
        ])
        ->limit($limit, $offset)
        ->get();

    $cache->set($cacheKey, $data, 120);
}

// =========================
// 📊 COUNT QUERY (NEW OBJECT)
// =========================
$countORM = new ORM($db, "tblEmployees e");

$countData = $countORM
    // ->cache(300)
    ->select("COUNT(*) as total")
    ->join("tblDepartments d", "e.department_id = d.department_id")
    ->search($search, [
        "e.employee_id",
        "e.first_name_kh",
        "e.last_name_kh",
        "e.first_name_en",
        "e.last_name_en",
        "e.email",
        "e.phone1",
        "e.phone2",
        "d.department_name"
    ])
    ->get();

$totalEmployees = $countData['total'] ?? 0;
$totalPages = ceil($totalEmployees / $limit);


$log->info("Employees page loaded");

$log->error("Database connection failed");

$log->security("Failed login attempt", [
    "user" => $_SESSION['user_id'] ?? null,
    "ip" => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    "agent" => $_SERVER['HTTP_USER_AGENT'] ?? null,
    "time" => date('H:i:s')
]);

$log->cache("CACHE HIT: employees_list", [
    "key" => $cacheKey
]);

$log->sql("SELECT * FROM tblEmployees WHERE id = ?", [10]);


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employees | <?= htmlspecialchars($infoSchemaData[1]["name_short"]) ?></title>
    <link rel="icon" type="image/png" href="<?= $infoSchemaData[5]["image"] ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.13.1/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="../../src/style.css">
    <style>
        .table-custom th:nth-child(1),
        .table-custom td:nth-child(1) {
            width: 80px;
        }

        .table-custom th:nth-child(2),
        .table-custom td:nth-child(2) {
            width: 150px;
        }

        .table-custom th:nth-child(3),
        .table-custom td:nth-child(3) {
            width: 150px;
        }

        .table-custom th:nth-child(4),
        .table-custom td:nth-child(4) {
            width: 120px;
        }

        .table-custom th:nth-child(5),
        .table-custom td:nth-child(5) {
            width: 80px;
        }

        .table-custom th:nth-child(6),
        .table-custom td:nth-child(6) {
            width: 200px;
        }

        .table-custom th:nth-child(7),
        .table-custom td:nth-child(7) {
            width: 150px;
        }

        .table-custom th:nth-child(8),
        .table-custom td:nth-child(8) {
            width: 180px;
        }
    </style>
</head>

<body class="container-fluid p-0 overflow-x-hidden">
    <div class="row g-3">
        <!-- Sidebar -->
        <?php Navbar($infoSchemaData, $routeAdmin); ?>

        <!-- Main content -->
        <main class="col-10 bg-light">
            <div class="d-flex justify-content-between align-items-center px-2 py-2 bg-white position-sticky top-0 z-3">
                <div class="title">Welcome to <?= htmlspecialchars($infoSchemaData[0]["name"]) ?></div>
                <div class="dropdown">
                    <button id="account" class="d-flex align-items-center border-0 bg-white gap-2" data-bs-toggle="dropdown">
                        <img id="profileImg" width="60" height="60" style="border-radius:50%">
                        <div id="username"></div>
                    </button>
                    <ul class="dropdown-menu bg-white">
                        <li><a href="../auth/signout.php" class="dropdown-item">Sign Out</a></li>
                        <li><a href="#" class="dropdown-item">Account</a></li>
                    </ul>
                </div>
            </div>

            <div class="container-lg p-3">
                <div class="w-100 bg-white shadow px-4 py-3 rounded">

                    <div class="d-flex justify-content-between align-items-center fw-semibold mb-3">

                        <!-- Left -->
                        <div class="d-flex align-items-center gap-3 w-50">
                            <div class="fw-semibold w-25">
                                <i class="bi bi-credit-card-fill me-1"></i>Employees List
                            </div>

                            <form method="GET" class="d-flex mb-0 w-75" autocomplete="off">
                                <input type="text" name="search"
                                    class="form-control me-2"
                                    placeholder="Search employee..."
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
                                    <th class="col-1">EmpID</th>
                                    <th class="col-2">NameKh</th>
                                    <th class="col-2">NameEng</th>
                                    <th class="col-1">DateOfBirth</th>
                                    <th class="col-1">Gender</th>
                                    <th class="col-3">Email</th>
                                    <th class="col-2">Position</th>
                                    <th class="col-3">Phone</th>
                                </tr>
                            </thead>
                            <tbody id="employeeTable" class="text-lg-start fs-08">
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
        </main>
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


        let selectedId = null;
        const tableBody = document.getElementById("employeeTable");
        const editBtn = document.getElementById("editBtn");
        const detailBtn = document.getElementById("detailBtn");

        tableBody.addEventListener("click", function(e) {
            const row = e.target.closest("tr");
            if (!row) return;
            document.querySelectorAll("#employeeTable tr").forEach(r => r.classList.remove("table-active"));
            row.classList.add("table-active");
            selectedId = row.dataset.id;
            editBtn.disabled = false;
            detailBtn.disabled = false;
        });

        editBtn.addEventListener("click", () => {
            if (!selectedId) return;
            window.location.href = "edit?type=employee&id=" + selectedId;
        });

        detailBtn.addEventListener("click", () => {
            if (!selectedId) return;
            window.location.href = "detail?type=employee&id=" + selectedId;
        });

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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>