<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

require '../database/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    try {
        $stmt = $conn->prepare("UPDATE users SET name = :name, email = :email WHERE id = :id");
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':id' => $userId
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully.']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error updating profile: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
