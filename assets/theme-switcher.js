// Apply theme based on system preference for Bootstrap pages
(function() {
    const htmlElement = document.documentElement;
    const darkModeQuery = window.matchMedia('(prefers-color-scheme: dark)');
    
    // Function to apply theme
    function applyTheme(isDark) {
        htmlElement.setAttribute('data-bs-theme', isDark ? 'dark' : 'light');
        
        // Handle admin navbar specifically
        const adminNavbar = document.getElementById('admin-navbar');
        if (adminNavbar) {
            if (isDark) {
                adminNavbar.classList.remove('navbar-light', 'bg-light');
                adminNavbar.classList.add('navbar-dark', 'bg-dark');
            } else {
                adminNavbar.classList.remove('navbar-dark', 'bg-dark');
                adminNavbar.classList.add('navbar-light', 'bg-light');
            }
        }
    }
    
    // Set initial theme based on system preference
    applyTheme(darkModeQuery.matches);
    
    // Listen for changes in system theme preference
    darkModeQuery.addEventListener('change', e => {
        applyTheme(e.matches);
    });
})();
