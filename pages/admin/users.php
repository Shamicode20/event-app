<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit;
}

// Correct path to the database connection file
define('BASE_PATH', realpath(__DIR__ . '/../../'));
require BASE_PATH . '/database/connection.php';

// Fetch users dynamically from the database
try {
    $stmt = $conn->prepare("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $_SESSION['error'] = "Error fetching users: " . $e->getMessage();
    $users = [];
}

// Include layout files
$title = "Manage Users";
include '../../includes/header.php';
include '../../includes/topbar.php';
include '../../includes/sidebar.php';
?>

<style>
    /* Modal Styles */
    .modal-content {
        border-radius: 8px;
        background-color: #ffffff;
        /* Default light mode background */
        color: #1B4965;
        /* Default light mode text color */
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        transition: background-color 0.3s, color 0.3s;
    }

    .modal-header,
    .modal-footer {
        background-color: #f8f9fa;
        /* Light background for modal header/footer */
        border-color: #e9ecef;
        /* Light border for modal header/footer */
    }

    .modal-body {
        background-color: #ffffff;
        /* Light background for modal body */
        color: #1B4965;
        /* Light text color for modal body */
    }

    body.dark-mode .modal-content {
        background-color: #2c2c2c;
        /* Dark mode background */
        color: #e0e0e0;
        /* Dark mode text color */
    }

    body.dark-mode .modal-header,
    body.dark-mode .modal-footer {
        background-color: #333;
        /* Dark background for modal header/footer */
        border-color: #444;
        /* Dark border for modal header/footer */
    }

    body.dark-mode .modal-body {
        background-color: #2c2c2c;
        /* Dark mode background for modal body */
        color: #e0e0e0;
        /* Dark mode text color for modal body */
    }

    /* Buttons inside modals */
    .modal-footer .btn {
        font-weight: bold;
        transition: background-color 0.3s, color 0.3s;
    }

    .modal-footer .btn-primary {
        background-color: #4a90e2;
        border-color: #4a90e2;
        color: white;
    }

    .modal-footer .btn-primary:hover {
        background-color: #3b78c2;
        border-color: #3b78c2;
    }

    body.dark-mode .modal-footer .btn-primary {
        background-color: #3b78c2;
        /* Dark mode button background */
        border-color: #3b78c2;
        /* Dark mode button border */
        color: white;
        /* Maintain light text for visibility */
    }

    body.dark-mode .modal-footer .btn-primary:hover {
        background-color: #336699;
        /* Slightly darker on hover for dark mode */
        border-color: #336699;
    }

    /* Scrollbar for modal body */
    .modal-body::-webkit-scrollbar {
        width: 6px;
    }

    .modal-body::-webkit-scrollbar-thumb {
        background-color: #dcdcdc;
        /* Light mode scrollbar */
        border-radius: 3px;
    }

    body.dark-mode .modal-body::-webkit-scrollbar-thumb {
        background-color: #555;
        /* Dark mode scrollbar */
    }
</style>

<div id="page-content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Users</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">Create User</button>
    </div>

    <!-- Display success or error messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php echo $_SESSION['success'];
            unset($_SESSION['success']); ?>
        </div>
    <?php elseif (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?php echo $_SESSION['error'];
            unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)) : ?>
                    <?php foreach ($users as $index => $user) : ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td><?php echo htmlspecialchars(date('F j, Y, g:i A', strtotime($user['created_at']))); ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm edit-user-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editModal"
                                    data-id="<?php echo $user['id']; ?>"
                                    data-name="<?php echo htmlspecialchars($user['name']); ?>"
                                    data-email="<?php echo htmlspecialchars($user['email']); ?>"
                                    data-role="<?php echo htmlspecialchars($user['role']); ?>">
                                    Edit
                                </button>
                                <button class="btn btn-danger btn-sm delete-user-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteModal"
                                    data-id="<?php echo $user['id']; ?>">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="6" class="text-center">No users found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modals -->
<!-- Create User Modal -->
<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="createUserForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="createModalLabel">Create User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="modal-feedback" style="display: none;"></div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-control" id="role" name="role" required>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="editUserForm">
                <input type="hidden" id="editUserId" name="id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="modal-feedback" style="display: none;"></div>
                    <div class="mb-3">
                        <label for="editName" class="form-label">Name</label>
                        <input type="text" class="form-control" id="editName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="editEmail" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="editRole" class="form-label">Role</label>
                        <select class="form-control" id="editRole" name="role" required>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-warning">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete User Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="deleteUserForm">
                <input type="hidden" id="deleteUserId" name="id">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Delete User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="modal-feedback" style="display: none;"></div>
                    Are you sure you want to delete this user?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
<script src="../../assets/js/main.js"></script> <!-- Custom JS -->


<script>
    function showModalFeedback(modalId, message, type = 'success') {
        const modal = document.querySelector(`#${modalId}`);
        const feedbackDiv = modal.querySelector('.modal-feedback');
        feedbackDiv.style.display = 'block';
        feedbackDiv.className = 'modal-feedback text-' + (type === 'success' ? 'success' : 'danger');
        feedbackDiv.textContent = message;
    }

    document.getElementById('createUserForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('../../api/create_user.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showModalFeedback('createModal', data.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showModalFeedback('createModal', data.message, 'error');
                }
            });
    });

    // Populate Edit Modal with User Data
    document.querySelectorAll('.edit-user-btn').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('editUserId').value = this.dataset.id;
            document.getElementById('editName').value = this.dataset.name;
            document.getElementById('editEmail').value = this.dataset.email;
            document.getElementById('editRole').value = this.dataset.role;
        });
    });

    // Handle Edit User Form Submission
    document.getElementById('editUserForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('../../api/edit_user.php', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showModalFeedback('editModal', data.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showModalFeedback('editModal', data.message, 'error');
                }
            })
            .catch(error => {
                showModalFeedback('editModal', 'An error occurred. Please try again.', 'error');
                console.error('Error:', error);
            });
    });

    // Populate Delete Modal with User ID
    document.querySelectorAll('.delete-user-btn').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('deleteUserId').value = this.dataset.id;
        });
    });

    // Handle Delete User Form Submission
    document.getElementById('deleteUserForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('../../api/delete_user.php', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showModalFeedback('deleteModal', data.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showModalFeedback('deleteModal', data.message, 'error');
                }
            })
            .catch(error => {
                showModalFeedback('deleteModal', 'An error occurred. Please try again.', 'error');
                console.error('Error:', error);
            });
    });
</script>