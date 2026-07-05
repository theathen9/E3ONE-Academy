<?php
// ./config/db.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
<<<<<<< HEAD
}
// Database credentials (replace with your actual values)
$servername = "localhost";   // Hosting mySQL
$username   = "root";        // Your DB username
$password   = "";            // Your DB password            
$db         = "systemacademy";   // Your DB name
// Create connection
$conn = new mysqli($servername, $username, $password, $db);

// Check connection
if ($conn->connect_error) {
    die("❌ Database connection failed: " . $conn->connect_error);
=======
>>>>>>> 15f990b2d0e48b5f24d1b2efc137569c06d05fb2
}
$host = getenv('DB_HOST');
$dbname = getenv('DB_NAME');
$username = getenv('DB_USER');
$password = getenv('DB_PASSWORD');
$port = getenv('DB_PORT') ?: 5432;

<<<<<<< HEAD
// Optional: Set charset to UTF-8 (recommended)
$conn->set_charset("utf8");


// ./config/db.php

// $host = "dpg-d89g5db7uimc739j69og-a";
// $port = 5432;
// $dbname = "dbacademy_aa3x";
// $username = "dbacademy_aa3x_user";
// $password = "TFTh0EuIhlm2V1WznA5EKwM8iMDjHL52";

// try {
//     $conn = new PDO(
//         "pgsql:host=$host;port=$port;dbname=$dbname",
//         $username,
//         $password
//     );

//     $conn->setAttribute(
//         PDO::ATTR_ERRMODE,
//         PDO::ERRMODE_EXCEPTION
//     );

//     echo "✅ Database connected";
// } catch (PDOException $e) {
//     die("❌ Connection failed: " . $e->getMessage());
// }
=======
try {
    $conn = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $username,
        $password
    );

    $conn->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    // optional
    $conn->exec("SET NAMES 'UTF8'");

} catch (PDOException $e) {
    die("❌ Connection failed: " . $e->getMessage());
}
>>>>>>> 15f990b2d0e48b5f24d1b2efc137569c06d05fb2
