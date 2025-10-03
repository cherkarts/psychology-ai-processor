<?php
// API для сохранения статьи
// Гарантируем чистый JSON и перехват фатальных ошибок, чтобы не было пустого тела
while (ob_get_level()) {
  ob_end_clean();
}
header('Content-Type: application/json; charset=UTF-8');
error_reporting(E_ALL);
ini_set('display_errors', '0');

$__didOutput = false;

register_shutdown_function(function () use (&$__didOutput) {
  $e = error_get_last();
  if ($__didOutput)
    return;
  if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
    error_log('save-article FATAL: ' . $e['message'] . ' in ' . $e['file'] . ':' . $e['line']);
    echo json_encode(['success' => false, 'message' => 'FATAL: ' . $e['message']], JSON_UNESCAPED_UNICODE);
    return;
  }
  // Пустой ответ — отдадим диагностическое сообщение
  echo json_encode(['success' => false, 'message' => 'Empty response (guard)'], JSON_UNESCAPED_UNICODE);
});

set_error_handler(function ($errno, $errstr, $errfile, $errline) use (&$__didOutput) {
  error_log("save-article PHP-$errno: $errstr in $errfile:$errline");
  http_response_code(500);
  $__didOutput = true;
  echo json_encode(['success' => false, 'message' => $errstr], JSON_UNESCAPED_UNICODE);
  exit();
});

set_exception_handler(function ($e) use (&$__didOutput) {
  error_log('save-article EX: ' . $e->getMessage());
  http_response_code(500);
  $__didOutput = true;
  echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
  exit();
});

session_start();

// Проверка авторизации
if (!isset($_SESSION['admin_user'])) {
  http_response_code(401);
  echo json_encode(['success' => false, 'message' => 'Неавторизован'], JSON_UNESCAPED_UNICODE);
  exit();
}

// Разрешаем POST (create/update) и PATCH (toggle)
if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PATCH'])) {
  http_response_code(405);
  $__didOutput = true;
  echo json_encode(['success' => false, 'message' => 'Метод не разрешен'], JSON_UNESCAPED_UNICODE);
  exit();
}

// Получение данных
$input = json_decode(file_get_contents('php://input'), true);
// Быстрый переключатель публикации
if (($_SERVER['REQUEST_METHOD'] === 'PATCH') || (isset($input['action']) && $input['action'] === 'toggle_publish')) {
  $id = isset($input['id']) ? (int) $input['id'] : null;
  $publish = isset($input['publish']) ? (bool) $input['publish'] : null;
  if (!$id || $publish === null) {
    $__didOutput = true;
    echo json_encode(['success' => false, 'message' => 'Некорректные данные'], JSON_UNESCAPED_UNICODE);
    exit();
  }

  try {
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

    // Определяем имя колонки статуса
    $cols = $pdo->query("SHOW COLUMNS FROM articles")->fetchAll(PDO::FETCH_COLUMN);
    $statusCol = in_array('is_active', $cols) ? 'is_active' : (in_array('is_published', $cols) ? 'is_published' : null);
    if (!$statusCol)
      throw new Exception('Не найдена колонка статуса в таблице articles');

    $stmt = $pdo->prepare("UPDATE articles SET {$statusCol} = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->execute([$publish ? 1 : 0, $id]);

    $__didOutput = true;
    echo json_encode([
      'success' => true,
      'message' => $publish ? 'Статья опубликована' : 'Статья снята с публикации',
      'id' => $id,
      'is_active' => $publish
    ], JSON_UNESCAPED_UNICODE);
    exit();
  } catch (Exception $e) {
    $__didOutput = true;
    echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit();
  }
}

if (!$input) {
  $__didOutput = true;
  echo json_encode(['success' => false, 'message' => 'Неверные данные'], JSON_UNESCAPED_UNICODE);
  exit();
}

$id = isset($input['id']) ? (int) $input['id'] : null;
// Функция для правильной обработки текста перед сохранением
$processText = function ($text) {
  if (empty($text))
    return $text;

  // Убеждаемся, что текст в правильной кодировке UTF-8
  $text = trim($text);

  // Если текст уже в правильной кодировке, возвращаем как есть
  if (mb_check_encoding($text, 'UTF-8')) {
    return $text;
  }

  // Если нет, пытаемся исправить
  $fixed = mb_convert_encoding($text, 'UTF-8', 'auto');
  return $fixed;
};

$title = $processText($input['title'] ?? '');
$content = $processText($input['content'] ?? '');
$excerpt = $processText($input['excerpt'] ?? '');
$author = $processText($input['author'] ?? '');
$tags = $processText($input['tags'] ?? '');
$slug = $processText($input['slug'] ?? '');
$categoryId = isset($input['category_id']) ? (int) $input['category_id'] : null;
$hasImageField = array_key_exists('image', $input);
$image = $hasImageField ? trim($input['image']) : null;
$isActive = isset($input['is_active']) ? (bool) $input['is_active'] : true;
$sortOrder = isset($input['sort_order']) ? (int) $input['sort_order'] : 0;
$date = isset($input['date']) ? trim($input['date']) : '';

// Валидация
if (empty($title)) {
  $__didOutput = true;
  echo json_encode(['success' => false, 'message' => 'Название обязательно'], JSON_UNESCAPED_UNICODE);
  exit();
}

if (empty($content)) {
  $__didOutput = true;
  echo json_encode(['success' => false, 'message' => 'Содержание обязательно'], JSON_UNESCAPED_UNICODE);
  exit();
}

// Готовим корректное значение для колонки tags (JSON)
$tagsValue = null;
if ($tags !== '') {
  error_log("Tags input: " . $tags);

  $isJson = null !== json_decode($tags, true) && json_last_error() === JSON_ERROR_NONE;
  if ($isJson) {
    // Если пришёл JSON, декодируем, очищаем и кодируем заново
    $decoded = json_decode($tags, true);
    error_log("Tags decoded from JSON: " . print_r($decoded, true));

    // Удаляем дубликаты и пустые значения
    $cleaned = array_values(array_unique(array_filter(array_map('trim', $decoded), fn($v) => $v !== '')));
    error_log("Tags cleaned: " . print_r($cleaned, true));

    $tagsValue = json_encode($cleaned, JSON_UNESCAPED_UNICODE);
  } else {
    // Если пришла строка через запятую
    $parts = array_values(array_filter(array_map('trim', explode(',', $tags)), fn($v) => $v !== ''));
    // Удаляем дубликаты
    $parts = array_values(array_unique($parts));
    error_log("Tags from comma-separated: " . print_r($parts, true));

    $tagsValue = json_encode($parts, JSON_UNESCAPED_UNICODE);
  }

  error_log("Tags final JSON: " . $tagsValue);
}

try {
  // Подключение к БД с правильной кодировкой
  $config = require '../../config.php';
  $dsn = "mysql:host={$config['database']['host']};port={$config['database']['port']};dbname={$config['database']['dbname']};charset={$config['database']['charset']}";
  $pdo = new PDO($dsn, $config['database']['username'], $config['database']['password'], $config['database']['options']);

  // Убеждаемся, что соединение использует UTF-8
  $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");

  // Определяем доступные колонки, чтобы избежать ошибок вида Unknown column
  $columns = $pdo->query("SHOW COLUMNS FROM articles")->fetchAll(PDO::FETCH_COLUMN);
  $imageCol = in_array('image', $columns) ? 'image' : (in_array('featured_image', $columns) ? 'featured_image' : null);
  $statusCol = in_array('is_active', $columns) ? 'is_active' : (in_array('is_published', $columns) ? 'is_published' : null);
  $sortCol = in_array('sort_order', $columns) ? 'sort_order' : null;
  $publishedAtCol = in_array('published_at', $columns) ? 'published_at' : null;

  if ($id) {
    // Обновление существующей статьи
    $set = ['title = ?', 'content = ?', 'excerpt = ?', 'author = ?', 'tags = ?', 'category_id = ?'];
    $values = [$title, $content, $excerpt, $author, $tagsValue, $categoryId];
    
    // Добавляем slug если он есть
    if (!empty($slug)) {
      $set[] = 'slug = ?';
      $values[] = $slug;
    }
    if ($imageCol && $hasImageField) {
      $set[] = "$imageCol = ?";
      $values[] = $image;
    }
    if ($statusCol) {
      $set[] = "$statusCol = ?";
      $values[] = $isActive ? 1 : 0;
    }
    if ($sortCol) {
      $set[] = "$sortCol = ?";
      $values[] = $sortOrder;
    }
    if ($publishedAtCol && $date !== '') {
      $set[] = "$publishedAtCol = ?";
      $values[] = $date;
    }
    $sql = "UPDATE articles SET " . implode(', ', $set) . ", updated_at = CURRENT_TIMESTAMP WHERE id = ?";
    $values[] = $id;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($values);

    $__didOutput = true;
    echo json_encode([
      'success' => true,
      'message' => 'Статья обновлена',
      'id' => $id
    ], JSON_UNESCAPED_UNICODE);
  } else {
    // Создание новой статьи
    $fields = ['title', 'content', 'excerpt', 'author', 'tags', 'category_id'];
    $placeholders = ['?', '?', '?', '?', '?', '?'];
    $values = [$title, $content, $excerpt, $author, $tagsValue, $categoryId];
    
    // Добавляем slug если он есть
    if (!empty($slug)) {
      $fields[] = 'slug';
      $placeholders[] = '?';
      $values[] = $slug;
    }
    if ($imageCol && $hasImageField) {
      $fields[] = $imageCol;
      $placeholders[] = '?';
      $values[] = $image;
    }
    if ($statusCol) {
      $fields[] = $statusCol;
      $placeholders[] = '?';
      $values[] = $isActive ? 1 : 0;
    }
    if ($sortCol) {
      $fields[] = $sortCol;
      $placeholders[] = '?';
      $values[] = $sortOrder;
    }
    if ($publishedAtCol && $date !== '') {
      $fields[] = $publishedAtCol;
      $placeholders[] = '?';
      $values[] = $date;
    }

    $sql = "INSERT INTO articles (" . implode(',', $fields) . ") VALUES (" . implode(',', $placeholders) . ")";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($values);

    $newId = $pdo->lastInsertId();

    $__didOutput = true;
    echo json_encode([
      'success' => true,
      'message' => 'Статья создана',
      'id' => $newId
    ], JSON_UNESCAPED_UNICODE);
  }

} catch (Exception $e) {
  $__didOutput = true;
  echo json_encode([
    'success' => false,
    'message' => 'Ошибка: ' . $e->getMessage()
  ], JSON_UNESCAPED_UNICODE);
}
?>