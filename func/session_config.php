<?php
// Secure session configuration
// Include this file before calling session_start()

// Prevent session fixation attacks
ini_set('session.use_strict_mode', 1);

// Use cookies only (not URL parameters)
ini_set('session.use_only_cookies', 1);

// Make cookies HTTP only to prevent XSS attacks
ini_set('session.cookie_httponly', 1);

// Set secure flag if HTTPS is being used
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}

// Set SameSite attribute to prevent CSRF
ini_set('session.cookie_samesite', 'Lax');

// Use strong session ID
ini_set('session.sid_length', 48);
ini_set('session.sid_bits_per_character', 6);

// Set session timeout (30 minutes of inactivity)
ini_set('session.gc_maxlifetime', 1800);

// Session name (change this to something unique)
session_name('CLASSLINK_SESSION');
?>
