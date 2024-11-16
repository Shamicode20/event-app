<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit;
}

// Include database connection
define('BASE_PATH', realpath(__DIR__ . '/../../'));
require '../database/connection.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventId = $_POST['id'];

    if (!empty($eventId)) {
        try {
            $stmt = $conn->prepare("DELETE FROM events WHERE id = :id");
            $stmt->execute([':id' => $eventId]);

            $_SESSION['success'] = "Event deleted successfully.";
        } catch (Exception $e) {
            $_SESSION['error'] = "Error deleting event: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = "Invalid event ID.";
    }
    header('Location: ../pages/admin/events.php');
    exit;
}
