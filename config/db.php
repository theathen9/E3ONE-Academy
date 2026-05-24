<?php
// ./config/db.php

$host = getenv('DB_HOST');
$dbname = getenv('DB_NAME');
$username = getenv('DB_USER');
$password = getenv('DB_PASSWORD');
$port = getenv('DB_PORT') ?: 5432;

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
