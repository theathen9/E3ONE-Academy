<?php

include_once __DIR__ . "/../../config/bootstrap.php";
include_once __DIR__ . "/../../data/dataSchema.php";
include_once __DIR__ . "/../../components/Navbar.php";


// Active menu
$routeAdmin[0]["active"] = false;
$routeAdmin[3]["active"] = true;
$routeAdmin[3]['submenu'][1]['active'] = true;

$db = new DB($conn);
$enrollmentCRUD = new ORM($db, "tblEnrollment", "enrollment_id");
$studentCRUD = new ORM($db, "tblStudents", "student_id");
$courseCRUD = new ORM($db, "tblCourses", "course_id");
$teacherCRUD = new ORM($db, "tblTeachers", "teacher_id");
$classCRUD = new ORM($db, "tblClass", "class_id");
$roomCRUD = new ORM($db, "tblRooms", "room_id");




// 3️⃣ Fetch courses for the dropdown
$courses = $conn->query("SELECT * FROM tblCourses ORDER BY course_name ASC");

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Enrollment | <?php echo htmlspecialchars($infoSchemaData[1]["name_short"]) ?></title>
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

        <?php Navbar($infoSchemaData, $routeAdmin) ?>


        <!-- Main area -->
        <main class="col-10 col-lg-10 bg-light vh-100">
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

            <div class="w-100 d-flex mt-3 justify-content-between gap-3 flex-wrap">
                <div class="w-100 bg-white shadow px-4 py-3 rounded">
                    <div class="d-flex justify-content-between align-items-center fw-semibold mb-2">
                        <div class="d-flex w-50 align-items-center justify-content-between gap-3 mb-3">
                            <div class="fw-semibold">
                                <i class="bi bi-credit-card-fill me-1"></i>
                                Enrollment List
                            </div>

                            <div class="w-75">
                                <input type="text"
                                    id="searchStudent"
                                    class="form-control"
                                    placeholder="Search Student...">
                            </div>

                        </div>
                        <div>

                            <button id="editBtn" style="width: 99px;" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editEnrollmentModal">
                                Edit
                            </button>
                            <button id="deleteBtn" style="width: 99px;" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#deleteEnrollmentModal">
                                Delete
                            </button>
                            <button style="width: 150px;" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#enrollmentModal">
                                New Enrollment
                            </button>
                        </div>
                        <div class="modal fade" id="editEnrollmentModal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-xl">
                                <div class="modal-content px-3">
                                    <form id="enrollmentForm" method="POST">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="enrollment_id" id="edit_enrollment_id">


                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Enrollment</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>

                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Enrollment Code</label>
                                                <input type="text" class="form-control" name="enrollment_code" id="edit_enrollment_code" require>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Enrollment Name</label>
                                                <input type="text" class="form-control" name="enrollment_name" id="edit_enrollment_name" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Description</label>
                                                <input type="text" class="form-control" name="description" id="edit_description" require>
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



                        <!-- Delete -->
                        <div class=" modal fade" id="deleteEnrollmentModal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-xl">
                                <div class=" modal-content px-3 ">
                                    <form id="enrollmentForm" method="POST">
                                        <input type="hidden" name="enrollment_id" id="delete_enrollment_id">
                                        <input type="hidden" name="action" value="delete">

                                        <div class="modal-header">
                                            <h5 class="modal-title">Delete Enrollment</h5>
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
                        <div class="modal fade" id="enrollmentModal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-xl">
                                <div class="modal-content px-3">
                                    <form id="enrollmentForm" method="POST">
                                        <input type="hidden" name="action" value="add">

                                        <div class="modal-header">
                                            <h5 class="modal-title">Add Enrollment</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>

                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Enrollment Code</label>
                                                <input type="text" class="form-control" name="enrollment_code" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Enrollment Name</label>
                                                <input type="text" class="form-control" name="enrollment_name" required>
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
                                    <th class="col-1">Class</th>
                                    <th class="col-1">Student Name</th>
                                    <th class="col-1">Subject</th>
                                    <th class="col-1">Course</th>
                                    <th class="col-1">Teacher</th>
                                    <th class="col-1">Study Days</th>
                                    <th class="col-1">Room</th>
                                    <th class="col-1">Academic Year</th>
                                </tr>
                            </thead>
                            <tbody id="enrollmentTable">

                                <!--  -->
                                <?php if (!empty($data)): ?>
                                    <?php foreach($data as $key => $row): ?>
                                        <tr data-id="<?= $row['enrollment_id'] ?>"
                                            data-code="<?= htmlspecialchars($row['enrollment_code']) ?>"
                                            data-name="<?= htmlspecialchars($row['enrollment_name']) ?>"
                                            data-description="<?= htmlspecialchars($row['description']) ?>"
                                            data-created="<?= date('Y-m-d', strtotime($row['created_at'])) ?>">
                                            <td><?= htmlspecialchars($row['enrollment_id']) ?></td>
                                            <td><?= htmlspecialchars($row['enrollment_code']) ?></td>
                                            <td><?= htmlspecialchars($row['enrollment_name']) ?></td>
                                            <td><?= htmlspecialchars($row['description']) ?></td>
                                            <td class="col-2"> <?= date('Y-m-d', strtotime($row['created_at'])) ?></td>
                                            <td class="col-2"> <?= date('Y-m-d', strtotime($row['updated_at'])) ?></td>
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

                    document.querySelector("#profileImg").src =
                        "http://localhost/system-management/uploads/photos/" +
                        data.data.profile_image;
                } else {
                    console.log("Failed:", data);
                }
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
                    document.getElementById("studentTable").innerHTML = data;

                    selectedId = null;
                    editBtn.disabled = true;
                    detailBtn.disabled = true;
                });
        });
    </script>

</body>

</html>