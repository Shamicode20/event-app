<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

require '../database/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim($_POST['id']);

    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid user ID.']);
        exit;
    }

    try {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        echo json_encode(['status' => 'success', 'message' => 'User deleted successfully.']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error deleting user: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
