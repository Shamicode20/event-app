<?php
session_start();
require_once '../database/connection.php';

$method = $_SERVER['REQUEST_METHOD'];

// Login
if ($method === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];

        echo json_encode(['message' => 'Login successful', 'role' => $user['role']]);
    } else {
        http_response_code(401);
        echo json_encode(['message' => 'Invalid email or password']);
    }
}

// Register
if ($method === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Check if email already exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    if ($stmt->rowCount() > 0) {
        http_response_code(400);
        echo json_encode(['message' => 'Email already registered']);
        exit;
    }

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
    $stmt->execute([':name' => $name, ':email' => $email, ':password' => $password]);
    echo json_encode(['message' => 'Registration successful']);
}

// Logout
if ($method === 'POST' && isset($_POST['action']) && $_POST['action'] === 'logout') {
    session_destroy();
    echo json_encode(['message' => 'Logout successful']);
}

// Check Session
if ($method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'check_session') {
    if (isset($_SESSION['user_id'])) {
        echo json_encode([
            'authenticated' => true,
            'user_id' => $_SESSION['user_id'],
            'role' => $_SESSION['role'],
            'name' => $_SESSION['name']
        ]);
    } else {
        echo json_encode(['authenticated' => false]);
    }
}
