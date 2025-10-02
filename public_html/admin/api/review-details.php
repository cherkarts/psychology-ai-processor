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

// Check permission
if (!hasPermission('reviews')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Недостаточно прав']);
    exit();
}

// Set JSON header
header('Content-Type: application/json');

$reviewId = $_GET['id'] ?? '';

if (empty($reviewId)) {
    echo json_encode(['success' => false, 'message' => 'ID отзыва не указан']);
    exit();
}

try {
    $db = getAdminDB();
    
    if ($db) {
        // Get review from database
        $stmt = $db->prepare("SELECT * FROM reviews WHERE id = ?");
        $stmt->execute([$reviewId]);
        $review = $stmt->fetch();
        
        if ($review) {
            echo json_encode([
                'success' => true,
                'review' => $review
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Отзыв не найден']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Ошибка подключения к базе данных']);
    }
    
} catch (Exception $e) {
    error_log("Review details error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ошибка сервера']);
}
?>