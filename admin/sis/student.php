<?php
//./admin/institute/student.php
// require_once( __DIR__ . "/../../config/db.php");
include_once __DIR__ . '/../../config/bootstrap.php';
include_once __DIR__ . '/../../data/dataSchema.php';
include_once __DIR__ . '/../../components/Navbar.php';





// include_once "/config/db.php";
$routeAdmin[0]["active"] = false;
$routeAdmin[2]["active"] = true;
$routeAdmin[2]['submenu'][0]['active'] = true;

$userId = checkAuth();
if (!$userId) {
    header("Location: ../auth/signin.php");
    exit;
}
authorizeRole('admin');

$db = new DB($conn);
$cache = new Cache();

$studentCRUD = new ORM($db, "tblStudents s");

// Pagination & search FIRST
$limit = 18;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page  = max($page, 1);
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';


$cacheKey = "student_list_{$search}_{$page}_{$limit}";

$data = $cache->get($cacheKey);

if ($data === null) {

    $data = $studentCRUD
        ->search($search, [
            "s.student_id",
            "s.first_name_kh",
            "s.last_name_kh",
            "s.first_name_en",
            "s.last_name_en",
            "s.email",
            "s.phone1",
            "s.phone2"
        ])
        ->limit($limit)
        ->offset($offset)
        ->get();

    $cache->set($cacheKey, $data, 120);
}

/*
|--------------------------------------------------------------------------
| COUNT QUERY
|--------------------------------------------------------------------------
*/

$countCacheKey = "student_count_{$search}";

$totalStudents = $cache->get($countCacheKey);

if ($totalStudents === null) {

    $countORM = new ORM($db, "tblStudents s");

    $countData = $countORM
        ->select("COUNT(*) as total")
        ->search($search, [
            "s.student_id",
            "s.first_name_kh",
            "s.last_name_kh",
            "s.first_name_en",
            "s.last_name_en",
            "s.email",
            "s.phone1",
            "s.phone2"
        ])
        ->first();

    $totalStudents = $countData['total'] ?? 0;

    $cache->set($countCacheKey, $totalStudents, 120);
}
$totalPages = ceil($totalStudents / $limit);


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students | <?php echo $infoSchemaData[1]["name_short"] ?></title>
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
    <style>
        .table-custom th:nth-child(1),
        .table-custom td:nth-child(1) {
            width: 80px;
        }

        .table-custom th:nth-child(2),
        .table-custom td:nth-child(2) {
            width: 90px;
        }

        .table-custom th:nth-child(3),
        .table-custom td:nth-child(3) {
            width: 110px;
        }

        .table-custom th:nth-child(4),
        .table-custom td:nth-child(4) {
            width: 30px;
        }

        .table-custom th:nth-child(5),
        .table-custom td:nth-child(5) {
            width: 80px;
        }

        .table-custom th:nth-child(6),
        .table-custom td:nth-child(6) {
            width: 150px;
        }

        .table-custom th:nth-child(7),
        .table-custom td:nth-child(7) {
            width: 250px;
        }
    </style>
</head>

<body class="container-fluid p-0 overflow-x-hidden">
    <div class="row g-3">


        <?php Navbar($infoSchemaData, $routeAdmin); ?>


        <!-- Main area -->
        <main class="col-lg-10 col-sm-12 bg-light">
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

            <div class="container-lg container-md container-sm p-3">
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
                            <div>
                                <img style="width: 135px; border-radius: 50%;" src="<?php echo $infoSchemaData[4]["image"] ?>"
                                    alt="" srcset="" class="h-100">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-100 d-flex mt-3 gap-3 flex-wrap">
                    <div class="w-100 bg-white shadow px-4 py-3 rounded">
                        <div class="d-flex justify-content-between align-items-center fw-semibold mb-2">
                            <div class="d-flex w-50 align-items-center justify-content-between gap-3 mb-3">
                                <div class="fw-semibold">
                                    <i class="bi bi-credit-card-fill me-1"></i>
                                    Student List
                                </div>

                                <form method="GET" class="d-flex mb-0 w-75" autocomplete="off">
                                    <input type="text" name="search"
                                        class="form-control me-2"
                                        placeholder="Search student..."
                                        value="<?= htmlspecialchars($search) ?>">
                                    <!-- <button type="submit" class="btn btn-primary">Search</button> -->
                                </form>

                            </div>
                            <div>
                                <button id="editBtn" class="btn btn-primary" disabled>
                                    Edit
                                </button>
                                <button id="detailBtn" class="btn btn-primary" disabled>
                                    Detail
                                </button>
                                <button class="btn btn-primary z-1 px-3" onclick="window.location.href='../register?type=student'">
                                    Add
                                </button>
                                <button id="newEnrollmentBtn" class="btn btn-primary z-1 px-3" disabled>
                                    New Enrollemt
                                </button>
                            </div>
                            <!-- nav student lable -->
                        </div>

                        <!-- SCROLL CONTAINER -->
                        <div style="min-height: 600px;" class="table-scroll modelBox ps-3">
                            <table class="table table-hover table-custom mb-0">
                                <thead class="head-custom">
                                    <tr class="headLabel">
                                        <th class="col-sm-1">ID</th>
                                        <th class="col-sm-2">NameKh</th>
                                        <th class="col-sm-2">NameEng</th>
                                        <th class="col-sm-1">Gender</th>
                                        <th class="col-sm-1">DateOfBirth</th>
                                        <th class="col-sm-2">Phone</th>
                                        <th class="col-sm-4">Address</th>
                                    </tr>
                                </thead>
                                <tbody id="studentTable">
                                    <?php if (!empty($data)): ?>
                                        <?php foreach ($data as $row): ?>
                                            <tr data-id="<?= $row['student_id'] ?>">
                                                <td><?= htmlspecialchars($row['student_id'] ?? 'N/A') ?></td>

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

                                                <td><?= htmlspecialchars($row['gender'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($row['dob'] ?? 'N/A') ?></td>
                                                <td>
                                                    <?= !empty($row['phone1'])
                                                        ? htmlspecialchars($row['phone1']) .
                                                        (!empty($row['phone2']) ? ', ' . htmlspecialchars($row['phone2']) : '')
                                                        : 'N/A' ?>
                                                </td>
                                                <td>
                                                    <?= !empty($row['curr_addr_village'] && $row['curr_addr_village'])
                                                        ? htmlspecialchars($row['curr_addr_village']) . ', ' . htmlspecialchars($row['curr_addr_commune']) . ', ' . htmlspecialchars($row['curr_addr_province'])
                                                        : 'N/A' ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">No records found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center align-items-center mt-3 gap-2">

                            <form method="GET" class="d-flex gap-2 ">

                                <!-- Preserve search -->
                                <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">

                                <!-- Previous -->
                                <?php if ($page > 1): ?>
                                    <button type="submit" name="page" value="<?= $page - 1 ?>" class="btn btn-outline-primary">
                                        ⬅ Prev
                                    </button>
                                <?php endif; ?>

                                <!-- Page numbers -->
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
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

        const tableBody = document.getElementById("studentTable");
        const newEnrollmentBtn = document.getElementById("newEnrollmentBtn");
        const editBtn = document.getElementById("editBtn");
        const detailBtn = document.getElementById("detailBtn");

        tableBody.addEventListener("click", function(e) {
            const row = e.target.closest("tr");
            if (!row) return;
            console.dir(e.target);
            console.dir(e.target.closest);

            // Remove highlight from all rows
            document.querySelectorAll("#studentTable tr")
                .forEach(r => r.classList.remove("table-active"));

            // Highlight clicked row
            row.classList.add("table-active");

            // Store ID
            selectedId = row.dataset.id;

            // Enable buttons
            newEnrollmentBtn.disabled = false;
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
        // Update enrollment button URL
        newEnrollmentBtn.addEventListener("click", () => {
            if (!selectedId) return;
            window.location.href = "../enrollment/add?id=" + selectedId;
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Select all collapse elements
            const collapses = document.querySelectorAll('.collapse');

            collapses.forEach(collapse => {
                collapse.addEventListener('show.bs.collapse', function() {
                    // Set the clicked submenu icon to chevron-down
                    const icon = document.querySelector(`a[href="#${collapse.id}"] .submenu-icon`);
                    if (icon) icon.classList.replace('bi-chevron-left', 'bi-chevron-down');

                    // Set all other submenu icons to chevron-left
                    collapses.forEach(other => {
                        if (other.id !== collapse.id) {
                            const otherIcon = document.querySelector(`a[href="#${other.id}"] .submenu-icon`);
                            if (otherIcon) otherIcon.classList.replace('bi-chevron-down', 'bi-chevron-left');
                        }
                    });
                });

                collapse.addEventListener('hide.bs.collapse', function() {
                    const icon = document.querySelector(`a[href="#${collapse.id}"] .submenu-icon`);
                    if (icon) icon.classList.replace('bi-chevron-down', 'bi-chevron-left');
                });
            });
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