<?php
// Simple CSRF helper. Stores token in session and provides helper functions.
if (session_status() === PHP_SESSION_NONE) session_start();

function csrf_token() {
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['_csrf_token'];
}

function csrf_field() {
    $t = htmlspecialchars(csrf_token(), ENT_QUOTES);
    return "<input type=\"hidden\" name=\"_csrf\" value=\"{$t}\">";
}

function verify_csrf($token) {
    if (!$token) return false;
    return hash_equals($_SESSION['_csrf_token'] ?? '', $token);
}
