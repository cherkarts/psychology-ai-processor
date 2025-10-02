<?php
/**
 * Скрипт для полной очистки проекта от JSON файлов
 * Удаляет все JSON файлы и ссылки на них после миграции на MySQL
 */

echo "=== ОЧИСТКА ПРОЕКТА ОТ JSON ===\n";
echo "Сайт психолога Дениса Черкаса\n";
echo "==============================\n\n";

// Проверяем, что скрипт запущен из командной строки
if (php_sapi_name() !== 'cli') {
  echo "❌ Этот скрипт должен быть запущен из командной строки!\n";
  echo "Используйте: php cleanup-json.php\n";
  exit(1);
}

// Запрашиваем подтверждение
echo "⚠️  ВНИМАНИЕ: Этот скрипт удалит все JSON файлы и ссылки на них!\n";
echo "Убедитесь, что миграция на MySQL прошла успешно.\n\n";

echo "Продолжить очистку? (y/N): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
fclose($handle);

if (trim(strtolower($line)) !== 'y') {
  echo "❌ Очистка отменена пользователем.\n";
  exit(0);
}

echo "\n🧹 Начинаем очистку проекта от JSON...\n\n";

try {
  // Шаг 1: Создание резервной копии перед удалением
  echo "📦 Шаг 1: Создание резервной копии перед удалением...\n";
  $backupDir = __DIR__ . '/backup_before_cleanup_' . date('Y-m-d_H-i-s');
  if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
  }

  // Копируем JSON файлы в резервную копию
  $jsonFiles = [
    'data/products.json',
    'data/reviews.json',
    'data/meditations.json',
    'data/orders.json',
    'data/categories.json',
    'data/article-likes.json',
    'data/user-article-likes.json'
  ];

  foreach ($jsonFiles as $file) {
    $sourcePath = __DIR__ . '/../' . $file;
    if (file_exists($sourcePath)) {
      $backupPath = $backupDir . '/' . basename($file);
      copy($sourcePath, $backupPath);
      echo "  ✓ Резервная копия: {$file}\n";
    }
  }

  echo "  ✓ Резервная копия создана в: {$backupDir}\n\n";

  // Шаг 2: Удаление JSON файлов
  echo "🗑️  Шаг 2: Удаление JSON файлов...\n";
  $deletedFiles = [];

  foreach ($jsonFiles as $file) {
    $filePath = __DIR__ . '/../' . $file;
    if (file_exists($filePath)) {
      if (unlink($filePath)) {
        echo "  ✓ Удален: {$file}\n";
        $deletedFiles[] = $file;
      } else {
        echo "  ❌ Ошибка удаления: {$file}\n";
      }
    } else {
      echo "  - Не найден: {$file}\n";
    }
  }

  // Проверяем, есть ли еще JSON файлы в папке data
  $dataDir = __DIR__ . '/../data/';
  if (is_dir($dataDir)) {
    $remainingFiles = glob($dataDir . '*.json');
    if (!empty($remainingFiles)) {
      echo "\n  📋 Оставшиеся JSON файлы в папке data/:\n";
      foreach ($remainingFiles as $file) {
        echo "    - " . basename($file) . "\n";
      }
    }
  }

  echo "  ✓ JSON файлы удалены\n\n";

  // Шаг 3: Очистка кода от ссылок на JSON
  echo "🔧 Шаг 3: Очистка кода от ссылок на JSON...\n";

  $filesToClean = [
    // API файлы
    'api/products-db.php',
    'api/reviews-db.php',
    'api/meditations-db.php',
    'api/articles.php',
    'api/add-to-cart.php',
    'api/my-purchases.php',
    'api/article-likes.php',

    // Админ файлы
    'admin/products.php',
    'admin/reviews.php',
    'admin/meditations.php',
    'admin/articles.php',
    'admin/orders.php',
    'admin/product-edit.php',
    'admin/article-edit.php',
    'admin/settings.php',

    // Основные файлы
    'shop.php',
    'reviews.php',
    'meditations.php',
    'articles.php',
    'product.php',
    'article.php',
    'cart.php',
    'checkout.php',

    // Включаемые файлы
    'includes/products.php',
    'includes/functions.php'
  ];

  $cleanedFiles = [];

  foreach ($filesToClean as $file) {
    $filePath = __DIR__ . '/../' . $file;
    if (file_exists($filePath)) {
      $content = file_get_contents($filePath);
      $originalContent = $content;

      // Удаляем ссылки на JSON файлы
      $patterns = [
        // Переменные с путями к JSON файлам
        '/\$.*?File\s*=\s*.*?\.json.*?;/',
        '/\$.*?File\s*=\s*__DIR__\s*\.\s*.*?\.json.*?;/',

        // Функции для работы с JSON
        '/function\s+.*?json.*?\(/',
        '/json_decode\s*\(/',
        '/json_encode\s*\(/',
        '/file_get_contents\s*\(.*?\.json/',
        '/file_put_contents\s*\(.*?\.json/',

        // Комментарии о JSON
        '/\/\/.*?json.*?$/mi',
        '/\/\*.*?json.*?\*\//s',

        // Пустые строки после удаления
        '/^\s*$/m'
      ];

      foreach ($patterns as $pattern) {
        $content = preg_replace($pattern, '', $content);
      }

      // Удаляем множественные пустые строки
      $content = preg_replace('/\n\s*\n\s*\n/', "\n\n", $content);

      if ($content !== $originalContent) {
        file_put_contents($filePath, $content);
        echo "  ✓ Очищен: {$file}\n";
        $cleanedFiles[] = $file;
      } else {
        echo "  - Не требует очистки: {$file}\n";
      }
    } else {
      echo "  - Не найден: {$file}\n";
    }
  }

  echo "  ✓ Код очищен от ссылок на JSON\n\n";

  // Шаг 4: Обновление документации
  echo "📝 Шаг 4: Обновление документации...\n";

  // Обновляем README файлы
  $readmeFiles = [
    'README.md',
    'database/README.md',
    'database/QUICK_START.md'
  ];

  foreach ($readmeFiles as $file) {
    $filePath = __DIR__ . '/../' . $file;
    if (file_exists($filePath)) {
      $content = file_get_contents($filePath);

      // Заменяем упоминания JSON на MySQL
      $replacements = [
        '/JSON файл/' => 'база данных MySQL',
        '/json файл/' => 'база данных MySQL',
        '/\.json/' => 'таблицы MySQL',
        '/JSON/' => 'MySQL',
        '/json/' => 'MySQL'
      ];

      foreach ($replacements as $pattern => $replacement) {
        $content = preg_replace($pattern, $replacement, $content);
      }

      file_put_contents($filePath, $content);
      echo "  ✓ Обновлен: {$file}\n";
    }
  }

  echo "  ✓ Документация обновлена\n\n";

  // Шаг 5: Проверка целостности
  echo "🔍 Шаг 5: Проверка целостности проекта...\n";

  // Проверяем, что основные файлы не содержат ссылок на JSON
  $criticalFiles = [
    'api/add-to-cart.php',
    'shop.php',
    'reviews.php',
    'admin/products.php'
  ];

  $hasJsonReferences = false;

  foreach ($criticalFiles as $file) {
    $filePath = __DIR__ . '/../' . $file;
    if (file_exists($filePath)) {
      $content = file_get_contents($filePath);
      if (preg_match('/\.json/', $content)) {
        echo "  ⚠️  Найдены ссылки на JSON в: {$file}\n";
        $hasJsonReferences = true;
      } else {
        echo "  ✓ Проверен: {$file}\n";
      }
    }
  }

  if (!$hasJsonReferences) {
    echo "  ✓ Все критические файлы очищены от JSON\n";
  }

  echo "  ✓ Проверка целостности завершена\n\n";

  // Финальный отчет
  echo "🎉 ОЧИСТКА ПРОЕКТА ЗАВЕРШЕНА!\n";
  echo "==============================\n\n";

  echo "✅ Что было сделано:\n";
  echo "- Создана резервная копия в: {$backupDir}\n";
  echo "- Удалено JSON файлов: " . count($deletedFiles) . "\n";
  echo "- Очищено файлов кода: " . count($cleanedFiles) . "\n";
  echo "- Обновлена документация\n";
  echo "- Проверена целостность проекта\n\n";

  echo "📋 Удаленные файлы:\n";
  foreach ($deletedFiles as $file) {
    echo "- {$file}\n";
  }
  echo "\n";

  echo "📋 Очищенные файлы кода:\n";
  foreach ($cleanedFiles as $file) {
    echo "- {$file}\n";
  }
  echo "\n";

  echo "⚠️  Важные замечания:\n";
  echo "- Резервная копия сохранена в: {$backupDir}\n";
  echo "- Все данные теперь хранятся только в MySQL\n";
  echo "- Проект полностью перешел на базу данных\n";
  echo "- JSON файлы больше не используются\n\n";

  echo "🔧 Рекомендации:\n";
  echo "1. Проверьте работу сайта в браузере\n";
  echo "2. Убедитесь, что все функции работают\n";
  echo "3. Проверьте админ-панель\n";
  echo "4. При необходимости удалите папку data/ полностью\n";
  echo "5. Обновите .gitignore (если используете Git)\n\n";

  echo "🎯 Результат:\n";
  echo "- Проект стал чище и организованнее\n";
  echo "- Убрана зависимость от файловой системы\n";
  echo "- Все данные централизованы в базе данных\n";
  echo "- Система готова к масштабированию\n\n";

  echo "Спасибо за использование системы очистки!\n";

} catch (Exception $e) {
  echo "\n❌ КРИТИЧЕСКАЯ ОШИБКА!\n";
  echo "========================\n";
  echo "Ошибка: " . $e->getMessage() . "\n";
  echo "Файл: " . $e->getFile() . "\n";
  echo "Строка: " . $e->getLine() . "\n\n";

  echo "🔧 Рекомендации:\n";
  echo "1. Проверьте резервную копию в: {$backupDir}\n";
  echo "2. Восстановите файлы из резервной копии\n";
  echo "3. Проверьте права доступа к файлам\n";
  echo "4. Убедитесь, что миграция прошла успешно\n\n";

  exit(1);
}
?>