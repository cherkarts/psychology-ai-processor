<?php
$config = require_once 'config.php';
$db = $config['database'];

try {
  $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['dbname']};charset={$db['charset']}";
  $pdo = new PDO($dsn, $db['username'], $db['password'], $db['options']);

  echo "<h2>Исправление API категорий</h2>";

  // Проверяем, какая таблица существует
  $categories_exists = false;
  $article_categories_exists = false;

  try {
    $pdo->query("SELECT 1 FROM categories LIMIT 1");
    $categories_exists = true;
    echo "<p>✅ Таблица 'categories' существует</p>";
  } catch (Exception $e) {
    echo "<p>❌ Таблица 'categories' не существует</p>";
  }

  try {
    $pdo->query("SELECT 1 FROM article_categories LIMIT 1");
    $article_categories_exists = true;
    echo "<p>✅ Таблица 'article_categories' существует</p>";
  } catch (Exception $e) {
    echo "<p>❌ Таблица 'article_categories' не существует</p>";
  }

  if ($categories_exists && !$article_categories_exists) {
    echo "<h3>Решение: Переименуем таблицу 'categories' в 'article_categories'</h3>";

    // Переименовываем таблицу
    $pdo->exec("RENAME TABLE categories TO article_categories");
    echo "<p>✅ Таблица переименована: categories → article_categories</p>";

    // Проверяем результат
    $stmt = $pdo->query("SELECT * FROM article_categories");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>✅ Проверка: найдено " . count($categories) . " категорий в новой таблице</p>";

    echo "<h3>Категории в таблице article_categories:</h3>";
    echo "<table border='1'><tr><th>ID</th><th>Name</th><th>Slug</th><th>Created</th></tr>";
    foreach ($categories as $cat) {
      echo "<tr><td>{$cat['id']}</td><td>{$cat['name']}</td><td>{$cat['slug']}</td><td>{$cat['created_at']}</td></tr>";
    }
    echo "</table>";

    echo "<p><strong>🎉 Готово! Теперь админка должна отображать категории.</strong></p>";

  } elseif (!$categories_exists && !$article_categories_exists) {
    echo "<h3>❌ Ни одна таблица категорий не найдена!</h3>";
    echo "<p>Нужно создать таблицу категорий.</p>";

  } elseif ($categories_exists && $article_categories_exists) {
    echo "<h3>⚠️ Обе таблицы существуют!</h3>";
    echo "<p>Нужно решить, какую использовать.</p>";

  } else {
    echo "<h3>✅ Таблица 'article_categories' уже существует</h3>";
    echo "<p>API должен работать корректно.</p>";
  }

} catch (Exception $e) {
  echo "Ошибка: " . $e->getMessage();
}
?>