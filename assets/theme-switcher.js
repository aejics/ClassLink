// Apply theme based on system preference for Bootstrap pages
(function() {
    const htmlElement = document.documentElement;
    
    // Set initial theme based on system preference
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        htmlElement.setAttribute('data-bs-theme', 'dark');
    } else {
        htmlElement.setAttribute('data-bs-theme', 'light');
    }
    
    // Listen for changes in system theme preference
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
        htmlElement.setAttribute('data-bs-theme', e.matches ? 'dark' : 'light');
    });
})();
