<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

require '../database/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash(trim($_POST['password']), PASSWORD_BCRYPT);
    $role = trim($_POST['role']);

    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit;
    }

    try {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)");
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':password' => $password,
            ':role' => $role,
        ]);

        echo json_encode(['status' => 'success', 'message' => 'User created successfully.']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error creating user: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
