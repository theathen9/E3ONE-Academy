<?php
// ./auth/signin.php (from Page)
date_default_timezone_set('Asia/Phnom_Penh');

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../app/api/v1/auth.php';

$user = checkAuth();
if ($user && isset($_SESSION['loggedin'])) {
    switch (strtolower($_SESSION['role'])) {
        case 'admin':
            header("Location: " . BASE_URL . "/admin/dashboard.php");
            break;
        case 'accountant':
            header("Location: " . BASE_URL . "/account/dashboard.php");
            break;
        case 'teacher':
            header("Location: " . BASE_URL . "/teacher/dashboard.php");
            break;
        case 'student':
            header("Location: " . BASE_URL . "/student/dashboard.php");
            break;
        default:
            session_destroy();
            break;
    }
    exit();
}
$success = "";
$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    verifyCSRF();

    $user = trim($_POST["username"]);
    $pass = trim($_POST["password"]);

    if (empty($user) || empty($pass)) {
        $error = "Username or ID and Password are required!";
    } else {

        $id = intval($user);

        $stmt = $conn->prepare("
            SELECT 
                u.user_id,
                u.username,
                u.password,
                u.role_id,
                r.role_name,
                u.email,
                u.reference_id,
                u.reference_type,
                u.user_agent,
                u.ip_address
            FROM tblUsers u
            LEFT JOIN tblRoles r ON u.role_id = r.role_id
            WHERE u.username = :user 
               OR u.user_id = :id 
               OR u.email = :user
            LIMIT 1
        ");

        $stmt->execute([
            ":user" => $user,
            ":id"   => $id
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        

        if ($row && password_verify($pass, $row["password"])) {

            session_regenerate_id(true);

            $accessToken  = bin2hex(random_bytes(32));
            $refreshToken = bin2hex(random_bytes(64));

            $hashedToken   = hash('sha256', $accessToken);
            $hashedRefresh = hash('sha256', $refreshToken);

            $accessExpiry  = date('Y-m-d H:i:s', strtotime('+5 minutes'));
            $refreshExpiry = date('Y-m-d H:i:s', strtotime('+1 days'));

            $update = $conn->prepare("
                UPDATE tblUsers 
                SET last_login = NOW(),
                    access_token = :access_token,
                    refresh_token = :refresh_token,
                    access_expiry = :access_expiry,
                    refresh_expiry = :refresh_expiry
                WHERE user_id = :user_id
            ");

            $update->execute([
                ":access_token"  => $hashedToken,
                ":refresh_token" => $hashedRefresh,
                ":access_expiry" => $accessExpiry,
                ":refresh_expiry"=> $refreshExpiry,
                ":user_id"       => $row['user_id']
            ]);

            $_SESSION['loggedin'] = true;
            $_SESSION["user_id"] = $row["user_id"];
            $_SESSION["role"] = strtolower($row["role_name"]);
            $_SESSION["reference_id"] = $row["reference_id"];
            $_SESSION["reference_type"] = $row["reference_type"];

            $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

            $userId = $_SESSION["user_id"];
            $signature = hash_hmac('sha256', $userId, APP_SECRET);
            $value = $userId . "." . $signature;

            setcookie("c_user", $value, [
                'expires' => strtotime($refreshExpiry),
                'path' => '/',
                'secure' => $secure,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            setcookie("access_token", $accessToken, [
                'expires' => strtotime($accessExpiry),
                'path' => '/',
                'secure' => $secure,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            setcookie("refresh_token", $refreshToken, [
                'expires' => strtotime($refreshExpiry),
                'path' => '/',
                'secure' => $secure,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            switch (strtolower($row["role_name"])) {
                case "admin":
                    header("Location: ../admin/dashboard.php");
                    break;
                case "accountant":
                    header("Location: ../account/dashboard.php");
                    break;
                case "teacher":
                    header("Location: ../teacher/dashboard.php");
                    break;
                default:
                    header("Location: ../student/dashboard.php");
            }
            exit;
        }

        $error = "Invalid username or password!";
    }
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in | Empowerment Education English One></title>
    <link rel="icon" type="image/png" href="../src/assets/icon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>

<body style="background-image: url(../src/assets/bgSignin.jpg); " class="">
    <div style="background: rgba(0, 0, 0, 0.64);" class="w-100 h-100 vh-100 vw-100 ">
        <div class="container vh-100 ">
            <div class="row justify-content-center align-items-center h-100">
                <div class="col-12 col-sm-8 col-md-6 col-lg-5 col-xl-5">
                    <div class="card shadow">
                        <div class="card-body pb-4">
                            <img style="width: 125px;height: 125px" class="d-block mx-auto mb-4"
                                src="../src/assets/logo.jpg" alt="logo">
                            <h5 class="fw-bold mb-3  text-center ">3E ONE</h5>

                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger text-center">
                                    <?= htmlspecialchars($error) ?>
                                </div>
                            <?php endif; ?>

                            <form method="post" class="m-3" autocomplete="off">
                                <!-- <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>"> -->
                                <?= csrf_field() ?>
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="username" placeholder="Username or ID"
                                        name="username">
                                    <label for="username">Username or ID or Email</label>
                                </div>

                                <div class="form-floating mb-3">
                                    <input type="password" class="form-control" id="password"
                                        placeholder="Password" name="password">
                                    <label for="password">Password</label>
                                </div>

                                <div class="d-grid">
                                    <button class="btn btn-primary btn-login text-uppercase fw-bold" type="submit" id="submitBtn">
                                        Sign in
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Auto focus username on page load
        document.getElementById("username").focus();

        // Move to password when pressing Enter in username
        document.getElementById("username").addEventListener("keypress", function(e) {
            if (e.key === "Enter") {
                e.preventDefault();
                document.getElementById("password").focus();
            }
        });

        // Submit form when pressing Enter in password
        document.getElementById("password").addEventListener("keypress", function(e) {
            if (e.key === "Enter") {
                e.preventDefault();
                document.querySelector("form").submit();
            }
        });
    </script>
</body>

</html>
