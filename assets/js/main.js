function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const contentWrapper = document.getElementById('page-content-wrapper');
    
    // Toggle the sidebar's collapsed state
    sidebar.classList.toggle('collapsed');
    contentWrapper.classList.toggle('expanded');
}

        function toggleDarkMode() {
            document.body.classList.toggle('dark-mode');
        }