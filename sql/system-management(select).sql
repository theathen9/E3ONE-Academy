SELECT p.permission_name
FROM tblUsers u
JOIN tblRoles r ON u.role_id = r.role_id
JOIN tblRolePermissions rp ON r.role_id = rp.role_id
JOIN tblPermissions p ON rp.permission_id = p.permission_id
WHERE u.user_id = 1;


SHOW CREATE TABLE tblstudentresults;


SELECT
    c.class_name,
    MIN(d.sort_order) AS start_day,
    MAX(d.sort_order) AS end_day
FROM tblTimetables t
JOIN tblClasses c ON c.class_id = t.class_id
JOIN tblDays d ON d.day_id = t.day_id
GROUP BY c.class_id;


SELECT
    c.class_id,
    c.class_name,
    c.class_code,
    s.subject_name,
    CONCAT(emp.first_name_en, ' ', emp.last_name_en) AS teacher_name,
    r.room_name,
    'Mon - Fri' AS days,
    ts.start_time,
    ts.end_time
FROM tblClasses c
JOIN tblCourseSubjects cs ON cs.id = c.course_subject_id
JOIN tblSubjects s ON s.subject_id = cs.subject_id
JOIN tblEmployees emp ON emp.employee_id = c.teacher_id
JOIN tblRooms r ON r.room_id = c.room_id
JOIN tblTimeSlots ts ON ts.slot_id = c.slot_id;


SELECT * FROM tblExams
WHERE exam_type_id = 1
AND class_id = 2
AND exam_date = '2026-05-30';


SELECT 
    e.exam_id,
    e.exam_name,
    e.exam_date,
    et.exam_type_name,
    e.class_id
FROM tblExams e
JOIN tblExamTypes et ON et.exam_type_id = e.exam_type_id;



SELECT 
    e.exam_id,
    e.exam_name,
    e.exam_date,

    et.exam_type_name,

    s.score_id,
    s.score,

    st.score_type_name,
    st.percentage,

    s.enrollment_id
FROM tblScores s

JOIN tblExams e ON e.exam_id = s.exam_id
JOIN tblExamTypes et ON et.exam_type_id = e.exam_type_id
JOIN tblScoreTypes st ON st.score_type_id = s.score_type_id;


SELECT 
    e.exam_id,
    e.exam_name,
    e.exam_date,
    et.exam_type_name,
    e.class_id,

    s.score_id,
    s.score,

    st.score_type_name,
    st.percentage,

    en.enrollment_id,
    stus.student_id,
    CONCAT(stus.first_name_kh, ' ', stus.last_name_kh) AS student_name

FROM tblScores s

JOIN tblExams e ON e.exam_id = s.exam_id
JOIN tblExamTypes et ON et.exam_type_id = e.exam_type_id
JOIN tblScoreTypes st ON st.score_type_id = s.score_type_id

JOIN tblEnrollments en ON en.enrollment_id = s.enrollment_id
JOIN tblStudents stus ON stus.student_id = en.student_id

WHERE 1=1

SELECT 
    emp.*,
   -- GROUP_CONCAT(DISTINCT c.course_name SEPARATOR ', ') AS courses,
    GROUP_CONCAT(DISTINCT s.subject_name SEPARATOR ', ') AS subjects

FROM tblEmployees emp
LEFT JOIN tblSubjects s
    ON c.teacher_id = emp.employee_id

WHERE emp.department_id = 4

GROUP BY emp.employee_id;



SELECT class_id, class_name FROM tblClasses WHERE academic_year = 2025;


SELECT DISTINCT SUBSTRING_INDEX(academic_year, '-', 1) AS year
    FROM tblClasses
    ORDER BY year ASC
    
    
    SELECT 
    SUM(a.status='Present') as present,
    SUM(a.status='Absent') as absent,
    SUM(a.status='Late') as late
FROM tblAttendances a
JOIN tblEnrollments e ON a.enrollment_id = e.enrollment_id

SELECT 
    SUM(inv.status = 'Paid')   AS Paid,
    SUM(inv.status = 'Unpaid') AS Unpaid,

FROM tblinvoices inv
JOIN tblEnrollments enr 
    ON inv.enrollment_id = enr.enrollment_id;
    
    
    
    SELECT 
    i.invoice_id,
    s.fst_name,
    i.invoice_date,
    i.total_amount,
    i.status,

    it.description,
    it.amount AS item_amount,

    p.amount AS payment_amount,
    p.payment_date

FROM tblInvoices i
JOIN tblStudents s ON i.student_id = s.student_id
LEFT JOIN tblInvoiceItems it ON i.invoice_id = it.invoice_id
LEFT JOIN tblPayments p ON i.invoice_id = p.invoice_id

WHERE i.invoice_id = 6;



---------- 1. Dashboard Queries 	-------

------ A. Total Students	-----
SELECT COUNT(*) AS total_students
FROM tblstudents;

---------	B. Active Classes	---------
SELECT COUNT(*) AS active_classes
FROM tblClasses
WHERE status = 'Open';

--------	C. Total Revenue (Paid Only)	------
SELECT SUM(p.amount) AS total_revenue
FROM tblPayments p
JOIN tblInvoices i ON p.invoice_id = i.invoice_id
WHERE i.status = 'Paid';

------	D. Monthly Revenue (Chart)	-----------
SELECT 
    DATE_FORMAT(p.payment_date, '%Y-%m') AS month,
    SUM(p.amount) AS revenue
FROM tblPayments p
GROUP BY month
ORDER BY MONTH;

--------	E. Enrollment Last 7 Days		
SELECT 
    DATE(created_at) AS day,
    COUNT(*) AS total
FROM tblEnrollments
WHERE created_at >= CURDATE() - INTERVAL 7 DAY
GROUP BY day
ORDER BY DAY;

-------	F. Attendance Summary	---------

SELECT 
    status,
    COUNT(*) AS total
FROM tblAttendances
GROUP BY STATUS;



SELECT 
    t.timetable_id,
    c.class_name,
    c.class_code,
    d.day_name,
    ts.slot_name,
    ts.start_time,
    ts.end_time,
    s.subject_name,
    CONCAT(e.first_name_en, ' ', e.last_name_en) AS teacher_name,
    r.room_name
FROM tblTimetables t
JOIN tblClasses c ON t.class_id = c.class_id
JOIN tblDays d ON t.day_id = d.day_id
JOIN tblTimeSlots ts ON t.slot_id = ts.slot_id
JOIN tblSubjects s ON t.subject_id = s.subject_id
JOIN tblEmployees e ON t.teacher_id = e.employee_id
JOIN tblRooms r ON t.room_id = r.room_id
ORDER BY d.sort_order, ts.start_time;



SELECT 
    c.class_id,
    c.class_code,
    c.class_name,

    co.course_name,

    CONCAT(e.first_name_en, ' ', e.last_name_en) AS teacher_name,
    r.room_name,

    GROUP_CONCAT(DISTINCT d.day_name ORDER BY d.sort_order SEPARATOR ', ') AS study_days,

    CONCAT(ts.start_time, ' - ', ts.end_time) AS study_time,

    en.price

FROM tblClasses c

LEFT JOIN tblCourses co 
    ON c.course_id = co.course_id

LEFT JOIN tblEmployees e 
    ON c.teacher_id = e.employee_id

LEFT JOIN tblRooms r 
    ON c.room_id = r.room_id

LEFT JOIN tblTimetables t 
    ON t.class_id = c.class_id

LEFT JOIN tblDays d 
    ON t.day_id = d.day_id

LEFT JOIN tblTimeSlots ts 
    ON t.slot_id = ts.slot_id

LEFT JOIN tblEnrollments en 
    ON en.class_id = c.class_id

GROUP BY 
    c.class_id, en.price;


SELECT 
c.*,
cr.price,
cr.course_name
FROM tblClasses c
LEFT JOIN tblCourses cr ON c.course_id = cr.course_id




SELECT 
    c.*,
    
    co.course_name,
    co.price,
    
    CONCAT(e.first_name_en, ' ', e.last_name_en) AS teacher_name,
    
    r.room_name,

    GROUP_CONCAT(
        CONCAT(t.day_of_week, ' ', ts.start_time, '-', ts.end_time)
        SEPARATOR ', '
    ) AS schedule

FROM tblClasses c

LEFT JOIN tblCourses co ON c.course_id = co.course_id
LEFT JOIN tblEmployees e ON c.teacher_id = e.employee_id
LEFT JOIN tblRooms r ON c.room_id = r.room_id

LEFT JOIN tblTimetables t ON c.class_id = t.class_id
LEFT JOIN tblTimeSlots ts ON t.slot_id = ts.slot_id

GROUP BY c.class_id
LIMIT 100;


SELECT c.teacher_id, e.employee_id
FROM tblClasses c
LEFT JOIN tblEmployees e ON c.teacher_id = e.employee_id;



SELECT
    c.class_code,
    c.class_name,

    co.course_name,
    s.subject_name,

    CONCAT(e.first_name_en,' ',e.last_name_en) AS teacher_name,

    r.room_name,
    
     CONCAT(ts.start_time,' - ',ts.end_time) AS study_time,

    GROUP_CONCAT(d.day_name ORDER BY d.sort_order) AS study_days

FROM tblClasses c

JOIN tblCourses co ON c.course_id = co.course_id
JOIN tblSubjects s ON c.subject_id = s.subject_id
JOIN tblEmployees e ON c.teacher_id = e.employee_id
JOIN tblRooms r ON c.room_id = r.room_id
JOIN tblTimeSlots ts ON c.slot_id = ts.slot_id

LEFT JOIN tblTimetables t ON c.class_id = t.class_id
LEFT JOIN tblDays d ON t.day_id = d.day_id

GROUP BY c.class_id;


SELECT tt.class_id, d.day_code, d.sort_order
   FROM tblTimetables tt
   JOIN tblDays d ON tt.day_id = d.day_id
   WHERE tt.class_id = 1
   ORDER BY d.sort_order ASC
   
   
   
   
    SELECT student_code 
        FROM tblStudents
        WHERE student_code LIKE 'STU-$year-%'
        ORDER BY student_id DESC
        LIMIT 1
        
       
		 
SELECT student_id,status FROM tblstudents WHERE STATUS = 'active' ORDER BY student_id DESC LIMIT 1;
        
        
          SELECT COUNT(*) as total 
        FROM tblStudents 
        WHERE status = 'active'
        
        
SELECT 
    e.enrollment_id,
    e.student_id,
    e.class_id,
    e.price,
    e.discount,
    e.created_at,

    c.class_name,
    c.class_code,
    c.teacher_id,

    emp.first_name_en,
    emp.last_name_en

FROM tblEnrollments e

INNER JOIN tblClasses c 
    ON e.class_id = c.class_id

INNER JOIN tblEmployees emp
    ON c.teacher_id = emp.employee_id

WHERE c.teacher_id = 1
 AND e.class_id = c.class_id;


SELECT *
FROM tblEnrollments
WHERE class_id = 1;


SELECT 
    c.class_code,
    c.class_name,
    ei.amount,
    CONCAT(t.first_name_kh, ' ', t.last_name_kh) AS teacher_name,
    c.room_id,
    c.slot_id
FROM tblInvoiceItems ei
JOIN tblEnrollments e 
    ON ei.enrollment_id = e.enrollment_id
JOIN tblClasses c 
    ON e.class_id = c.class_id
LEFT JOIN tblEmployees t 
    ON c.teacher_id = t.employee_id
WHERE ei.invoice_id = 1;



SELECT 
    c.class_code,
    c.class_name,
    ei.amount AS price,
    CONCAT(t.first_name_kh, ' ', t.last_name_kh) AS teacher_name,
    CONCAT('Room ', c.room_id) AS room,
    CONCAT('Slot ', c.slot_id) AS slot
FROM tblInvoiceItems ei
JOIN tblEnrollments e 
    ON ei.enrollment_id = e.enrollment_id
JOIN tblClasses c 
    ON e.class_id = c.class_id
LEFT JOIN tblEmployees t 
    ON c.teacher_id = t.employee_id
WHERE ei.invoice_id = 1;



SELECT employee_id, first_name_kh, last_name_kh
FROM tblEmployees
JOIN tblDepartments
ON tblEmployees.department_id = tblDepartments.department_id
WHERE department_name = 'Teacher';



SELECT
    SUM(s.score * st.percentage / 100) AS total_score
FROM tblScores s
JOIN tblScoreTypes st
ON st.score_type_id = s.score_type
WHERE s.student_id = 21;






SELECT 
    emp.*,
    GROUP_CONCAT(s.subject_name SEPARATOR ', ') AS subjects,
    d.department_name
FROM tblEmployees emp
JOIN tblEmployeeSubjects empsub 
    ON emp.employee_id = empsub.employee_id
JOIN tblDepartments d 
    ON emp.department_id = d.department_id
JOIN tblSubjects s 
    ON empsub.subject_id = s.subject_id
WHERE d.department_name = 'Teacher'

GROUP BY emp.employee_id;



SELECT 
    emp.*,
    GROUP_CONCAT(s.subject_name SEPARATOR ', ') AS subjects,
    d.department_name
FROM tblEmployees emp
LEFT JOIN tblEmployeeSubjects empsub 
    ON emp.employee_id = empsub.employee_id
LEFT JOIN tblDepartments d 
    ON emp.department_id = d.department_id
LEFT JOIN tblSubjects s 
    ON empsub.subject_id = s.subject_id
WHERE d.department_name = 'Teacher'
GROUP BY emp.employee_id;




SELECT 
    department_id,
    department_name
FROM tblDepartments
WHERE department_name = 'teacher';

-------- 2. Auto Invoice + Payment Status Logic ---------



-------- 2. Auto Invoice + Payment Status Logic ---------

SELECT
    c.class_id,
    c.class_code,
    c.class_name,
    CONCAT(e.first_name_en, ' ', e.last_name_en) AS teacher_name,
    COUNT(en.student_id) AS total_students,
    c.max_students,
    (c.max_students - COUNT(en.student_id)) AS available_seats,
    c.status
FROM tblClasses c
INNER JOIN tblEmployees e
    ON e.employee_id = c.teacher_id
LEFT JOIN tblEnrollments en
    ON en.class_id = c.class_id
    AND en.status = 'active'
WHERE c.teacher_id = 10
GROUP BY
    c.class_id,
    c.class_code,
    c.class_name,
    teacher_name,
    c.max_students,
    c.status
ORDER BY c.class_name;



SELECT
    d.department_id,
    d.department_name,
    COUNT(e.employee_id) AS total_teachers
FROM tblDepartments d
LEFT JOIN tblEmployees e
    ON e.department_id = d.department_id
    AND e.employee_type = 'Teacher'
GROUP BY
    d.department_id,
    d.department_name
ORDER BY
    d.department_name;




ALTER TABLE tblDepartments
ALTER COLUMN status SET DEFAULT 1;




