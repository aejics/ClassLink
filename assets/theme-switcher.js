// Apply theme based on system preference for Bootstrap pages
(function() {
    const htmlElement = document.documentElement;
    const darkModeQuery = window.matchMedia('(prefers-color-scheme: dark)');
    
    // Set initial theme based on system preference
    if (darkModeQuery.matches) {
        htmlElement.setAttribute('data-bs-theme', 'dark');
    } else {
        htmlElement.setAttribute('data-bs-theme', 'light');
    }
    
    // Listen for changes in system theme preference
    darkModeQuery.addEventListener('change', e => {
        htmlElement.setAttribute('data-bs-theme', e.matches ? 'dark' : 'light');
    });
})();
