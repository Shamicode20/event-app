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
    /* Responsive Styles for Modals, Table, and Light/Dark Mode */
    .modal-content {
        border-radius: 8px;
        background-color: #ffffff;
        color: #1B4965;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        transition: background-color 0.3s, color 0.3s;
    }
    body.dark-mode .modal-content {
        background-color: #2c2c2c;
        color: #e0e0e0;
    }
    .table-responsive {
        overflow-x: auto;
    }
    body.dark-mode .table {
        color: #e0e0e0;
    }
    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .d-flex {
            flex-wrap: wrap;
        }
        .form-control.d-inline-block {
            width: 100%;
            margin-bottom: 10px;
        }
    }
</style>

<div id="page-content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Users</h2>
        <div>
            <input type="text" id="searchInput" class="form-control d-inline-block w-auto" placeholder="Search..." onkeyup="filterTable()">
            <button class="btn btn-secondary ms-2" onclick="exportToCSV()">Export CSV</button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">Create User</button>
        </div>
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
        <table class="table table-striped table-hover" id="usersTable">
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

<script>
    // Search Functionality
    function filterTable() {
        const input = document.getElementById('searchInput').value.toLowerCase();
        const table = document.getElementById('usersTable');
        const rows = table.querySelectorAll('tbody tr');

        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            const rowText = Array.from(cells).map(cell => cell.textContent.toLowerCase()).join(' ');
            row.style.display = rowText.includes(input) ? '' : 'none';
        });
    }

    // Export to CSV
    function exportToCSV() {
        const rows = document.querySelectorAll('#usersTable tr');
        let csvContent = "";

        rows.forEach(row => {
            const columns = row.querySelectorAll('th, td');
            const rowData = Array.from(columns).map(column => `"${column.textContent}"`).join(",");
            csvContent += rowData + "\r\n";
        });

        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.setAttribute('href', url);
        a.setAttribute('download', 'users_report.csv');
        a.click();
        window.URL.revokeObjectURL(url);
    }
</script>

<!-- Modals (Create, Edit, Delete) -->
<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="createUserForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="createModalLabel">Create User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
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

<?php include '../../includes/footer.php'; ?>
