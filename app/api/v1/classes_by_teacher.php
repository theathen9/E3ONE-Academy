<?php
// ./api/v1/class_by_teacher.php
header("Content-Type: application/json");
require_once __DIR__ . '/../../../config/bootstrap.php';
$teacherId = $_GET['teacher_id'] ?? null;
$response = [];
try {
    $sql = "SELECT * FROM tblClasses WHERE 1=1";
    $params = [];
    $types = "";
    if ($teacherId && $teacherId !== 'allTeachers') {
        $sql .= " AND teacher_id = ?";
        $params[] = (int)$teacherId;
        $types .= "i";
    }
    $sql .= " ORDER BY class_name ASC";
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $classes = [];
    while ($row = $result->fetch_assoc()) {
        $classes[] = $row;
    }
    $response = [
        "success" => true,
        "data" => $classes
    ];

} catch (Exception $e) {
    $response = [
        "success" => false,
        "message" => $e->getMessage()
    ];
}

echo json_encode($response);