<?php
session_start();

if (!isset($_SESSION["loggedin"])) {
if (isset($_COOKIE["remember_token"])) {

$token = $_COOKIE["remember_token"];

$stmt = $conn->prepare("SELECT * FROM users WHERE remember_token=? AND remember_expire > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$res = $stmt->get_result();

if ($user = $res->fetch_assoc()) {
$_SESSION["loggedin"] = true;
$_SESSION["user_id"] = $user["id"];
$_SESSION["c_user"] = $user["username"];
$_SESSION["role"] = $user["role"];
$_SESSION["email"] = $user["email"];
return;
}
}
header("Location: ../auth/login.php");
exit;
}