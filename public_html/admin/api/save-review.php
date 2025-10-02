<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';

// Require authentication
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'success' => false, 
        'message' => 'Сессия истекла. Пожалуйста, войдите в систему заново.',
        'action' => 'redirect',
        'redirect_url' => '/admin/login.php'
    ]);
    exit();
}

// Check permission
if (!hasPermission('reviews')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Недостаточно прав']);
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

$action = $_POST['action'] ?? '';
$reviewId = $_POST['review_id'] ?? '';

// Validate required fields
$requiredFields = ['name', 'email', 'content'];
foreach ($requiredFields as $field) {
    if (empty($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => "Поле '$field' обязательно для заполнения"]);
        exit();
    }
}

// Sanitize input data
$reviewData = [
    'name' => sanitizeInput($_POST['name']),
    'email' => sanitizeInput($_POST['email']),
    'phone' => sanitizeInput($_POST['phone'] ?? ''),
    'content' => sanitizeInput($_POST['content']),
    'rating' => intval($_POST['rating'] ?? 0) ?: null,
    'status' => sanitizeInput($_POST['status'] ?? 'pending'),
    'type' => sanitizeInput($_POST['type'] ?? 'text')
];

// Validate email
if (!filter_var($reviewData['email'], FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Неверный формат email']);
    exit();
}

// Validate status
if (!in_array($reviewData['status'], ['pending', 'approved', 'rejected'])) {
    $reviewData['status'] = 'pending';
}

// Validate type
if (!in_array($reviewData['type'], ['text', 'photo'])) {
    $reviewData['type'] = 'text';
}

// Handle photo upload if provided
$photoPath = null;
if ($reviewData['type'] === 'photo' && isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $uploadResult = handlePhotoUpload($_FILES['photo']);
    if ($uploadResult['success']) {
        $photoPath = $uploadResult['path'];
    } else {
        echo json_encode(['success' => false, 'message' => $uploadResult['message']]);
        exit();
    }
}

try {
    $db = getAdminDB();
    
    if ($db) {
        if ($action === 'create') {
            // Create new review
            $reviewData['created_at'] = date('Y-m-d H:i:s');
            $reviewData['updated_at'] = date('Y-m-d H:i:s');
            if ($photoPath) $reviewData['photo'] = $photoPath;
            
            $stmt = $db->prepare("
                INSERT INTO reviews (name, email, phone, text, rating, status, type, image, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $reviewData['name'],
                $reviewData['email'],
                $reviewData['phone'],
                $reviewData['content'], // maps to 'text' field in database
                $reviewData['rating'],
                $reviewData['status'],
                $reviewData['type'],
                $photoPath,
                $reviewData['created_at'],
                $reviewData['updated_at']
            ]);
            
            if ($result) {
                logAdminActivity('create', "Created new review from {$reviewData['name']}");
                echo json_encode(['success' => true, 'message' => 'Отзыв успешно создан']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Ошибка создания отзыва']);
            }
            
        } else if ($action === 'update' && $reviewId) {
            // Update existing review
            $reviewData['updated_at'] = date('Y-m-d H:i:s');
            
            if ($photoPath) {
                // Update with new photo
                $stmt = $db->prepare("
                    UPDATE reviews 
                    SET name = ?, email = ?, phone = ?, text = ?, rating = ?, status = ?, type = ?, image = ?, updated_at = ? 
                    WHERE id = ?
                ");
                $params = [
                    $reviewData['name'],
                    $reviewData['email'],
                    $reviewData['phone'],
                    $reviewData['content'], // maps to 'text' field
                    $reviewData['rating'],
                    $reviewData['status'],
                    $reviewData['type'],
                    $photoPath,
                    $reviewData['updated_at'],
                    $reviewId
                ];
            } else {
                // Update without changing photo
                $stmt = $db->prepare("
                    UPDATE reviews 
                    SET name = ?, email = ?, phone = ?, text = ?, rating = ?, status = ?, type = ?, updated_at = ? 
                    WHERE id = ?
                ");
                $params = [
                    $reviewData['name'],
                    $reviewData['email'],
                    $reviewData['phone'],
                    $reviewData['content'], // maps to 'text' field
                    $reviewData['rating'],
                    $reviewData['status'],
                    $reviewData['type'],
                    $reviewData['updated_at'],
                    $reviewId
                ];
            }
            
            $result = $stmt->execute($params);
            
            if ($result) {
                logAdminActivity('update', "Updated review #{$reviewId}");
                echo json_encode(['success' => true, 'message' => 'Отзыв успешно обновлен']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Ошибка обновления отзыва']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Неверное действие или отсутствует ID отзыва']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Ошибка подключения к базе данных']);
    }
    
} catch (Exception $e) {
    error_log("Save review error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Provide specific error feedback
    $errorMessage = 'Ошибка сервера';
    
    if (strpos($e->getMessage(), 'permission') !== false || strpos($e->getMessage(), 'write') !== false) {
        $errorMessage = 'Ошибка записи файла. Проверьте права доступа.';
    } elseif (strpos($e->getMessage(), 'upload') !== false) {
        $errorMessage = 'Ошибка загрузки файла.';
    } elseif (strpos($e->getMessage(), 'database') !== false || strpos($e->getMessage(), 'SQL') !== false) {
        $errorMessage = 'Ошибка базы данных.';
    }
    
    echo json_encode([
        'success' => false, 
        'message' => $errorMessage,
        'debug' => $config['environment'] === 'development' ? $e->getMessage() : null
    ]);
}

function handlePhotoUpload($file) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Неподдерживаемый тип файла. Разрешены: JPG, PNG, GIF, WEBP'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'Файл слишком большой. Максимальный размер: 5MB'];
    }
    
    $uploadDir = __DIR__ . '/../../uploads/reviews';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'review_' . uniqid() . '.' . $extension;
    $uploadPath = $uploadDir . '/' . $filename;
    $webPath = '/uploads/reviews/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return ['success' => true, 'path' => $webPath];
    } else {
        return ['success' => false, 'message' => 'Ошибка загрузки файла'];
    }
}


?>