<?php
/**
 * Скрипт для исправления категорий AI-статей
 */

echo "<h1>🏷️ Исправление категорий AI-статей</h1>";

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

  // Проверяем таблицу категорий
  echo "<h2>📊 Проверка категорий:</h2>";

  try {
    $sql = "SELECT * FROM article_categories ORDER BY id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>📋 Доступные категории:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f8f9fa;'>";
    echo "<th style='padding: 8px;'>ID</th>";
    echo "<th style='padding: 8px;'>Название</th>";
    echo "<th style='padding: 8px;'>Описание</th>";
    echo "</tr>";

    foreach ($categories as $category) {
      echo "<tr>";
      echo "<td style='padding: 8px;'>{$category['id']}</td>";
      echo "<td style='padding: 8px;'>{$category['name']}</td>";
      echo "<td style='padding: 8px;'>{$category['description']}</td>";
      echo "</tr>";
    }
    echo "</table>";
    echo "</div>";

  } catch (PDOException $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>❌ Ошибка при получении категорий</h3>";
    echo "<p><strong>Ошибка:</strong> " . $e->getMessage() . "</p>";
    echo "</div>";
    exit;
  }

  // Ищем AI-статьи без категории или с неправильной категорией
  $sql = "SELECT id, title, category_id, author FROM articles WHERE author = 'AI Assistant' ORDER BY created_at DESC";
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo "<h2>📄 AI-статьи для исправления:</h2>";
  echo "<p>Найдено статей: " . count($articles) . "</p>";

  if (empty($articles)) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>❌ AI-статьи не найдены</h3>";
    echo "</div>";
    exit;
  }

  // Определяем правильную категорию (Психология)
  $psychology_category_id = 1; // По умолчанию используем ID 1

  // Проверяем, есть ли категория "Психология"
  foreach ($categories as $category) {
    if (stripos($category['name'], 'психолог') !== false) {
      $psychology_category_id = $category['id'];
      break;
    }
  }

  echo "<p><strong>Будем использовать категорию ID:</strong> {$psychology_category_id}</p>";

  $updated_count = 0;

  foreach ($articles as $article) {
    echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #007bff;'>";
    echo "<h3>📄 Статья ID: {$article['id']}</h3>";
    echo "<p><strong>Название:</strong> " . htmlspecialchars($article['title']) . "</p>";
    echo "<p><strong>Текущая категория ID:</strong> {$article['category_id']}</p>";

    // Проверяем, нужно ли обновлять категорию
    if ($article['category_id'] != $psychology_category_id) {
      // Обновляем категорию
      $update_sql = "UPDATE articles SET category_id = :category_id, updated_at = NOW() WHERE id = :id";
      $update_stmt = $pdo->prepare($update_sql);

      $result = $update_stmt->execute([
        ':category_id' => $psychology_category_id,
        ':id' => $article['id']
      ]);

      if ($result) {
        echo "<p style='color: green;'><strong>✅ Категория обновлена на ID: {$psychology_category_id}</strong></p>";
        $updated_count++;
      } else {
        echo "<p style='color: red;'><strong>❌ Ошибка при обновлении категории</strong></p>";
      }
    } else {
      echo "<p style='color: green;'><strong>✅ Категория уже правильная</strong></p>";
    }

    echo "</div>";
  }

  echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
  echo "<h3>🎉 Исправление категорий завершено!</h3>";
  echo "<p><strong>Обновлено статей:</strong> {$updated_count}</p>";
  echo "<p>Проверьте результат:</p>";
  echo "<p><a href='/admin/articles.php' target='_blank' style='color: #0066cc; text-decoration: none; font-weight: bold;'>⚙️ Админ панель</a></p>";
  echo "</div>";

} catch (PDOException $e) {
  echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
  echo "<h3>❌ Ошибка базы данных</h3>";
  echo "<p><strong>Ошибка:</strong> " . $e->getMessage() . "</p>";
  echo "</div>";
} catch (Exception $e) {
  echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
  echo "<h3>❌ Общая ошибка</h3>";
  echo "<p><strong>Ошибка:</strong> " . $e->getMessage() . "</p>";
  echo "</div>";
}
?>