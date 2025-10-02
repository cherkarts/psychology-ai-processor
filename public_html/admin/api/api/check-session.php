<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';

// Set JSON header
header('Content-Type: application/json');

// Check if session is valid
$isValid = isLoggedIn() && checkSessionTimeout();

echo json_encode([
    'valid' => $isValid,
    'user' => $isValid ? getCurrentUser() : null,
    'timestamp' => time()
]);
?>