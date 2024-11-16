<div id="sidebar" class="sidebar">
    <ul>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <!-- Admin Sidebar -->
            <li><a href="home.php"><i class="bi bi-house-door-fill me-2"></i>Home</a></li>
            <li><a href="events.php"><i class="bi bi-calendar-event-fill me-2"></i>Events</a></li>
            <li><a href="users.php"><i class="bi bi-people-fill me-2"></i>Users</a></li>
            <li><a href="settings.php"><i class="bi bi-gear-fill me-2"></i>Settings</a></li>
            <li class="logout"><a href="../../logout.php"><i class="bi bi-box-arrow-right me-2"></i>Log Out</a></li>
        <?php else: ?>
            <!-- User Sidebar -->
            <li><a href="home.php"><i class="bi bi-house-door-fill me-2"></i>Home</a></li>
            <li><a href="profile.php"><i class="bi bi-person-circle me-2"></i>User Profile</a></li>
            <li><a href="event_dashboard.php"><i class="bi bi-bar-chart-fill me-2"></i>Event Dashboard</a></li>
            <li><a href="event_registration.php"><i class="bi bi-pencil-square me-2"></i>Event Registration</a></li>
            <li class="logout"><a href="../../logout.php"><i class="bi bi-box-arrow-right me-2"></i>Log Out</a></li>
        <?php endif; ?>
    </ul>
</div>