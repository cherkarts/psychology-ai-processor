<?php
// Чистая версия для сохранения медитации
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
$title = trim($input['title'] ?? '');
$description = trim($input['description'] ?? '');
$audioFile = trim($input['audio_file'] ?? '');
$categoryId = isset($input['category_id']) ? (int) $input['category_id'] : null;
$duration = isset($input['duration']) ? (int) $input['duration'] : 0;
$isActive = isset($input['is_active']) ? (bool) $input['is_active'] : true;
$sortOrder = isset($input['sort_order']) ? (int) $input['sort_order'] : 0;

// Валидация
if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Название обязательно'], JSON_UNESCAPED_UNICODE);
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

    if ($id) {
        // Обновление существующей медитации
        $stmt = $pdo->prepare("
            UPDATE meditations 
            SET title = ?, description = ?, audio_file = ?, category_id = ?, duration = ?, is_active = ?, sort_order = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        $stmt->execute([$title, $description, $audioFile, $categoryId, $duration, $isActive, $sortOrder, $id]);

        echo json_encode([
            'success' => true,
            'message' => 'Медитация обновлена',
            'id' => $id
        ], JSON_UNESCAPED_UNICODE);
    } else {
        // Создание новой медитации
        $stmt = $pdo->prepare("
            INSERT INTO meditations (title, description, audio_file, category_id, duration, is_active, sort_order) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$title, $description, $audioFile, $categoryId, $duration, $isActive, $sortOrder]);

        $newId = $pdo->lastInsertId();

        echo json_encode([
            'success' => true,
            'message' => 'Медитация создана',
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