<?php
require_once '../database/connection.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Fetch user details
    $user_id = $_GET['user_id'];
    $stmt = $conn->prepare("SELECT id, name, email FROM users WHERE id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($user);
}

if ($method === 'PUT') {
    // Update user profile
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $conn->prepare("UPDATE users SET name = :name, email = :email WHERE id = :id");
    $stmt->execute([
        ':name' => $data['name'],
        ':email' => $data['email'],
        ':id' => $data['id'],
    ]);
    echo json_encode(['message' => 'Profile updated successfully']);
}
