<?php
// Чистая версия для сохранения категории медитации
session_start();
header('Content-Type: application/json');

// Проверка авторизации
if (!isset($_SESSION['admin_user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Неавторизован'], JSON_UNESCAPED_UNICODE);
    exit();
}

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Метод не разрешен'], JSON_UNESCAPED_UNICODE);
    exit();
}

// Получение данных
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Неверные данные'], JSON_UNESCAPED_UNICODE);
    exit();
}

$id = isset($input['id']) ? (int) $input['id'] : null;
$name = trim($input['name'] ?? '');
$description = trim($input['description'] ?? '');
$sortOrder = isset($input['sort_order']) ? (int) $input['sort_order'] : 0;
$isActive = isset($input['is_active']) ? (bool) $input['is_active'] : true;

// Валидация
if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Название обязательно'], JSON_UNESCAPED_UNICODE);
    exit();
}

// Генерируем slug из названия
$slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

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

    if ($id) {
        // Обновление существующей категории
        $stmt = $pdo->prepare("
            UPDATE meditation_categories 
            SET name = ?, description = ?, sort_order = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        $stmt->execute([$name, $description, $sortOrder, $isActive, $id]);

        echo json_encode([
            'success' => true,
            'message' => 'Категория обновлена',
            'id' => $id
        ], JSON_UNESCAPED_UNICODE);
    } else {
        // Создание новой категории
        $stmt = $pdo->prepare("
            INSERT INTO meditation_categories (name, slug, description, sort_order, is_active) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $slug, $description, $sortOrder, $isActive]);

        $newId = $pdo->lastInsertId();

        echo json_encode([
            'success' => true,
            'message' => 'Категория создана',
            'id' => $newId
        ], JSON_UNESCAPED_UNICODE);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Ошибка: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>