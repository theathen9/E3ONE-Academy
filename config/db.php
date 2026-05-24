<?php
// ./config/db.php
// Database credentials (replace with your actual values)
$servername = "dpg-d89g5db7uimc739j69og-a";   // Hosting mySQL
$username   = "dbacademy_aa3x_user";        // Your DB username
$password   = "TFTh0EuIhlm2V1WznA5EKwM8iMDjHL52";            // Your DB password
$db         = "dbacademy_aa3x";   // Your DB name
$port       = 5432;
// Create connection
$conn = new mysqli($servername, $username, $password, $db, $port);

// Check connection
if ($conn->connect_error) {
    die("❌ Database connection failed: " . $conn->connect_error);
}

// Optional: Set charset to UTF-8 (recommended)
$conn->set_charset("utf8");
