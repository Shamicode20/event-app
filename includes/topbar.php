<div class="top-navbar">
    <div class="d-flex align-items-center">
        <span class="toggle-btn me-3" onclick="toggleSidebar()" aria-label="Toggle sidebar">☰</span>
        <div class="navbar-brand">Dashboard</div>
    </div>
    <div class="d-flex align-items-center">
        <i class="bi bi-moon-fill me-3" onclick="toggleDarkMode()" style="cursor: pointer;" aria-label="Toggle dark mode"></i>
        <span><?php echo htmlspecialchars($_SESSION['name'] ?? 'Admin'); ?></span>
    </div>
</div>

