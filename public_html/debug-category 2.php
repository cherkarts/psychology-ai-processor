<?php
// Отладочный скрипт для проверки категории

// Получаем ID статьи из URL
$articleId = $_GET['id'] ?? 38;

echo "<h2>Проверка категории для статьи ID: $articleId</h2>";

// Подключаем конфиг
$config = require_once 'config.php';
$dbConfig = $config['database'];
$dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";

try {
  $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);

  // Получаем статью
  $stmt = $pdo->prepare('SELECT id, title, category_id FROM articles WHERE id = ?');
  $stmt->execute([$articleId]);
  $article = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$article) {
    echo "<p style='color: red;'>Статья не найдена</p>";
    exit;
  }

  echo "<h3>Данные статьи:</h3>";
  echo "<pre>" . print_r($article, true) . "</pre>";

  // Получаем категорию
  if ($article['category_id']) {
    $stmt = $pdo->prepare('SELECT id, name FROM categories WHERE id = ?');
    $stmt->execute([$article['category_id']]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "<h3>Категория:</h3>";
    if ($category) {
      echo "<pre>" . print_r($category, true) . "</pre>";
    } else {
      echo "<p style='color: red;'>Категория с ID {$article['category_id']} не найдена</p>";
    }
  } else {
    echo "<p style='color: orange;'>У статьи нет категории (category_id пустой)</p>";
  }

  // Получаем все доступные категории
  $stmt = $pdo->query('SELECT id, name FROM categories ORDER BY name');
  $allCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo "<h3>Все доступные категории:</h3>";
  echo "<pre>" . print_r($allCategories, true) . "</pre>";

} catch (PDOException $e) {
  echo "Ошибка подключения к БД: " . $e->getMessage();
}
?>