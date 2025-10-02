<?php
// Authentication functions for admin panel
require_once __DIR__ . '/config.php';

// Initialize and validate admin session
function initializeAdminSession() {
    // Ensure session is started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Validate and clean session
    validateAndCleanSession();
}

// Call session initialization
initializeAdminSession();

// Check if user is logged in
function isLoggedIn() {
    // Handle legacy session format for backward compatibility
    if (isset($_SESSION['admin_logged_in']) && !isset($_SESSION['admin_user'])) {
        // Convert legacy session to new format or clear it
        unset($_SESSION['admin_logged_in']);
        return false;
    }
    
    return isset($_SESSION['admin_user']) && !empty($_SESSION['admin_user']);
}

// Get current admin user
function getCurrentUser() {
    return $_SESSION['admin_user'] ?? null;
}

// Check user permissions
function hasPermission($permission) {
    $user = getCurrentUser();
    if (!$user) return false;
    
    // Admin role has all permissions
    if ($user['role'] === 'admin') return true;
    
    // Check specific permissions
    return in_array($permission, $user['permissions']) || in_array('all', $user['permissions']);
}

// Login function
function loginUser($username, $password) {
    global $adminUsers;
    
    // Check if user exists
    if (!isset($adminUsers[$username])) {
        return ['success' => false, 'message' => 'Неверные данные для входа'];
    }
    
    $user = $adminUsers[$username];
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        // Log failed attempt
        logLoginAttempt($username, false);
        return ['success' => false, 'message' => 'Неверные данные для входа'];
    }
    
    // Check for too many failed attempts
    if (isAccountLocked($username)) {
        return ['success' => false, 'message' => 'Учетная запись временно заблокирована из-за чрезмерного количества неудачных попыток'];
    }
    
    // Successful login
    $_SESSION['admin_user'] = [
        'username' => $user['username'],
        'name' => $user['name'],
        'role' => $user['role'],
        'permissions' => $user['permissions'],
        'login_time' => time()
    ];
    
    // Clear failed attempts
    clearFailedAttempts($username);
    
    // Log successful attempt
    logLoginAttempt($username, true);
    
    return ['success' => true, 'message' => 'Вход выполнен успешно'];
}

// Logout function
function logoutUser() {
    $user = getCurrentUser();
    if ($user) {
        logAdminActivity('logout', "User {$user['username']} logged out");
    }
    
    // Clear all authentication-related session data
    $authKeys = ['admin_user', 'admin_csrf_token', 'admin_logged_in', 'admin_last_activity'];
    foreach ($authKeys as $key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
    
    // Regenerate session ID for security
    session_regenerate_id(true);
}

// Log login attempts
function logLoginAttempt($username, $success) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $logData = [
        'username' => $username,
        'success' => $success,
        'ip' => $ip,
        'user_agent' => $userAgent,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    // Store in session for failed attempts tracking
    if (!$success) {
        if (!isset($_SESSION['failed_attempts'])) {
            $_SESSION['failed_attempts'] = [];
        }
        if (!isset($_SESSION['failed_attempts'][$username])) {
            $_SESSION['failed_attempts'][$username] = [];
        }
        $_SESSION['failed_attempts'][$username][] = time();
        
        // Keep only recent attempts (last hour)
        $_SESSION['failed_attempts'][$username] = array_filter(
            $_SESSION['failed_attempts'][$username],
            function($timestamp) {
                return $timestamp > (time() - 3600);
            }
        );
    }
    
    // Log to file
    $logMessage = sprintf(
        "[%s] Login attempt for user '%s' from IP %s: %s",
        $logData['timestamp'],
        $username,
        $ip,
        $success ? 'SUCCESS' : 'FAILED'
    );
    
    error_log($logMessage, 3, __DIR__ . '/logs/admin_auth.log');
}

// Check if account is locked
function isAccountLocked($username) {
    if (!isset($_SESSION['failed_attempts'][$username])) {
        return false;
    }
    
    $recentAttempts = array_filter(
        $_SESSION['failed_attempts'][$username],
        function($timestamp) {
            return $timestamp > (time() - ADMIN_LOGIN_LOCKOUT_TIME);
        }
    );
    
    return count($recentAttempts) >= ADMIN_LOGIN_ATTEMPTS_LIMIT;
}

// Clear failed attempts
function clearFailedAttempts($username) {
    if (isset($_SESSION['failed_attempts'][$username])) {
        unset($_SESSION['failed_attempts'][$username]);
    }
}

// Log admin activity
function logAdminActivity($action, $description, $context = []) {
    $user = getCurrentUser();
    $logData = [
        'user' => $user ? $user['username'] : 'anonymous',
        'action' => $action,
        'description' => $description,
        'context' => $context,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    $logMessage = sprintf(
        "[%s] User '%s' performed action '%s': %s",
        $logData['timestamp'],
        $logData['user'],
        $action,
        $description
    );
    
    error_log($logMessage, 3, __DIR__ . '/logs/admin_activity.log');
    
    // Also store in database if available
    try {
        $db = getAdminDB();
        if ($db) {
            $stmt = $db->prepare("
                INSERT INTO admin_activity_log (user, action, description, context, ip, created_at) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $logData['user'],
                $action,
                $description,
                json_encode($context),
                $logData['ip'],
                $logData['timestamp']
            ]);
        }
    } catch (Exception $e) {
        // Silent fail for logging - don't break functionality
        error_log("Failed to log admin activity to database: " . $e->getMessage());
    }
}

// Session cleanup and validation
function validateAndCleanSession() {
    // Remove any legacy session data that might conflict
    $legacyKeys = ['admin_logged_in'];
    foreach ($legacyKeys as $key) {
        if (isset($_SESSION[$key]) && !isset($_SESSION['admin_user'])) {
            unset($_SESSION[$key]);
        }
    }
    
    // Validate current session structure
    if (isset($_SESSION['admin_user'])) {
        $user = $_SESSION['admin_user'];
        $requiredFields = ['username', 'name', 'role', 'permissions', 'login_time'];
        
        foreach ($requiredFields as $field) {
            if (!isset($user[$field])) {
                // Invalid session structure, clear it
                unset($_SESSION['admin_user']);
                return false;
            }
        }
        return true;
    }
    
    return false;
}

// Session timeout check
function checkSessionTimeout() {
    // First validate and clean the session
    if (!validateAndCleanSession()) {
        return false;
    }
    
    $user = getCurrentUser();
    if (!$user) return false;
    
    $sessionTimeout = 3600; // 1 hour
    $lastActivity = $_SESSION['admin_last_activity'] ?? $user['login_time'];
    
    if (time() - $lastActivity > $sessionTimeout) {
        logoutUser();
        return false;
    }
    
    $_SESSION['admin_last_activity'] = time();
    return true;
}

// Require login middleware
function requireLogin() {
    if (!isLoggedIn() || !checkSessionTimeout()) {
        header('Location: login.php');
        exit();
    }
}

// Require permission middleware
function requirePermission($permission) {
    requireLogin();
    
    if (!hasPermission($permission)) {
        http_response_code(403);
        include __DIR__ . '/403.php';
        exit();
    }
}
?>