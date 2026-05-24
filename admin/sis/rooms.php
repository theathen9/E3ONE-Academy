<?php
session_start();
//./admin/institute/department.php
include_once __DIR__ . '/../../config/bootstrap.php';
include_once __DIR__ . '/../../data/dataSchema.php';
include_once __DIR__ . '/../../components/Navbar.php';


// echo '<pre>';
// print_r($_POST);
// exit;

$limit = 18;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page  = max($page, 1);
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';


$db = new DB($conn);
$cache = new Cache($db);

$roomCRUD = new ORM($conn, 'tblRooms', 'room_id');

$cacheKey = "rooms_" . $page . "_" . $search;

$data = $cache->get($cacheKey);
if (!$data) {
    $data = $roomCRUD
        ->search($search, [
            "room_name"
        ])
        ->limit($limit)
        ->offset($offset)
        ->get();
    $cache->set($cacheKey, $data, 300);
    $cache->clearByPrefix("rooms_");
}

$countCacheKey = "room_count_{$search}";

$totalRooms = $cache->get($countCacheKey);

if ($totalRooms === null) {

    $countORM = new ORM($db, "tblRooms", "room_id");

    $countData = $countORM
        ->select("COUNT(*) as total")
        ->search($search, [
            "room_name"
        ])
        ->first();

    $totalRooms = $countData['total'] ?? 0;

    $cache->set($countCacheKey, $totalRooms, 120);
}


$totalRoom = $roomCRUD->count($search);
$totalPages = ceil($totalRoom / $limit);

$result = $roomCRUD->get($limit, $offset, $search);




// include_once "/config/db.php";
$routeAdmin[0]["active"] = false;
$routeAdmin[2]["active"] = true;
$routeAdmin[2]['submenu'][1]['active'] = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    // ---------------- DELETE ----------------
    if ($action === 'delete') {
        $id = (int) ($_POST['room_id'] ?? 0);

        if ($id > 0) {
            $roomCRUD->delete($id);
            $_SESSION['success'] = "Room deleted successfully!";
        }

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // ---------------- ADD / UPDATE ----------------
    $room_code = trim($_POST['room_code'] ?? '');
    $room_name = trim($_POST['room_name'] ?? '');
    $description     = trim($_POST['description'] ?? '');

    if ($room_code === '' || $room_name === '') {
        die('❌ Room code and name are required');
    }

    if ($action === 'add') {

        $created_at = trim($_POST['created_at'] ?? date('Y-m-d H:i:s'));

        $data = [
            'room_code' => $room_code,
            'room_name' => $room_name,
            'description'     => $description,
            'created_at'      => $created_at
        ];

        $roomCRUD->insert($data);
        $_SESSION['success'] = "Room added successfully!";
    } elseif ($action === 'update') {

        $id = (int) ($_POST['room_id'] ?? 0);
        if ($id <= 0) die('❌ Missing room ID');
        $updated_at = date('Y-m-d H:i:s');

        $data = [
            'room_code' => $room_code,
            'room_name' => $room_name,
            'description'     => $description,
            'updated_at'      => $updated_at
        ];

        $roomCRUD->update($id, $data);
        $_SESSION['success'] = "Room updated successfully!";
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}










?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room | <?php echo $infoSchemaData[1]["name_short"] ?></title>
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
        <main class="col-lg-10 bg-light">
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

            <div class="container-lg container-md container-sm p-3 m-0">
                <div class="w-100 d-flex mt-3 justify-content-between gap-3 flex-wrap">
                    <div class="w-100 bg-white shadow px-4 py-3 rounded">
                        <div class="d-flex justify-content-between align-items-center fw-semibold mb-2">
                            <div class="d-flex w-50 align-items-center justify-content-between gap-3 mb-3">
                                <div class="fw-semibold w-25">
                                    <i class="bi bi-credit-card-fill me-1"></i>
                                    Rooms List
                                </div>
                                <div class="w-75">
                                    <input type="text"
                                        class="form-control"
                                        placeholder="Search room...">
                                </div>
                            </div>
                            <div>
                                <button id="editBtn" style="width: 99px;" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editRoomModal">
                                    Edit
                                </button>
                                <button id="deleteBtn" style="width: 99px;" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#deleteRoomModal">
                                    Delete
                                </button>
                                <button style="width: 99px;" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#roomModal">
                                    Add
                                </button>
                            </div>
                            <div class="modal fade" id="editRoomModal" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-xl">
                                    <div class="modal-content px-3">
                                        <form id="roomForm" method="POST">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="room_id" id="edit_room_id">


                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Room</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>

                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Room Code</label>
                                                    <input type="text" class="form-control" name="room_code" id="edit_room_code" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Room Name</label>
                                                    <input type="text" class="form-control" name="room_name" id="edit_room_name" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Description</label>
                                                    <input type="text" class="form-control" name="description" id="edit_description" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">date update</label>
                                                    <input type="date" class="form-control" name="updated_at" id="edit_updated_at" required>
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

                            <!--  -->
                            <div class=" modal fade" id="deleteRoomModal" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-xl">
                                    <div class=" modal-content px-3 ">
                                        <form id="roomForm" method="POST">
                                            <input type="hidden" name="room_id" id="delete_room_id">
                                            <input type="hidden" name="action" value="delete">

                                            <div class="modal-header">
                                                <h5 class="modal-title">Delete Room</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>

                                            <div>
                                                Are you sure you want to delete
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
                            <div class="modal fade" id="roomModal" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-xl">
                                    <div class="modal-content px-3">
                                        <form id="roomForm" method="POST">
                                            <input type="hidden" name="action" value="add">

                                            <div class="modal-header">
                                                <h5 class="modal-title">Add Room</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>

                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Room Code</label>
                                                    <input type="text" class="form-control" name="room_code" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Room Name</label>
                                                    <input type="text" class="form-control" name="room_name" required>
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
                                        <th class="col-1">ID</th>
                                        <th class="col-1">Room Name</th>
                                        <th class="col-2">Capacity</th>
                                        <th class="col-1">status</th>
                                    </tr>
                                </thead>
                                <tbody id="roomTable">

                                    <!--  -->
                                    <?php if (!empty($result)): ?>
                                        <?php foreach ($result as $row): ?>
                                            <tr data-id="<?= $row['room_id'] ?>"
                                                data-name="<?= htmlspecialchars($row['room_name']) ?>">
                                                <td><?= htmlspecialchars($row['room_id']) ?></td>
                                                <td><?= htmlspecialchars($row['room_name']) ?></td>
                                                <td><?= htmlspecialchars($row['capacity']) ?></td>
                                                <td><?= htmlspecialchars($row['status']) ?></td>

                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="text-center text-muted">No records found</td>
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

        const tableBody = document.getElementById("roomTable");
        const editBtn = document.getElementById("editBtn");
        const detailBtn = document.getElementById("deleteBtn");

        editBtn.disabled = true;
        detailBtn.disabled = true;

        tableBody.addEventListener("click", function(e) {
            const row = e.target.closest("tr");
            if (!row) return;
            console.dir(e.target);
            console.dir(e.target.closest);

            // Remove highlight from all rows
            document.querySelectorAll("#roomTable tr")
                .forEach(r => r.classList.remove("table-active"));

            // Highlight clicked row
            row.classList.add("table-active");

            // Store ID
            selectedRow = row;
            selectedId = row.dataset.id;

            // Enable buttons
            editBtn.disabled = false;
            detailBtn.disabled = false;

            console.log("Selected ID:", selectedId);
        });

        editBtn.addEventListener("click", () => {
            if (!selectedId) return;
            document.getElementById("edit_room_id").value = selectedRow.dataset.id;
            document.getElementById("edit_room_code").value = selectedRow.dataset.code;
            document.getElementById("edit_room_name").value = selectedRow.dataset.name;
            document.getElementById("edit_description").value = selectedRow.dataset.description;
            document.getElementById("edit_created_at").value = selectedRow.dataset.created;
        });

        detailBtn.addEventListener("click", () => {
            if (!selectedId) return;
            document.getElementById("delete_room_id").value = selectedRow.dataset.id;
            document.getElementById("delete_room_code").value = selectedRow.dataset.code;
            document.getElementById("delete_room_name").value = selectedRow.dataset.name;
            document.getElementById("delete_description").value = selectedRow.dataset.description;
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