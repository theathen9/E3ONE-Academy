<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    $stmt = $conn->prepare("SELECT user_id FROM tblUsers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $update = $conn->prepare("UPDATE tblUsers SET reset_token = ?, reset_expiry = ? WHERE user_id = ?");
        $update->bind_param("ssi", $token, $expiry, $user['user_id']);
        $update->execute();

        $resetLink = "http://localhost/system-management/auth/reset_password.php?token=$token";

        echo "Password reset link: <a href='$resetLink'>$resetLink</a>";
    } else {
        echo "Email not found!";
    }
}
?>

<form method="post">
    Email: <input type="email" name="email" required>
    <button type="submit">Send Reset Link</button>
</form>