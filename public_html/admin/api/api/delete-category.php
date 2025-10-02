<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';

// Require authentication
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Неавторизован']);
    exit();
}

// Check permission
if (!hasPermission('articles')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Недостаточно прав']);
    exit();
}

// Set JSON header
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Метод не разрешен']);
    exit();
}

// Check CSRF token
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Неверный CSRF токен']);
    exit();
}

$categoryId = intval($_POST['category_id'] ?? 0);

if (empty($categoryId)) {
    echo json_encode(['success' => false, 'message' => 'ID категории обязателен']);
    exit();
}

try {
    $db = getAdminDB();

    if (!$db) {
        echo json_encode(['success' => false, 'message' => 'Ошибка подключения к базе данных']);
        exit();
    }

    // Check if category exists
    $stmt = $db->prepare("SELECT id, name FROM article_categories WHERE id = ?");
    $stmt->execute([$categoryId]);
    $category = $stmt->fetch();

    if (!$category) {
        echo json_encode(['success' => false, 'message' => 'Категория не найдена']);
        exit();
    }

    // Check if any articles are using this category
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM articles WHERE category_id = ?");
    $stmt->execute([$categoryId]);
    $result = $stmt->fetch();

    if ($result['count'] > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Невозможно удалить категорию, так как к ней привязаны статьи (' . $result['count'] . ' шт.)'
        ]);
        exit();
    }

    // Delete the category
    $stmt = $db->prepare("DELETE FROM article_categories WHERE id = ?");
    $stmt->execute([$categoryId]);

    if ($stmt->rowCount() > 0) {
        logAdminActivity('delete', "Deleted category: {$category['name']}");
        echo json_encode([
            'success' => true,
            'message' => 'Категория успешно удалена'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Ошибка удаления категории'
        ]);
    }

} catch (Exception $e) {
    error_log("Delete category error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ошибка удаления категории']);
}
?>