<?php
/**
 * Применение миграции для системы комментариев
 */

require_once __DIR__ . '/../includes/Database.php';

try {
  $db = Database::getInstance();

  echo "Начинаем применение миграции для системы комментариев...\n";

  // Читаем SQL файлы
  $sqlFiles = [
    __DIR__ . '/final-fix.sql'
  ];

  $allQueries = [];

  foreach ($sqlFiles as $sqlFile) {
    if (file_exists($sqlFile)) {
      $sql = file_get_contents($sqlFile);
      $queries = array_filter(array_map('trim', explode(';', $sql)));
      $allQueries = array_merge($allQueries, $queries);
    }
  }

  if (empty($allQueries)) {
    throw new Exception("Файлы миграции не найдены");
  }

  // Выполняем все запросы
  foreach ($allQueries as $query) {
    if (empty($query))
      continue;

    echo "Выполняем запрос: " . substr($query, 0, 50) . "...\n";

    try {
      $db->exec($query);
      echo "✓ Успешно\n";
    } catch (Exception $e) {
      // Игнорируем ошибки "таблица уже существует" и "столбец уже существует"
      if (
        strpos($e->getMessage(), 'already exists') !== false ||
        strpos($e->getMessage(), 'Duplicate column') !== false
      ) {
        echo "⚠ Пропущено (уже существует)\n";
      } else {
        throw $e;
      }
    }
  }

  echo "\n✅ Миграция успешно применена!\n";
  echo "Созданы таблицы:\n";
  echo "- comments (комментарии)\n";
  echo "- comment_likes (лайки комментариев)\n";
  echo "- comment_reports (жалобы на комментарии)\n";
  echo "- telegram_users (пользователи Telegram)\n";
  echo "\nОбновлена таблица reviews (добавлены поля Telegram)\n";

} catch (Exception $e) {
  echo "❌ Ошибка при применении миграции: " . $e->getMessage() . "\n";
  exit(1);
}
?>