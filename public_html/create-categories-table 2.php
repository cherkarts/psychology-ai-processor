<?php
// Создание таблицы categories
$config = require_once 'config.php';
$dbConfig = $config['database'];
$dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";

try {
  $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);

  echo "<h2>Создание таблицы categories</h2>";

  // SQL для создания таблицы categories
  $sql = "
    CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        slug VARCHAR(255) UNIQUE,
        description TEXT,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

  $pdo->exec($sql);
  echo "<p style='color: green;'>✅ Таблица categories создана успешно!</p>";

  // Добавляем базовые категории
  $categories = [
    ['name' => 'Психология', 'slug' => 'psychology'],
    ['name' => 'Саморазвитие', 'slug' => 'self-development'],
    ['name' => 'Отношения', 'slug' => 'relationships'],
    ['name' => 'Стресс и тревога', 'slug' => 'stress-anxiety'],
    ['name' => 'Детская психология', 'slug' => 'child-psychology'],
    ['name' => 'Семейная терапия', 'slug' => 'family-therapy']
  ];

  $stmt = $pdo->prepare("INSERT IGNORE INTO categories (name, slug) VALUES (?, ?)");

  foreach ($categories as $category) {
    $stmt->execute([$category['name'], $category['slug']]);
  }

  echo "<p style='color: green;'>✅ Базовые категории добавлены!</p>";

  // Показываем созданные категории
  $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
  $allCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo "<h3>Созданные категории:</h3>";
  echo "<ul>";
  foreach ($allCategories as $cat) {
    echo "<li>ID: {$cat['id']} - " . htmlspecialchars($cat['name']) . " (slug: {$cat['slug']})</li>";
  }
  echo "</ul>";

  // Проверяем статью ID 38
  echo "<h3>Проверка статьи ID 38:</h3>";
  $stmt = $pdo->prepare("SELECT id, title, category_id FROM articles WHERE id = 38");
  $stmt->execute();
  $article = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($article) {
    echo "<p>Title: " . htmlspecialchars($article['title'] ?? 'NULL') . "</p>";
    echo "<p>Category ID: " . ($article['category_id'] ?? 'NULL') . "</p>";

    if ($article['category_id']) {
      $stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
      $stmt->execute([$article['category_id']]);
      $category = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($category) {
        echo "<p style='color: green;'>✅ Категория: " . htmlspecialchars($category['name']) . "</p>";
      } else {
        echo "<p style='color: red;'>❌ Категория с ID {$article['category_id']} не найдена</p>";
      }
    }
  }

} catch (PDOException $e) {
  echo "<p style='color: red;'>Ошибка: " . $e->getMessage() . "</p>";
}
?>