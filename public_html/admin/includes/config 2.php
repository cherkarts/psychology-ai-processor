<?php
// Admin configuration file
require_once __DIR__ . '/../../config.php';

// Load main site config
$config = require __DIR__ . '/../../config.php';

// Admin specific configuration
define('ADMIN_SESSION_NAME', 'cherkas_admin_session');
define('ADMIN_LOGIN_ATTEMPTS_LIMIT', 5);
define('ADMIN_LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// Admin user credentials (in production, move to database)
$adminUsers = [
    'admin' => [
        'username' => 'admin',
        'password' => password_hash('admin123!', PASSWORD_DEFAULT), // Change in production
        'name' => 'Administrator',
        'role' => 'admin',
        'permissions' => ['all']
    ],
    'denis' => [
        'username' => 'denis',
        'password' => password_hash('denis2024!', PASSWORD_DEFAULT), // Change in production
        'name' => 'Denis Cherkas',
        'role' => 'editor',
        'permissions' => ['reviews', 'articles', 'products', 'meditations', 'promos', 'comments']
    ]
];

// Database connection function
function getAdminDB()
{
    global $config;

    try {
        $socket = $config['database']['socket'] ?? null;
        $charset = trim($config['database']['charset'] ?? '');

        if ($socket) {
            $dsn = "mysql:unix_socket={$socket};dbname={$config['database']['dbname']}";
        } else {
            $dsn = "mysql:host={$config['database']['host']};dbname={$config['database']['dbname']}";
        }

        // Append charset only when explicitly provided
        if ($charset !== '') {
            $dsn .= ";charset={$charset}";
        }

        $pdo = new PDO($dsn, $config['database']['username'], $config['database']['password'], $config['database']['options']);

        // Test the connection by running a simple query
        $pdo->query("SELECT 1");

        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        error_log("PDO Error Code: " . $e->getCode());
        error_log("Connection string: " . ($dsn ?? 'DSN not set'));
        return null;
    }
}

// Security functions - moved to includes/functions.php

// XSS protection
function sanitizeOutput($data)
{
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

function sanitizeInput($data)
{
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return trim(strip_tags($data));
}

// File upload configuration
define('UPLOAD_MAX_SIZE', 50 * 1024 * 1024); // 50MB для аудио файлов
define('UPLOAD_ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'mp3', 'wav']);
define('UPLOAD_PATH', __DIR__ . '/../../uploads/');

// Create upload directory if it doesn't exist
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}

// Pagination settings
define('ADMIN_ITEMS_PER_PAGE', 20);

// Date format
define('ADMIN_DATE_FORMAT', 'Y-m-d H:i:s');
define('ADMIN_DATE_DISPLAY_FORMAT', 'd.m.Y H:i');

// Error logging
function logAdminError($message, $context = [])
{
    $logMessage = date('[Y-m-d H:i:s] ') . $message;
    if (!empty($context)) {
        $logMessage .= ' Context: ' . json_encode($context);
    }
    error_log($logMessage, 3, __DIR__ . '/logs/admin_errors.log');
}

// Create logs directory if it doesn't exist
$logsDir = __DIR__ . '/logs';
if (!file_exists($logsDir)) {
    mkdir($logsDir, 0755, true);
}
?>