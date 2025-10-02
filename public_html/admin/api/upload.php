<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';

// Require authentication
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Неавторизован']);
    exit();
}

// Set JSON header
header('Content-Type: application/json');

// Check CSRF token
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Неверный CSRF токен']);
    exit();
}

// Check if file was uploaded
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Файл не загружен или ошибка загрузки']);
    exit();
}

$file = $_FILES['file'];
$uploadType = $_POST['type'] ?? 'general'; // general, image, audio, document

try {
    // Validate file
    $validation = validateUploadedFile($file, $uploadType);
    if (!$validation['valid']) {
        echo json_encode(['success' => false, 'message' => $validation['message']]);
        exit();
    }
    
    // Generate unique filename
    $originalName = $file['name'];
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $fileName = generateUniqueFileName($originalName, $extension);
    
    // Create upload directory
    $uploadDir = createUploadDirectory($uploadType);
    $filePath = $uploadDir . '/' . $fileName;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        echo json_encode(['success' => false, 'message' => 'Ошибка сохранения файла']);
        exit();
    }
    
    // Log upload activity
    logAdminActivity('upload', "File uploaded: {$originalName}", [
        'type' => $uploadType,
        'size' => $file['size'],
        'filename' => $fileName
    ]);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Файл успешно загружен',
        'file' => [
            'original_name' => $originalName,
            'filename' => $fileName,
            'url' => getFileUrl($uploadType, $fileName),
            'size' => $file['size'],
            'type' => $file['type'],
            'upload_type' => $uploadType
        ]
    ]);
    
} catch (Exception $e) {
    logAdminError("File upload failed: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ошибка загрузки: ' . $e->getMessage()]);
}

function validateUploadedFile($file, $uploadType) {
    // Check file size
    if ($file['size'] > UPLOAD_MAX_SIZE) {
        return ['valid' => false, 'message' => 'Файл слишком большой. Максимальный размер: ' . formatBytes(UPLOAD_MAX_SIZE)];
    }
    
    // Check file extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExtensions = getAllowedExtensions($uploadType);
    
    if (!in_array($extension, $allowedExtensions)) {
        return ['valid' => false, 'message' => 'Тип файла не разрешен. Разрешенные типы: ' . implode(', ', $allowedExtensions)];
    }
    
    // Check MIME type
    $allowedMimeTypes = getAllowedMimeTypes($uploadType);
    if (!empty($allowedMimeTypes) && !in_array($file['type'], $allowedMimeTypes)) {
        return ['valid' => false, 'message' => 'Неверный тип файла'];
    }
    
    return ['valid' => true];
}

function getAllowedExtensions($uploadType) {
    $extensions = [
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'audio' => ['mp3', 'wav', 'ogg', 'm4a'],
        'document' => ['pdf', 'doc', 'docx', 'txt'],
        'general' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp3', 'wav', 'pdf', 'doc', 'docx', 'txt']
    ];
    
    return $extensions[$uploadType] ?? $extensions['general'];
}

function getAllowedMimeTypes($uploadType) {
    $mimeTypes = [
        'image' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        'audio' => ['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/mp4'],
        'document' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'],
        'general' => []
    ];
    
    return $mimeTypes[$uploadType] ?? [];
}

function generateUniqueFileName($originalName, $extension) {
    $baseName = pathinfo($originalName, PATHINFO_FILENAME);
    $baseName = preg_replace('/[^a-zA-Z0-9\-_]/', '_', $baseName);
    $baseName = substr($baseName, 0, 50); // Limit length
    
    return $baseName . '_' . time() . '_' . uniqid() . '.' . $extension;
}

function createUploadDirectory($uploadType) {
    $baseDir = __DIR__ . '/../../uploads';
    $typeDir = $baseDir . '/' . $uploadType;
    $dateDir = $typeDir . '/' . date('Y/m');
    
    if (!file_exists($dateDir)) {
        mkdir($dateDir, 0755, true);
    }
    
    return $dateDir;
}

function getFileUrl($uploadType, $fileName) {
    return '/uploads/' . $uploadType . '/' . date('Y/m') . '/' . $fileName;
}

function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}
?>