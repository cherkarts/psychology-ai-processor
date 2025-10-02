<?php
// Исправление категорий для статей
$config = require_once 'config.php';
$dbConfig = $config['database'];
$dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";

try {
  $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);

  echo "<h2>Исправление категорий для статей</h2>";

  // Получаем все статьи с неправильными category_id
  $stmt = $pdo->query("SELECT id, title, category_id FROM articles WHERE category_id NOT IN (SELECT id FROM categories) OR category_id IS NULL");
  $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo "<h3>Статьи с неправильными категориями:</h3>";
  echo "<ul>";
  foreach ($articles as $article) {
    echo "<li>ID: {$article['id']} - Category ID: " . ($article['category_id'] ?? 'NULL') . "</li>";
  }
  echo "</ul>";

  // Получаем доступные категории
  $stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
  $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo "<h3>Доступные категории:</h3>";
  echo "<ul>";
  foreach ($categories as $cat) {
    echo "<li>ID: {$cat['id']} - " . htmlspecialchars($cat['name']) . "</li>";
  }
  echo "</ul>";

  // Исправляем категории для статей
  // Ставим категорию "Психология" (ID: 1) для всех статей с неправильными категориями
  $stmt = $pdo->prepare("UPDATE articles SET category_id = 1 WHERE category_id NOT IN (SELECT id FROM categories) OR category_id IS NULL");
  $result = $stmt->execute();

  if ($result) {
    $affectedRows = $stmt->rowCount();
    echo "<p style='color: green;'>✅ Исправлено {$affectedRows} статей. Все статьи теперь имеют категорию 'Психология' (ID: 1)</p>";
  }

  // Проверяем статью ID 38
  echo "<h3>Проверка статьи ID 38 после исправления:</h3>";
  $stmt = $pdo->prepare("SELECT a.id, a.title, a.category_id, c.name as category_name FROM articles a LEFT JOIN categories c ON a.category_id = c.id WHERE a.id = 38");
  $stmt->execute();
  $article = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($article) {
    echo "<p>ID: {$article['id']}</p>";
    echo "<p>Title: " . htmlspecialchars($article['title'] ?? 'NULL') . "</p>";
    echo "<p>Category ID: " . ($article['category_id'] ?? 'NULL') . "</p>";
    echo "<p>Category Name: " . htmlspecialchars($article['category_name'] ?? 'NULL') . "</p>";

    if ($article['category_name']) {
      echo "<p style='color: green;'>✅ Категория найдена: " . htmlspecialchars($article['category_name']) . "</p>";
    } else {
      echo "<p style='color: red;'>❌ Категория всё ещё не найдена</p>";
    }
  }

  // Показываем все статьи с их категориями
  echo "<h3>Все статьи с категориями:</h3>";
  $stmt = $pdo->query("SELECT a.id, a.title, a.category_id, c.name as category_name FROM articles a LEFT JOIN categories c ON a.category_id = c.id ORDER BY a.id DESC LIMIT 10");
  $allArticles = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
  echo "<tr><th>ID</th><th>Title</th><th>Category ID</th><th>Category Name</th></tr>";
  foreach ($allArticles as $art) {
    echo "<tr>";
    echo "<td>{$art['id']}</td>";
    echo "<td>" . htmlspecialchars(substr($art['title'] ?? 'NULL', 0, 50)) . "...</td>";
    echo "<td>{$art['category_id']}</td>";
    echo "<td>" . htmlspecialchars($art['category_name'] ?? 'NULL') . "</td>";
    echo "</tr>";
  }
  echo "</table>";

} catch (PDOException $e) {
  echo "<p style='color: red;'>Ошибка: " . $e->getMessage() . "</p>";
}
?>