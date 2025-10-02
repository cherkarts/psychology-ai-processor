<?php
// Гарантируем JSON-ответ и перехват любых ошибок/фаталов, чтобы не было пустого тела
header('Content-Type: application/json; charset=UTF-8');
while (ob_get_level()) {
  ob_end_clean();
}
error_reporting(E_ALL);
ini_set('display_errors', '0');

$__didOutput = false;

register_shutdown_function(function () use (&$__didOutput) {
  $last = error_get_last();
  if ($__didOutput) {
    return;
  }
  if ($last && in_array($last['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
    error_log('get-article FATAL: ' . $last['message'] . ' in ' . $last['file'] . ':' . $last['line']);
    echo json_encode(['success' => false, 'message' => 'FATAL: ' . $last['message']], JSON_UNESCAPED_UNICODE);
    return;
  }
  echo json_encode(['success' => false, 'message' => 'Empty response (guard)'], JSON_UNESCAPED_UNICODE);
});

set_error_handler(function ($errno, $errstr, $errfile, $errline) use (&$__didOutput) {
  error_log("get-article PHP-$errno: $errstr in $errfile:$errline");
  http_response_code(500);
  $__didOutput = true;
  echo json_encode(['success' => false, 'message' => $errstr], JSON_UNESCAPED_UNICODE);
  exit();
});

set_exception_handler(function ($e) use (&$__didOutput) {
  error_log('get-article EX: ' . $e->getMessage());
  http_response_code(500);
  $__didOutput = true;
  echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
  exit();
});

// Подсчёт количества кириллицы
function count_cyr($s)
{
  if (!is_string($s) || $s === '')
    return 0;
  preg_match_all('/[А-Яа-яЁё]/u', $s, $m);
  return isset($m[0]) ? count($m[0]) : 0;
}

// Данные уже в правильной кодировке UTF-8, никаких преобразований не нужно!
function repair_string($s)
{
  // Просто возвращаем строку как есть - она уже в UTF-8
  return $s;
}

session_start();

// Проверка авторизации
if (!isset($_SESSION['admin_user'])) {
  http_response_code(401);
  $__didOutput = true;
  echo json_encode(['success' => false, 'message' => 'Неавторизован'], JSON_UNESCAPED_UNICODE);
  exit();
}

// Проверка ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  $__didOutput = true;
  echo json_encode(['success' => false, 'message' => 'Неверный ID статьи'], JSON_UNESCAPED_UNICODE);
  exit();
}

$articleId = (int) $_GET['id'];

// Подключение через общий админ‑хелпер
require_once __DIR__ . '/../includes/config.php';

$pdo = getAdminDB();
if (!$pdo) {
  http_response_code(500);
  $__didOutput = true;
  echo json_encode(['success' => false, 'message' => 'Сбой подключения к БД (admin helper)'], JSON_UNESCAPED_UNICODE);
  exit();
}

try {
  $stmt = $pdo->prepare('SELECT * FROM articles WHERE id = ?');
  $stmt->execute([$articleId]);
  $article = $stmt->fetch();

  if (!$article) {
    $__didOutput = true;
    echo json_encode(['success' => false, 'message' => 'Статья не найдена'], JSON_UNESCAPED_UNICODE);
    return;
  }

  // Чиним текстовые поля
  foreach (['title', 'excerpt', 'author', 'content', 'meta_title', 'meta_description'] as $field) {
    if (isset($article[$field]) && is_string($article[$field])) {
      $article[$field] = repair_string($article[$field]);
    }
  }

  $response = json_encode(['success' => true, 'article' => $article], JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
  if ($response === false) {
    $__didOutput = true;
    echo json_encode(['success' => false, 'message' => 'JSON encode failed: ' . json_last_error_msg()], JSON_UNESCAPED_UNICODE);
    return;
  }

  $__didOutput = true;
  echo $response;
  return;
} catch (Throwable $e) {
  error_log('get-article error: ' . $e->getMessage());
  http_response_code(500);
  $__didOutput = true;
  echo json_encode(['success' => false, 'message' => 'Ошибка БД: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
  return;
}
?>