<?php
require_once '../database/connection.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Register a user to an event
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $conn->prepare("INSERT INTO event_participants (event_id, user_id) VALUES (:event_id, :user_id)");
    $stmt->execute([
        ':event_id' => $data['event_id'],
        ':user_id' => $data['user_id'],
    ]);
    echo json_encode(['message' => 'User registered successfully']);
}

if ($method === 'GET') {
    // Fetch participants of an event
    $event_id = $_GET['event_id'];
    $stmt = $conn->prepare("SELECT users.id, users.name FROM event_participants INNER JOIN users ON event_participants.user_id = users.id WHERE event_participants.event_id = :event_id");
    $stmt->execute([':event_id' => $event_id]);
    $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($participants);
}
