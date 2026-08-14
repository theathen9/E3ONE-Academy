<?php
// ./helpers/csrf.php
function generateCSRF()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
}

function verifyCSRF()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $token = $_POST['csrf_token']
        ?? $_SERVER['HTTP_X_CSRF_TOKEN']
        ?? '';

    if (!$token || !isset($_SESSION['csrf_token'])) {
        http_response_code(403);
        exit(json_encode([
            "success" => false,
            "error" => "CSRF missing",
            "debug" => [
                "session" => $_SESSION['csrf_token'] ?? null,
                "post" => $token
            ]
        ]));
    }

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        exit(json_encode([
            "success" => false,
            "error" => "Invalid CSRF token"
        ]));
    }
}
