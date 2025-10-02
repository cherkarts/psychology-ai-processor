<?php
// Чистая версия API для получения категорий
session_start();
header('Content-Type: application/json');

// Проверка авторизации
if (!isset($_SESSION['admin_user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Неавторизован'], JSON_UNESCAPED_UNICODE);
    exit();
}

try {
    // Подключение к БД
    $config = require '../../config.php';

    $pdo = new PDO(
        "mysql:host=" . $config['database']['host'] . ";dbname=" . $config['database']['dbname'],
        $config['database']['username'],
        $config['database']['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    // Получаем категории медитаций
    $stmt = $pdo->query("SELECT id, name, description, sort_order, is_active FROM meditation_categories WHERE is_active = 1 ORDER BY sort_order, name");
    $categories = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'categories' => $categories
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Ошибка: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>