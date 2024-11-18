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
