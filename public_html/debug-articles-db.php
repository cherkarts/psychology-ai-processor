<?php
// Отладочный скрипт для проверки статей в БД
$config = require_once 'config.php';

$dbConfig = $config['database'];
$dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";

try {
  $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);
  echo "<h2>Проверка статей в базе данных</h2>";

  // Получаем все статьи
  $stmt = $pdo->query("SELECT id, title, author, content, excerpt, tags, created_at FROM articles ORDER BY id DESC LIMIT 5");
  $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo "<h3>Последние 5 статей:</h3>";
  echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
  echo "<tr><th>ID</th><th>Title</th><th>Author</th><th>Content (первые 100 символов)</th><th>Excerpt</th><th>Tags</th><th>Created</th></tr>";

  foreach ($articles as $article) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($article['id']) . "</td>";
    echo "<td>" . htmlspecialchars($article['title'] ?? 'NULL') . "</td>";
    echo "<td>" . htmlspecialchars($article['author'] ?? 'NULL') . "</td>";
    echo "<td>" . htmlspecialchars(substr($article['content'] ?? 'NULL', 0, 100)) . "...</td>";
    echo "<td>" . htmlspecialchars($article['excerpt'] ?? 'NULL') . "</td>";
    echo "<td>" . htmlspecialchars($article['tags'] ?? 'NULL') . "</td>";
    echo "<td>" . htmlspecialchars($article['created_at']) . "</td>";
    echo "</tr>";
  }
  echo "</table>";

  // Проверяем конкретную статью (последнюю)
  if (!empty($articles)) {
    $lastArticle = $articles[0];
    echo "<h3>Детальная проверка последней статьи (ID: {$lastArticle['id']}):</h3>";
    echo "<pre>";
    echo "Title: " . var_export($lastArticle['title'], true) . "\n";
    echo "Author: " . var_export($lastArticle['author'], true) . "\n";
    echo "Content length: " . strlen($lastArticle['content'] ?? '') . "\n";
    echo "Excerpt: " . var_export($lastArticle['excerpt'], true) . "\n";
    echo "Tags: " . var_export($lastArticle['tags'], true) . "\n";
    echo "</pre>";
  }

} catch (PDOException $e) {
  echo "Ошибка подключения к БД: " . $e->getMessage();
}
?>