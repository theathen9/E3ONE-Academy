CREATE TABLE tblBranches (
    branch_id INT AUTO_INCREMENT PRIMARY KEY,
    branch_code VARCHAR(20) UNIQUE NOT NULL,
    branch_name VARCHAR(150) NOT NULL,
    phone1 VARCHAR(20),
    phone2 VARCHAR(20),
	 email VARCHAR(150),
    address TEXT,
    location VARCHAR(20),
    status TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE tblRooms (
    room_id INT AUTO_INCREMENT PRIMARY KEY,
    -- branch_id INT NOT NULL,
    room_name VARCHAR(50) NOT NULL,
    capacity INT NOT NULL,
    status ENUM('Active','Inactive') DEFAULT 'Active'

    -- FOREIGN KEY (branch_id) REFERENCES tblBranches(branch_id)
);

CREATE TABLE tblDepartments (
    department_id INT AUTO_INCREMENT PRIMARY KEY,
    -- branch_id INT NOT NULL,
    department_code VARCHAR(20) UNIQUE NOT NULL,
    department_name VARCHAR(100) NOT NULL,
    description TEXT,
    status TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    
    -- FOREIGN KEY (branch_id) REFERENCES tblBranches(branch_id)
);


CREATE TABLE tblEmployees (
    employee_id INT AUTO_INCREMENT PRIMARY KEY,

    department_id INT NULL,

    first_name_kh VARCHAR(100) NOT NULL,
    last_name_kh VARCHAR(100) NOT NULL,
    first_name_en VARCHAR(100) NOT NULL,
    last_name_en VARCHAR(100) NOT NULL,

    gender ENUM('Male','Female') NOT NULL,
    dob DATE NOT NULL,

    birth_village VARCHAR(100) NOT NULL,
    birth_commune VARCHAR(100) NOT NULL,
    birth_district VARCHAR(100) NOT NULL,
    birth_province VARCHAR(100) NOT NULL,

     curr_addr_village VARCHAR(100) NOT NULL,
    curr_addr_commune VARCHAR(100) NOT NULL,
    curr_addr_district VARCHAR(100) NOT NULL,
    curr_addr_province VARCHAR(100) NOT NULL,

    phone1 VARCHAR(20) NOT NULL,
    phone2 VARCHAR(20) NULL,

    email VARCHAR(150) NULL UNIQUE,
    

    profile_image VARCHAR(255),
    
	hired_at DATE NOT NULL,

    status ENUM('Active','Inactive') DEFAULT 'Active',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

   -- INDEX idx_department (department_id),
   -- INDEX idx_phone (phone1),

    FOREIGN KEY (department_id) REFERENCES tblDepartments(department_id) ON DELETE SET NULL
);



CREATE TABLE tblPlatforms (
	platform_id INT AUTO_INCREMENT PRIMARY KEY,
	employee_id INT NULL,
   platform_type VARCHAR(20)  NULL,
	account_name VARCHAR(150) NULL,
	account_url VARCHAR(255) NULL,
	phone_number VARCHAR(20) NULL,
   FOREIGN KEY (employee_id) REFERENCES tblEmployees(employee_id) ON DELETE SET NULL
);


CREATE TABLE tblStudents (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    student_code VARCHAR(100) UNIQUE,
   -- branch_id INT NULL,
    
    first_name_kh VARCHAR(100) NOT NULL,
    last_name_kh VARCHAR(100) NOT NULL,
    first_name_en VARCHAR(100) NOT NULL,
    last_name_en VARCHAR(100) NOT NULL,
    gender ENUM('Male','Female') NOT NULL,
    dob DATE NOT NULL,
    
    birth_village VARCHAR(100) NOT NULL,
    birth_commune VARCHAR(100) NOT NULL,
    birth_district VARCHAR(100) NOT NULL,
    birth_province VARCHAR(100) NOT NULL,
    
    curr_addr_village VARCHAR(100) NOT NULL,
    curr_addr_commune VARCHAR(100) NOT NULL,
    curr_addr_district VARCHAR(100) NOT NULL,
    curr_addr_province VARCHAR(100) NOT NULL,
    phone1 VARCHAR(20) NOT NULL,
    phone2 VARCHAR(20) NULL,
    email VARCHAR(150),
    
    profile_image VARCHAR(255) NULL,
    academic_year VARCHAR(100) NOT NULL,
    register_at DATE NULL,
    
    guardian1_name VARCHAR(150) NOT NULL,
    guardian2_name VARCHAR(150) NOT NULL,
    guardian1_relationship VARCHAR(150) NOT NULL,
    guardian2_relationship VARCHAR(150) NOT NULL,
    
    guardian_curr_addr_village VARCHAR(100) NOT NULL,
    guardian_curr_addr_commune VARCHAR(100) NOT NULL,
    guardian_curr_addr_district VARCHAR(100) NOT NULL,
    guardian_curr_addr_province VARCHAR(100) NOT NULL,
    guardian1_phone VARCHAR(20) ,
    guardian2_phone VARCHAR(20) ,
    guardian_email VARCHAR(150),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT NULL,

    status ENUM('draft','active','cancelled') DEFAULT 'draft',
    
    -- FOREIGN KEY (branch_id) REFERENCES tblBranches(branch_id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES tblEmployees(employee_id) ON DELETE SET NULL
);


CREATE TABLE tblSubjects ( 
   subject_id INT AUTO_INCREMENT PRIMARY KEY,
   subject_code VARCHAR(150) NOT NULL UNIQUE,
   subject_name VARCHAR(150) NOT NULL
    
);


CREATE TABLE tblCourses (
    course_id INT AUTO_INCREMENT PRIMARY KEY,
     course_code VARCHAR(50) NOT NULL UNIQUE,
    course_name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    duration VARCHAR(50) NULL
);

CREATE TABLE tblLevels (
    level_id INT AUTO_INCREMENT PRIMARY KEY,
    level_name VARCHAR(50),
    level_number TINYINT UNSIGNED NOT NULL UNIQUE
);

CREATE TABLE tblCourseSubjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT  NULL,
    subject_id INT NOT NULL,
    level_id INT NOT NULL,

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (course_id) REFERENCES tblCourses(course_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES tblSubjects(subject_id) ON DELETE CASCADE,
    FOREIGN KEY (level_id) REFERENCES tblLevels(level_id) ON DELETE CASCADE,
    UNIQUE (course_id, subject_id, level_id)
);



CREATE TABLE tblEmployeeSubjects (
    employee_subject_id INT AUTO_INCREMENT PRIMARY KEY,

    employee_id INT NOT NULL,
    subject_id INT NOT NULL,

    UNIQUE (employee_id, subject_id),


    FOREIGN KEY (employee_id)
        REFERENCES tblEmployees(employee_id)
        ON DELETE CASCADE,

    FOREIGN KEY (subject_id)
        REFERENCES tblSubjects(subject_id)
        ON DELETE CASCADE
);

CREATE TABLE tblTimeSlots (
    slot_id INT AUTO_INCREMENT PRIMARY KEY,
    slot_name VARCHAR(50),             
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    status ENUM('Active','Inactive') DEFAULT 'Active',
    
    UNIQUE(start_time, end_time)
);


CREATE TABLE tblDays (
    day_id INT AUTO_INCREMENT PRIMARY KEY,
    day_code VARCHAR(10) NOT NULL UNIQUE,   -- Mon
    day_name VARCHAR(50) NOT NULL,          -- Monday
    sort_order TINYINT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE
);

CREATE TABLE tblClasses (
    class_id INT AUTO_INCREMENT PRIMARY KEY,

    class_name VARCHAR(100),
    class_code VARCHAR(50) UNIQUE NOT NULL,

    course_subject_id INT NOT NULL,

    teacher_id INT NOT NULL,
    room_id INT NOT NULL,
    slot_id INT NOT NULL,

    academic_year VARCHAR(9) NOT NULL,

    max_students INT DEFAULT 50,
    current_students INT DEFAULT 0,

    status ENUM('Active','Inactive','Completed','Cancelled')
    DEFAULT 'Active',

    FOREIGN KEY (course_subject_id)
        REFERENCES tblCourseSubjects(id),

    FOREIGN KEY (teacher_id)
        REFERENCES tblEmployees(employee_id),

    FOREIGN KEY (room_id)
        REFERENCES tblRooms(room_id),

    FOREIGN KEY (slot_id)
        REFERENCES tblTimeSlots(slot_id)
);

CREATE TABLE tblTimetables (
    timetable_id INT AUTO_INCREMENT PRIMARY KEY,

    class_id INT NOT NULL,
    day_id INT NOT NULL,

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (class_id) REFERENCES tblClasses(class_id) ON DELETE CASCADE,
    FOREIGN KEY (day_id) REFERENCES tblDays(day_id) ON DELETE CASCADE,

    UNIQUE (class_id, day_id)
);


CREATE TABLE tblEnrollments (
    enrollment_id INT AUTO_INCREMENT PRIMARY KEY,

    student_id INT NOT NULL,
    class_id INT NOT NULL,

    price DECIMAL(10,2) NOT NULL,        -- ✅ snapshot price
    discount DECIMAL(10,2) DEFAULT 0,    -- optional per class

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,

    created_by INT NULL,
    
    status ENUM('draft','active','cancelled') DEFAULT 'draft',

    FOREIGN KEY (student_id) REFERENCES tblStudents(student_id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES tblClasses(class_id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES tblEmployees(employee_id) ON DELETE SET NULL,
  UNIQUE(student_id, class_id)
);




CREATE TABLE tblGrade (
    grade_id INT AUTO_INCREMENT PRIMARY KEY,
    
    grade_name VARCHAR(50) NOT NULL,
    
    min_average DECIMAL(5,2),
    max_average DECIMAL(5,2),
    
    remark VARCHAR(100)
);


CREATE TABLE tblScoreTypes (
    score_type_id INT AUTO_INCREMENT PRIMARY KEY,

    score_type_name VARCHAR(50) NOT NULL,
    
    percentage DECIMAL(5,2) DEFAULT 0
);



CREATE TABLE tblScores (
    score_id INT AUTO_INCREMENT PRIMARY KEY,

    enrollment_id INT NOT NULL,

    score_type_id INT NOT NULL,

    score DECIMAL(5,2) NOT NULL DEFAULT 0,

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (enrollment_id)
        REFERENCES tblEnrollments(enrollment_id)
        ON DELETE CASCADE,

    FOREIGN KEY (score_type_id)
        REFERENCES tblScoreTypes(score_type_id)
        ON DELETE CASCADE,

    CHECK (score >= 0 AND score <= 100),

    INDEX idx_enrollment (enrollment_id),
    INDEX idx_score_type (score_type_id)
);


CREATE TABLE tblStudentResults (
    result_id INT AUTO_INCREMENT PRIMARY KEY,

    enrollment_id INT NOT NULL,
    class_id INT NOT NULL,
    academic_year VARCHAR(20) NOT NULL,

    total_score DECIMAL(6,2) DEFAULT 0,
    average_score DECIMAL(5,2) DEFAULT 0,

    grade_id INT NULL,

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (enrollment_id)
        REFERENCES tblEnrollments(enrollment_id)
        ON DELETE CASCADE,

    FOREIGN KEY (class_id)
        REFERENCES tblClasses(class_id)
        ON DELETE CASCADE,

    FOREIGN KEY (grade_id)
        REFERENCES tblGrade(grade_id)
        ON DELETE SET NULL
);


CREATE TABLE tblAttendances (
    attendance_id INT AUTO_INCREMENT PRIMARY KEY,
    enrollment_id INT NOT NULL,

    attendance_date DATE NOT NULL,
    
    status ENUM('present','absent','late') NOT NULL DEFAULT 'absent',
    
     remarks VARCHAR(255) NULL,
     
     created_by INT NULL,

    FOREIGN KEY (enrollment_id) REFERENCES tblEnrollments(enrollment_id) ON DELETE CASCADE,
     FOREIGN KEY (created_by) REFERENCES tblEmployees(employee_id) ON DELETE CASCADE,
    UNIQUE(enrollment_id, attendance_date)
);



CREATE TABLE tblInvoices (
    invoice_id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_no VARCHAR(50) UNIQUE,
    student_id INT NOT NULL,

    invoice_date DATE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL CHECK (total_amount >= 0),
    discount DECIMAL(10,2) DEFAULT 0,
    
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_by INT NOT NULL ,
     status ENUM('Draft','Unpaid','Partial','Paid','Cancelled') DEFAULT 'Draft',

    FOREIGN KEY (student_id) REFERENCES tblStudents(student_id),
    FOREIGN KEY (created_by) REFERENCES tblEmployees(employee_id)
);


CREATE TABLE tblPaymentMethods (
    method_id INT AUTO_INCREMENT PRIMARY KEY,
    method_name VARCHAR(50) NOT NULL UNIQUE
);


CREATE TABLE tblPayments (
     payment_id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,

    payment_date DATE NOT NULL,
    amount DECIMAL(10,2) NOT NULL CHECK (amount > 0),

    payment_method_id INT NOT NULL,
    reference_no VARCHAR(100),

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_by INT NOT NULL,

	status ENUM('Pending','Completed','Failed') DEFAULT 'Completed',
	
    FOREIGN KEY (invoice_id) REFERENCES tblInvoices(invoice_id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES tblEmployees(employee_id),
    FOREIGN KEY (payment_method_id) REFERENCES tblPaymentMethods(method_id)
);

CREATE TABLE tblInvoiceItems (
   item_id INT AUTO_INCREMENT PRIMARY KEY,
   enrollment_id INT NOT  NULL,
    invoice_id INT NOT NULL,

    description VARCHAR(200) NOT NULL,
    amount DECIMAL(10,2) NOT NULL CHECK (amount >= 0),

    FOREIGN KEY (invoice_id) REFERENCES tblInvoices(invoice_id) ON DELETE CASCADE,
    FOREIGN KEY (enrollment_id) REFERENCES tblEnrollments(enrollment_id) ON DELETE CASCADE
    
);



CREATE TABLE tblRoles (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) UNIQUE NOT NULL,
    description VARCHAR(255)
);


CREATE TABLE tblPermissions (
    permission_id INT AUTO_INCREMENT PRIMARY KEY,
    permission_name VARCHAR(100) UNIQUE NOT NULL,
    description VARCHAR(255)
);

CREATE TABLE tblRolePermissions (
    role_id INT,
    permission_id INT,
    
    PRIMARY KEY (role_id, permission_id),
    
    FOREIGN KEY (role_id) REFERENCES tblRoles(role_id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES tblPermissions(permission_id) ON DELETE CASCADE
);


CREATE TABLE tblUsers (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    
    reference_id INT NOT NULL,
    reference_type ENUM('Employee','Student') NOT NULL,

    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,

    role_id INT NOT NULL,
    status TINYINT(1) DEFAULT 1,

    last_login DATETIME NULL,

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- 🔐 Password reset
    reset_token CHAR(64) NULL,
    reset_expiry DATETIME NULL,
    
     user_agent TEXT,
    ip_address VARCHAR(45),
    
    
    access_token VARCHAR(255) NULL,
    access_expiry DATETIME NULL,

    refresh_token CHAR(64) NULL,
    refresh_expiry DATETIME NULL,
    public_id VARCHAR(20) UNIQUE,

    FOREIGN KEY (role_id) REFERENCES tblRoles(role_id)
);

ALTER TABLE tblUsers
ADD COLUMN user_agent TEXT,
ADD COLUMN ip_address VARCHAR(45);

ALTER TABLE tblUsers
ADD COLUMN public_id VARCHAR(20) UNIQUE;

-- CREATE TABLE tblUserTokens (
--    token_id INT AUTO_INCREMENT PRIMARY KEY,
--
  --  user_id INT NOT NULL,
--
  --  access_token VARCHAR(255) NOT NULL,
   -- access_expiry DATETIME NOT NULL,
--
  --  refresh_token VARCHAR(255) NOT NULL,
    -- refresh_expiry DATETIME NOT NULL,
--
  --  device_info VARCHAR(255) NULL,
  --  ip_address VARCHAR(45) NULL,
--
  --  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    -- FOREIGN KEY (user_id) REFERENCES tblUsers(user_id) ON DELETE CASCADE
-- );



RENAME TABLE tblenrollments TO tblEnrollments;