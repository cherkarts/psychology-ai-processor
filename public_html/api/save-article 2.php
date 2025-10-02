<?php
// API для сохранения статьи
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
$content = trim($input['content'] ?? '');
$excerpt = trim($input['excerpt'] ?? '');
$author = trim($input['author'] ?? '');
$tags = trim($input['tags'] ?? '');
$categoryId = isset($input['category_id']) ? (int) $input['category_id'] : null;
$image = trim($input['image'] ?? '');
$isActive = isset($input['is_active']) ? (bool) $input['is_active'] : true;
$sortOrder = isset($input['sort_order']) ? (int) $input['sort_order'] : 0;

// Валидация
if (empty($title)) {
  echo json_encode(['success' => false, 'message' => 'Название обязательно'], JSON_UNESCAPED_UNICODE);
  exit();
}

if (empty($content)) {
  echo json_encode(['success' => false, 'message' => 'Содержание обязательно'], JSON_UNESCAPED_UNICODE);
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
    // Обновление существующей статьи
    $stmt = $pdo->prepare("
            UPDATE articles 
            SET title = ?, content = ?, excerpt = ?, author = ?, tags = ?, category_id = ?, image = ?, is_active = ?, sort_order = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
    $stmt->execute([$title, $content, $excerpt, $author, $tags, $categoryId, $image, $isActive, $sortOrder, $id]);

    echo json_encode([
      'success' => true,
      'message' => 'Статья обновлена',
      'id' => $id
    ], JSON_UNESCAPED_UNICODE);
  } else {
    // Создание новой статьи
    $stmt = $pdo->prepare("
            INSERT INTO articles (title, content, excerpt, author, tags, category_id, image, is_active, sort_order) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
    $stmt->execute([$title, $content, $excerpt, $author, $tags, $categoryId, $image, $isActive, $sortOrder]);

    $newId = $pdo->lastInsertId();

    echo json_encode([
      'success' => true,
      'message' => 'Статья создана',
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