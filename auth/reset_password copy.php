<?php
require_once '../config/db.php';

$token = $_GET['token'] ?? '';

$stmt = $conn->prepare("SELECT user_id, reset_expiry FROM tblUsers WHERE reset_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || strtotime($user['reset_expiry']) < time()) {
    die("Invalid or expired token!");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    if ($password !== $confirm) {
        echo "Passwords do not match!";
    } else {
        $hashed = password_hash($password, PASSWORD_BCRYPT);

        $update = $conn->prepare("UPDATE tblUsers SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE user_id = ?");
        $update->bind_param("si", $hashed, $user['user_id']);
        $update->execute();

        echo "Password updated successfully! <a href='signin.php'>Login</a>";
    }
}
?>

<form method="post">
    New Password: <input type="password" name="password" required><br>
    Confirm Password: <input type="password" name="confirm" required><br>
    <button type="submit">Reset Password</button>
</form>