<?php
session_start();
// Redirect non-admin users to the login page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit;
}

require '../../database/connection.php';

// Set the page title
$title = "Admin Dashboard";

// Include common layout files
include '../../includes/header.php';
include '../../includes/topbar.php';
include '../../includes/sidebar.php';

// Function to fetch count from a table with PDO
function fetchCount($conn, $tableName)
{
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM $tableName");
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data['count'] ?? 0;
    } catch (Exception $e) {
        // Log the error for debugging purposes
        error_log("Error fetching count from $tableName: " . $e->getMessage());
        return 0;
    }
}

// Fetch counts for total users, total events, and total participants
$totalUsers = fetchCount($conn, 'users');
$totalEvents = fetchCount($conn, 'events');
$totalParticipants = fetchCount($conn, 'event_participants');
?>

<div id="page-content-wrapper">
    <div class="container mt-4">
        <h1 class="mb-4">Welcome to Admin Dashboard</h1>
        <div class="row">
            <!-- Total Users Card -->
            <div class="col-md-3">
                <div class="card text-center p-3">
                    <h5>Total Users</h5>
                    <h3><?php echo htmlspecialchars($totalUsers); ?></h3>
                    <small>Last updated: <?php echo htmlspecialchars(date('Y-m-d H:i:s')); ?></small>
                </div>
            </div>

            <!-- Total Events Card -->
            <div class="col-md-3">
                <div class="card text-center p-3">
                    <h5>Total Events</h5>
                    <h3><?php echo htmlspecialchars($totalEvents); ?></h3>
                    <small>Last updated: <?php echo htmlspecialchars(date('Y-m-d H:i:s')); ?></small>
                </div>
            </div>

            <!-- Total Participants Card -->
            <div class="col-md-3">
                <div class="card text-center p-3">
                    <h5>Total Participants</h5>
                    <h3><?php echo htmlspecialchars($totalParticipants); ?></h3>
                    <small>Last updated: <?php echo htmlspecialchars(date('Y-m-d H:i:s')); ?></small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
