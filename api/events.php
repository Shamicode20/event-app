<?php
require_once '../database/connection.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Fetch events
    $stmt = $conn->prepare("SELECT * FROM events");
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($events);
}

if ($method === 'POST') {
    // Create an event
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $conn->prepare("INSERT INTO events (title, description, schedule, created_by) VALUES (:title, :description, :schedule, :created_by)");
    $stmt->execute([
        ':title' => $data['title'],
        ':description' => $data['description'],
        ':schedule' => $data['schedule'],
        ':created_by' => $data['created_by'],
    ]);
    echo json_encode(['message' => 'Event created successfully']);
}

if ($method === 'PUT') {
    // Update an event
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $conn->prepare("UPDATE events SET title = :title, description = :description, schedule = :schedule WHERE id = :id");
    $stmt->execute([
        ':title' => $data['title'],
        ':description' => $data['description'],
        ':schedule' => $data['schedule'],
        ':id' => $data['id'],
    ]);
    echo json_encode(['message' => 'Event updated successfully']);
}

if ($method === 'DELETE') {
    // Delete an event
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $conn->prepare("DELETE FROM events WHERE id = :id");
    $stmt->execute([':id' => $data['id']]);
    echo json_encode(['message' => 'Event deleted successfully']);
}
