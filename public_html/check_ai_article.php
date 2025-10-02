<?php
/**
 * Скрипт для проверки AI-сгенерированной статьи
 */

// Подключаем конфигурацию
$config = require_once 'config.php';

try {
  // Извлекаем настройки базы данных
  $db_config = $config['database'];
  $dsn = "mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['dbname']};charset={$db_config['charset']}";
  $username = $db_config['username'];
  $password = $db_config['password'];
  $options = $db_config['options'];

  $pdo = new PDO($dsn, $username, $password, $options);

  // Ищем последнюю статью от AI Assistant
  $sql = "SELECT * FROM articles WHERE author = 'AI Assistant' ORDER BY created_at DESC LIMIT 1";
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $article = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($article) {
    echo "<h1>🤖 AI-сгенерированная статья найдена!</h1>";
    echo "<div style='background: #f0f8ff; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h2>📄 Информация о статье:</h2>";
    echo "<p><strong>ID:</strong> {$article['id']}</p>";
    echo "<p><strong>Название:</strong> {$article['title']}</p>";
    echo "<p><strong>Автор:</strong> {$article['author']}</p>";
    echo "<p><strong>Категория ID:</strong> {$article['category_id']}</p>";
    echo "<p><strong>Статус:</strong> " . ($article['is_active'] ? '✅ Активна' : '❌ Неактивна') . "</p>";
    echo "<p><strong>Создана:</strong> {$article['created_at']}</p>";
    echo "<p><strong>Обновлена:</strong> {$article['updated_at']}</p>";

    // Декодируем теги
    $tags = json_decode($article['tags'], true);
    if ($tags && is_array($tags)) {
      echo "<p><strong>Теги:</strong> " . implode(', ', $tags) . "</p>";
    }

    echo "</div>";

    echo "<div style='background: #fff8dc; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h2>📝 Превью контента:</h2>";
    echo "<div style='max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;'>";
    echo htmlspecialchars(substr($article['content'], 0, 500)) . "...";
    echo "</div>";
    echo "</div>";

    echo "<div style='background: #f0fff0; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h2>🔗 Ссылки:</h2>";
    echo "<p><a href='/article.php?id={$article['id']}' target='_blank' style='color: #0066cc; text-decoration: none; font-weight: bold;'>👁️ Просмотр статьи</a></p>";
    echo "<p><a href='/articles/' target='_blank' style='color: #0066cc; text-decoration: none;'>📋 Список всех статей</a></p>";
    echo "<p><a href='/admin/articles.php' target='_blank' style='color: #0066cc; text-decoration: none;'>⚙️ Админ панель</a></p>";
    echo "</div>";

  } else {
    echo "<h1>❌ AI-статьи не найдены</h1>";
    echo "<p>Статьи от автора 'AI Assistant' не найдены в базе данных.</p>";
    echo "<p><a href='/upload_article.php'>Загрузить статью</a></p>";
  }

  // Показываем общую статистику
  $sql = "SELECT COUNT(*) as total FROM articles";
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

  $sql = "SELECT COUNT(*) as ai_count FROM articles WHERE author = 'AI Assistant'";
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $ai_count = $stmt->fetch(PDO::FETCH_ASSOC)['ai_count'];

  echo "<div style='background: #f5f5f5; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
  echo "<h2>📊 Статистика:</h2>";
  echo "<p><strong>Всего статей:</strong> {$total}</p>";
  echo "<p><strong>AI-статей:</strong> {$ai_count}</p>";
  echo "<p><strong>Обычных статей:</strong> " . ($total - $ai_count) . "</p>";
  echo "</div>";

} catch (PDOException $e) {
  echo "<h1>❌ Ошибка базы данных</h1>";
  echo "<p><strong>Ошибка:</strong> " . $e->getMessage() . "</p>";
}
?>