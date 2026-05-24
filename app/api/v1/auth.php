<?php
// ./api/v1/login.php

session_start();

header('Content-Type: application/json');

include_once __DIR__ . '/../../../config/app.php';
include_once __DIR__ . '/../../../config/db.php';

/**
 * =========================================
 * JSON RESPONSE FUNCTION
 * =========================================
 */
function jsonResponse($success, $message, $data = [], $status = 200)
{
    http_response_code($status);

    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data
    ]);

    exit;
}

/**
 * =========================================
 * ONLY POST METHOD
 * =========================================
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, "Method not allowed", [], 405);
}

/**
 * =========================================
 * GET JSON INPUT
 * =========================================
 */
$input = json_decode(file_get_contents("php://input"), true);

$email = trim($input['email'] ?? '');
$password = trim($input['password'] ?? '');

/**
 * =========================================
 * VALIDATION
 * =========================================
 */
if (empty($email) || empty($password)) {

    jsonResponse(false, "Email and password are required", [], 422);
}

/**
 * =========================================
 * FIND USER
 * =========================================
 */
$stmt = $conn->prepare("
    SELECT
        user_id,
        reference_id,
        reference_type,
        role_id,
        email,
        password,
        status
    FROM tblUsers
    WHERE email = ?
    LIMIT 1
");

if (!$stmt) {
    jsonResponse(false, "Database prepare failed", [], 500);
}

$stmt->bind_param("s", $email);

if (!$stmt->execute()) {
    jsonResponse(false, "Database execute failed", [], 500);
}

$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();

/**
 * =========================================
 * USER NOT FOUND
 * =========================================
 */
if (!$user) {
    jsonResponse(false, "Invalid credentials", [], 401);
}

/**
 * =========================================
 * ACCOUNT STATUS
 * =========================================
 */
// if ($user['status'] !== 'active') {
//     jsonResponse(false, "Account disabled", [], 403);
// }

/**
 * =========================================
 * VERIFY PASSWORD
 * =========================================
 */
if (!password_verify($password, $user['password'])) {
    jsonResponse(false, "Invalid credentials", [], 401);
}

/**
 * =========================================
 * GENERATE TOKENS
 * =========================================
 */
$accessToken  = bin2hex(random_bytes(32));
$refreshToken = bin2hex(random_bytes(64));

$hashedAccessToken  = hash('sha256', $accessToken);
$hashedRefreshToken = hash('sha256', $refreshToken);

$accessExpiry  = date('Y-m-d H:i:s', strtotime('+15 minutes'));
$refreshExpiry = date('Y-m-d H:i:s', strtotime('+7 days'));

/**
 * =========================================
 * SAVE TOKENS
 * =========================================
 */
$update = $conn->prepare("
    UPDATE tblUsers
    SET
        access_token = ?,
        access_expiry = ?,
        refresh_token = ?,
        refresh_expiry = ?,
        last_login = NOW()
    WHERE user_id = ?
");

if (!$update) {
    jsonResponse(false, "Database update prepare failed", [], 500);
}

$update->bind_param(
    "ssssi",
    $hashedAccessToken,
    $accessExpiry,
    $hashedRefreshToken,
    $refreshExpiry,
    $user['user_id']
);

if (!$update->execute()) {
    jsonResponse(false, "Failed to save tokens", [], 500);
}

$update->close();

/**
 * =========================================
 * CREATE SESSION
 * =========================================
 */
session_regenerate_id(true);

$_SESSION['loggedin'] = true;
$_SESSION['user_id'] = (int)$user['user_id'];
$_SESSION['reference_id'] = (int)$user['reference_id'];
$_SESSION['reference_type'] = $user['reference_type'];
$_SESSION['role_id'] = (int)$user['role_id'];

/**
 * =========================================
 * CREATE USER COOKIE
 * =========================================
 */
$signature = hash_hmac(
    'sha256',
    $user['user_id'],
    APP_SECRET
);

$cUser = $user['user_id'] . "." . $signature;

/**
 * =========================================
 * COOKIE SETTINGS
 * =========================================
 */
$isSecure = (
    !empty($_SERVER['HTTPS']) &&
    $_SERVER['HTTPS'] !== 'off'
);

$cookieOptions = [
    'path' => '/',
    'secure' => $isSecure,
    'httponly' => true,
    'samesite' => 'Lax'
];

/**
 * =========================================
 * SET COOKIES
 * =========================================
 */
setcookie(
    'c_user',
    $cUser,
    $cookieOptions + [
        'expires' => strtotime($refreshExpiry)
    ]
);

setcookie(
    'access_token',
    $accessToken,
    $cookieOptions + [
        'expires' => strtotime($accessExpiry)
    ]
);

setcookie(
    'refresh_token',
    $refreshToken,
    $cookieOptions + [
        'expires' => strtotime($refreshExpiry)
    ]
);

/**
 * =========================================
 * SUCCESS RESPONSE
 * =========================================
 */
jsonResponse(
    true,
    "Login successful",
    [
        "user_id" => (int)$user['user_id'],
        "role_id" => (int)$user['role_id'],
        "reference_id" => (int)$user['reference_id'],
        "reference_type" => $user['reference_type'],
        "access_token" => $accessToken,
        "refresh_token" => $refreshToken
    ],
    200
);