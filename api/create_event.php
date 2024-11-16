<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

require '../database/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $schedule = trim($_POST['schedule']);
    $poster = $_FILES['poster'];

    // Handle poster upload
    $posterFileName = 'default.jpg'; // Default image if no poster is uploaded
    if (!empty($poster['name'])) {
        $targetDir = "../assets/img/";
        $posterFileName = time() . "_" . basename($poster['name']);
        $targetFilePath = $targetDir . $posterFileName;

        if (!move_uploaded_file($poster['tmp_name'], $targetFilePath)) {
            $_SESSION['error'] = "Failed to upload poster.";
            header('Location: ../pages/admin/events.php');
            exit;
        }
    }

    try {
        $stmt = $conn->prepare("INSERT INTO events (title, description, schedule, poster, created_by) VALUES (:title, :description, :schedule, :poster, :created_by)");
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':schedule' => $schedule,
            ':poster' => $posterFileName,
            ':created_by' => $_SESSION['user_id']
        ]);

        $_SESSION['success'] = "Event created successfully.";
        header('Location: ../pages/admin/events.php');
    } catch (Exception $e) {
        $_SESSION['error'] = "Error creating event: " . $e->getMessage();
        header('Location: ../pages/admin/events.php');
    }
}
