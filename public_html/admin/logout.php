<?php
session_start();
require_once __DIR__ . '/includes/auth.php';

// Log the logout action
if (isLoggedIn()) {
    logAdminActivity('logout', 'User logged out');
}

// Perform logout
logoutUser();

// Redirect to login page
header('Location: login.php?message=logged_out');
exit();
?>