<?php
// ./app/api/v1/auth.php
// header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . '/../../../config/app.php';
include_once __DIR__ . '/../../../config/db.php';

// use mysqli for database interactions instead of PDO to maintain compatibility with existing code.
// function checkAuth()
// {
//     global $conn;

//     // 1. SESSION 
//     if (
//         isset($_SESSION['loggedin']) &&
//         $_SESSION['loggedin'] === true &&
//         isset($_SESSION['last_auth_check']) &&
//         (time() - $_SESSION['last_auth_check']) < 300 // 5 min
//     ) {
//         return $_SESSION['user_id'];
//     }

//     // 2. COOKIE
//     $userId = verifyUserCookie();

//     if (!$userId) {
//         return false;
//     }

//     // 3. LOAD USER
//     session_regenerate_id(true);

//     $stmt = $conn->prepare("
//        SELECT 
//     u.user_id,
//     u.reference_id,
//     u.reference_type,
//     u.role_id,
//     r.role_name
//     FROM tblUsers u
//     JOIN tblRoles r ON r.role_id = u.role_id
//     WHERE u.user_id = ?
//     AND u.status = 1
//     LIMIT 1
//     ");

//     $stmt->bind_param("i", $userId);

//     if (!$stmt->execute()) {
//         return false; // ✅ FIX
//     }

//     $result = $stmt->get_result();
//     $user = $result->fetch_assoc();

//     if (!$user) {
//         return false; // ✅ FIX
//     }

//     // 4. SESSION
//     $_SESSION['loggedin'] = true;
//     $_SESSION['user_id'] = (int)$user['user_id'] ?? false;
//     $_SESSION['reference_id'] = (int)$user['reference_id'];
//     $_SESSION['reference_type'] = $user['reference_type'];
//     // $_SESSION['role_id'] = (int)$user['role_id'];
//     $_SESSION['role'] = $user['role_name'] ?? false;
//     $_SESSION['last_auth_check'] = time();

//     return (int)$user['user_id'];
// }


// use PDO for database interactions instead of mysqli to improve security and prevent SQL injection.
function checkAuth()
{
    global $conn;

    // 1. SESSION
    if (
        isset($_SESSION['loggedin']) &&
        $_SESSION['loggedin'] === true &&
        isset($_SESSION['last_auth_check']) &&
        (time() - $_SESSION['last_auth_check']) < 300
    ) {
        return $_SESSION['user_id'];
    }

    // 2. COOKIE
    $userId = verifyUserCookie();

    if (!$userId) {
        return false;
    }

    session_regenerate_id(true);

    // 3. LOAD USER (PDO FIXED)
    $stmt = $conn->prepare("
        SELECT 
            u.user_id,
            u.reference_id,
            u.reference_type,
            u.role_id,
            r.role_name
        FROM tblUsers u
        JOIN tblRoles r ON r.role_id = u.role_id
        WHERE u.user_id = ?
        AND u.status = 1
        LIMIT 1
    ");

    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        return false;
    }

    // 4. SESSION
    $_SESSION['loggedin'] = true;
    $_SESSION['user_id'] = (int)$user['user_id'];
    $_SESSION['reference_id'] = (int)$user['reference_id'];
    $_SESSION['reference_type'] = $user['reference_type'];
    $_SESSION['role'] = $user['role_name'];
    $_SESSION['last_auth_check'] = time();

    return (int)$user['user_id'];
}


function authorizeRole($roles = [])
{
    if (!is_array($roles)) {
        $roles = [$roles];
    }

    $userRole = strtolower(trim($_SESSION['role'] ?? ''));

    $roles = array_map(function ($role) {
        return strtolower(trim($role));
    }, $roles);

    if (!$userRole || !in_array($userRole, $roles)) {

        header("Location: " . BASE_URL . "/auth/signin.php");

        exit();
        // better than redirect loop
        // http_response_code(403);

        // exit("403 Forbidden - Access Denied");
    }
}

/**
 * Permission check
 */
function hasPermission($permission)
{
    return isset($_SESSION['permissions']) && in_array($permission, $_SESSION['permissions']);
}


// use mysqli for database interactions instead of PDO to maintain compatibility with existing code.
// function verifyUserCookie()
// {
//     global $conn;

//     // =========================
//     // 0. BASIC COOKIE CHECK
//     // =========================
//     if (empty($_COOKIE['c_user'])) {
//         return false;
//     }

//     $parts = explode('.', $_COOKIE['c_user']);
//     if (count($parts) !== 2) {
//         return false;
//     }

//     [$userId, $signature] = $parts;

//     if (!ctype_digit($userId)) {
//         return false;
//     }

//     $userId = (int)$userId;

//     // =========================
//     // 1. VERIFY SIGNATURE
//     // =========================
//     $expectedSignature = hash_hmac('sha256', $userId, APP_SECRET);

//     if (!hash_equals($expectedSignature, $signature)) {
//         return false;
//     }

//     // =========================
//     // 2. LOAD USER TOKENS
//     // =========================
//     $stmt = $conn->prepare("
//         SELECT access_token, access_expiry, refresh_token, refresh_expiry
//         FROM tblUsers
//         WHERE user_id = ?
//         LIMIT 1
//     ");

//     if (!$stmt) return false;

//     $stmt->bind_param("i", $userId);

//     if (!$stmt->execute()) return false;

//     $res = $stmt->get_result()->fetch_assoc();

//     if (!$res) return false;

//     $now = time();

//     // =========================
//     // 3. CHECK ACCESS TOKEN
//     // =========================

//     $accessValid = false;
//     $accessExpired = true;

//     if (!empty($_COOKIE['access_token'])) {

//         $hashedAccess = hash('sha256', $_COOKIE['access_token']);

//         $accessValid =
//             hash_equals($res['access_token'], $hashedAccess);

//         $accessExpired =
//             strtotime($res['access_expiry']) <= $now;
//     }

//     // CASE 1: access token still valid → allow
//     if ($accessValid && !$accessExpired) {
//         return $userId;
//     }

//     // =========================
//     // 4. VALID REFRESH TOKEN
//     // =========================
//     if (empty($_COOKIE['refresh_token'])) {
//         return false;
//     }

//     $hashedRefresh = hash('sha256', $_COOKIE['refresh_token']);

//     if (!hash_equals($res['refresh_token'], $hashedRefresh)) {
//         return false;
//     }

//     if (strtotime($res['refresh_expiry']) <= $now) {
//         return false;
//     }

//     // =========================
//     // 5. ROTATE TOKENS
//     // =========================
//     $newAccessToken  = bin2hex(random_bytes(32));
//     // $newRefreshToken = bin2hex(random_bytes(64));

//     $hashedAccessToken  = hash('sha256', $newAccessToken);
//     // $hashedRefreshToken = hash('sha256', $newRefreshToken);

//     $newAccessExpiry  = date('Y-m-d H:i:s', strtotime('+5 minutes'));
//     // $newRefreshExpiry = date('Y-m-d H:i:s', strtotime('+3 minutes'));
//     // $newRefreshExpiry = date('Y-m-d H:i:s', strtotime('+7 days'));

//     $update = $conn->prepare("
//         UPDATE tblUsers
//         SET access_token = ?, 
//             access_expiry = ?
//         WHERE user_id = ?
//     ");

//     if (!$update) return false;

//     $update->bind_param(
//         "ssi",
//         $hashedAccessToken,
//         $newAccessExpiry,
//         $userId
//     );

//     if (!$update->execute()) {
//         return false;
//     }

//     // =========================
//     // 6. SET NEW COOKIES
//     // =========================
//     $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

//     $newSignature = hash_hmac('sha256', $userId, APP_SECRET);
//     $cookieValue = $userId . "." . $newSignature;

//     // $cookieOptions = [
//     //     'path' => '/',
//     //     'secure' => $isSecure,
//     //     'httponly' => true,
//     //     'samesite' => 'Lax' // safer for dev
//     // ];

//     setcookie("c_user", $cookieValue, [
//         'expires' => strtotime('+1 days'),
//         'path' => '/',
//         'secure' => $isSecure,
//         'httponly' => true,
//         'samesite' => 'Lax'
//     ]);
//     setcookie("access_token", $newAccessToken, [
//         'expires' => strtotime($newAccessExpiry),
//         'path' => '/',
//         'secure' => $isSecure,
//         'httponly' => true,
//         'samesite' => 'Lax'
//     ]);

//     return $userId;
// }

// use PDO for database interactions instead of mysqli to improve security and prevent SQL injection.
function verifyUserCookie()
{
    global $conn;

    if (empty($_COOKIE['c_user'])) {
        return false;
    }

    $parts = explode('.', $_COOKIE['c_user']);
    if (count($parts) !== 2) return false;

    [$userId, $signature] = $parts;

    if (!ctype_digit($userId)) return false;

    $userId = (int)$userId;

    $expectedSignature = hash_hmac('sha256', $userId, APP_SECRET);

    if (!hash_equals($expectedSignature, $signature)) {
        return false;
    }

    // PDO query FIX
    $stmt = $conn->prepare("
        SELECT access_token, access_expiry, refresh_token, refresh_expiry
        FROM tblUsers
        WHERE user_id = ?
        LIMIT 1
    ");

    $stmt->execute([$userId]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$res) return false;

    $now = time();

    // ACCESS TOKEN CHECK
    if (!empty($_COOKIE['access_token'])) {

        $hashedAccess = hash('sha256', $_COOKIE['access_token']);

        $accessValid = hash_equals($res['access_token'], $hashedAccess);
        $accessExpired = strtotime($res['access_expiry']) <= $now;

        if ($accessValid && !$accessExpired) {
            return $userId;
        }
    }

    // REFRESH TOKEN
    if (empty($_COOKIE['refresh_token'])) {
        return false;
    }

    $hashedRefresh = hash('sha256', $_COOKIE['refresh_token']);

    if (!hash_equals($res['refresh_token'], $hashedRefresh)) {
        return false;
    }

    if (strtotime($res['refresh_expiry']) <= $now) {
        return false;
    }

    // ROTATE TOKEN (PDO FIX)
    $newAccessToken = bin2hex(random_bytes(32));
    $hashedAccessToken = hash('sha256', $newAccessToken);
    $newAccessExpiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));

    $update = $conn->prepare("
        UPDATE tblUsers
        SET access_token = ?,
            access_expiry = ?
        WHERE user_id = ?
    ");

    $update->execute([
        $hashedAccessToken,
        $newAccessExpiry,
        $userId
    ]);

    // cookies
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

    $newSignature = hash_hmac('sha256', $userId, APP_SECRET);

    setcookie("c_user", $userId . "." . $newSignature, [
        'expires' => strtotime('+1 days'),
        'path' => '/',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    setcookie("access_token", $newAccessToken, [
        'expires' => strtotime($newAccessExpiry),
        'path' => '/',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    return $userId;
}
// var_dump($_COOKIE);
// exit;
