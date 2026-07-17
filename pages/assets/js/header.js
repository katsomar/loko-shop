// Theme Toggle Functionality
(function() {
    const themeToggle = document.getElementById("themeToggle");
    const body = document.body;
    const icon = document.querySelector(".theme-switch i");

    // Load saved theme
    if (localStorage.getItem("theme") === "dark") {
        body.classList.add("dark-mode");
        themeToggle.checked = true;
        icon.classList.remove("fa-moon");
        icon.classList.add("fa-sun");
    }

    // Toggle theme
    themeToggle.addEventListener("change", () => {
        body.classList.toggle("dark-mode");
        if (body.classList.contains("dark-mode")) {
            localStorage.setItem("theme", "dark");
            icon.classList.remove("fa-moon");
            icon.classList.add("fa-sun");
        } else {
            localStorage.setItem("theme", "light");
            icon.classList.remove("fa-sun");
            icon.classList.add("fa-moon");
        }
    });
})();

// Fetch notification count dynamically
async function fetchNotificationCount() {
    try {
        const response = await fetch('../pages/notification_count.php');
        const data = await response.json();
        const badge = document.getElementById('notification-badge');
        if (data.count > 0) {
            badge.textContent = data.count;
            badge.classList.remove('d-none');
        } else {
            badge.classList.add('d-none');
        }
    } catch (error) {
        console.error('Error fetching notification count:', error);
    }
}
fetchNotificationCount();

// Sidebar Toggle Functionality
(function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');

    function openSidebar() {
        if (sidebar) {
            sidebar.classList.add('sidebar-open');
            document.body.classList.add('sidebar-overlay');
            sidebarToggle.classList.add('active');
        }
    }

    function closeSidebar() {
        if (sidebar) {
            sidebar.classList.remove('sidebar-open');
            document.body.classList.remove('sidebar-overlay');
            sidebarToggle.classList.remove('active');
        }
    }

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            if (sidebar.classList.contains('sidebar-open')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
    }

    // Close sidebar when clicking outside (overlay)
    document.addEventListener('click', function(e) {
        if (document.body.classList.contains('sidebar-overlay')) {
            if (sidebar && !sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                closeSidebar();
            }
        }
    });

    // Close sidebar on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeSidebar();
    });
})();
