<?php
/**
 * Быстрое исправление тегов с дублями
 * Запустите этот скрипт один раз чтобы исправить все статьи
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Исправление дублирующихся тегов</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
    .fixed { background: #d4edda; padding: 10px; margin: 10px 0; border-left: 4px solid #28a745; }
    .ok { background: #d1ecf1; padding: 10px; margin: 10px 0; border-left: 4px solid #17a2b8; }
    .error { background: #f8d7da; padding: 10px; margin: 10px 0; border-left: 4px solid #dc3545; }
    pre { background: #fff; padding: 10px; border-radius: 4px; overflow-x: auto; }
</style>";

require_once __DIR__ . '/includes/Database.php';

try {
  $db = Database::getInstance();
  $pdo = $db->getConnection();

  echo "<p>Начинаю проверку...</p>";

  // Получаем все статьи с тегами
  $stmt = $pdo->query("SELECT id, title, slug, tags FROM articles WHERE tags IS NOT NULL AND tags != '' AND tags != 'null'");
  $articles = $stmt->fetchAll();

  echo "<p><strong>Найдено статей с тегами: " . count($articles) . "</strong></p>";

  $fixed = 0;
  $checked = 0;

  foreach ($articles as $article) {
    $checked++;
    $id = $article['id'];
    $title = $article['title'];
    $slug = $article['slug'];
    $tagsJson = $article['tags'];

    echo "<div style='background: white; padding: 15px; margin: 15px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>";
    echo "<h3>[$checked] " . htmlspecialchars($title) . "</h3>";
    echo "<p><small>ID: $id | Slug: $slug</small></p>";
    echo "<p><strong>Теги (JSON):</strong> <code>" . htmlspecialchars($tagsJson) . "</code></p>";

    // Декодируем JSON
    $tagsArray = json_decode($tagsJson, true);

    if (!is_array($tagsArray)) {
      echo "<div class='error'>⚠️ Теги не являются массивом, пропускаем</div>";
      echo "</div>";
      continue;
    }

    echo "<p><strong>Теги (массив):</strong> " . implode(', ', $tagsArray) . "</p>";
    echo "<p><strong>Количество:</strong> " . count($tagsArray) . "</p>";

    // Удаляем дубликаты
    $originalCount = count($tagsArray);
    $uniqueTags = array_values(array_unique(array_filter(array_map('trim', $tagsArray), fn($v) => $v !== '')));
    $uniqueCount = count($uniqueTags);

    if ($originalCount !== $uniqueCount) {
      echo "<div class='fixed'>";
      echo "<strong>🔧 Найдены дубликаты!</strong><br>";
      echo "Было тегов: <strong>$originalCount</strong><br>";
      echo "Стало тегов: <strong>$uniqueCount</strong><br>";
      echo "Оригинал: <code>" . implode(', ', $tagsArray) . "</code><br>";
      echo "Очищено: <code>" . implode(', ', $uniqueTags) . "</code><br>";

      // Сохраняем исправленные теги
      $newTagsJson = json_encode($uniqueTags, JSON_UNESCAPED_UNICODE);
      $updateStmt = $pdo->prepare("UPDATE articles SET tags = ? WHERE id = ?");
      $updateStmt->execute([$newTagsJson, $id]);

      echo "<strong>✅ Теги обновлены в БД!</strong><br>";
      echo "Новый JSON: <code>" . htmlspecialchars($newTagsJson) . "</code>";
      echo "</div>";
      $fixed++;
    } else {
      echo "<div class='ok'>✓ Дубликатов нет, всё в порядке</div>";
    }

    echo "</div>";

    // Ограничение для безопасности
    if ($checked >= 50) {
      echo "<p><strong>⚠️ Достигнут лимит 50 статей. Остановка для безопасности.</strong></p>";
      break;
    }
  }

  echo "<hr style='margin: 30px 0;'>";
  echo "<div style='background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>";
  echo "<h2>📊 ИТОГО</h2>";
  echo "<p><strong>Проверено статей:</strong> $checked</p>";
  echo "<p><strong>Исправлено статей:</strong> $fixed</p>";

  if ($fixed > 0) {
    echo "<div class='fixed' style='margin-top: 20px;'>";
    echo "<strong>✅ Исправление завершено успешно!</strong><br>";
    echo "Теперь обновите страницы статей - теги должны отображаться правильно.";
    echo "</div>";
  } else {
    echo "<div class='ok' style='margin-top: 20px;'>";
    echo "<strong>✓ Все статьи в порядке!</strong><br>";
    echo "Дубликаты не найдены.";
    echo "</div>";
  }

  echo "</div>";

  echo "<p style='margin-top: 20px; color: #999;'><small>Этот файл можно удалить после использования: fix-tags-now.php</small></p>";

} catch (Exception $e) {
  echo "<div class='error'>";
  echo "<strong>❌ Ошибка:</strong> " . htmlspecialchars($e->getMessage());
  echo "</div>";
}
?>
