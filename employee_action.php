<?php
require_once "./config/db.php";
require_once "./data/dbShemaData.php";
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$response = ['success'=>false, 'message'=>'Invalid request'];

if($action == 'add'){
    $data = $_POST;
    if(addEmployee($conn, $data)) $response = ['success'=>true,'message'=>'Employee added'];
    else $response = ['success'=>false,'message'=>$conn->error];

} elseif($action == 'edit'){
    $id = intval($_POST['id']);
    $data = $_POST;
    if(updateEmployee($conn, $id, $data)) $response = ['success'=>true,'message'=>'Employee updated'];
    else $response = ['success'=>false,'message'=>$conn->error];

} elseif($action == 'delete'){
    $id = intval($_POST['id']);
    if(deleteEmployee($conn, $id)) $response = ['success'=>true,'message'=>'Employee deleted'];
    else $response = ['success'=>false,'message'=>$conn->error];

} elseif($action == 'fetch'){
    $res = $conn->query("SELECT * FROM tblEmployees ORDER BY id DESC");
    $data = [];
    while($row = $res->fetch_assoc()) $data[] = $row;
    $response = ['success'=>true, 'data'=>$data];

} elseif($action == 'get'){
    $id = intval($_POST['id']);
    $res = $conn->query("SELECT * FROM tblEmployees WHERE id=$id");
    $data = $res->fetch_assoc();
    $response = ['success'=>true,'data'=>$data];
}

echo json_encode($response);
