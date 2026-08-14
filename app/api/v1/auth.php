<?php
// ./app/api/v1/auth.php
// use PDO for database interactions instead of mysqli to improve security and prevent SQL injection.
function checkAuth()
{
    global $conn;

    /*
     * =========================================================
     * 1. EXISTING SESSION
     * =========================================================
     */
    if (
        isset($_SESSION['loggedin']) &&
        $_SESSION['loggedin'] === true &&
        !empty($_SESSION['user_id']) &&
        isset($_SESSION['last_auth_check']) &&
        (time() - $_SESSION['last_auth_check']) < 300
    ) {
        return (int) $_SESSION['user_id'];
    }

    /*
     * =========================================================
     * 2. COOKIE AUTHENTICATION
     * =========================================================
     */
    $userId = verifyUserCookie();

    if (!$userId) {
        return false;
    }

    /*
     * =========================================================
     * 3. REGENERATE SESSION
     * =========================================================
     */
    session_regenerate_id(true);

    /*
     * =========================================================
     * 4. LOAD ACTIVE USER
     * =========================================================
     */
    $stmt = $conn->prepare("
        SELECT
            u.user_id,
            u.reference_id,
            u.reference_type,
            u.role_id,
            r.role_name
        FROM tblUsers u
        INNER JOIN tblRoles r
            ON r.role_id = u.role_id
        WHERE u.user_id = ?
          AND u.status = 1
        LIMIT 1
    ");

    $stmt->execute([$userId]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        return false;
    }

    /*
     * =========================================================
     * 5. CREATE SESSION
     * =========================================================
     */
    $_SESSION['loggedin'] = true;
    $_SESSION['user_id'] = (int) $user['user_id'];
    $_SESSION['reference_id'] = (int) $user['reference_id'];
    $_SESSION['reference_type'] = $user['reference_type'];
    $_SESSION['role'] = strtolower(trim($user['role_name']));
    $_SESSION['last_auth_check'] = time();

    return (int) $user['user_id'];
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



// use PDO for database interactions instead of mysqli to improve security and prevent SQL injection.
function verifyUserCookie()
{
    global $conn;

    if (empty($_COOKIE['c_user'])) {
        return false;
    }

    $parts = explode('.', $_COOKIE['c_user']);

    if (count($parts) !== 2) {
        return false;
    }

    [$userId, $signature] = $parts;

    if (!ctype_digit($userId)) {
        return false;
    }

    $userId = (int) $userId;

    // Verify c_user signature
    $expectedSignature = hash_hmac(
        'sha256',
        (string) $userId,
        APP_SECRET
    );

    if (!hash_equals($expectedSignature, $signature)) {
        return false;
    }

    /*
     * IMPORTANT:
     * signin.php stores tokens in tblUserTokens,
     * NOT tblUsers.
     */
    $stmt = $conn->prepare("
        SELECT
            token_id,
            access_token,
            access_expiry,
            refresh_token,
            refresh_expiry
        FROM tblUserTokens
        WHERE user_id = ?
        ORDER BY token_id DESC
        LIMIT 1
    ");

    $stmt->execute([$userId]);

    $token = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$token) {
        return false;
    }

    $now = time();

    /*
     * =========================================================
     * 1. ACCESS TOKEN
     * =========================================================
     */
    if (!empty($_COOKIE['access_token'])) {

        $hashedAccess = hash(
            'sha256',
            $_COOKIE['access_token']
        );

        $accessValid = !empty($token['access_token'])
            && hash_equals(
                $token['access_token'],
                $hashedAccess
            );

        $accessExpired = empty($token['access_expiry'])
            || strtotime($token['access_expiry']) <= $now;

        if ($accessValid && !$accessExpired) {
            return $userId;
        }
    }

    /*
     * =========================================================
     * 2. REFRESH TOKEN
     * =========================================================
     */
    if (empty($_COOKIE['refresh_token'])) {
        return false;
    }

    $hashedRefresh = hash(
        'sha256',
        $_COOKIE['refresh_token']
    );

    $refreshValid = !empty($token['refresh_token'])
        && hash_equals(
            $token['refresh_token'],
            $hashedRefresh
        );

    $refreshExpired = empty($token['refresh_expiry'])
        || strtotime($token['refresh_expiry']) <= $now;

    if (!$refreshValid || $refreshExpired) {
        return false;
    }

    /*
     * =========================================================
     * 3. ROTATE ACCESS TOKEN
     * =========================================================
     */
    $newAccessToken = bin2hex(random_bytes(32));

    $hashedAccessToken = hash(
        'sha256',
        $newAccessToken
    );

    $newAccessExpiry = date(
        'Y-m-d H:i:s',
        strtotime('+5 minutes')
    );

    $update = $conn->prepare("
        UPDATE tblUserTokens
        SET
            access_token = ?,
            access_expiry = ?
        WHERE token_id = ?
          AND user_id = ?
    ");

    $update->execute([
        $hashedAccessToken,
        $newAccessExpiry,
        $token['token_id'],
        $userId
    ]);

    /*
     * =========================================================
     * 4. UPDATE ACCESS COOKIE
     * =========================================================
     */
    $isSecure = (
        !empty($_SERVER['HTTPS'])
        && $_SERVER['HTTPS'] !== 'off'
    );

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
