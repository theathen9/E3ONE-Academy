<?php
session_start();
//./admin/institute/department.php
// require_once( __DIR__ . "/../../config/db.php");
// include_once __DIR__ . '/../../config/db.php';
include_once __DIR__ . '/../../config/bootstrap.php';
// include_once __DIR__ . '/../../core/CRUD.php';
require_once __DIR__ . '/../../data/dataSchema.php';
require_once __DIR__ . '/../../core/logger.php';
include_once __DIR__ . '/../../components/Navbar.php';




// echo '<pre>';
// print_r($_POST);
// exit;

$userId = checkAuth();

if (!$userId) {
    header("Location: ../auth/signin.php");
    exit;
}

authorizeRole('admin');

// include_once "/config/db.php";
$routeAdmin[0]["active"] = false;
$routeAdmin[1]["active"] = true;
$routeAdmin[1]['submenu'][1]['active'] = true;

$db = new DB($conn);
$cache = new Cache();
$log = new Logger();
$departmentCRUD = new ORM($db, "tblDepartments", "department_id");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    $id = (int)($_POST['department_id'] ?? 0);

    // ---------------- STATUS TOGGLE ----------------
    if ($action === 'status' && $id > 0) {

        $statusORM = new ORM($db, "tblDepartments", "department_id");

        $row = $statusORM
            ->where("department_id", "=", $id)
            ->first();

        if ($row) {

            $newStatus = ((int)$row['status'] === 1) ? 0 : 1;

            $updateORM = new ORM($db, "tblDepartments", "department_id");

            $updateORM
                ->where("department_id", "=", $id)
                ->update([
                    "status" => $newStatus,
                    "updated_at" => date("Y-m-d H:i:s")
                ]);

            $cache->clearByPrefix('department_list_');

            $_SESSION['success'] =
                $newStatus === 1
                ? "Department activated successfully!"
                : "Department deactivated successfully!";
        } else {

            $_SESSION['error'] = "Department not found!";
        }

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // ---------------- DELETE ----------------
    if ($action === 'delete' && $id > 0) {

        $departmentCRUD->where("department_id", "=", $id)->delete($id);

        $_SESSION['success'] = "Department deleted!";
    }

    // ---------------- ADD ----------------
    if ($action === 'add') {

        $departmentCRUD->insert([
            "department_code" => $_POST['department_code'],
            "department_name" => $_POST['department_name'],
            "description" => $_POST['description'],
            "created_at" => $_POST['created_at']
        ]);
        $cache->clearByPrefix('department_list_');

        $_SESSION['success'] = "Department added!";
    }

    // ---------------- UPDATE ----------------
    if ($action === 'update' && $id > 0) {

        $departmentCRUD->where("department_id", "=", $id)->update([
            "department_code" => $_POST['department_code'],
            "department_name" => $_POST['department_name'],
            "description" => $_POST['description'],
            "status" => $_POST['status'] ?? 1,
            "updated_at" => date("Y-m-d H:i:s")
        ]);

        $_SESSION['success'] = "Department updated!";
    }

    $cache->clearByPrefix('department_list_');

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}




$limit = 18;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $limit;


$search = isset($_GET['search']) ? trim($_GET['search']) : '';



// =========================
// 📄 DATA QUERY
// =========================

$cacheKey = "department_list_{$search}_{$page}_{$limit}";

$data = $cache->get($cacheKey, 120);

if ($data === null) {

    $data = $departmentCRUD
        // ->cache(120)
        ->select("*")
        ->search($search, [
            "department_code",
            "department_name",
            "description"
        ])
        ->limit($limit, $offset)
        ->get();

    $cache->set($cacheKey, $data, 120);
}

// =========================
// 📊 COUNT QUERY (NEW OBJECT)
// =========================
$countORM = new ORM($db, "tblDepartments", "department_id");

$countData = $countORM
    ->select("COUNT(*) as total")
    ->search($search, [
        "department_code",
        "department_name",
        "description"
    ])
    ->first();

$totalDepartments = $countData['total'] ?? 0;

$totalPages = ceil($totalDepartments / $limit);


$log->info("Departments page loaded");

$log->error("Database connection failed");

$log->security("Failed login attempt", [
    "user" => $_SESSION['user_id'] ?? null,
    "ip" => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    "agent" => $_SERVER['HTTP_USER_AGENT'] ?? null,
    "time" => date('H:i:s')
]);

$log->cache("CACHE HIT: department_list", [
    "key" => $cacheKey
]);

$log->sql("SELECT * FROM tblDepartments WHERE department_id = ?", [10]);


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department | <?php echo $infoSchemaData[1]["name_short"] ?></title>
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

</head>

<body class="container-fluid p-0 overflow-x-hidden">
    <div class="row g-3">

        <?php Navbar($infoSchemaData, $routeAdmin); ?>


        <!-- Main area -->
        <main class="col-10 col-lg-10 bg-light">
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

            <div class="container-lg container-md container-sm p-3 m-0">
                <div style="background: #006d9c;
background: linear-gradient(139deg, rgba(0, 109, 156, 1) 32%, rgba(0, 109, 156, 1) 50%, rgba(101, 155, 90, 1) 61%, rgba(128, 164, 74, 1) 63%, rgba(154, 173, 59, 1) 66%, rgba(255, 208, 0, 1) 70%, rgba(255, 208, 0, 1) 100%);"
                    class="w-100 bg-gradient-custom py-3 px-4 rounded">
                    <div class="d-flex justify-content-between text-white">
                        <div class="">
                            <h3 class="mb-4"><?php echo $infoSchemaData[0]["name"] ?></h3>
                            <div>
                                <div>
                                    <i
                                        class="bi bi-envelope-fill me-1 mb-0"></i><?php echo $infoSchemaData[2]["email"] ?>
                                </div>
                                <div>
                                    <i
                                        class="bi bi-geo-alt-fill me-1 mb-0"></i><?php echo $infoSchemaData[3]["address"] ?>
                                </div>
                                <div>
                                    <i
                                        class="bi bi-telephone-fill me-1 mb-0"></i><?php echo $infoSchemaData[4]["phone"] ?>
                                </div>
                            </div>
                        </div>
                        <img style="width: 135px; border-radius: 50%;" src="<?php echo $infoSchemaData[5]["image"] ?>"
                            alt="" srcset="" class="h-100">
                    </div>
                </div>
                <div class="w-100 d-flex mt-3 justify-content-between gap-3 flex-wrap">
                    <div class="w-100 bg-white shadow px-4 py-3 rounded">
                        <div class="d-flex justify-content-between align-items-center fw-semibold mb-2">
                            <div class="d-flex w-50 align-items-center justify-content-between gap-3 mb-3">
                                <div class="fw-semibold w-25">
                                    <i class="bi bi-credit-card-fill me-1"></i>
                                    Department List
                                </div>
                                <div class="w-75">
                                    <form method="GET" class="d-flex mb-0 w-75" autocomplete="off">
                                        <input type="text" name="search"
                                            class="form-control me-2"
                                            placeholder="Search employee..."
                                            value="<?= htmlspecialchars($search) ?>">
                                        <!-- <button type="submit" class="btn btn-primary">Search</button> -->
                                    </form>
                                </div>
                            </div>
                            <div>

                                <button id="editBtn" style="width: 99px;" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editDepartmentModal">
                                    Edit
                                </button>
                                <button hidden id="deleteBtn" style="width: 99px;" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#deleteDepartmentModal">
                                    Delete
                                </button>
                                <button id="statusBtn" style="width: 99px;" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#statusDepartmentModal">
                                    Status
                                </button>
                                <button style="width: 99px;" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#departmentModal">
                                    Add
                                </button>
                            </div>
                            <div class="modal fade" id="editDepartmentModal" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-xl">
                                    <div class="modal-content px-3">
                                        <form id="departmentForm" method="POST">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="department_id" id="edit_department_id">


                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Department</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>

                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Department Code</label>
                                                    <input type="text" class="form-control" name="department_code" id="edit_department_code" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Department Name</label>
                                                    <input type="text" class="form-control" name="department_name" id="edit_department_name" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Description</label>
                                                    <input type="text" class="form-control" name="description" id="edit_description" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">date update</label>
                                                    <input type="date" class="form-control" name="updated_at" id="edit_created_at" required>
                                                </div>
                                            </div>

                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">
                                                    Cancel
                                                </button>
                                                <button type="submit" class="btn btn-success">
                                                    Save
                                                </button>
                                            </div>
                                        </form>

                                    </div>
                                </div>
                            </div>

                            <!-- update status -->
                            <div class=" modal fade" id="statusDepartmentModal" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-xl">
                                    <div class=" modal-content px-3 ">
                                        <form id="departmentForm" method="POST">
                                            <input type="hidden" name="department_id" id="status_department_id">
                                            <input type="hidden" name="action" value="status">

                                            <div class="modal-header">
                                                <h5 class="modal-title">status Department</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>

                                            <div id="statusText">

                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">
                                                    Cancel
                                                </button>
                                                <button type="submit" class="btn btn-success">
                                                    Submit
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Delete -->
                            <div class=" modal fade d-none" id="deleteDepartmentModal" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-xl">
                                    <div class=" modal-content px-3 ">
                                        <form id="departmentForm" method="POST">
                                            <input type="hidden" name="department_id" id="delete_department_id">
                                            <input type="hidden" name="action" value="delete">

                                            <div class="modal-header">
                                                <h5 class="modal-title">Delete Department</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>

                                            <div id="deleteText">

                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">
                                                    Cancel
                                                </button>
                                                <button type="submit" class="btn btn-success">
                                                    Delete
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>



                            <!--  -->
                            <div class="modal fade" id="departmentModal" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-xl">
                                    <div class="modal-content px-3">
                                        <form id="departmentForm" method="POST">
                                            <input type="hidden" name="action" value="add">

                                            <div class="modal-header">
                                                <h5 class="modal-title">Add Department</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>

                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Department Code</label>
                                                    <input type="text" class="form-control" name="department_code" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Department Name</label>
                                                    <input type="text" class="form-control" name="department_name" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Description</label>
                                                    <input type="text" class="form-control" name="description">
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Create At</label>
                                                    <input type="date" class="form-control" name="created_at" required>
                                                </div>
                                            </div>

                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">
                                                    Cancel
                                                </button>
                                                <button type="submit" class="btn btn-success">
                                                    Save
                                                </button>
                                            </div>
                                        </form>

                                    </div>
                                </div>
                            </div>

                            <!-- nav student lable -->
                        </div>

                        <!-- SCROLL CONTAINER -->
                        <div style="min-height: 600px;" class="table-scroll modelBox ps-3 w-100">
                            <table class="table  table-hover mb-0">
                                <thead class="head-custom w-100">
                                    <tr class="headLabel">
                                        <th class="col-1">DepmCode</th>
                                        <th class="col-2">depmName</th>
                                        <th class="col-4">Description</th>
                                        <th class="col-2">created_at</th>
                                        <th class="col-2">updated_at</th>
                                        <th class="col-1">status</th>
                                    </tr>
                                </thead>
                                <tbody id="departmentTable">

                                    <?php if (!empty($data)): ?>
                                        <?php foreach ($data as $row): ?>
                                            <tr data-id="<?= $row['department_id'] ?>"
                                                data-code="<?= htmlspecialchars($row['department_code']) ?>"
                                                data-name="<?= htmlspecialchars($row['department_name']) ?>"
                                                data-description="<?= !empty($row['description']) ? htmlspecialchars($row['description']) : 'N/A' ?>"
                                                data-status="<?= $row['status'] ?>"
                                                data-updated="<?= htmlspecialchars($row['updated_at']) ?>"
                                                data-created="<?= date('Y-m-d', strtotime($row['created_at'])) ?>">

                                                <td><?= htmlspecialchars($row['department_code']) ?></td>
                                                <td><?= htmlspecialchars($row['department_name']) ?></td>
                                                <td><?= !empty($row['description']) ? htmlspecialchars($row['description']) : 'N/A' ?></td>
                                                <td class="col-2"><?= date('Y-m-d', strtotime($row['created_at'])) ?></td>
                                                <td class="col-2"><?= date('Y-m-d', strtotime($row['updated_at'])) ?></td>

                                                <td>
                                                    <?php if ($row['status'] == 1): ?>
                                                        <span class="badge bg-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Inactive</span>
                                                    <?php endif; ?>
                                                </td>

                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="text-center text-muted">
                                                No records found
                                            </td>
                                        </tr>
                                    <?php endif; ?>

                                </tbody>

                            </table>
                        </div>
                        <div class="d-flex justify-content-center align-items-center mt-3 gap-2">
                            <form method="GET" class="d-flex gap-2">

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
    <script src="../../script.js"></script>

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

        let selectedRow = null;
        let selectedId = null;

        // عناصر
        const tableBody = document.getElementById("departmentTable");
        const editBtn = document.getElementById("editBtn");
        const deleteBtn = document.getElementById("deleteBtn");
        const statusBtn = document.getElementById("statusBtn");

        // modal text containers
        const deleteText = document.getElementById("deleteText");
        const statusText = document.getElementById("statusText");

        // default state
        editBtn.disabled = true;
        deleteBtn.disabled = true;
        statusBtn.disabled = true;


        // ================= ROW SELECT =================
        tableBody.addEventListener("click", function(e) {
            const row = e.target.closest("tr");
            if (!row) return;

            // remove highlight
            document.querySelectorAll("#departmentTable tr")
                .forEach(r => r.classList.remove("table-active"));

            // highlight selected
            row.classList.add("table-active");

            // store data
            selectedRow = row;
            selectedId = row.dataset.id;

            // enable buttons
            editBtn.disabled = false;
            deleteBtn.disabled = false;
            statusBtn.disabled = false;

            console.log("Selected ID:", selectedId);
        });


        // ================= EDIT =================
        editBtn.addEventListener("click", () => {
            if (!selectedRow) return;

            document.getElementById("edit_department_id").value = selectedRow.dataset.id;
            document.getElementById("edit_department_code").value = selectedRow.dataset.code;
            document.getElementById("edit_department_name").value = selectedRow.dataset.name;
            document.getElementById("edit_description").value = selectedRow.dataset.description;

            // ❌ REMOVE THIS if input doesn't exist
            // document.getElementById("edit_created_at").value = selectedRow.dataset.created;
        });


        // ================= DELETE =================
        deleteBtn.addEventListener("click", () => {
            if (!selectedRow) return;

            document.getElementById("delete_department_id").value = selectedRow.dataset.id;

            deleteText.innerHTML =
                `Are you sure you want to <strong class='text-danger'>Delete</strong> 
         <strong>${selectedRow.dataset.name}</strong> department?`;
        });


        // ================= STATUS =================
        statusBtn.addEventListener("click", () => {
            if (!selectedRow) return;

            document.getElementById("status_department_id").value =
                selectedRow.dataset.id;

            const currentStatus = selectedRow
                .querySelector("td:last-child span")
                .textContent
                .trim()
                .toLowerCase();

            if (currentStatus === "active") {

                statusText.innerHTML =
                    `Are you sure you want to <strong class='text-danger'>Deactivate</strong> 
         <strong>${selectedRow.dataset.name}</strong> department?`;

            } else {

                statusText.innerHTML =
                    `Are you sure you want to <strong class='text-success'>Activate</strong> 
         <strong>${selectedRow.dataset.name}</strong> department?`;
            }
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