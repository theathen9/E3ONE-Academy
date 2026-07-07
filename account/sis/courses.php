<?php
session_start();
//./admin/institute/department.php
// require_once( __DIR__ . "/../../config/db.php");
include_once __DIR__ . '/../../config/bootstrap.php';
include_once __DIR__ . '/../../data/dataSchema.php';
include_once __DIR__ . '/../../components/Navbar.php';
include_once __DIR__ . '/../../components/Avatar.php';


// echo '<pre>';
// print_r($_POST);
// exit;

$db = new DB($conn);
$courseCRUD = new ORM($db, "tblCourses", "course_id");
$cache = new Cache();

$userId = checkAuth();
if (!$userId) {
    header("Location: ../auth/signin.php");
    exit;
}
authorizeRole('accountant');

$routeAccount[0]["active"] = false;
$routeAccount[2]["active"] = true;
$routeAccount[2]['submenu'][1]['active'] = true;


$limit = 18;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page  = max($page, 1);
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';


$cachekey = "courses_list_{$page}_{$search}_{$limit}";

$data = $cache->get($cachekey);

if ($data === null) {
    $data = $courseCRUD
        ->search($search, [
            "course_code",
            "course_name",
            "price",
            "duration"
        ])
        ->limit($limit)
        ->offset($offset)
        ->get();

    $cache->set($cachekey, $data, 300);
}

$countCacheKey = "course_count_{$search}";

$totalCourses = $cache->get($countCacheKey);

if ($totalCourses === null) {

    $countORM = new ORM($db, "tblCourses", "course_id");

    $countData = $countORM
        ->select("COUNT(*) as total")
        ->search($search, [
            "course_code",
            "course_name",
            "price",
            "duration"
        ])
        ->first();

    $totalCourses = $countData['total'] ?? 0;

    $cache->set($countCacheKey, $totalCourses, 120);
}


$totalCourse = $courseCRUD->count($search);
$totalPages = ceil($totalCourse / $limit);

$result = $courseCRUD->get($limit, $offset, $search);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    try {

        switch ($action) {

            // =========================
            // DELETE
            // =========================
            case 'delete':

                $id = (int) ($_POST['course_id'] ?? 0);

                if ($id <= 0) {
                    throw new Exception('Invalid course ID');
                }

                $courseCRUD->delete($id);

                $cache->clearByPrefix("courses_list_");
                $countCacheKey = "course_count_{$search}";

                $_SESSION['success'] = "Course deleted successfully!";

                break;


            // =========================
            // ADD
            // =========================
            case 'add':

                $course_code     = trim($_POST['course_code'] ?? '');
                $course_name     = trim($_POST['course_name'] ?? '');
                $course_price    = trim($_POST['course_price'] ?? '');
                $course_duration = trim($_POST['course_duration'] ?? '');

                if ($course_code === '' || $course_name === '') {
                    throw new Exception('Course code and name are required');
                }

                $data = [
                    'course_code'     => $course_code,
                    'course_name'     => $course_name,
                    'price'    => $course_price,
                    'duration' => $course_duration
                    // 'created_at'      => date('Y-m-d H:i:s')
                ];

                $courseCRUD->insert($data);

                $_SESSION['success'] = "Course added successfully!";
                $cache->clearByPrefix("courses_list_");
                break;


            // =========================
            // UPDATE
            // =========================
            case 'update':

                $id = (int) ($_POST['course_id'] ?? 0);

                if ($id <= 0) {
                    throw new Exception('Invalid course ID');
                }

                $course_code     = trim($_POST['course_code'] ?? '');
                $course_name     = trim($_POST['course_name'] ?? '');
                $course_price    = trim($_POST['course_price'] ?? '');
                $course_duration = trim($_POST['course_duration'] ?? '');

                if ($course_code === '' || $course_name === '') {
                    throw new Exception('Course code and name are required');
                }

                $data = [
                    'course_code'     => $course_code,
                    'course_name'     => $course_name,
                    'price'    => $course_price,
                    'duration' => $course_duration
                    // 'updated_at'      => date('Y-m-d H:i:s')
                ];

                $courseCRUD->where("course_id", "=", $id)->update($data);

                $_SESSION['success'] = "Course updated successfully!";
                $cache->clearByPrefix("courses_list_");

                break;


            default:
                throw new Exception('Invalid action');
        }
    } catch (Exception $e) {

        $_SESSION['error'] = $e->getMessage();
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
    <title>Courses | <?php echo $infoSchemaData[1]["name_short"] ?></title>
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
    <script src="/system-management/src/assets/js/user-profile.js"></script>
    <style>
        .page-title {
            font-weight: 700;
        }
    </style>

</head>

<body class="container-fluid p-0 overflow-x-hidden">
    <div class="row g-3">

        <?php Navbar($infoSchemaData, $routeAccount); ?>


        <!-- Main area -->
        <main class="col-lg-10 col-sm-12 bg-light">
            <div
                class="d-flex justify-content-between align-items-center px-2 py-2 bg-white position-sticky top-0 z-3">

                <div class="title">Welcome to <?php echo $infoSchemaData[0]["name"] ?></div>

                <?php Avatar($_SESSION['role']); ?>

            </div>

            <div class="w-100 d-flex mt-3 ms-3 justify-content-between gap-3 flex-wrap m-0">
                <div class="w-100 bg-white shadow px-4 py-3 rounded">
                    <div class="d-flex justify-content-between align-items-center fw-semibold mb-2">
                        <div class="d-flex w-50 align-items-center justify-content-between gap-3 mb-3">
                            <div class="fw-semibold w-25">
                                <i class="bi bi-credit-card-fill me-1"></i>
                                Courses List
                            </div>
                            <form method="GET" class="d-flex mb-0 w-75" autocomplete="off">
                                <input type="text" name="search"
                                    class="form-control me-2"
                                    placeholder="Search courses..."
                                    value="<?= htmlspecialchars($search) ?>">
                                <!-- <button type="submit" class="btn btn-primary">Search</button> -->
                            </form>
                        </div>
                        <div>
                            <button id="editBtn" style="width: 99px;" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editCourseModal">
                                Edit
                            </button>
                            <button id="deleteBtn" style="width: 99px;" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#deleteCourseModal">
                                Delete
                            </button>
                            <button style="width: 99px;" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#courseModal">
                                Add
                            </button>
                        </div>

                        <!--  -->
                        <div class="modal fade" id="editCourseModal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-xl">
                                <div class="modal-content px-3">
                                    <form id="courseForm" method="POST">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="course_id" id="edit_course_id">


                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Course</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>

                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Course Code</label>
                                                <input type="text" class="form-control" name="course_code" id="edit_course_code" require>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Course Name</label>
                                                <input type="text" class="form-control" name="course_name" id="edit_course_name" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Course Price</label>
                                                <input type="number" class="form-control" name="course_price" id="edit_course_price" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Course Duration</label>
                                                <input type="text" class="form-control" name="course_duration" id="edit_course_duration" required>
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
                        <div class=" modal fade" id="deleteCourseModal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-xl">
                                <div class=" modal-content px-3 ">
                                    <form id="courseForm" method="POST">
                                        <input type="hidden" name="course_id" id="delete_course_id">
                                        <input type="hidden" name="action" value="delete">

                                        <div class="modal-header">
                                            <h5 class="modal-title">Delete Course</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>

                                        <div class="modal-body" id="deleteText">
                                            Are you sure you want to delete
                                        </div>



                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">
                                                Cancel
                                            </button>
                                            <button type="submit" class="btn btn-danger">
                                                Delete
                                            </button>
                                        </div>
                                    </form>

                                </div>
                            </div>
                        </div>

                        <!--  -->
                        <div class="modal fade" id="courseModal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-xl">
                                <div class="modal-content px-3">
                                    <form id="courseForm" method="POST">
                                        <input type="hidden" name="action" value="add">

                                        <div class="modal-header">
                                            <h5 class="modal-title">Add Course</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>

                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Course Code</label>
                                                <input type="text" class="form-control" name="course_code" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Course Name</label>
                                                <input type="text" class="form-control" name="course_name" required>
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
                        <table class="table  table-hover">
                            <thead class="head-custom w-100">
                                <tr class="headLabel">
                                    <th class="col-1 text-center">id</th>
                                    <th class="col-1">course code</th>
                                    <th class="col-3">course name</th>
                                    <th class="col-1">price</th>
                                    <th class="col-2">duration</th>
                                </tr>
                            </thead>
                            <tbody id="courseTable">

                                <!--  -->
                                <?php if (!empty($data)): ?>
                                    <?php foreach ($data as $key => $row): ?>
                                        <tr data-id="<?= $row['course_id'] ?>"
                                            data-code="<?= htmlspecialchars($row['course_code']) ?>"
                                            data-name="<?= htmlspecialchars($row['course_name']) ?>"
                                            data-duration="<?= htmlspecialchars($row['duration']) ?>"
                                            data-price="<?= htmlspecialchars($row['price']) ?>">
                                            <td class="text-center"><?= $key + 1 ?></td>
                                            <td><?= htmlspecialchars($row['course_code']) ?></td>
                                            <td><?= htmlspecialchars($row['course_name']) ?></td>
                                            <td><?= htmlspecialchars($row['price']) ?></td>
                                            <td><?= htmlspecialchars($row['duration']) ?></td>
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
        </main>
    </div>
    <script src="../../script.js"></script>

    <script src="<?= BASE_URL ?>/src/assets/js/navbar-toggle-action.js"></script>

    <script>
        let selectedRow = null;
        let selectedId = null;

        const tableBody = document.getElementById("courseTable");
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
            document.querySelectorAll("#courseTable tr")
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
            document.getElementById("edit_course_id").value = selectedRow.dataset.id;
            document.getElementById("edit_course_code").value = selectedRow.dataset.code;
            document.getElementById("edit_course_name").value = selectedRow.dataset.name;
            document.getElementById("edit_course_price").value = selectedRow.dataset.price;
            document.getElementById("edit_course_duration").value = selectedRow.dataset.duration;
        });

        // ================= DELETE =================
        deleteBtn.addEventListener("click", () => {
            if (!selectedRow) return;

            document.getElementById("delete_course_id").value = selectedRow.dataset.id;

            deleteText.innerHTML =
                `Are you sure you want to <strong class='text-danger'>Delete</strong> 
         <strong>${selectedRow.dataset.name}</strong> Course?`;
        });
    </script>
</body>

</html>