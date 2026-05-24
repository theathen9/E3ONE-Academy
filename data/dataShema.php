<?php
<<<<<<< HEAD
$infoShemaData=[
    ["name"=>"Empowerment Education English One"],
    ["email"=>"info@empowermentacademy.com"],
    ["address"=>"36, Siem Reap, Siem Reap"],
    ["phone"=>"095355521"],
    ["image"=>"/src/assets/icon.png"]
];
$staticShemaData=[
    // ===== Dashboard =====
    [
        "title"  => "Dashboard",              // Menu title
        "icon"   => "bi bi-speedometer2",     // Bootstrap icon
        "link"   => "/admin/dashboard",           // Page link
        "active" => true                      // Mark as active
    ],

    // ===== Institute (Has submenu) =====
    [
        "title" => "Institute",
        "submenu_id" => "instituteSubmenu",   // Bootstrap collapse ID
        "submenu" => [

            // Submenu item
            [
                "title"  => "Employees",
                "link"   => "/admin/institute/employees",
                "active" => false              // Active submenu item
            ],
            [
                "title" => "Teachers",
                "link"  => "/admin/institute/teacher.php",
                "active" => false              // Active submenu item

            ],
            [
                "title" => "Students",
                "link"  => "/admin/institute/student.php",
                "active" => false              // Active submenu item
            ]
        ],
        "active" => false                      // Mark as active
    ],
 // ===== Enrollment (Has submenu) =====
    [
        "title" => "Enrollment",
        "submenu_id" => "enrollmentSubmenu",   // Bootstrap collapse ID
        "submenu" => [

            // Submenu item
            [
                "title"  => "Enrollment",
                "link"   => "/admin/enrollment/enrollment.php",
                "active" => false              // Active submenu item
            ]
        ],
        "active" => false                      // Mark as active
=======
// ./data/dataShema.php

$infoShemaData = [
    ["name"    => "Empowerment Education English One"],
    ["email"   => "info@empowermentacademy.com"],
    ["address" => "36, Siem Reap, Siem Reap"],
    ["phone"   => "095355521"],
    ["image"   => "/systemmanagement/src/assets/icon.png"]
];

$staticShemaData = [

    // ===== Dashboard =====
    [
        "title"  => "Dashboard",
        "icon"   => "bi bi-speedometer2",
        "link"   => "/systemmanagement/dashboard",
        "active" => true
    ],

    // ===== Institute =====
    [
        "title" => "Institute",
        "submenu_id" => "instituteSubmenu",
        "submenu" => [
            [
                "title"  => "Employees",
                "link"   => "/systemmanagement/admin/institute/employees",
                "active" => false
            ],
            [
                "title"  => "Department",
                "link"   => "/systemmanagement/admin/institute/department",
                "active" => false
            ],
            [
                "title"  => "Teachers",
                "link"   => "/systemmanagement/admin/institute/teacher",
                "active" => false
            ],
            [
                "title"  => "Students",
                "link"   => "/systemmanagement/admin/institute/student",
                "active" => false
            ]
        ],
        "active" => false
    ],

    // ===== Enrollment =====
    [
        "title" => "Enrollment",
        "submenu_id" => "enrollmentSubmenu",
        "submenu" => [
            [
                "title"  => "Enrollment",
                "link"   => "/systemmanagement/admin/enrollment/enrollment",
                "active" => false
            ]
        ],
        "active" => false
>>>>>>> 512c3b3 (first commit)
    ],

    // ===== Attendance =====
    [
        "title" => "Attendance",
        "submenu_id" => "attendanceSubmenu",
        "submenu" => [
<<<<<<< HEAD
            ["title" => "Dashboard", "link" => "./Dashboard.php"],
            ["title" => "Attendance", "link" => "#"]
=======
            [
                "title" => "Dashboard",
                "link"  => "/systemmanagement/teacher/attendent"
            ],
            [
                "title" => "Attendance",
                "link"  => "#"
            ]
>>>>>>> 512c3b3 (first commit)
        ]
    ],

    // ===== Examination =====
    [
        "title" => "Examination",
        "submenu_id" => "examSubmenu",
        "submenu" => [
            ["title" => "Schedule", "link" => "#"],
            ["title" => "Results", "link" => "#"]
        ]
    ],

    // ===== Schedule =====
    [
        "title" => "Schedule",
        "submenu_id" => "scheduleSubmenu",
        "submenu" => [
            ["title" => "Schedule", "link" => "#"]
        ]
    ],

    // ===== Normal Links =====
    [
        "title" => "Billing",
        "link"  => "#"
    ],
    [
        "title" => "Accounts",
        "link"  => "#"
    ]
<<<<<<< HEAD
];
=======
];
>>>>>>> 512c3b3 (first commit)
