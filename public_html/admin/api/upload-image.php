<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';

// Require authentication
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Неавторизован']);
    exit();
}

// Check permission
if (!hasPermission('products')) {
    http_response_code(403);
    echo json_encode(['error' => 'Недостаточно прав']);
    exit();
}

// Set JSON header
header('Content-Type: application/json');

// Check CSRF token
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['error' => 'Неверный CSRF токен']);
    exit();
}

// Check if file was uploaded
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['error' => 'Файл не был загружен']);
    exit();
}

$file = $_FILES['file'];

// Validate file type
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode(['error' => 'Неподдерживаемый тип файла. Разрешены: JPG, PNG, GIF, WEBP']);
    exit();
}

// Validate file size (5MB max)
$maxSize = 5 * 1024 * 1024;
if ($file['size'] > $maxSize) {
    echo json_encode(['error' => 'Файл слишком большой. Максимальный размер: 5MB']);
    exit();
}

// Create upload directory
$uploadDir = __DIR__ . '/../../uploads/editor';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'editor_' . uniqid() . '.' . $extension;
$uploadPath = $uploadDir . '/' . $filename;
$webPath = '/uploads/editor/' . $filename;

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
    // Log the upload
    logAdminActivity('upload', "Uploaded image for editor: {$filename}");
    
    // Return success response for TinyMCE
    echo json_encode([
        'location' => $webPath
    ]);
} else {
    echo json_encode(['error' => 'Ошибка загрузки файла']);
}
?>