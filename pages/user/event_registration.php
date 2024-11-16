<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../index.php');
    exit;
}

// Correct path to the database connection file
define('BASE_PATH', realpath(__DIR__ . '/../../'));
require BASE_PATH . '/database/connection.php';

// Handle event registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    try {
        $eventId = $_POST['event_id'];

        // Check if the user is already registered for the event
        $stmt = $conn->prepare("SELECT COUNT(*) FROM event_participants WHERE user_id = :user_id AND event_id = :event_id");
        $stmt->execute([':user_id' => $_SESSION['user_id'], ':event_id' => $eventId]);
        $alreadyRegistered = $stmt->fetchColumn();

        if (!$alreadyRegistered) {
            // Register the user for the event
            $stmt = $conn->prepare("INSERT INTO event_participants (user_id, event_id) VALUES (:user_id, :event_id)");
            $stmt->execute([':user_id' => $_SESSION['user_id'], ':event_id' => $eventId]);

            $_SESSION['success'] = "You have successfully registered for the event!";
        } else {
            $_SESSION['error'] = "You are already registered for this event.";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error registering for the event: " . $e->getMessage();
    }

    // Reload the page to update the list and show any messages
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Initialize variables
$upcomingEvents = [];
$currentEvents = [];
$recentEvents = [];

try {
    // Fetch upcoming events that the user is not registered for
    $stmt = $conn->prepare("
        SELECT events.id, events.title, events.description, events.schedule, events.poster 
        FROM events 
        WHERE events.schedule > NOW()
        AND events.id NOT IN (
            SELECT event_id 
            FROM event_participants 
            WHERE user_id = :id
        )
        ORDER BY events.schedule ASC
    ");
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $upcomingEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch current events (ongoing or happening today) that the user is not registered for
    $stmt = $conn->prepare("
        SELECT events.id, events.title, events.description, events.schedule, events.poster 
        FROM events 
        WHERE DATE(events.schedule) = CURDATE()
        AND events.id NOT IN (
            SELECT event_id 
            FROM event_participants 
            WHERE user_id = :id
        )
        ORDER BY events.schedule ASC
    ");
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $currentEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch recent events the user has registered for
    $stmt = $conn->prepare("
        SELECT events.id, events.title, events.schedule, events.poster 
        FROM events 
        JOIN event_participants ON events.id = event_participants.event_id 
        WHERE event_participants.user_id = :id
        ORDER BY events.schedule DESC
        LIMIT 5
    ");
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $recentEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $_SESSION['error'] = "Error fetching events: " . $e->getMessage();
}

// Include layout files
$title = "Event Registration";
include '../../includes/header.php';
include '../../includes/topbar.php';
include '../../includes/sidebar.php';
?>

<div id="page-content-wrapper">
    <div class="dashboard-header text-center mb-4">
        <h2 class="dark-text">Event Registration</h2>
        <p class="text-muted">Browse upcoming, current events and view your recent registrations.</p>
    </div>

    <div class="container">
        <!-- Display Success or Error Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success text-center">
                <?php echo $_SESSION['success'];
                unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger text-center">
                <?php echo $_SESSION['error'];
                unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Current Events Section -->
        <div class="mb-5">
            <h4 class="text-center mb-3 dark-text">Current Events</h4>
            <div class="row">
                <?php if (!empty($currentEvents)): ?>
                    <?php foreach ($currentEvents as $event): ?>
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                            <div class="card border-0 shadow-sm h-100 dark-card">
                                <img src="../../assets/img/<?php echo htmlspecialchars($event['poster'] ?? 'default.jpg'); ?>"
                                    alt="<?php echo htmlspecialchars($event['title']); ?>"
                                    class="card-img-top rounded-top"
                                    style="height: auto; width: 100%; object-fit: contain; background-color: #f8f9fa;">
                                <div class="card-body d-flex flex-column dark-card-body">
                                    <h5 class="card-title text-center dark-text"><?php echo htmlspecialchars($event['title']); ?></h5>
                                    <p class="text-muted small text-center dark-text"><?php echo htmlspecialchars($event['description']); ?></p>
                                    <p class="text-muted small text-center dark-text">Schedule: <?php echo date('F j, Y, g:i A', strtotime($event['schedule'])); ?></p>
                                    <div class="mt-auto text-center">
                                        <form method="POST" action="">
                                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                            <button type="submit" class="btn btn-primary btn-sm" name="register">Register</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-muted dark-text">No current events available for registration.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Upcoming Events Section -->
        <div class="mb-5">
            <h4 class="text-center mb-3 dark-text">Upcoming Events</h4>
            <div class="row">
                <?php if (!empty($upcomingEvents)): ?>
                    <?php foreach ($upcomingEvents as $event): ?>
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                            <div class="card border-0 shadow-sm h-100 dark-card">
                                <img src="../../assets/img/<?php echo htmlspecialchars($event['poster'] ?? 'default.jpg'); ?>"
                                    alt="<?php echo htmlspecialchars($event['title']); ?>"
                                    class="card-img-top rounded-top"
                                    style="height: auto; width: 100%; object-fit: contain; background-color: #f8f9fa;">
                                <div class="card-body d-flex flex-column dark-card-body">
                                    <h5 class="card-title text-center dark-text"><?php echo htmlspecialchars($event['title']); ?></h5>
                                    <p class="text-muted small text-center dark-text"><?php echo htmlspecialchars($event['description']); ?></p>
                                    <p class="text-muted small text-center dark-text">Schedule: <?php echo date('F j, Y, g:i A', strtotime($event['schedule'])); ?></p>
                                    <div class="mt-auto text-center">
                                        <form method="POST" action="">
                                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                            <button type="submit" class="btn btn-primary btn-sm" name="register">Register</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-muted dark-text">No upcoming events available for registration.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recently Registered Events Section -->
        <div class="mb-5">
            <h4 class="text-center mb-3 dark-text">Recently Registered Events</h4>
            <div class="row">
                <?php if (!empty($recentEvents)): ?>
                    <?php foreach ($recentEvents as $event): ?>
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                            <div class="card border-0 shadow-sm h-100 dark-card">
                                <img src="../../assets/img/<?php echo htmlspecialchars($event['poster'] ?? 'default.jpg'); ?>"
                                    alt="<?php echo htmlspecialchars($event['title']); ?>"
                                    class="card-img-top rounded-top"
                                    style="height: auto; width: 100%; object-fit: contain; background-color: #f8f9fa;">
                                <div class="card-body text-center dark-card-body">
                                    <h5 class="card-title dark-text"><?php echo htmlspecialchars($event['title']); ?></h5>
                                    <p class="text-muted small dark-text">Schedule: <?php echo date('F j, Y, g:i A', strtotime($event['schedule'])); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-muted dark-text">No recently registered events.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>