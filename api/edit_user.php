<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

require '../database/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim($_POST['id']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);

    if (empty($id) || empty($name) || empty($email) || empty($role)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit;
    }

    try {
        $stmt = $conn->prepare("UPDATE users SET name = :name, email = :email, role = :role WHERE id = :id");
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':role' => $role,
            ':id' => $id,
        ]);
        echo json_encode(['status' => 'success', 'message' => 'User updated successfully.']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error updating user: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
