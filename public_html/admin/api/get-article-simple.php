<?php
/**
 * Упрощенный API для получения статьи без сложной обработки кодировки
 */

header('Content-Type: application/json; charset=UTF-8');
error_reporting(E_ALL);
ini_set('display_errors', '0');

session_start();

// Проверка авторизации
if (!isset($_SESSION['admin_user'])) {
  http_response_code(401);
  echo json_encode(['success' => false, 'message' => 'Неавторизован'], JSON_UNESCAPED_UNICODE);
  exit();
}

// Проверка ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  echo json_encode(['success' => false, 'message' => 'Неверный ID статьи'], JSON_UNESCAPED_UNICODE);
  exit();
}

$articleId = (int) $_GET['id'];

try {
  // Подключаем конфигурацию
  $config = require_once __DIR__ . '/../../config.php';

  // Извлекаем настройки базы данных
  $db_config = $config['database'];
  $dsn = "mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['dbname']};charset={$db_config['charset']}";
  $username = $db_config['username'];
  $password = $db_config['password'];
  $options = $db_config['options'];

  // Подключаемся к базе данных
  $pdo = new PDO($dsn, $username, $password, $options);

  // Получаем статью
  $stmt = $pdo->prepare('SELECT * FROM articles WHERE id = ?');
  $stmt->execute([$articleId]);
  $article = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$article) {
    echo json_encode(['success' => false, 'message' => 'Статья не найдена'], JSON_UNESCAPED_UNICODE);
    exit();
  }

  // Простая обработка тегов
  if (isset($article['tags']) && !empty($article['tags'])) {
    try {
      $tags = json_decode($article['tags'], true);
      if (is_array($tags)) {
        $article['tags'] = json_encode($tags, JSON_UNESCAPED_UNICODE);
      }
    } catch (Exception $e) {
      // Если не удается декодировать, оставляем как есть
    }
  }

  // Возвращаем статью как есть
  echo json_encode(['success' => true, 'article' => $article], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
  echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
  echo json_encode(['success' => false, 'message' => 'Общая ошибка: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>