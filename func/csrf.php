<?php
// CSRF Protection Functions

function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function csrf_token_field() {
    $token = generate_csrf_token();
    return "<input type='hidden' name='csrf_token' value='" . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . "'>";
}
?>
