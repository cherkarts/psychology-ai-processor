<?php
/**
 * Ультра-простой API для получения статьи
 */

header('Content-Type: application/json; charset=UTF-8');

session_start();

// Проверка авторизации
if (!isset($_SESSION['admin_user'])) {
  http_response_code(401);
  echo json_encode(['success' => false, 'message' => 'Неавторизован']);
  exit();
}

// Проверка ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  echo json_encode(['success' => false, 'message' => 'Неверный ID статьи']);
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
    echo json_encode(['success' => false, 'message' => 'Статья не найдена']);
    exit();
  }

  // Очищаем все поля от проблемных символов
  $cleanArticle = [];
  foreach ($article as $key => $value) {
    if (is_string($value)) {
      // Убираем все непечатаемые символы и исправляем кодировку
      $cleanValue = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);
      $cleanValue = mb_convert_encoding($cleanValue, 'UTF-8', 'UTF-8');
      $cleanArticle[$key] = $cleanValue;
    } else {
      $cleanArticle[$key] = $value;
    }
  }

  // Особо обрабатываем теги
  if (isset($cleanArticle['tags']) && !empty($cleanArticle['tags'])) {
    // Если теги не являются валидным JSON, устанавливаем пустой массив
    $tags = json_decode($cleanArticle['tags'], true);
    if (json_last_error() !== JSON_ERROR_NONE) {
      $cleanArticle['tags'] = '[]';
    }
  } else {
    $cleanArticle['tags'] = '[]';
  }

  // Убеждаемся, что все обязательные поля заполнены
  $cleanArticle['title'] = $cleanArticle['title'] ?: 'Статья без названия';
  $cleanArticle['author'] = $cleanArticle['author'] ?: 'Денис Черкас';
  $cleanArticle['content'] = $cleanArticle['content'] ?: '';
  $cleanArticle['excerpt'] = $cleanArticle['excerpt'] ?: '';
  $cleanArticle['slug'] = $cleanArticle['slug'] ?: 'article-' . $articleId;

  // Возвращаем очищенную статью
  echo json_encode(['success' => true, 'article' => $cleanArticle], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
  echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
}
?>