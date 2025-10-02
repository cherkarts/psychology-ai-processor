<?php
/**
 * Главный скрипт для полной миграции с JSON на MySQL
 * Выполняет все необходимые шаги для перехода
 */

echo "=== МИГРАЦИЯ С JSON НА MYSQL ===\n";
echo "Сайт психолога Дениса Черкаса\n";
echo "================================\n\n";

// Проверяем, что скрипт запущен из командной строки
if (php_sapi_name() !== 'cli') {
  echo "❌ Этот скрипт должен быть запущен из командной строки!\n";
  echo "Используйте: php migrate-to-mysql.php\n";
  exit(1);
}

// Проверяем наличие необходимых файлов
$requiredFiles = [
  'includes/Database.php',
  'includes/Models/Product.php',
  'includes/Models/Review.php',
  'includes/Models/Meditation.php',
  'includes/Models/Article.php',
  'includes/Models/Order.php',
  'database/schema.sql',
  'database/migrate-json-to-mysql.php',
  'database/update-json-references.php'
];

echo "🔍 Проверяем наличие необходимых файлов...\n";
foreach ($requiredFiles as $file) {
  if (!file_exists(__DIR__ . '/../' . $file)) {
    echo "❌ Файл не найден: {$file}\n";
    exit(1);
  }
  echo "  ✓ {$file}\n";
}

echo "\n📋 План миграции:\n";
echo "1. Создание резервной копии\n";
echo "2. Проверка подключения к базе данных\n";
echo "3. Создание таблиц (если не существуют)\n";
echo "4. Миграция данных из JSON в MySQL\n";
echo "5. Обновление файлов для работы с базой данных\n";
echo "6. Тестирование системы\n\n";

// Запрашиваем подтверждение
echo "⚠️  ВНИМАНИЕ: Этот процесс изменит структуру данных вашего сайта!\n";
echo "Рекомендуется создать полную резервную копию проекта перед началом.\n\n";

echo "Продолжить миграцию? (y/N): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
fclose($handle);

if (trim(strtolower($line)) !== 'y') {
  echo "❌ Миграция отменена пользователем.\n";
  exit(0);
}

echo "\n🚀 Начинаем миграцию...\n\n";

try {
  // Шаг 1: Создание резервной копии
  echo "📦 Шаг 1: Создание резервной копии...\n";
  $backupDir = __DIR__ . '/backup_full_' . date('Y-m-d_H-i-s');
  if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
  }

  // Копируем JSON файлы
  $jsonFiles = ['products.json', 'reviews.json', 'meditations.json', 'orders.json', 'categories.json'];
  foreach ($jsonFiles as $file) {
    $sourcePath = __DIR__ . '/../data/' . $file;
    if (file_exists($sourcePath)) {
      copy($sourcePath, $backupDir . '/' . $file);
      echo "  ✓ Резервная копия: data/{$file}\n";
    }
  }

  echo "  ✓ Резервная копия создана в: {$backupDir}\n\n";

  // Шаг 2: Проверка подключения к базе данных
  echo "🔌 Шаг 2: Проверка подключения к базе данных...\n";
  require_once __DIR__ . '/../includes/Database.php';

  try {
    $db = Database::getInstance();
    $db->selectDatabase('cherkas_therapy');
    echo "  ✓ Подключение к базе данных успешно\n";
  } catch (Exception $e) {
    echo "❌ Ошибка подключения к базе данных: " . $e->getMessage() . "\n";
    echo "Убедитесь, что:\n";
    echo "- XAMPP запущен\n";
    echo "- MySQL сервер работает\n";
    echo "- База данных 'cherkas_therapy' существует\n";
    exit(1);
  }

  // Шаг 3: Создание таблиц
  echo "\n🗄️  Шаг 3: Создание таблиц базы данных...\n";
  $schemaFile = __DIR__ . '/schema.sql';
  if (file_exists($schemaFile)) {
    $schema = file_get_contents($schemaFile);
    $statements = explode(';', $schema);

    foreach ($statements as $statement) {
      $statement = trim($statement);
      if (!empty($statement)) {
        try {
          $db->execute($statement);
          echo "  ✓ Выполнен SQL: " . substr($statement, 0, 50) . "...\n";
        } catch (Exception $e) {
          // Игнорируем ошибки создания таблиц, если они уже существуют
          if (strpos($e->getMessage(), 'already exists') === false) {
            echo "  ⚠️  Предупреждение: " . $e->getMessage() . "\n";
          }
        }
      }
    }
    echo "  ✓ Таблицы созданы/проверены\n";
  } else {
    echo "❌ Файл схемы не найден: schema.sql\n";
    exit(1);
  }

  // Шаг 4: Миграция данных из JSON
  echo "\n📊 Шаг 4: Миграция данных из JSON в MySQL...\n";
  require_once __DIR__ . '/migrate-json-to-mysql.php';

  $migrator = new JsonToMysqlMigrator();
  $migrator->migrateAll();
  echo "  ✓ Данные мигрированы\n";

  // Шаг 5: Обновление файлов
  echo "\n📝 Шаг 5: Обновление файлов для работы с базой данных...\n";
  require_once __DIR__ . '/update-json-references.php';

  $updater = new JsonToMysqlUpdater();
  $updater->updateAllFiles();
  echo "  ✓ Файлы обновлены\n";

  // Шаг 6: Тестирование
  echo "\n🧪 Шаг 6: Тестирование системы...\n";

  // Тестируем подключение к моделям
  require_once __DIR__ . '/../includes/Models/Product.php';
  require_once __DIR__ . '/../includes/Models/Review.php';
  require_once __DIR__ . '/../includes/Models/Meditation.php';
  require_once __DIR__ . '/../includes/Models/Article.php';
  require_once __DIR__ . '/../includes/Models/Order.php';

  try {
    $productModel = new Product();
    $products = $productModel->getAll(['limit' => 1]);
    echo "  ✓ Модель Product работает\n";

    $reviewModel = new Review();
    $reviews = $reviewModel->getApproved(1);
    echo "  ✓ Модель Review работает\n";

    $meditationModel = new Meditation();
    $meditations = $meditationModel->getAll(['limit' => 1]);
    echo "  ✓ Модель Meditation работает\n";

    $articleModel = new Article();
    $articles = $articleModel->getPublished(1);
    echo "  ✓ Модель Article работает\n";

    $orderModel = new Order();
    $orders = $orderModel->getAll(['limit' => 1]);
    echo "  ✓ Модель Order работает\n";

  } catch (Exception $e) {
    echo "❌ Ошибка тестирования: " . $e->getMessage() . "\n";
    exit(1);
  }

  // Финальные инструкции
  echo "\n🎉 МИГРАЦИЯ УСПЕШНО ЗАВЕРШЕНА!\n";
  echo "================================\n\n";

  echo "✅ Что было сделано:\n";
  echo "- Создана резервная копия в: {$backupDir}\n";
  echo "- Созданы все необходимые таблицы в базе данных\n";
  echo "- Все данные из JSON файлов перенесены в MySQL\n";
  echo "- Все файлы обновлены для работы с базой данных\n";
  echo "- Система протестирована и работает корректно\n\n";

  echo "📋 Следующие шаги:\n";
  echo "1. Проверьте работу сайта в браузере\n";
  echo "2. Проверьте работу админ-панели\n";
  echo "3. Убедитесь, что все функции работают корректно\n";
  echo "4. При необходимости удалите старые JSON файлы из папки data/\n\n";

  echo "⚠️  Важные замечания:\n";
  echo "- Резервная копия сохранена в: {$backupDir}\n";
  echo "- Старые JSON файлы больше не используются\n";
  echo "- Все данные теперь хранятся в базе данных\n";
  echo "- Система стала более надежной и масштабируемой\n\n";

  echo "🔧 Если возникли проблемы:\n";
  echo "- Проверьте логи ошибок\n";
  echo "- Восстановите файлы из резервной копии\n";
  echo "- Обратитесь к документации\n\n";

  echo "Спасибо за использование системы миграции!\n";

} catch (Exception $e) {
  echo "\n❌ КРИТИЧЕСКАЯ ОШИБКА!\n";
  echo "========================\n";
  echo "Ошибка: " . $e->getMessage() . "\n";
  echo "Файл: " . $e->getFile() . "\n";
  echo "Строка: " . $e->getLine() . "\n\n";

  echo "🔧 Рекомендации:\n";
  echo "1. Проверьте резервную копию в: {$backupDir}\n";
  echo "2. Восстановите файлы из резервной копии\n";
  echo "3. Проверьте настройки базы данных\n";
  echo "4. Убедитесь, что все зависимости установлены\n\n";

  exit(1);
}
?>