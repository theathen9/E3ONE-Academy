<?php
include "./data/dataShema.php";

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Empowerment Education English One</title>
    <link rel="icon" type="image/png" href="../src/assets/icon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>
    <link rel="stylesheet" href="./src/style.css">

</head>

<body class="d-flex w-100 vw-100">

    <body class="container-fluid p-0">
        <div class="row g-0">

            <!-- Sidebar -->
            <nav class="navBar col-12 col-md-3 col-lg-2 p-3">
                <div class="d-flex gap-2 mb-4">
                    <img style="height: 60px;" src="../src/assets/logo.jpg" alt="logo">
                    <div class="title">
                        <p>Empowerment <br>Education English One</p>
                    </div>
                </div>
                a
            </nav>

            <!-- Main area -->
            <main class="col-12 col-md-9 col-lg-10 bg-light">
                <div class="d-flex justify-content-between align-items-center px-2 py-3 bg-white">
                    <div>Welcome to <?php echo $infoShemaData[0]["name"] ?></div>

                    <div class="dropdown">
                        <button class="d-flex align-items-center border-0 bg-white gap-2" data-bs-toggle="dropdown">
                            <img src="../src/assets/logo.jpg" width="60" height="60" style="border-radius:50%">
                            <div>Username</div>
                        </button>

                        <ul class="dropdown-menu">
                            <a href="../auth/signout.php" class="text-decoration-none">
                                <li><button class="dropdown-item">Sign Out</button></li>
                            </a>
                        </ul>
                    </div>
                </div>

                <div class="container p-4">
                    <div></div>
                </div>
            </main>

        </div>
    </body>


    <script src="../script.js"></script>

</body>

</html>