<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}

// Correct path to the database connection file
define('BASE_PATH', realpath(__DIR__ . '/../../'));
require BASE_PATH . '/database/connection.php';

// Fetch current user's data
try {
    $stmt = $conn->prepare("SELECT name, email FROM users WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "<script>alert('User data not found!');</script>";
        $user = [];
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error fetching user data: " . $e->getMessage();
    $user = [];
}

// Include layout files
$title = "Settings";
include '../../includes/header.php';
include '../../includes/topbar.php';
include '../../includes/sidebar.php';
?>



<div id="page-content-wrapper">
    <h2>Settings</h2>

    <!-- Profile Settings -->
    <div class="card p-4 mb-4">
        <h4>Profile Settings</h4>
        <form id="profileSettingsForm">
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name"
                    value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email"
                    value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Save Profile</button>
        </form>
    </div>

    <!-- Password Settings -->
    <div class="card p-4 mb-4">
        <h4>Password Settings</h4>
        <form id="passwordSettingsForm">
            <div class="mb-3">
                <label for="currentPassword" class="form-label">Current Password</label>
                <input type="password" class="form-control" id="currentPassword" name="current_password" required>
            </div>
            <div class="mb-3">
                <label for="newPassword" class="form-label">New Password</label>
                <input type="password" class="form-control" id="newPassword" name="new_password" required>
            </div>
            <div class="mb-3">
                <label for="confirmPassword" class="form-label">Confirm New Password</label>
                <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-primary">Change Password</button>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
    // Handle Profile Settings Form Submission
    document.getElementById('profileSettingsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('../../api/update_profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.status === 'success') {
                    location.reload();
                }
            })
            .catch(error => console.error('Error:', error));
    });

    // Handle Password Settings Form Submission
    document.getElementById('passwordSettingsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('../../api/update_password.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.status === 'success') {
                    document.getElementById('passwordSettingsForm').reset();
                }
            })
            .catch(error => console.error('Error:', error));
    });
</script>