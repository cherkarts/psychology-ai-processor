<?php
// Исправленный отладочный скрипт для проверки API ответа

// Получаем ID статьи из URL
$articleId = $_GET['id'] ?? 38;

echo "<h2>Проверка API ответа для статьи ID: $articleId (ИСПРАВЛЕННАЯ ВЕРСИЯ)</h2>";

// Подключаем конфиг
$config = require_once 'config.php';
$dbConfig = $config['database'];
$dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";

try {
  $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);

  // Получаем статью напрямую из БД
  $stmt = $pdo->prepare('SELECT * FROM articles WHERE id = ?');
  $stmt->execute([$articleId]);
  $article = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$article) {
    echo "<p style='color: red;'>Статья не найдена</p>";
    exit;
  }

  echo "<h3>Данные из БД (RAW):</h3>";
  echo "<pre>" . print_r($article, true) . "</pre>";

  // Декодируем Unicode escape sequences
  echo "<h3>Данные после декодирования:</h3>";
  $decodedArticle = $article;

  foreach (['title', 'excerpt', 'author', 'content', 'meta_title', 'meta_description', 'tags'] as $field) {
    if (isset($decodedArticle[$field]) && is_string($decodedArticle[$field])) {
      // Пробуем декодировать Unicode escape sequences
      $decoded = json_decode('"' . $decodedArticle[$field] . '"', true);
      if ($decoded !== null) {
        $decodedArticle[$field] = $decoded;
      }
    }
  }

  echo "<pre>" . print_r($decodedArticle, true) . "</pre>";

  // Имитируем API ответ
  $apiResponse = [
    'success' => true,
    'article' => $decodedArticle
  ];

  echo "<h3>Имитированный API ответ:</h3>";
  echo "<pre>" . json_encode($apiResponse, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "</pre>";

  // Проверяем поля
  echo "<h3>Поля статьи:</h3>";
  echo "<ul>";
  echo "<li>ID: " . ($decodedArticle['id'] ?? 'NULL') . "</li>";
  echo "<li>Title: " . ($decodedArticle['title'] ?? 'NULL') . "</li>";
  echo "<li>Author: " . ($decodedArticle['author'] ?? 'NULL') . "</li>";
  echo "<li>Content length: " . strlen($decodedArticle['content'] ?? '') . "</li>";
  echo "<li>Excerpt: " . ($decodedArticle['excerpt'] ?? 'NULL') . "</li>";
  echo "<li>Tags: " . ($decodedArticle['tags'] ?? 'NULL') . "</li>";
  echo "</ul>";

} catch (PDOException $e) {
  echo "Ошибка подключения к БД: " . $e->getMessage();
}
?>