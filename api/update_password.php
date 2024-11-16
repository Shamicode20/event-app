<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

require '../database/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $currentPassword = trim($_POST['current_password']);
    $newPassword = trim($_POST['new_password']);
    $confirmPassword = trim($_POST['confirm_password']);

    if ($newPassword !== $confirmPassword) {
        echo json_encode(['status' => 'error', 'message' => 'New passwords do not match.']);
        exit;
    }

    try {
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($currentPassword, $user['password'])) {
            echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect.']);
            exit;
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE users SET password = :password WHERE id = :id");
        $stmt->execute([
            ':password' => $hashedPassword,
            ':id' => $userId
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Password updated successfully.']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error updating password: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
