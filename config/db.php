<?php
// ./config/db.php
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

$host = $_ENV['DB_POSTGRESQL_HOST_KEY'];
$port = $_ENV['DB_POSTGRESQL_PORT_KEY'];
$username = $_ENV['DB_POSTGRESQL_USERNAME_KEY'];
$password = $_ENV['DB_POSTGRESQL_PASSWORD_KEY'];
$dbname = $_ENV['DB_POSTGRESQL_DATABASE_KEY'];


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
