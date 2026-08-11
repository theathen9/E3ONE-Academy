<?php
// helpers/csrf.php

function generateCSRF(): string
{
    $token = $_COOKIE['XSRF-TOKEN'] ?? '';

    if (
        empty($token) ||
        !preg_match('/^[a-f0-9]{64}$/', $token)
    ) {
        $token = bin2hex(random_bytes(32));

        $secure = (
            !empty($_SERVER['HTTPS']) &&
            $_SERVER['HTTPS'] !== 'off'
        );

        setcookie('XSRF-TOKEN', $token, [
            'expires'  => time() + 3600,
            'path'     => '/',
            'secure'   => $secure,
            'httponly' => false,
            'samesite' => 'Lax',
        ]);
    }

    return $token;
}

function csrf_field(): string
{
    $token = generateCSRF();

    return '<input type="hidden" name="csrf_token" value="' .
        htmlspecialchars($token, ENT_QUOTES, 'UTF-8') .
        '">';
}

function verifyCSRF(): void
{
    $sessionToken = $_COOKIE['XSRF-TOKEN'] ?? '';

    $requestToken =
        $_POST['csrf_token']
        ?? $_SERVER['HTTP_X_CSRF_TOKEN']
        ?? '';

    if (
        empty($sessionToken) ||
        empty($requestToken)
    ) {
        http_response_code(403);

        exit(json_encode([
            'success' => false,
            'error' => 'CSRF missing'
        ]));
    }

    if (
        !is_string($sessionToken) ||
        !is_string($requestToken) ||
        !hash_equals($sessionToken, $requestToken)
    ) {
        http_response_code(403);

        exit(json_encode([
            'success' => false,
            'error' => 'Invalid CSRF token'
        ]));
    }
}
