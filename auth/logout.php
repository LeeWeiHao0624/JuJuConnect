<?php
require_once '../config/config.php';

// Log activity before destroying session
if (is_logged_in()) {
    log_activity($pdo, $_SESSION['user_id'], 'logout', 'User', $_SESSION['user_id']);
}

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Clear remember me cookie if exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Redirect to home page
redirect(SITE_URL . '/index.php?logged_out=1');
?>

