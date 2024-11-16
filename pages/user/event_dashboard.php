<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../index.php');
    exit;
}

// Correct path to the database connection file
define('BASE_PATH', realpath(__DIR__ . '/../../'));
require BASE_PATH . '/database/connection.php';

// Initialize variables
$events = [];

try {
    // Fetch events the user is participating in, including the `poster` field
    $stmt = $conn->prepare("
        SELECT events.id, events.title, events.description, events.schedule, events.poster 
        FROM events 
        JOIN event_participants ON events.id = event_participants.event_id 
        WHERE event_participants.user_id = :id 
        ORDER BY events.schedule ASC
    ");
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $_SESSION['error'] = "Error fetching events: " . $e->getMessage();
}

// Include layout files
$title = "Event Dashboard";
include '../../includes/header.php';
include '../../includes/topbar.php';
include '../../includes/sidebar.php';
?>



<div id="page-content-wrapper">
    <div class="text-center mb-4">
        <h2 class="dark-text">Your Events</h2>
        <p class="text-muted dark-text">Events you are participating in.</p>
    </div>

    <div class="container">
        <div class="row">
            <?php if (!empty($events)): ?>
                <?php foreach ($events as $event): ?>
                    <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                        <div class="card h-100 border-0 shadow-sm dark-card">
                            <!-- Event poster -->
                            <img
                                src="../../assets/img/<?php echo htmlspecialchars($event['poster'] ?? 'default.jpg'); ?>"
                                alt="<?php echo htmlspecialchars($event['title']); ?>"
                                class="card-img-top rounded-top"
                                style="height: 250px; object-fit: contain; background-color: #f8f9fa;">

                            <!-- Event details -->
                            <div class="card-body text-center dark-card-body">
                                <h5 class="card-title mb-2 dark-text">
                                    <?php echo htmlspecialchars($event['title']); ?>
                                </h5>
                                <p class="card-text text-muted small mb-3 dark-text">
                                    <?php echo htmlspecialchars($event['description']); ?>
                                </p>
                                <p class="text-muted small dark-text">
                                    Schedule: <?php echo date('F j, Y, g:i A', strtotime($event['schedule'])); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted text-center w-100 dark-text">No events found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>



<?php include '../../includes/footer.php'; ?>