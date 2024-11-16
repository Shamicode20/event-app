<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit;
}

// Correct path to the database connection file
define('BASE_PATH', realpath(__DIR__ . '/../../'));
require_once BASE_PATH . '/database/connection.php';

// Fetch events dynamically from the database
try {
    $stmt = $conn->prepare("SELECT * FROM events ORDER BY schedule DESC");
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $_SESSION['error'] = "Error fetching events: " . $e->getMessage();
    $events = [];
}

// Include layout files
$title = "Manage Events";
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
        <h2>Events</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">Create</button>
    </div>
    <div class="row">
        <?php if (!empty($events)) : ?>
            <?php foreach ($events as $event) : ?>
                <div class="col-md-4 col-sm-6 col-xs-12 mb-4">
                    <div class="card">
                        <img src="../../assets/img/<?php echo htmlspecialchars($event['poster'] ?? 'default.jpg'); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" class="card-img-top">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($event['description']); ?></p>
                            <p class="card-text">Date: <?php echo htmlspecialchars(date('F j, Y, g:i A', strtotime($event['schedule']))); ?></p>
                            <div class="d-flex justify-content-between">
                                <button class="btn btn-warning btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editModal"
                                    data-id="<?php echo $event['id']; ?>"
                                    data-title="<?php echo htmlspecialchars($event['title']); ?>"
                                    data-description="<?php echo htmlspecialchars($event['description']); ?>"
                                    data-schedule="<?php echo htmlspecialchars($event['schedule']); ?>">
                                    Edit
                                </button>
                                <button class="btn btn-danger btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteModal"
                                    data-id="<?php echo $event['id']; ?>">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p class="text-center">No events found. Create your first event!</p>
        <?php endif; ?>
    </div>
</div>

<!-- Modals -->
<!-- Create Event Modal -->
<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="../../api/create_event.php" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="createModalLabel">Create Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="schedule" class="form-label">Schedule</label>
                        <input type="datetime-local" class="form-control" id="schedule" name="schedule" required>
                    </div>
                    <div class="mb-3">
                        <label for="poster" class="form-label">Poster</label>
                        <input type="file" class="form-control" id="poster" name="poster">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Create Event</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Event Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="../../api/edit_event.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="editEventId" name="id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editTitle" class="form-label">Title</label>
                        <input type="text" class="form-control" id="editTitle" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="editDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="editDescription" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editSchedule" class="form-label">Schedule</label>
                        <input type="datetime-local" class="form-control" id="editSchedule" name="schedule" required>
                    </div>
                    <div class="mb-3">
                        <label for="editPoster" class="form-label">Poster</label>
                        <input type="file" class="form-control" id="editPoster" name="poster">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-warning">Update Event</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Event Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="../../api/delete_event.php" method="POST">
                <input type="hidden" id="deleteEventId" name="id">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Delete Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this event?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
<script>
    // Populate Edit Modal with Event Data
    const editModal = document.getElementById('editModal');
    editModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        document.getElementById('editEventId').value = button.getAttribute('data-id');
        document.getElementById('editTitle').value = button.getAttribute('data-title');
        document.getElementById('editDescription').value = button.getAttribute('data-description');
        document.getElementById('editSchedule').value = button.getAttribute('data-schedule');
    });

    // Populate Delete Modal with Event ID
    const deleteModal = document.getElementById('deleteModal');
    deleteModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        document.getElementById('deleteEventId').value = button.getAttribute('data-id');
    });
</script>