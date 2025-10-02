<?php
// Простой тест категории
$config = require_once 'config.php';
$dbConfig = $config['database'];
$dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";

try {
  $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);

  // Статья ID 38
  $stmt = $pdo->prepare('SELECT id, title, category_id FROM articles WHERE id = 38');
  $stmt->execute();
  $article = $stmt->fetch(PDO::FETCH_ASSOC);

  echo "<h2>Статья ID 38:</h2>";
  echo "<p>Title: " . htmlspecialchars($article['title'] ?? 'NULL') . "</p>";
  echo "<p>Category ID: " . ($article['category_id'] ?? 'NULL') . "</p>";

  if ($article['category_id']) {
    $stmt = $pdo->prepare('SELECT id, name FROM categories WHERE id = ?');
    $stmt->execute([$article['category_id']]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($category) {
      echo "<p>Category Name: " . htmlspecialchars($category['name']) . "</p>";
    } else {
      echo "<p style='color: red;'>Категория не найдена!</p>";
    }
  }

  // Все категории
  $stmt = $pdo->query('SELECT id, name FROM categories ORDER BY name');
  $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo "<h3>Все категории:</h3>";
  foreach ($categories as $cat) {
    echo "<p>ID: {$cat['id']} - Name: " . htmlspecialchars($cat['name']) . "</p>";
  }

} catch (PDOException $e) {
  echo "Ошибка: " . $e->getMessage();
}
?>