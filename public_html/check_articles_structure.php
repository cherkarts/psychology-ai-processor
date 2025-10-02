<?php
/**
 * Проверка структуры таблицы articles
 */

echo "<h1>🔍 Проверка структуры таблицы articles</h1>";

try {
  // Подключаем конфигурацию
  $config = require_once 'config.php';

  // Извлекаем настройки базы данных
  $db_config = $config['database'];
  $dsn = "mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['dbname']};charset={$db_config['charset']}";
  $username = $db_config['username'];
  $password = $db_config['password'];
  $options = $db_config['options'];

  // Подключаемся к базе данных
  $pdo = new PDO($dsn, $username, $password, $options);

  echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
  echo "<h3>✅ Подключение к базе данных успешно!</h3>";
  echo "</div>";

  // Проверяем структуру таблицы articles
  echo "<h2>📊 Структура таблицы articles:</h2>";

  $sql = "DESCRIBE articles";
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
  echo "<tr style='background: #f8f9fa;'>";
  echo "<th style='padding: 8px;'>Поле</th>";
  echo "<th style='padding: 8px;'>Тип</th>";
  echo "<th style='padding: 8px;'>Null</th>";
  echo "<th style='padding: 8px;'>Ключ</th>";
  echo "<th style='padding: 8px;'>По умолчанию</th>";
  echo "</tr>";

  $existing_columns = [];
  foreach ($columns as $column) {
    $existing_columns[] = $column['Field'];
    echo "<tr>";
    echo "<td style='padding: 8px;'>{$column['Field']}</td>";
    echo "<td style='padding: 8px;'>{$column['Type']}</td>";
    echo "<td style='padding: 8px;'>{$column['Null']}</td>";
    echo "<td style='padding: 8px;'>{$column['Key']}</td>";
    echo "<td style='padding: 8px;'>{$column['Default']}</td>";
    echo "</tr>";
  }
  echo "</table>";

  // Проверяем, какие колонки отсутствуют
  $required_columns = [
    'id',
    'title',
    'content',
    'excerpt',
    'meta_title',
    'meta_description',
    'tags',
    'category_id',
    'is_active',
    'author',
    'created_at',
    'updated_at'
  ];

  $missing_columns = array_diff($required_columns, $existing_columns);
  $extra_columns = array_diff($existing_columns, $required_columns);

  echo "<h2>🔍 Анализ колонок:</h2>";

  if (!empty($missing_columns)) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>❌ Отсутствующие колонки:</h3>";
    echo "<ul>";
    foreach ($missing_columns as $column) {
      echo "<li><code>{$column}</code></li>";
    }
    echo "</ul>";
    echo "</div>";
  }

  if (!empty($extra_columns)) {
    echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>ℹ️ Дополнительные колонки:</h3>";
    echo "<ul>";
    foreach ($extra_columns as $column) {
      echo "<li><code>{$column}</code></li>";
    }
    echo "</ul>";
    echo "</div>";
  }

  if (empty($missing_columns)) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>✅ Все необходимые колонки присутствуют!</h3>";
    echo "</div>";
  }

  // Показываем SQL для добавления недостающих колонок
  if (!empty($missing_columns)) {
    echo "<h2>🔧 SQL для исправления:</h2>";
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>Выполните эти команды в phpMyAdmin:</h3>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto;'>";

    foreach ($missing_columns as $column) {
      switch ($column) {
        case 'id':
          echo "ALTER TABLE articles ADD COLUMN id INT AUTO_INCREMENT PRIMARY KEY FIRST;\n";
          break;
        case 'title':
          echo "ALTER TABLE articles ADD COLUMN title VARCHAR(255) NOT NULL;\n";
          break;
        case 'content':
          echo "ALTER TABLE articles ADD COLUMN content TEXT;\n";
          break;
        case 'excerpt':
          echo "ALTER TABLE articles ADD COLUMN excerpt TEXT;\n";
          break;
        case 'meta_title':
          echo "ALTER TABLE articles ADD COLUMN meta_title VARCHAR(255);\n";
          break;
        case 'meta_description':
          echo "ALTER TABLE articles ADD COLUMN meta_description TEXT;\n";
          break;
        case 'tags':
          echo "ALTER TABLE articles ADD COLUMN tags JSON;\n";
          break;
        case 'category_id':
          echo "ALTER TABLE articles ADD COLUMN category_id INT DEFAULT 1;\n";
          break;
        case 'is_active':
          echo "ALTER TABLE articles ADD COLUMN is_active TINYINT(1) DEFAULT 1;\n";
          break;
        case 'author':
          echo "ALTER TABLE articles ADD COLUMN author VARCHAR(100) DEFAULT 'Admin';\n";
          break;
        case 'created_at':
          echo "ALTER TABLE articles ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;\n";
          break;
        case 'updated_at':
          echo "ALTER TABLE articles ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;\n";
          break;
      }
    }
    echo "</pre>";
    echo "</div>";
  }

} catch (PDOException $e) {
  echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
  echo "<h3>❌ Ошибка подключения к базе данных</h3>";
  echo "<p><strong>Ошибка:</strong> " . $e->getMessage() . "</p>";
  echo "</div>";
} catch (Exception $e) {
  echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
  echo "<h3>❌ Общая ошибка</h3>";
  echo "<p><strong>Ошибка:</strong> " . $e->getMessage() . "</p>";
  echo "</div>";
}
?>