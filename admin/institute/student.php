<?php
include "../../data/dataShema.php";
<<<<<<< HEAD
$staticShemaData[0]["active"]=false;
$staticShemaData[1]["active"]=true;
$staticShemaData[1]['submenu'][2]['active'] = true;
=======
$staticShemaData[0]["active"] = false;
$staticShemaData[1]["active"] = true;
$staticShemaData[1]['submenu'][3]['active'] = true;
>>>>>>> 512c3b3 (first commit)

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Empowerment Education English One</title>
<<<<<<< HEAD
    <link rel="icon" type="image/png" href="<?php echo $infoShemaData[4]["image"] ?>" >
=======
    <link rel="icon" type="image/png" href="<?php echo $infoShemaData[4]["image"] ?>">
>>>>>>> 512c3b3 (first commit)
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

<body class="container-fluid p-0 ">
    <div class="row g-3">
        <nav class="navBar col-12 col-md-3 col-sm-3 col-lg-2 p-3 vh-100 position-sticky top-0">
            <div class="d-flex gap-1 mb-4 align-items-center align-self-center">
                <img src="<?php echo $infoShemaData[4]["image"] ?>" width="60" height="60" alt="logo" class="rounded-circle">
                <div class="title">
                    <p class="m-auto">Empowerment <br>Education English One</p>
                </div>
            </div>
            <ul class="nav flex-column">
                <ul class="nav flex-column">
                    <?php foreach ($staticShemaData as $item): ?>
                        <?php if (isset($item['submenu'])): ?>
                            <li class="nav-item mb-1">
                                <a class="nav-link rounded d-flex justify-content-between align-items-center
            <?= !empty($item['active']) ? 'text-dark' : ' text-dark'; ?>" data-bs-toggle="collapse"
                                    href="#<?= $item['submenu_id']; ?>">
                                    <?= $item['title']; ?>
                                    <span class="bi bi-chevron-down"></span>
                                </a>
                                <ul class="nav collapse flex-column ms-3
            <?= !empty($item['active']) ? 'show' : ''; ?>" id="<?= $item['submenu_id']; ?>">

                                    <?php foreach ($item['submenu'] as $sub): ?>
                                        <li class="nav-item mb-1 w-100">
                                            <a href="<?= $sub['link']; ?>" class="nav-link rounded
                        <?= !empty($sub['active']) ? 'bg-primary text-white' : 'text-dark'; ?>">
                                                <?= $sub['title']; ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>

                                </ul>
                            </li>

                        <?php else: ?>
                            <li class="nav-item mb-1 w-100">
                                <a href="<?= $item['link']; ?>" class="nav-link rounded
            <?= !empty($item['active']) ? 'bg-primary text-white' : 'text-dark'; ?>">
                                    <?php if (!empty($item['icon'])): ?>
                                        <i class="<?= $item['icon']; ?> me-1"></i>
                                    <?php endif; ?>
                                    <?= $item['title']; ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </ul>
        </nav>

        <!-- Main area -->
        <main class="col-12 col-md-6 col-lg-9 col-sm-12 bg-light">
            <div
                class="d-flex justify-content-between align-items-center px-2 py-2 bg-white py-md-1 position-sticky top-0 ">
                <div class="title">Welcome to <?php echo $infoShemaData[0]["name"] ?></div>

                <div class="dropdown">
                    <button class="d-flex align-items-center border-0 bg-white gap-2" data-bs-toggle="dropdown">
                        <img src="../src/assets/logo.jpg" width="60" height="60" style="border-radius:50%">
                        <div>Username</div>
                    </button>

                    <ul class="dropdown-menu bg-white">
                        <a href="../auth/signout.php" class="text-decoration-none">
                            <li><button class="dropdown-item">Sign Out</button></li>
                            <li><button class="dropdown-item">Account</button></li>
                        </a>
                    </ul>
                </div>
            </div>

            <div class="container-lg container-md container-sm p-3">
                <div style="background: #006d9c;
background: linear-gradient(139deg, rgba(0, 109, 156, 1) 32%, rgba(0, 109, 156, 1) 50%, rgba(101, 155, 90, 1) 61%, rgba(128, 164, 74, 1) 63%, rgba(154, 173, 59, 1) 66%, rgba(255, 208, 0, 1) 70%, rgba(255, 208, 0, 1) 100%);"
                    class="w-100 bg-gradient-custom py-3 px-4 rounded">
                    <div class="d-flex justify-content-between text-white">
                        <div class="">
                            <h3 class="mb-4"><?php echo $infoShemaData[0]["name"] ?></h3>
                            <div>
                                <div>
                                    <i
                                        class="bi bi-envelope-fill me-1 mb-0"></i><?php echo $infoShemaData[1]["email"] ?>
                                </div>
                                <div>
                                    <i
                                        class="bi bi-geo-alt-fill me-1 mb-0"></i><?php echo $infoShemaData[2]["address"] ?>
                                </div>
                                <div>
                                    <i
                                        class="bi bi-telephone-fill me-1 mb-0"></i><?php echo $infoShemaData[3]["phone"] ?>
                                </div>
                            </div>
                        </div>
                        <img style="width: 135px; border-radius: 50%;" src="<?php echo $infoShemaData[4]["image"] ?>"
                            alt="" srcset="" class="h-100">
                    </div>
                </div>
<<<<<<< HEAD
               
                <div class="w-100 d-flex mt-3 justify-content-between gap-3 flex-wrap">
                    <div class="bg-white shadow p-3 rounded">
                       <div class="d-flex justify-content-between fw-semibold mb-2">
    <div>
        <i class="bi bi-credit-card-fill"></i> Students List
    </div>

    <div class="dropend">
        <button class="btn btn-primary dropdown-toggle"
                data-bs-toggle="dropdown"
                data-bs-auto-close="outside">
            Add
        </button>

       <div id="studentDropdown" class="dropdown-menu position-fixed
            top-0 start-0 w-100 h-100
            d-flex justify-content-center align-items-center
            p-3"
     style="background: rgba(0,0,0,0.5);">

    <form onsubmit="this.closest('.dropdown-menu').classList.remove('show')" class="bg-white p-4 rounded shadow"
          style="min-width: 1000px; max-width: 95%;">
        
        <div class="mb-3">
            <label for="studentId" class="form-label">Student ID</label>
            <input type="text" class="form-control" id="studentId"
                   placeholder="Enter Student ID">
        </div>

        <div class="mb-3">
            <label for="studentName" class="form-label">Student Name</label>
            <input type="text" class="form-control" id="studentName"
                   placeholder="Enter Student Name">
        </div>

        <div class="mb-3">
            <label for="dob" class="form-label">Date Of Birth</label>
            <input type="date" class="form-control" id="dob">
        </div>

       <div class="d-flex justify-content-between w-75 mx-auto">
    <button data-bs-dismiss="dropdown" data-close-dropdown type="button" class="btn btn-success w-25">Cancel</button>
    <button type="submit" class="btn btn-success w-25">Save</button>
</div>

    </form>
</div>

    </div>
</div>
=======

                <div class="w-100 d-flex mt-3 justify-content-between gap-3 flex-wrap">
                    <div class="bg-white shadow px-4 py-3 rounded">
                        <div class="d-flex justify-content-between align-items-center fw-semibold mb-2">
                            <div class="d-flex w-50 align-items-center justify-content-between gap-3 mb-3">
                                <div class="fw-semibold">
                                    <i class="bi bi-credit-card-fill me-1"></i>
                                    Students List
                                </div>

                                <div class="w-75">
                                    <input type="text"
                                        class="form-control"
                                        placeholder="Search students...">
                                </div>
                            </div>
                            <div>
                                <a href="../enrollment/enrollment.php" class="text-decoration-none">
                                    <button style="width: 99px;" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#studentModal">
                                        Enrollment
                                    </button>
                                </a>

                                <button style="width: 99px;" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#studentModal">
                                    Add
                                </button>
                            </div>

                            <div class="modal fade" id="studentModal" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-xl">
                                    <div class="modal-content">

                                        <form id="studentForm">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Add Student</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>

                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Student ID</label>
                                                    <input type="text" class="form-control" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Student Name</label>
                                                    <input type="text" class="form-control" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Date Of Birth</label>
                                                    <input type="date" class="form-control">
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

>>>>>>> 512c3b3 (first commit)

                        <!-- SCROLL CONTAINER -->
                        <div class="table-scroll modelBox ps-3">
                            <table class="table text-center table-hover mb-0">
                                <thead class="head-custom">
                                    <tr class="headLabel">
                                        <th class="col-sm-1">Student ID</th>
                                        <th>Student Name</th>
                                        <th>Date Of Birth</th>
                                        <th>Gender</th>
                                        <th>Phone</th>
                                        <th>Address</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>ST001</td>
                                        <td>Cash</td>
                                        <td>$50</td>
                                        <td><span class="badge bg-success">Paid</span></td>
                                        <td>Monthly fee</td>
                                        <td>2025-01-01</td>
<<<<<<< HEAD
                                          <td class="text-nowrap">
=======
                                        <td class="text-nowrap">
>>>>>>> 512c3b3 (first commit)
                                            <button class="btn btn-sm btn-primary">Edit</button>
                                            <button class="btn btn-sm btn-danger">Delete</button>
                                            <button class="btn btn-sm btn-info text-white">Detail</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>ST001</td>
                                        <td>Cash</td>
                                        <td>$50</td>
                                        <td><span class="badge bg-success">Paid</span></td>
                                        <td>Monthly fee</td>
                                        <td>2025-01-01</td>
                                    </tr>
                                    <tr>
                                        <td>ST001</td>
                                        <td>Cash</td>
                                        <td>$50</td>
                                        <td><span class="badge bg-success">Paid</span></td>
                                        <td>Monthly fee</td>
                                        <td>2025-01-01</td>
                                    </tr>
                                    <tr>
                                        <td>ST001</td>
                                        <td>Cash</td>
                                        <td>$50</td>
                                        <td><span class="badge bg-success">Paid</span></td>
                                        <td>Monthly fee</td>
                                        <td>2025-01-01</td>
                                    </tr>
                                    <tr>
                                        <td>ST001</td>
                                        <td>Cash</td>
                                        <td>$50</td>
                                        <td><span class="badge bg-success">Paid</span></td>
                                        <td>Monthly fee</td>
                                        <td>2025-01-01</td>
                                    </tr>
                                    <tr>
                                        <td>ST001</td>
                                        <td>Cash</td>
                                        <td>$50</td>
                                        <td><span class="badge bg-success">Paid</span></td>
                                        <td>Monthly fee</td>
                                        <td>2025-01-01</td>
                                    </tr>
                                    <tr>
                                        <td>ST001</td>
                                        <td>Cash</td>
                                        <td>$50</td>
                                        <td><span class="badge bg-success">Paid</span></td>
                                        <td>Monthly fee</td>
                                        <td>2025-01-01</td>
                                    </tr>
                                    <tr>
                                        <td>ST001</td>
                                        <td>Cash</td>
                                        <td>$50</td>
                                        <td><span class="badge bg-success">Paid</span></td>
                                        <td>Monthly fee</td>
                                        <td>2025-01-01</td>
                                    </tr>
                                    <tr>
                                        <td>ST001</td>
                                        <td>Cash</td>
                                        <td>$50</td>
                                        <td><span class="badge bg-success">Paid</span></td>
                                        <td>Monthly fee</td>
                                        <td>2025-01-01</td>
                                    </tr>
                                    <tr>
                                        <td>ST001</td>
                                        <td>Cash</td>
                                        <td>$50</td>
                                        <td><span class="badge bg-success">Paid</span></td>
                                        <td>Monthly fee</td>
                                        <td>2025-01-01</td>
                                    </tr>
                                    <tr>
                                        <td>ST001</td>
                                        <td>Cash</td>
                                        <td>$50</td>
                                        <td><span class="badge bg-success">Paid</span></td>
                                        <td>Monthly fee</td>
                                        <td>2025-01-01</td>
                                    </tr>
                                    <tr>
                                        <td>ST001</td>
                                        <td>Cash</td>
                                        <td>$50</td>
                                        <td><span class="badge bg-success">Paid</span></td>
                                        <td>Monthly fee</td>
                                        <td>2025-01-01</td>
                                    </tr>
                                    <tr>
                                        <td>ST001</td>
                                        <td>Cash</td>
                                        <td>$50</td>
                                        <td><span class="badge bg-success">Paid</span></td>
                                        <td>Monthly fee</td>
                                        <td>2025-01-01</td>
                                    </tr>
                                    <tr>
                                        <td>ST001</td>
                                        <td>Cash</td>
                                        <td>$50</td>
                                        <td><span class="badge bg-success">Paid</span></td>
                                        <td>Monthly fee</td>
                                        <td>2025-01-01</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div>
                        c

                    </div>
                </div>
            </div>
        </main>
    </div>
<<<<<<< HEAD
=======
    <script src="../../script.js"></script>
>>>>>>> 512c3b3 (first commit)
</body>

</html>