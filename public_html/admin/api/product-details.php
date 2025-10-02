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
if (!hasPermission('products')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Недостаточно прав']);
    exit();
}

// Set JSON header
header('Content-Type: application/json');

$productId = $_GET['id'] ?? '';

if (empty($productId)) {
    echo json_encode(['success' => false, 'message' => 'ID товара не указан']);
    exit();
}

try {
    $db = getAdminDB();
    
    if ($db) {
        // Get product from database
        $stmt = $db->prepare("SELECT * FROM products WHERE id = ? OR slug = ?");
        $stmt->execute([$productId, $productId]);
        $product = $stmt->fetch();
        
        if ($product) {
            echo json_encode([
                'success' => true,
                'product' => $product
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Товар не найден']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Ошибка подключения к базе данных']);
    }
    
} catch (Exception $e) {
    error_log("Product details error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ошибка сервера']);
}
?>