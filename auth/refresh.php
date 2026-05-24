<?php

header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../config/bootstrap.php';

global $conn;

if (empty($_COOKIE['refresh_token'])) {
    http_response_code(401);
    echo json_encode(["error" => "No refresh token"]);
    exit;
}

// =========================
// VERIFY REFRESH TOKEN
// =========================

$hashedRefresh = hash('sha256', $_COOKIE['refresh_token']);

$stmt = $conn->prepare("
    SELECT user_id, refresh_token, refresh_expiry
    FROM tblUsers
    WHERE refresh_token = ?
    LIMIT 1
");

$stmt->bind_param("s", $hashedRefresh);
$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();

if (!$user || strtotime($user['refresh_expiry']) <= time()) {
    http_response_code(401);
    echo json_encode(["error" => "Refresh expired"]);
    exit;
}

// =========================
// GENERATE NEW ACCESS TOKEN
// =========================

$newAccessToken = bin2hex(random_bytes(32));
$hashedAccess = hash('sha256', $newAccessToken);
$expiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));

$update = $conn->prepare("
    UPDATE tblUsers
    SET access_token = ?, access_expiry = ?
    WHERE user_id = ?
");

$update->bind_param("ssi", $hashedAccess, $expiry, $user['user_id']);
$update->execute();

// =========================
// SET COOKIE
// =========================

setcookie("access_token", $newAccessToken, [
    'expires' => strtotime($expiry),
    'path' => '/',
    'httponly' => true,
    'secure' => !empty($_SERVER['HTTPS']),
    'samesite' => 'Lax'
]);

echo json_encode([
    "access_token" => $newAccessToken,
    "expires_in" => 300
]);