<?php
$config = require_once 'config.php';
$db = $config['database'];

try {
  $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['dbname']};charset={$db['charset']}";
  $pdo = new PDO($dsn, $db['username'], $db['password'], $db['options']);

  echo "<h2>Проверка таблиц категорий</h2>";

  // Проверяем таблицу categories
  echo "<h3>Таблица 'categories':</h3>";
  try {
    $stmt = $pdo->query("SELECT * FROM categories");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>✅ Таблица 'categories' существует</p>";
    echo "<p>Количество записей: " . count($categories) . "</p>";
    if ($categories) {
      echo "<table border='1'><tr><th>ID</th><th>Name</th><th>Slug</th><th>Created</th></tr>";
      foreach ($categories as $cat) {
        echo "<tr><td>{$cat['id']}</td><td>{$cat['name']}</td><td>{$cat['slug']}</td><td>{$cat['created_at']}</td></tr>";
      }
      echo "</table>";
    }
  } catch (Exception $e) {
    echo "<p>❌ Таблица 'categories' не существует: " . $e->getMessage() . "</p>";
  }

  // Проверяем таблицу article_categories
  echo "<h3>Таблица 'article_categories':</h3>";
  try {
    $stmt = $pdo->query("SELECT * FROM article_categories");
    $article_categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>✅ Таблица 'article_categories' существует</p>";
    echo "<p>Количество записей: " . count($article_categories) . "</p>";
    if ($article_categories) {
      echo "<table border='1'><tr><th>ID</th><th>Name</th><th>Slug</th><th>Created</th></tr>";
      foreach ($article_categories as $cat) {
        echo "<tr><td>{$cat['id']}</td><td>{$cat['name']}</td><td>{$cat['slug']}</td><td>{$cat['created_at']}</td></tr>";
      }
      echo "</table>";
    }
  } catch (Exception $e) {
    echo "<p>❌ Таблица 'article_categories' не существует: " . $e->getMessage() . "</p>";
  }

} catch (Exception $e) {
  echo "Ошибка: " . $e->getMessage();
}
?>