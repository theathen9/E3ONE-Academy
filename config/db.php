<?php
// ./config/db.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Database credentials (replace with your actual values)
// $servername = "localhost";   // Hosting mySQL
// $username   = "root";        // Your DB username
// $password   = "";            // Your DB password            
// $db         = "systemacademy";   // Your DB name
// // Create connection
// $conn = new mysqli($servername, $username, $password, $db);

// // Check connection
// if ($conn->connect_error) {
//     die("❌ Database connection failed: " . $conn->connect_error);
// }

// // Optional: Set charset to UTF-8 (recommended)
// $conn->set_charset("utf8");


// ./config/db.php

$host = "dpg-d953l9kvikkc73d87fo0-a.oregon-postgres.render.com";
$port = 5432;
$dbname = "systemacademy";
$username = "systemacademy_user";
$password = "nvO3JsebMJWRG0U0rJ3MU3oGNsPsy35J";


try {
    $conn = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $username,
        $password
    );

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // optional (safe)
    $conn->exec("SET client_encoding TO 'UTF8'");

    // echo "✅ Database connected";

} catch (PDOException $e) {
    die("❌ Connection failed: " . $e->getMessage());
}
