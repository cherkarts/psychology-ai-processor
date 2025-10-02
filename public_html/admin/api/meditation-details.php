<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Неавторизован']);
    exit();
}

if (!hasPermission('products')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Недостаточно прав']);
    exit();
}

header('Content-Type: application/json');

$meditationId = $_GET['id'] ?? '';

if (empty($meditationId)) {
    echo json_encode(['success' => false, 'message' => 'ID медитации не указан']);
    exit();
}

try {
    $db = getAdminDB();
    
    if ($db) {
        $stmt = $db->prepare("SELECT * FROM meditations WHERE id = ?");
        $stmt->execute([$meditationId]);
        $meditation = $stmt->fetch();
        
        if ($meditation) {
            echo json_encode([
                'success' => true,
                'meditation' => $meditation
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Медитация не найдена']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Ошибка подключения к базе данных']);
    }
    
} catch (Exception $e) {
    error_log("Meditation details error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ошибка сервера']);
}
?>