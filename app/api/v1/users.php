<?php

header('Content-Type: application/json');

include_once __DIR__ . '/../../../config/bootstrap.php';
include_once __DIR__ . '/auth.php';

// include_once __DIR__ . '/../../../config/bootstrap.php';
$user_id = checkAuth();

if (!isset($_COOKIE['refresh_token']) || empty($_COOKIE['refresh_token'])) {
    http_response_code(401);
    exit;
}

if (!isset($user_id)) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized"
    ]);
    exit;
}

$db = new DB($conn);
$userORM = new ORM($db, "tblUsers u", "user_id");

$user = $userORM
    ->select([
        "u.user_id",
        "u.username",
        "u.email AS login_email",
        "u.reference_type",
        "r.role_name",
        "e.employee_id",

        "TRIM(COALESCE(e.first_name_kh,'') || ' ' || COALESCE(e.last_name_kh,'')) AS full_name_kh",

        "TRIM(COALESCE(e.first_name_en,'') || ' ' || COALESCE(e.last_name_en,'')) AS full_name_en",

        "CASE
            WHEN e.first_name_kh IS NOT NULL AND e.first_name_kh <> ''
                THEN TRIM(e.first_name_kh || ' ' || COALESCE(e.last_name_kh,''))
            WHEN e.first_name_en IS NOT NULL AND e.first_name_en <> ''
                THEN TRIM(e.first_name_en || ' ' || COALESCE(e.last_name_en,''))
            ELSE u.username
        END AS display_name",

        "e.gender",
        "e.phone1",
        "e.phone2",
        "e.email AS employee_email",
        "e.profile_image",
        "e.status",
        "e.hired_at"
    ])
    ->join(
        "tblEmployees e",
        "u.reference_id = e.employee_id AND u.reference_type = 'Employee'",
        "LEFT"
    )
    ->join(
        "tblRoles r",
        "u.role_id = r.role_id",
        "LEFT"
    )
    ->where("u.user_id", "=", $user_id)
    ->first();

echo json_encode([
    "success" => true,
    "data" => [
        ...$user,
        "display_name" =>
            !empty($user['full_name_kh']) ? $user['full_name_kh']
            : (!empty($user['full_name_en']) ? $user['full_name_en']
            : $user['username'])
    ],
    "debug" => [
        'session_id' => session_id(),
        "cookie" => $_COOKIE,
        "session" => $_SESSION,
    ]
]);
?>
