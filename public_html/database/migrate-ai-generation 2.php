<?php
/**
 * Миграция для системы AI генерации статей
 */

require_once __DIR__ . '/../includes/Database.php';

$db = Database::getInstance();

echo "Начинаем миграцию системы AI генерации статей...\n";

try {
  // Читаем SQL файл
  $sqlFile = __DIR__ . '/ai_article_generation.sql';
  if (!file_exists($sqlFile)) {
    throw new Exception("Файл ai_article_generation.sql не найден");
  }

  $sql = file_get_contents($sqlFile);

  // Разбиваем на отдельные запросы
  $queries = array_filter(array_map('trim', explode(';', $sql)));

  $successCount = 0;
  $errorCount = 0;

  foreach ($queries as $query) {
    if (empty($query))
      continue;

    try {
      $db->execute($query);
      $successCount++;
      echo "✓ Выполнен запрос: " . substr($query, 0, 50) . "...\n";
    } catch (Exception $e) {
      $errorCount++;
      echo "✗ Ошибка в запросе: " . $e->getMessage() . "\n";
      echo "Запрос: " . substr($query, 0, 100) . "...\n";
    }
  }

  echo "\nМиграция завершена!\n";
  echo "Успешно выполнено запросов: $successCount\n";
  echo "Ошибок: $errorCount\n";

  if ($errorCount === 0) {
    echo "\n✅ Система AI генерации статей успешно установлена!\n";
    echo "\nСледующие шаги:\n";
    echo "1. Настройте внешний сервис (см. AI-ARTICLE-GENERATION-README.md)\n";
    echo "2. Получите OpenAI API ключ\n";
    echo "3. Запустите внешний сервис\n";
    echo "4. Перейдите в админку → AI Генератор\n";
  } else {
    echo "\n⚠️  Есть ошибки в миграции. Проверьте логи выше.\n";
  }

} catch (Exception $e) {
  echo "Критическая ошибка: " . $e->getMessage() . "\n";
  exit(1);
}
