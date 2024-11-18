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
    /* Responsive Table */
    .table-responsive {
        overflow-x: auto;
    }

    /* Responsive Table for Small Screens */
    @media (max-width: 768px) {
        table thead {
            display: none; /* Hide table headers on smaller screens */
        }

        table tbody tr {
            display: block;
            margin-bottom: 15px;
            border: 1px solid #ddd; /* Add border around rows for clarity */
            border-radius: 5px;
            padding: 10px;
            background: #fff; /* Ensure visibility on dark mode */
        }

        table tbody td {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #ddd;
            padding: 8px;
            font-size: 14px;
        }

        table tbody td::before {
            content: attr(data-label); /* Use data-label attribute as header text */
            font-weight: bold;
            text-transform: uppercase;
            color: #555;
            margin-right: 10px;
        }

        .btn {
            font-size: 14px;
            padding: 6px 10px;
        }
    }

    /* Add spacing for mobile-friendly buttons */
    @media (max-width: 768px) {
        .btn {
            margin-top: 5px;
        }
    }

    /* Dark Mode Adjustments */
    body.dark-mode table tbody tr {
        background-color: #333;
        color: #fff;
    }

    body.dark-mode table tbody td {
        border-bottom: 1px solid #444;
    }

    body.dark-mode table tbody td::before {
        color: #aaa;
    }

    /* Adjust input fields and buttons for mobile screens */
    @media (max-width: 576px) {
        #searchInput {
            width: 100%;
            margin-bottom: 10px;
        }

        .btn-custom,
        .btn-primary {
            width: 100%;
            margin-bottom: 10px;
        }
    }
</style>

<div id="page-content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <h2>Users</h2>
        <div class="d-flex flex-wrap">
            <input type="text" id="searchInput" class="form-control d-inline-block w-auto" placeholder="Search Users" style="margin-right: 10px;">
            <button class="btn btn-custom light" id="exportCsvBtn" style="margin-right: 10px;">Export CSV</button>
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
                            <td data-label="#"> <?php echo $index + 1; ?> </td>
                            <td data-label="Name"> <?php echo htmlspecialchars($user['name']); ?> </td>
                            <td data-label="Email"> <?php echo htmlspecialchars($user['email']); ?> </td>
                            <td data-label="Role"> <?php echo htmlspecialchars($user['role']); ?> </td>
                            <td data-label="Created At"> 
                                <?php echo htmlspecialchars(date('F j, Y, g:i A', strtotime($user['created_at']))); ?>
                            </td>
                            <td data-label="Actions">
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
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const query = this.value.toLowerCase();
        const rows = document.querySelectorAll('#usersTable tbody tr');

        rows.forEach(row => {
            const name = row.cells[1].textContent.toLowerCase();
            const email = row.cells[2].textContent.toLowerCase();
            if (name.includes(query) || email.includes(query)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // CSV Export Functionality
    document.getElementById('exportCsvBtn').addEventListener('click', function() {
        const rows = Array.from(document.querySelectorAll('#usersTable tr'));
        let csvContent = '';

        rows.forEach(row => {
            const cols = Array.from(row.children);
            const rowData = cols.map(col => col.textContent.replace(/,/g, '')).join(',');
            csvContent += rowData + '\n';
        });

        const blob = new Blob([csvContent], {
            type: 'text/csv;charset=utf-8;'
        });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.setAttribute('href', url);
        link.setAttribute('download', 'users.csv');
        link.click();
    });

    // Populate Edit Modal
    document.querySelectorAll('.edit-user-btn').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('editUserId').value = this.dataset.id;
            document.getElementById('editName').value = this.dataset.name;
            document.getElementById('editEmail').value = this.dataset.email;
            document.getElementById('editRole').value = this.dataset.role;
        });
    });

    // Handle Create User Form Submission
    document.getElementById('createUserForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('../../api/create_user.php', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    alert(data.message);
                }
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
                    alert(data.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    alert(data.message);
                }
            });
    });

    // Handle Delete User Form Submission

    document.querySelectorAll('.delete-user-btn').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.dataset.id; // Get ID from button's data attribute
            document.getElementById('deleteUserId').value = userId; // Set it in the hidden field
        });
    });

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
                    alert(data.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    alert(data.message);
                }
            });
    });
</script>

<?php include '../../includes/footer.php'; ?>
