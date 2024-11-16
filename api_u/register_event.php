<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id']) && $_SESSION['role'] === 'user') {
    require '../database/connection.php';


    $userId = $_SESSION['user_id'];
    $eventId = $_POST['event_id'] ?? null;

    if ($eventId) {
        try {
            // Check if already registered
            $stmt = $conn->prepare("
                SELECT * FROM event_participants 
                WHERE user_id = :user_id AND event_id = :event_id
            ");
            $stmt->execute([':user_id' => $userId, ':event_id' => $eventId]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'error', 'message' => 'You are already registered for this event.']);
                exit;
            }

            // Register for the event
            $stmt = $conn->prepare("
                INSERT INTO event_participants (user_id, event_id) 
                VALUES (:user_id, :event_id)
            ");
            $stmt->execute([':user_id' => $userId, ':event_id' => $eventId]);

            echo json_encode(['status' => 'success', 'message' => 'Successfully registered for the event.']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error registering for event: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid event ID.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
}
