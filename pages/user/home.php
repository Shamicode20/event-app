<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../index.php');
    exit;
}

// Correct path to the database connection file
define('BASE_PATH', realpath(__DIR__ . '/../../'));
require BASE_PATH . '/database/connection.php';

// Initialize variables to avoid "undefined variable" errors
$user = ['name' => 'Guest', 'email' => 'N/A'];
$eventSummary = ['total_events' => 0];

try {
    // Fetch user information
    $stmt = $conn->prepare("SELECT name, email FROM users WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // If no user found, set default values
        $_SESSION['error'] = "User data not found.";
        $user = ['name' => 'Guest', 'email' => 'N/A'];
    }

    // Fetch the number of registered events dynamically
    $stmt = $conn->prepare("SELECT COUNT(*) AS total_events FROM event_participants WHERE user_id = :id");
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $eventSummary = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Log the error for debugging
    $_SESSION['error'] = "Error fetching dashboard data: " . $e->getMessage();
}

// Include layout files
$title = "User Dashboard";
include '../../includes/header.php';
include '../../includes/topbar.php';
include '../../includes/sidebar.php';
?>

<div id="page-content-wrapper">
    <div class="dashboard-header text-center">
        <h2>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h2>
        <p>Your email: <?php echo htmlspecialchars($user['email']); ?></p>
    </div>

    <div class="container mt-4">
        <div class="row">
            <!-- Event Summary Card -->
            <div class="col-lg-6 col-md-6 col-sm-12 mb-4">
                <div class="card text-center p-3">
                    <h4>Event Summary</h4>
                    <p>You are registered for <strong><?php echo htmlspecialchars($eventSummary['total_events']); ?></strong> events.</p>
                    <a href="event_dashboard.php" class="btn btn-primary">View Events</a>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="col-lg-6 col-md-6 col-sm-12 mb-4">
                <div class="card text-center p-3">
                    <h4>Quick Actions</h4>
                    <a href="event_registration.php" class="btn btn-success mb-2">Register for Events</a>
                    <a href="profile.php" class="btn btn-secondary">Update Profile</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
