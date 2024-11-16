<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

require '../database/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $schedule = trim($_POST['schedule']);
    $poster = $_FILES['poster'];

    // Handle poster upload if a new poster is provided
    $posterFileName = null;
    if (!empty($poster['name'])) {
        $targetDir = "../assets/img/";
        $posterFileName = time() . "_" . basename($poster['name']);
        $targetFilePath = $targetDir . $posterFileName;

        if (!move_uploaded_file($poster['tmp_name'], $targetFilePath)) {
            $_SESSION['error'] = "Failed to upload new poster.";
            header('Location: ../pages/admin/events.php');
            exit;
        }
    }

    try {
        $query = "UPDATE events SET title = :title, description = :description, schedule = :schedule";
        if ($posterFileName) {
            $query .= ", poster = :poster";
        }
        $query .= " WHERE id = :id";

        $stmt = $conn->prepare($query);
        $params = [
            ':title' => $title,
            ':description' => $description,
            ':schedule' => $schedule,
            ':id' => $id
        ];
        if ($posterFileName) {
            $params[':poster'] = $posterFileName;
        }
        $stmt->execute($params);

        $_SESSION['success'] = "Event updated successfully.";
        header('Location: ../pages/admin/events.php');
    } catch (Exception $e) {
        $_SESSION['error'] = "Error updating event: " . $e->getMessage();
        header('Location: ../pages/admin/events.php');
    }
}
