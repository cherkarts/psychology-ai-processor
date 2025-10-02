<?php
// Проверка таблиц в БД
$config = require_once 'config.php';
$dbConfig = $config['database'];
$dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";

try {
  $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);

  echo "<h2>Проверка таблиц в БД</h2>";

  // Получаем все таблицы
  $stmt = $pdo->query("SHOW TABLES");
  $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

  echo "<h3>Существующие таблицы:</h3>";
  echo "<ul>";
  foreach ($tables as $table) {
    echo "<li>" . htmlspecialchars($table) . "</li>";
  }
  echo "</ul>";

  // Проверяем структуру таблицы articles
  if (in_array('articles', $tables)) {
    echo "<h3>Структура таблицы articles:</h3>";
    $stmt = $pdo->query("DESCRIBE articles");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>" . print_r($columns, true) . "</pre>";
  }

  // Проверяем, есть ли таблица categories
  if (in_array('categories', $tables)) {
    echo "<h3>Таблица categories существует!</h3>";
    $stmt = $pdo->query("SELECT * FROM categories LIMIT 5");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>" . print_r($categories, true) . "</pre>";
  } else {
    echo "<h3 style='color: red;'>Таблица categories НЕ существует!</h3>";
    echo "<p>Нужно создать таблицу categories</p>";
  }

} catch (PDOException $e) {
  echo "Ошибка: " . $e->getMessage();
}
?>