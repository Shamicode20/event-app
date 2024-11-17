<?php
// Database configuration
$host = 'localhost';  // Database host
$db_name = 'bara_event_app';  // Database name
$username = 'bara_event_app';  // Database username
$password = 'bara_event_app';  // Database password (update accordingly)

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}
