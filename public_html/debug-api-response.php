<?php
// Отладочный скрипт для проверки API ответа

// Проверяем, есть ли уже активная сессия
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Имитируем админскую сессию
$_SESSION['admin_user'] = 'admin';

// Получаем ID статьи из URL
$articleId = $_GET['id'] ?? 38;

echo "<h2>Проверка API ответа для статьи ID: $articleId</h2>";

// Подключаем API напрямую
$_GET['id'] = $articleId;

// Захватываем вывод API
ob_start();
require_once 'admin/api/get-article.php';
$apiResponse = ob_get_clean();

echo "<h3>JSON ответ от API:</h3>";
echo "<pre>" . htmlspecialchars($apiResponse) . "</pre>";

// Парсим JSON
$data = json_decode($apiResponse, true);
if ($data) {
  echo "<h3>Распарсенные данные:</h3>";
  echo "<pre>" . print_r($data, true) . "</pre>";

  if (isset($data['article'])) {
    $article = $data['article'];
    echo "<h3>Поля статьи:</h3>";
    echo "<ul>";
    echo "<li>ID: " . ($article['id'] ?? 'NULL') . "</li>";
    echo "<li>Title: " . ($article['title'] ?? 'NULL') . "</li>";
    echo "<li>Author: " . ($article['author'] ?? 'NULL') . "</li>";
    echo "<li>Content length: " . strlen($article['content'] ?? '') . "</li>";
    echo "<li>Excerpt: " . ($article['excerpt'] ?? 'NULL') . "</li>";
    echo "<li>Tags: " . ($article['tags'] ?? 'NULL') . "</li>";
    echo "</ul>";
  }
} else {
  echo "<p style='color: red;'>Ошибка парсинга JSON: " . json_last_error_msg() . "</p>";
}
?>