<?php
// CRUD API для категорий статей
while (ob_get_level()) {
  ob_end_clean();
}
header('Content-Type: application/json; charset=UTF-8');
session_start();
error_reporting(E_ALL);
ini_set('display_errors', '0');

if (!isset($_SESSION['admin_user'])) {
  http_response_code(401);
  echo json_encode(['success' => false, 'message' => 'Неавторизован'], JSON_UNESCAPED_UNICODE);
  exit;
}

require_once __DIR__ . '/../includes/config.php';
$pdo = getAdminDB();
if (!$pdo) {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Сбой подключения к БД'], JSON_UNESCAPED_UNICODE);
  exit;
}

function slugify($str)
{
  $str = trim(mb_strtolower($str));
  $map = [
    'а' => 'a',
    'б' => 'b',
    'в' => 'v',
    'г' => 'g',
    'д' => 'd',
    'е' => 'e',
    'ё' => 'e',
    'ж' => 'zh',
    'з' => 'z',
    'и' => 'i',
    'й' => 'y',
    'к' => 'k',
    'л' => 'l',
    'м' => 'm',
    'н' => 'n',
    'о' => 'o',
    'п' => 'p',
    'р' => 'r',
    'с' => 's',
    'т' => 't',
    'у' => 'u',
    'ф' => 'f',
    'х' => 'h',
    'ц' => 'c',
    'ч' => 'ch',
    'ш' => 'sh',
    'щ' => 'sch',
    'ъ' => '',
    'ы' => 'y',
    'ь' => '',
    'э' => 'e',
    'ю' => 'yu',
    'я' => 'ya'
  ];
  $str = strtr($str, $map);
  $str = preg_replace('~[^a-z0-9]+~', '-', $str);
  return trim($str, '-');
}

$method = $_SERVER['REQUEST_METHOD'];

try {
  if ($method === 'GET') {
    // Список
    $rows = $pdo->query('SELECT id, name, slug, is_active, sort_order, created_at FROM article_categories ORDER BY sort_order, name')->fetchAll();
    echo json_encode(['success' => true, 'items' => $rows], JSON_UNESCAPED_UNICODE);
    exit;
  }

  $payload = json_decode(file_get_contents('php://input'), true) ?? [];

  if ($method === 'POST') {
    $name = trim($payload['name'] ?? '');
    $slug = trim($payload['slug'] ?? '');
    $isActive = !empty($payload['is_active']) ? 1 : 0;
    $sort = (int) ($payload['sort_order'] ?? 0);
    if ($name === '') {
      echo json_encode(['success' => false, 'message' => 'Название обязательно'], JSON_UNESCAPED_UNICODE);
      exit;
    }
    if ($slug === '') {
      $slug = slugify($name);
    }
    $stmt = $pdo->prepare('INSERT INTO article_categories (name, slug, is_active, sort_order, created_at) VALUES (?,?,?,?,NOW())');
    $stmt->execute([$name, $slug, $isActive, $sort]);
    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()], JSON_UNESCAPED_UNICODE);
    exit;
  }

  if ($method === 'PATCH') {
    $id = (int) ($payload['id'] ?? 0);
    if ($id <= 0) {
      echo json_encode(['success' => false, 'message' => 'ID обязателен'], JSON_UNESCAPED_UNICODE);
      exit;
    }
    $fields = [];
    $params = [];
    if (isset($payload['name'])) {
      $fields[] = 'name = ?';
      $params[] = trim($payload['name']);
    }
    if (isset($payload['slug'])) {
      $fields[] = 'slug = ?';
      $params[] = trim($payload['slug']);
    }
    if (isset($payload['is_active'])) {
      $fields[] = 'is_active = ?';
      $params[] = $payload['is_active'] ? 1 : 0;
    }
    if (isset($payload['sort_order'])) {
      $fields[] = 'sort_order = ?';
      $params[] = (int) $payload['sort_order'];
    }
    if (!$fields) {
      echo json_encode(['success' => false, 'message' => 'Нет полей для обновления'], JSON_UNESCAPED_UNICODE);
      exit;
    }
    $sql = 'UPDATE article_categories SET ' . implode(', ', $fields) . ' WHERE id = ?';
    $params[] = $id;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
    exit;
  }

  if ($method === 'DELETE') {
    $id = (int) ($_GET['id'] ?? 0);
    if ($id <= 0) {
      echo json_encode(['success' => false, 'message' => 'ID обязателен'], JSON_UNESCAPED_UNICODE);
      exit;
    }
    $stmt = $pdo->prepare('DELETE FROM article_categories WHERE id = ?');
    $stmt->execute([$id]);
    echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
    exit;
  }

  http_response_code(405);
  echo json_encode(['success' => false, 'message' => 'Метод не разрешен'], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>

