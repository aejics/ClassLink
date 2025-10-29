<?php
// Input Validation Functions

function validate_uuid($uuid) {
    $pattern = '/^[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/i';
    return preg_match($pattern, $uuid) === 1;
}

function validate_date($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

function validate_action($action, $allowed_actions) {
    return in_array($action, $allowed_actions, true);
}

function sanitize_input($input, $max_length = 255) {
    $input = trim($input);
    $input = strip_tags($input);
    return substr($input, 0, $max_length);
}
?>
