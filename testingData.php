<?php
// session_start();
require_once "./config/db.php";

// Protect page
// if(!isset($_SESSION['user_id'])) {
//     header("Location: ./auth/login.php");
//     exit;
// }

// Sidebar menu data
$staticShemaData = [
    [
        "title" => "Dashboard",
        "link" => "#",
        "icon" => "bi bi-house",
        "active" => true
    ],
    [
        "title" => "Students",
        "submenu_id" => "studentsMenu",
        "submenu" => [
            ["title"=>"Add Student","link"=>"#","active"=>false],
            ["title"=>"All Students","link"=>"#","active"=>true]
        ],
        "active"=>true
    ],
    [
        "title"=>"Reports",
        "link"=>"#",
        "icon"=>"bi bi-file-earmark-text",
        "active"=>false
    ]
];

// Fetch employees dynamically
$employees = $conn->query("SELECT * FROM tblemployees ORDER BY employees_id DESC");

// Info schema (replace with actual DB or static array)
$infoShemaData = [
    ["name"=>"Empowerment Education English One"],
    ["email"=>"info@eeone.edu"],
    ["address"=>"Phnom Penh, Cambodia"],
    ["phone"=>"+855 12 345 678"],
    ["image"=>"../../src/assets/logo.jpg"]
];

// Function to render menu recursively
function renderMenu($items) {
    foreach($items as $item) {
        $activeClass = !empty($item['active']) ? 'bg-primary text-white' : 'text-dark';
        if(isset($item['submenu'])) {
            $show = !empty($item['active']) ? 'show' : '';
            echo "<li class='nav-item mb-1'>
                <a class='nav-link d-flex justify-content-between' data-bs-toggle='collapse' href='#{$item['submenu_id']}'>
                    {$item['title']} <span class='bi bi-chevron-down'></span>
                </a>
                <ul class='nav collapse flex-column ms-3 $show' id='{$item['submenu_id']}'>";
            renderMenu($item['submenu']);
            echo "</ul></li>";
        } else {
            echo "<li class='nav-item mb-1'>
                <a href='{$item['link']}' class='nav-link $activeClass'>{$item['title']}</a>
            </li>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard | Empowerment Education English One</title>
<link rel="icon" href="<?= $infoShemaData[4]['image'] ?>" type="image/png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.13.1/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="../../src/style.css">
<style>
.navBar { overflow-y:auto; max-height:100vh; }
.table-scroll { max-height:500px; overflow-y:auto; }
</style>
</head>
<body class="container-fluid p-0">
<div class="row g-3">
    <!-- Sidebar -->
    <nav class="navBar col-12 col-md-3 col-lg-2 p-3 vh-100 position-sticky top-0 bg-light">
        <div class="d-flex gap-2 mb-4 align-items-center">
            <img src="<?= $infoShemaData[4]['image'] ?>" width="60" height="60" class="rounded-circle">
            <div class="title"><p class="m-auto"><?= $infoShemaData[0]['name'] ?></p></div>
        </div>
        <ul class="nav flex-column">
            <?php renderMenu($staticShemaData); ?>
        </ul>
    </nav>

    <!-- Main Area -->
    <main class="col-12 col-md-9 col-lg-10 bg-light">
        <div class="d-flex justify-content-between align-items-center px-3 py-2 bg-white position-sticky top-0">
            <div class="fw-semibold">Welcome, <?= $_SESSION['username'] ?? 'User' ?></div>
            <div class="dropdown">
                <button class="d-flex align-items-center border-0 bg-white gap-2" data-bs-toggle="dropdown">
                    <img src="../../src/assets/logo.jpg" width="50" height="50" class="rounded-circle">
                    <span><?= $_SESSION['username'] ?? 'Username' ?></span>
                </button>
                <ul class="dropdown-menu">
                    <li><a href="../auth/signout.php" class="dropdown-item">Sign Out</a></li>
                    <li><a href="#" class="dropdown-item">Account</a></li>
                </ul>
            </div>
        </div>

        <div class="container p-3">
            <div class="bg-white shadow rounded p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5><i class="bi bi-people-fill"></i> Employees List</h5>
                    <div class="d-flex gap-2">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search Employees...">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">Add</button>
                    </div>
                </div>

                <!-- Employees Table -->
                <div class="table-scroll table-responsive">
                    <table class="table table-hover table-bordered text-center mb-0">
                        <thead class="table-primary">
                            <tr>
                                <th>ID</th><th>Name Kh</th><th>Name Eng</th><th>Position</th>
                                <th>Phone</th><th>Email</th><th>Address</th><th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="employeeTable">
                        <?php while($row = $employees->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['emp_id'] ?></td>
                                <td><?= $row['name_kh'] ?></td>
                                <td><?= $row['name_eng'] ?></td>
                                <td><?= $row['position'] ?></td>
                                <td><?= $row['phone'] ?></td>
                                <td><?= $row['email'] ?></td>
                                <td><?= $row['address'] ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-btn" data-id="<?= $row['id'] ?>">Edit</button>
                                    <button class="btn btn-sm btn-danger delete-btn" data-id="<?= $row['id'] ?>">Delete</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-3" id="pagination"></div>
            </div>
        </div>
    </main>
</div>

<!-- Add Employee Modal -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="employeeForm">
                <div class="modal-header">
                    <h5 class="modal-title">Add Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Employee ID</label>
                        <input type="text" name="emp_id" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Name Kh</label>
                        <input type="text" name="name_kh" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Name Eng</label>
                        <input type="text" name="name_eng" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Position</label>
                        <input type="text" name="position" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <input type="text" name="address" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Search Function
const searchInput = document.getElementById('searchInput');
searchInput.addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    document.querySelectorAll('#employeeTable tr').forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});

// Simple Pagination
const rowsPerPage = 5;
const table = document.getElementById('employeeTable');
const rows = Array.from(table.querySelectorAll('tr'));
const pagination = document.getElementById('pagination');
let currentPage = 1;

function renderPage(page) {
    rows.forEach((row,i) => row.style.display = (i >= (page-1)*rowsPerPage && i < page*rowsPerPage) ? '' : 'none');
    pagination.innerHTML = '';
    const totalPages = Math.ceil(rows.length / rowsPerPage);
    for(let i=1;i<=totalPages;i++) {
        const btn = document.createElement('button');
        btn.textContent = i;
        btn.className = 'btn btn-sm mx-1 ' + (i===page ? 'btn-primary' : 'btn-outline-primary');
        btn.onclick = ()=>{ currentPage=i; renderPage(currentPage); };
        pagination.appendChild(btn);
    }
}
renderPage(1);

// Add Employee AJAX
document.getElementById('employeeForm').addEventListener('submit', function(e){
    e.preventDefault();
    const data = new FormData(this);
    fetch('add_employee.php',{method:'POST',body:data})
    .then(res=>res.json())
    .then(res=>{
        if(res.success) location.reload();
        else alert("Failed to add employee!");
    });
});
</script>
</body>
</html>
