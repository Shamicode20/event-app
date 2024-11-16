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

try {
    // Fetch user information
    $stmt = $conn->prepare("SELECT name, email FROM users WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['error'] = "User data not found.";
        header('Location: home.php');
        exit;
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error fetching profile data: " . $e->getMessage();
}

// Include layout files
$title = "User Profile";
include '../../includes/header.php';
include '../../includes/topbar.php';
include '../../includes/sidebar.php';
?>

<style>
    .profile-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .profile-header h2 {
        margin-top: 1rem;
        font-size: 1.8rem;
        color: #4a90e2;
    }

    .profile-form,
    .password-form {
        background: #ffffff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        transition: background-color 0.3s;
        margin-bottom: 2rem;
    }

    .dark-mode .profile-form,
    .dark-mode .password-form {
        background: #333;
        color: #e0e0e0;
    }

    .dark-mode .profile-form label,
    .dark-mode .password-form label {
        color: #e0e0e0;
    }

    .dark-mode .form-control {
        background-color: #444;
        color: #fff;
        border: 1px solid #555;
    }

    .dark-mode .form-control::placeholder {
        color: #aaa;
    }
</style>

<div id="page-content-wrapper">
    <div class="profile-header">
        <h2><?php echo htmlspecialchars($user['name']); ?></h2>
        <p><?php echo htmlspecialchars($user['email']); ?></p>
    </div>

    <div class="container">
        <!-- Profile Form -->
        <div class="profile-form">
            <form id="updateProfileForm">
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </div>
            </form>
        </div>

        <!-- Password Form -->
        <div class="password-form">
            <h4>Change Password</h4>
            <form id="updatePasswordForm">
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
                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
    // Handle Profile Update
    document.getElementById('updateProfileForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('../../api_u/update_profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.status === 'success') {
                    location.reload(); // Reload to reflect changes
                }
            })
            .catch(error => console.error('Error:', error));
    });

    // Handle Password Update
    document.getElementById('updatePasswordForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('../../api_u/update_password.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.status === 'success') {
                    document.getElementById('updatePasswordForm').reset(); // Clear form after success
                }
            })
            .catch(error => console.error('Error:', error));
    });
</script>