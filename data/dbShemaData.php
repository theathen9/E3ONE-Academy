<?php
require_once "./config/db.php";

/**
 * ==========================
 * Employees CRUD
 * ==========================
 */
function getEmployees($conn) {
    return $conn->query("SELECT * FROM tblEmployees ORDER BY emp_id DESC");
}

function addEmployee($conn, $data) {
    $stmt = $conn->prepare("INSERT INTO tblEmployees (emp_id, name_kh, name_eng, position, phone, email, address) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "sssssss",
        $data['emp_id'], $data['name_kh'], $data['name_eng'],
        $data['position'], $data['phone'], $data['email'], $data['address']
    );
    return $stmt->execute();
}

function updateEmployee($conn, $id, $data) {
    $stmt = $conn->prepare("UPDATE tblEmployees SET emp_id=?, name_kh=?, name_eng=?, position=?, phone=?, email=?, address=? WHERE id=?");
    $stmt->bind_param(
        "sssssssi",
        $data['emp_id'], $data['name_kh'], $data['name_eng'],
        $data['position'], $data['phone'], $data['email'], $data['address'], $id
    );
    return $stmt->execute();
}

function deleteEmployee($conn, $id) {
    $stmt = $conn->prepare("DELETE FROM tblEmployees WHERE id=?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

/**
 * ==========================
 * Students CRUD
 * ==========================
 */
function getStudents($conn) {
    return $conn->query("SELECT * FROM tblStudent ORDER BY student_id DESC");
}

function addStudent($conn, $data) {
    $stmt = $conn->prepare("INSERT INTO tblStudent (student_id, name_kh, name_eng, dob, email, phone) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $data['student_id'], $data['name_kh'], $data['name_eng'], $data['dob'], $data['email'], $data['phone']);
    return $stmt->execute();
}

function updateStudent($conn, $id, $data) {
    $stmt = $conn->prepare("UPDATE tblStudent SET student_id=?, name_kh=?, name_eng=?, dob=?, email=?, phone=? WHERE id=?");
    $stmt->bind_param("ssssssi", $data['student_id'], $data['name_kh'], $data['name_eng'], $data['dob'], $data['email'], $data['phone'], $id);
    return $stmt->execute();
}

function deleteStudent($conn, $id) {
    $stmt = $conn->prepare("DELETE FROM tblStudent WHERE id=?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

/**
 * ==========================
 * Courses CRUD
 * ==========================
 */
function getCourses($conn) {
    return $conn->query("SELECT * FROM tblCourse ORDER BY course_id DESC");
}

function addCourse($conn, $data) {
    $stmt = $conn->prepare("INSERT INTO tblCourse (course_code, course_name, description) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $data['course_code'], $data['course_name'], $data['description']);
    return $stmt->execute();
}

function updateCourse($conn, $id, $data) {
    $stmt = $conn->prepare("UPDATE tblCourse SET course_code=?, course_name=?, description=? WHERE id=?");
    $stmt->bind_param("sssi", $data['course_code'], $data['course_name'], $data['description'], $id);
    return $stmt->execute();
}

function deleteCourse($conn, $id) {
    $stmt = $conn->prepare("DELETE FROM tblCourse WHERE id=?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

/**
 * ==========================
 * Departments CRUD
 * ==========================
 */
function getDepartments($conn) {
    return $conn->query("SELECT * FROM tblDepartment ORDER BY depm_id DESC");
}

function addDepartment($conn, $data) {
    $stmt = $conn->prepare("INSERT INTO tblDepartment (department_name) VALUES (?)");
    $stmt->bind_param("s", $data['department_name']);
    return $stmt->execute();
}

function updateDepartment($conn, $id, $data) {
    $stmt = $conn->prepare("UPDATE tblDepartment SET department_name=? WHERE id=?");
    $stmt->bind_param("si", $data['department_name'], $id);
    return $stmt->execute();
}

function deleteDepartment($conn, $id) {
    $stmt = $conn->prepare("DELETE FROM tblDepartment WHERE id=?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

/**
 * ==========================
 * Rooms CRUD
 * ==========================
 */
function getRooms($conn) {
    return $conn->query("SELECT * FROM tblRoom ORDER BY room_id DESC");
}

function addRoom($conn, $data) {
    $stmt = $conn->prepare("INSERT INTO tblRoom (room_name, capacity) VALUES (?, ?)");
    $stmt->bind_param("si", $data['room_name'], $data['capacity']);
    return $stmt->execute();
}

function updateRoom($conn, $id, $data) {
    $stmt = $conn->prepare("UPDATE tblRoom SET room_name=?, capacity=? WHERE id=?");
    $stmt->bind_param("sii", $data['room_name'], $data['capacity'], $id);
    return $stmt->execute();
}

function deleteRoom($conn, $id) {
    $stmt = $conn->prepare("DELETE FROM tblRoom WHERE id=?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

/**
 * ==========================
 * Enrollments CRUD
 * ==========================
 */
function getEnrollments($conn) {
    return $conn->query("SELECT * FROM tblenrollments ORDER BY id DESC");
}

function addEnrollment($conn, $data) {
    $stmt = $conn->prepare("INSERT INTO tblEnrollments (student_id, course_id, enrollment_date) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $data['student_id'], $data['course_id'], $data['enrollment_date']);
    return $stmt->execute();
}

function updateEnrollment($conn, $id, $data) {
    $stmt = $conn->prepare("UPDATE tblEnrollments SET student_id=?, course_id=?, enrollment_date=? WHERE id=?");
    $stmt->bind_param("iisi", $data['student_id'], $data['course_id'], $data['enrollment_date'], $id);
    return $stmt->execute();
}

function deleteEnrollment($conn, $id) {
    $stmt = $conn->prepare("DELETE FROM tblEnrollments WHERE id=?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}
