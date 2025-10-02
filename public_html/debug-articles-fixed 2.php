<?php
// Исправленный отладочный скрипт для проверки статей в БД
$config = require_once 'config.php';

$dbConfig = $config['database'];
$dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";

try {
  $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);
  echo "<h2>Проверка статей в базе данных (ИСПРАВЛЕННАЯ ВЕРСИЯ)</h2>";

  // Получаем все статьи
  $stmt = $pdo->query("SELECT id, title, author, content, excerpt, tags, created_at FROM articles ORDER BY id DESC LIMIT 5");
  $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo "<h3>Последние 5 статей:</h3>";
  echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
  echo "<tr><th>ID</th><th>Title</th><th>Author</th><th>Content (первые 100 символов)</th><th>Excerpt</th><th>Tags</th><th>Created</th></tr>";

  foreach ($articles as $article) {
    // Декодируем Unicode escape sequences
    $title = json_decode('"' . ($article['title'] ?? '') . '"', true) ?? $article['title'] ?? 'NULL';
    $author = json_decode('"' . ($article['author'] ?? '') . '"', true) ?? $article['author'] ?? 'NULL';
    $content = json_decode('"' . ($article['content'] ?? '') . '"', true) ?? $article['content'] ?? 'NULL';
    $excerpt = json_decode('"' . ($article['excerpt'] ?? '') . '"', true) ?? $article['excerpt'] ?? 'NULL';
    $tags = json_decode('"' . ($article['tags'] ?? '') . '"', true) ?? $article['tags'] ?? 'NULL';

    echo "<tr>";
    echo "<td>" . htmlspecialchars($article['id']) . "</td>";
    echo "<td>" . htmlspecialchars($title) . "</td>";
    echo "<td>" . htmlspecialchars($author) . "</td>";
    echo "<td>" . htmlspecialchars(substr($content, 0, 100)) . "...</td>";
    echo "<td>" . htmlspecialchars(substr($excerpt, 0, 50)) . "...</td>";
    echo "<td>" . htmlspecialchars(substr($tags, 0, 50)) . "...</td>";
    echo "<td>" . htmlspecialchars($article['created_at']) . "</td>";
    echo "</tr>";
  }
  echo "</table>";

  // Проверяем конкретную статью (последнюю)
  if (!empty($articles)) {
    $lastArticle = $articles[0];
    echo "<h3>Детальная проверка последней статьи (ID: {$lastArticle['id']}):</h3>";

    // Декодируем все поля
    $title = json_decode('"' . ($lastArticle['title'] ?? '') . '"', true) ?? $lastArticle['title'] ?? 'NULL';
    $author = json_decode('"' . ($lastArticle['author'] ?? '') . '"', true) ?? $lastArticle['author'] ?? 'NULL';
    $content = json_decode('"' . ($lastArticle['content'] ?? '') . '"', true) ?? $lastArticle['content'] ?? 'NULL';
    $excerpt = json_decode('"' . ($lastArticle['excerpt'] ?? '') . '"', true) ?? $lastArticle['excerpt'] ?? 'NULL';
    $tags = json_decode('"' . ($lastArticle['tags'] ?? '') . '"', true) ?? $lastArticle['tags'] ?? 'NULL';

    echo "<pre>";
    echo "Title: " . var_export($title, true) . "\n";
    echo "Author: " . var_export($author, true) . "\n";
    echo "Content length: " . strlen($content) . "\n";
    echo "Content preview: " . substr($content, 0, 200) . "...\n";
    echo "Excerpt: " . var_export($excerpt, true) . "\n";
    echo "Tags: " . var_export($tags, true) . "\n";
    echo "</pre>";
  }

} catch (PDOException $e) {
  echo "Ошибка подключения к БД: " . $e->getMessage();
}
?>