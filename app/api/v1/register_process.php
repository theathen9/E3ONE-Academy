<?php
// ./api/v1/register_process.php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/bootstrap.php';
if (!isset($_COOKIE['refresh_token']) || empty($_COOKIE['refresh_token'])) {
    // echo json_encode(["error" => "Session expired"]);
    http_response_code(401);
    exit;
}
if (!isset($_SESSION['csrf_token'])) {
    echo json_encode(["error" => "Session expired"]);
    exit;
}
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(["error" => "Invalid CSRF"]);
    exit;
}

$db = new DB($conn);

$userCRUD = new ORM($db, "tblUsers", "user_id");
$staffCRUD = new ORM($db, "tblEmployees", "employee_id");

$platformCRUD = new ORM($db, "tblPlatforms", "platform_id");

$studentCRUD = new ORM($db, "tblStudents", "student_id");
$ClassCRUD = new ORM($db, "tblClasses", "class_id");
$DepartmentCRUD = new ORM($db, "tblDepartments", "department_id");

$enrollCRUD = new ORM($db, "tblEnrollments", "enrollment_id");
$InvoiceCRUD = new ORM($db, "tblInvoices", "invoice_id");
$paymentCRUD = new ORM($db, "tblPayments", "payment_id");
$invoiceitemCRUD = new ORM($db, "tblInvoiceItems", "item_id");

function generateUserCode()
{
    return (bin2hex(random_bytes(32)));
}

$userCode = generateUserCode();

function generateStudentCode($conn)
{
    $year = date("Y");
    $res = $conn->query("SELECT student_id FROM tblstudents where status != 'draft' ORDER BY student_id DESC LIMIT 1
    ");
    $row = $res->fetch_assoc();
    $lastId = $row['student_id'] ?? 0;
    $count = $lastId + 1;
    return "STU-$year-" . str_pad($count, 2, '0', STR_PAD_LEFT);
}
$action = post('action');
$step = (int) post('step');
$role_id = null;
$department_id = (int) post('department_id');
$map = [
    1 => 1,
    2 => 2,
    3 => 2,
    4 => 2,
    5 => 3,
    6 => 3
];


if ($action === 'register_staff') {

    verifyCSRF();
    if (!isset($map[$department_id])) {

        echo json_encode([
            'success' => false,
            'error' => 'Invalid department role mapping'
        ]);

        exit;
    }
    $role_id = $map[$department_id];
    try {
        $dob = DateTime::createFromFormat('d-m-Y', post('dob'));
        $photo = uploadPhoto('staff_photo');
        $data = [
            'first_name_kh' => post('first_name_kh'),
            // 'middle_name' => post('middle_name', ''),
            'last_name_kh' => post('last_name_kh'),
            'first_name_en' => post('first_name_en'),
            // 'middle_name_eng' => post('middle_name_eng', ''),
            'last_name_en' => post('last_name_en'),
            'dob' => post('dob'),
            'gender' => post('gender'),
            'hired_at' => post('hired_at'),
            'department_id' => post('department_id'),
            'birth_province' => post('birth_addr_province'),
            'birth_district' => post('birth_addr_district'),
            'birth_commune' => post('birth_addr_commune'),
            'birth_village' => post('birth_addr_village'),
            'curr_addr_province' => post('curr_addr_province'),
            'curr_addr_district' => post('curr_addr_district'),
            'curr_addr_commune' => post('curr_addr_commune'),
            'curr_addr_village' => post('curr_addr_village'),
            'phone1' => post('phone1'),
            'phone2' => post('phone2', ''),
            'email' => strtolower(trim(post('email'))),
            'profile_image' => $photo ?: 'default-user.png'
        ];

        $id = $staffCRUD->insert($data);

        $check = $staffCRUD->where('employee_id', '=', $id)->first();
        if (empty($check->profile_image)) {
            $staffCRUD->where('employee_id', '=', $id)->update(['profile_image' => 'default-user.png']);
        }
        $platformData = [
            'employee_id'  => $id,
            'platform_type' => post('platform_type') ?? null,
            'account_name' => post('account_name') ?? null,
            'account_url'  => post('account_url') ?? null,
            'phone_number' => post('phone_number') ?? null,
        ];
        $platformCRUD->insert($platformData);
        $empCode = "EMP-" . date("Y") . "-" . str_pad($id, 5, '0', STR_PAD_LEFT);
        $username = strtolower("emp-" . date("Y") . str_pad($id, 5, '0', STR_PAD_LEFT));
        $userData = [
            'reference_id' => $id,
            'reference_type' => 'Employee',
            'username' => $username,
            'email' => post('email'),
            'password' => password_hash(11111, PASSWORD_DEFAULT),
            'role_id' => $role_id
        ];
        $userCRUD->insert($userData);
        // echo json_encode(['success' => true, 'data' => $data]);
        header("Location: " . BASE_URL . "/admin/register.php");
        exit;
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }

    exit;
}

// ==========================================================
// 🔥 STUDENT STEP 1 (CREATE DRAFT)
// ==========================================================
// ==========================================================
// ✅ STEP 1: CREATE STUDENT
// ==========================================================
if ($action === 'register_student' && $step === 1) {
    verifyCSRF();
    $conn->begin_transaction();
    if (!empty($_SESSION['student_id'])) {
        echo json_encode([
            'success' => true,
            'message' => 'Student already created in this session',
            'student_id' => $_SESSION['student_id']
        ]);
        exit;
    }

    try {
        $photo = uploadPhoto('student_photo');
        $guardian1_name = post('student_guardian1_first_name') . ' ' . post('student_guardian1_last_name');
        $guardian2_name = post('student_guardian2_first_name') . ' ' . post('student_guardian2_last_name');
        $data = [
            // 'student_code' => $studentCode,
            'register_at' => post('student_register_at'),
            'created_by' => post('created_by'),
            'first_name_kh' => post('student_first_name_kh'),
            'last_name_kh' => post('student_last_name_kh'),
            'first_name_en' => post('student_first_name_en'),
            'last_name_en' => post('student_last_name_en'),
            'dob' => post('student_dob'),
            'gender' => post('student_gender'),
            'academic_year' => post('student_academic_year'),
            'phone1' => post('student_phone1'),
            'email' => strtolower(trim(post('student_email'))),
            'birth_province' => post('student_birth_addr_province'),
            'birth_district' => post('student_birth_addr_district'),
            'birth_commune' => post('student_birth_addr_commune'),
            'birth_village' => post('student_birth_addr_village'),
            'curr_addr_province' => post('student_curr_addr_province'),
            'curr_addr_district' => post('student_curr_addr_district'),
            'curr_addr_commune' => post('student_curr_addr_commune'),
            'curr_addr_village' => post('student_curr_addr_village'),
            'guardian1_name' => $guardian1_name,
            'guardian2_name' =>  $guardian2_name,
            'guardian1_relationship' => post('student_guardian1_relationship'),
            'guardian2_relationship' => post('student_guardian2_relationship'),
            'guardian1_phone' => post('student_guardian1_phone'),
            'guardian2_phone' => post('student_guardian2_phone'),
            'guardian_email' => post('student_guardian_email'),
            'guardian_curr_addr_province' => post('student_guardian_curr_addr_province'),
            'guardian_curr_addr_district' => post('student_guardian_curr_addr_district'),
            'guardian_curr_addr_commune' => post('student_guardian_curr_addr_commune'),
            'guardian_curr_addr_village' => post('student_guardian_curr_addr_village'),
            'profile_image' => $photo ?: 'default-user.png',
            'status' => 'draft'
        ];
        $id = $studentCRUD->insert($data);
        if (!$id) {
            throw new Exception("Insert student failed");
        }
        $_SESSION['student_id'] = $id;
        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'step' => 'step 2',
            'error' => $e->getMessage()
        ]);
        exit; // 🔥 VERY IMPORTANT
    }
    exit;
}


// ==========================================================
// ✅ STEP 2: ENROLL MULTIPLE CLASSES
// ==========================================================
// var_dump(session_id());
// var_dump($_SESSION);
// exit;
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');
error_log("STEP 2 HIT");
error_log("SESSION student_id: " . ($_SESSION['student_id'] ?? 'NULL'));
error_log("POST class_ids: " . json_encode($_POST['class_ids'] ?? null));
// exit;

if ($action === 'register_student' && $step == 2) {

    verifyCSRF();
    if (!isset($_SESSION['student_id'])) {
        echo json_encode([
            'success' => false,
            'error' => 'Session expired (student not created)'
        ]);
        exit;
    }
    $studentId = $_SESSION['student_id'];
    $classIds = post('class_ids');
    if (empty($classIds)) {
        echo json_encode([
            'success' => false,
            'error' => 'No classes selected'
        ]);
        exit;
    }
    // 🔥 prevent duplicate enrollment creation
    if (!empty($_SESSION['enrollment_id'])) {
        echo json_encode([
            'success' => true,
            'message' => 'Enrollments already created',
            'enrollment_id' => $_SESSION['enrollment_id']
        ]);
        exit;
    }
    $conn->begin_transaction();
    try {
        $enrollmentIds = [];
        foreach ($classIds as $classId) {
            //  lock class row
            $stmt = $conn->prepare("SELECT cl.class_id, c.price FROM tblClasses cl JOIN tblCourseSubjects cs ON cl.course_subject_id = cs.id
        JOIN tblCourses c ON cs.course_id = c.course_id WHERE cl.class_id = ?
        FOR UPDATE ");

            $stmt->bind_param("i", $classId);
            $stmt->execute();
            $class = $stmt->get_result()->fetch_assoc();
            if (!$class) {
                continue;
            }
            // duplicate enrollment protection
            $check = $conn->prepare(" SELECT enrollment_id FROM tblEnrollments WHERE student_id = ? AND class_id = ? LIMIT 1 FOR UPDATE");

            $check->bind_param("ii", $studentId, $classId);
            $check->execute();

            $exists = $check->get_result()->fetch_assoc();

            if ($exists) {
                continue;
            }

            $enrollId = $enrollCRUD->insert([
                'student_id' => $studentId,
                'class_id' => $classId,
                'price' => $class['price'],
                'created_by' => post('created_by'),
                'discount' => 0,
                'status' => 'draft'
            ]);

            if ($enrollId) {
                $enrollmentIds[] = $enrollId;
            }
        }

        if (empty($enrollmentIds)) {
            throw new Exception("No valid enrollments created");
        }

        $_SESSION['enrollment_id'] = $enrollmentIds;

        $conn->commit();
        echo json_encode([
            'success' => true,
            'enrollment_ids' => $enrollmentIds
        ]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'step' => 2,
            'error' => $e->getMessage()
        ]);
        exit;
    }
    exit;
}

if ($action === 'register_student' && $step === 3) {
    verifyCSRF();
    $conn->begin_transaction();
    $studentId = $_SESSION['student_id'] ?? null;
    $enrollIds = $_SESSION['enrollment_id'] ?? [];
    $studentCode = generateStudentCode($conn);
    if (empty($enrollIds)) {
        echo json_encode([
            'success' => false,
            'error' => 'No enrollments in session (step 2 missing)'
        ]);
        exit;
    }
    if (!$studentId) {
        echo json_encode([
            'success' => false,
            'message' => 'step 3',
            'error' => 'Session expired'
        ]);
        exit;
    }
    $check = $enrollCRUD
        ->where('student_id', '=', $studentId)
        ->get();
    if (empty($check)) {
        echo json_encode([
            'success' => false,
            'error' => 'Step 2 not completed (no enrollments)'
        ]);
        exit;
    }
    try {
        $studentRoleId = 3;
        $role_id = $studentRoleId;
        $studentCRUD->where('student_id', '=', $studentId)->update(['student_code' => $studentCode]);
        $existingInvoice = $InvoiceCRUD->where('student_id', '=', $studentId)->where('status', '!=', 'Cancelled')->first();

        if ($existingInvoice) {
            throw new Exception("Invoice already exists");
        }
        // 🔒 Lock student row
        $stmt = $conn->prepare("SELECT status FROM tblStudents WHERE student_id=? FOR UPDATE");
        $stmt->bind_param("i", $studentId);
        $stmt->execute();
        $check = $stmt->get_result()->fetch_assoc();

        if ($check && $check['status'] === 'active') {
            throw new Exception("Student already activated");
        }
        // ✅ Prevent duplicate user
        $existingUser = $userCRUD
            ->where('reference_id', '=', $studentId)
            ->where('reference_type', '=', 'Student')
            ->first();

        if (!$existingUser) {
            $username = strtolower("stu-" . date("Y") . str_pad($studentId, 5, '0', STR_PAD_LEFT));

            $userCRUD->insert([
                'reference_id' => $studentId,
                'reference_type' => 'Student',
                'username' => $username,
                'email' => post('student_email'),
                'password' => password_hash(11111, PASSWORD_DEFAULT),
                'role_id' => $role_id
            ]);
        }

        // 🔒 Get enrollments WITH LOCK
        $stmt = $conn->prepare("SELECT e.*, c.class_name FROM tblEnrollments e JOIN tblClasses c ON e.class_id = c.class_id
            WHERE e.student_id = ? FOR UPDATE");
        $stmt->bind_param("i", $studentId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception("No enrollments found");
        }
        // 🔥 Create invoice
        $invoiceId = $InvoiceCRUD->insert([
            'student_id' => $studentId,
            'invoice_date' => date('Y-m-d'),
            'total_amount' => 0,
            'status' => 'Unpaid',
            'created_by' => post('created_by')
        ]);

        $invoiceCode =
            "INV-" .
            date("Y") . "-" .
            str_pad($invoiceId, 5, '0', STR_PAD_LEFT);

        $total = 0;
        $classIds = [];
        while ($row = $result->fetch_assoc()) {
            $amount = $row['price'] - $row['discount'];
            $total += $amount;
            $classIds[] = $row['class_id'];
            $invoiceitemCRUD->insert([
                'invoice_id' => $invoiceId,
                'enrollment_id' => $row['enrollment_id'],
                'description' => 'Class: ' . $row['class_name'],
                'amount' => $amount
            ]);
        }
        // 💰 Payment calculation
        $discount = (float) post('discount', 0);
        $paid     = (float) post('amount_paid', 0);

        $finalTotal = $total - ($total * $discount / 100);

        if ($paid > $finalTotal) {
            throw new Exception("Paid exceeds total");
        }
        $status = 'Unpaid';
        if ($paid == $finalTotal) $status = 'Paid';
        elseif ($paid > 0) $status = 'Partial';

        // ✅ Update invoice
        $InvoiceCRUD->where('invoice_id', '=', $invoiceId)->update(['total_amount' => $finalTotal, 'status' => $status]);
        // ✅ Insert payment
        $paymentId = null;
        if ($paid > 0) {
            $paymentId = $paymentCRUD->insert([
                'invoice_id' => $invoiceId,
                'payment_date' => date('Y-m-d'),
                'amount' => $paid,
                'payment_method_id' => post('payment_method_id'),
                'created_by' => post('created_by')
            ]);
        }
        // ==================================================
        //  FINAL ACTIVATION
        // ==================================================
        //  Activate enrollments
        $enrollCRUD->where('student_id', '=', $studentId)->update(['status' => 'active']);

        // Update class counts
        foreach (array_unique($classIds) as $classId) {
            $ClassCRUD->where('class_id', '=', $classId)->increment('current_students', 1);
        }
        // ✅ Activate student
        $studentCRUD->where('student_id', '=', $studentId)->update(['status' => 'active']);
        // 🧹 Cleanup session
        unset($_SESSION['student_id']);
        unset($_SESSION['enrollment_id']);
        $conn->commit();
        echo json_encode([
            'success'    => true,
            'invoice_id' => $invoiceId,
            'payment_id' => $paymentId,
            'student_id' => $studentId
        ]);
    } catch (Exception $e) {
        $conn->rollback();
        error_log("STEP 3 ERROR: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'step 3',
            'error' => $e->getMessage()
        ]);
        exit; // 🔥 VERY IMPORTANT
    }

    exit;
}


// ==========================================================
