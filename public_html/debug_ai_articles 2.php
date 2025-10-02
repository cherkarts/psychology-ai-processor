<?php
/**
 * Отладка AI-статей в базе данных
 */

echo "<h1>🔍 Отладка AI-статей</h1>";

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

  // Проверяем все статьи
  echo "<h2>📊 Все статьи в базе данных:</h2>";

  $sql = "SELECT id, title, author, created_at, is_active FROM articles ORDER BY created_at DESC LIMIT 10";
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $all_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

  if (empty($all_articles)) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>❌ В базе данных нет статей!</h3>";
    echo "</div>";
  } else {
    echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>📋 Найдено статей: " . count($all_articles) . "</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f8f9fa;'>";
    echo "<th style='padding: 8px;'>ID</th>";
    echo "<th style='padding: 8px;'>Название</th>";
    echo "<th style='padding: 8px;'>Автор</th>";
    echo "<th style='padding: 8px;'>Дата</th>";
    echo "<th style='padding: 8px;'>Активна</th>";
    echo "</tr>";

    foreach ($all_articles as $article) {
      echo "<tr>";
      echo "<td style='padding: 8px;'>{$article['id']}</td>";
      echo "<td style='padding: 8px;'>" . htmlspecialchars($article['title']) . "</td>";
      echo "<td style='padding: 8px;'>{$article['author']}</td>";
      echo "<td style='padding: 8px;'>{$article['created_at']}</td>";
      echo "<td style='padding: 8px;'>" . ($article['is_active'] ? 'Да' : 'Нет') . "</td>";
      echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
  }

  // Проверяем AI-статьи
  echo "<h2>🤖 AI-статьи:</h2>";

  $sql = "SELECT id, title, author, created_at, is_active FROM articles WHERE author = 'AI Assistant' ORDER BY created_at DESC";
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $ai_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

  if (empty($ai_articles)) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>❌ AI-статьи не найдены!</h3>";
    echo "<p>Проверьте, есть ли статьи с автором 'AI Assistant'</p>";
    echo "</div>";

    // Проверяем похожих авторов
    echo "<h3>🔍 Поиск похожих авторов:</h3>";
    $sql = "SELECT DISTINCT author FROM articles WHERE author LIKE '%AI%' OR author LIKE '%Assistant%'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $similar_authors = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($similar_authors)) {
      echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
      echo "<h4>Найденные похожие авторы:</h4>";
      echo "<ul>";
      foreach ($similar_authors as $author) {
        echo "<li>" . htmlspecialchars($author) . "</li>";
      }
      echo "</ul>";
      echo "</div>";
    }

  } else {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>✅ Найдено AI-статей: " . count($ai_articles) . "</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f8f9fa;'>";
    echo "<th style='padding: 8px;'>ID</th>";
    echo "<th style='padding: 8px;'>Название</th>";
    echo "<th style='padding: 8px;'>Автор</th>";
    echo "<th style='padding: 8px;'>Дата</th>";
    echo "<th style='padding: 8px;'>Активна</th>";
    echo "</tr>";

    foreach ($ai_articles as $article) {
      echo "<tr>";
      echo "<td style='padding: 8px;'>{$article['id']}</td>";
      echo "<td style='padding: 8px;'>" . htmlspecialchars($article['title']) . "</td>";
      echo "<td style='padding: 8px;'>{$article['author']}</td>";
      echo "<td style='padding: 8px;'>{$article['created_at']}</td>";
      echo "<td style='padding: 8px;'>" . ($article['is_active'] ? 'Да' : 'Нет') . "</td>";
      echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
  }

  // Проверяем последние статьи
  echo "<h2>📅 Последние 5 статей:</h2>";

  $sql = "SELECT id, title, author, created_at, is_active FROM articles ORDER BY created_at DESC LIMIT 5";
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $recent_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
  echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
  echo "<tr style='background: #f8f9fa;'>";
  echo "<th style='padding: 8px;'>ID</th>";
  echo "<th style='padding: 8px;'>Название</th>";
  echo "<th style='padding: 8px;'>Автор</th>";
  echo "<th style='padding: 8px;'>Дата</th>";
  echo "<th style='padding: 8px;'>Активна</th>";
  echo "</tr>";

  foreach ($recent_articles as $article) {
    echo "<tr>";
    echo "<td style='padding: 8px;'>{$article['id']}</td>";
    echo "<td style='padding: 8px;'>" . htmlspecialchars($article['title']) . "</td>";
    echo "<td style='padding: 8px;'>{$article['author']}</td>";
    echo "<td style='padding: 8px;'>{$article['created_at']}</td>";
    echo "<td style='padding: 8px;'>" . ($article['is_active'] ? 'Да' : 'Нет') . "</td>";
    echo "</tr>";
  }
  echo "</table>";
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