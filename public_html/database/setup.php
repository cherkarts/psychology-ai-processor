<?php
/**
 * Скрипт автоматической настройки базы данных
 * Выполняет все необходимые шаги для перевода сайта на MySQL
 */

echo "=== Автоматическая настройка базы данных ===\n\n";

// Проверяем, что скрипт запущен из командной строки
if (php_sapi_name() !== 'cli') {
  echo "Этот скрипт должен запускаться из командной строки\n";
  exit(1);
}

// Проверяем наличие необходимых расширений
echo "1. Проверка системных требований...\n";

if (!extension_loaded('pdo')) {
  echo "✗ Расширение PDO не установлено\n";
  exit(1);
}

if (!extension_loaded('pdo_mysql')) {
  echo "✗ Расширение PDO MySQL не установлено\n";
  exit(1);
}

if (!extension_loaded('json')) {
  echo "✗ Расширение JSON не установлено\n";
  exit(1);
}

echo "✓ Все необходимые расширения установлены\n\n";

// Проверяем наличие файлов
echo "2. Проверка файлов...\n";

$requiredFiles = [
  '../config.php',
  'schema.sql',
  'migrate.php',
  '../includes/Database.php'
];

foreach ($requiredFiles as $file) {
  if (!file_exists($file)) {
    echo "✗ Файл не найден: $file\n";
    exit(1);
  }
}

echo "✓ Все необходимые файлы найдены\n\n";

// Подключаем класс базы данных
require_once '../includes/Database.php';

// Функция для создания базы данных
function createDatabase()
{
  echo "3. Создание базы данных...\n";

  try {
    // Подключаемся к MySQL без указания базы данных
    $pdo = new PDO(
      "mysql:host=localhost;charset=utf8mb4",
      'root',
      '',
      [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      ]
    );

    // Создаем базу данных
    $pdo->exec("CREATE DATABASE IF NOT EXISTS cherkas_therapy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ База данных 'cherkas_therapy' создана\n";

    return true;
  } catch (PDOException $e) {
    echo "✗ Ошибка создания базы данных: " . $e->getMessage() . "\n";
    return false;
  }
}

// Функция для импорта схемы
function importSchema()
{
  echo "4. Импорт схемы базы данных...\n";

  try {
    $db = Database::getInstance();

    // Читаем SQL файл
    $sql = file_get_contents('schema.sql');

    // Разбиваем на отдельные запросы
    $queries = array_filter(array_map('trim', explode(';', $sql)));

    $successCount = 0;
    $totalQueries = count($queries);

    foreach ($queries as $query) {
      if (!empty($query)) {
        try {
          $db->query($query);
          $successCount++;
        } catch (Exception $e) {
          echo "  ⚠ Пропущен запрос: " . substr($query, 0, 50) . "...\n";
        }
      }
    }

    echo "✓ Схема импортирована ($successCount/$totalQueries запросов)\n";
    return true;

  } catch (Exception $e) {
    echo "✗ Ошибка импорта схемы: " . $e->getMessage() . "\n";
    return false;
  }
}

// Функция для миграции данных
function migrateData()
{
  echo "5. Миграция данных...\n";

  try {
    // Подключаем скрипт миграции
    require_once 'migrate.php';

    $migrator = new DataMigrator();
    $migrator->migrateAll();

    echo "✓ Данные мигрированы\n";
    return true;

  } catch (Exception $e) {
    echo "✗ Ошибка миграции данных: " . $e->getMessage() . "\n";
    return false;
  }
}

// Функция для тестирования подключения
function testConnection()
{
  echo "6. Тестирование подключения...\n";

  try {
    require_once 'test_connection.php';
    echo "✓ Подключение работает корректно\n";
    return true;

  } catch (Exception $e) {
    echo "✗ Ошибка тестирования: " . $e->getMessage() . "\n";
    return false;
  }
}

// Функция для создания резервной копии JSON файлов
function createBackup()
{
  echo "7. Создание резервной копии JSON файлов...\n";

  $backupDir = 'backup_' . date('Y-m-d_H-i-s');
  if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
  }

  $jsonFiles = [
    '../data/products.json',
    '../data/orders.json',
    '../data/meditations.json',
    '../data/reviews.json'
  ];

  $copiedCount = 0;
  foreach ($jsonFiles as $file) {
    if (file_exists($file)) {
      $destFile = $backupDir . '/' . basename($file);
      if (copy($file, $destFile)) {
        $copiedCount++;
      }
    }
  }

  echo "✓ Резервная копия создана в папке: $backupDir ($copiedCount файлов)\n";
  return true;
}

// Функция для создания .htaccess для защиты
function createHtaccess()
{
  echo "8. Создание защиты для базы данных...\n";

  $htaccessContent = "Order deny,allow\nDeny from all";

  if (file_put_contents('.htaccess', $htaccessContent)) {
    echo "✓ Файл .htaccess создан для защиты директории\n";
    return true;
  } else {
    echo "⚠ Не удалось создать .htaccess\n";
    return false;
  }
}

// Основной процесс
try {
  // Спрашиваем подтверждение
  echo "ВНИМАНИЕ: Этот скрипт выполнит следующие действия:\n";
  echo "- Создаст базу данных 'cherkas_therapy'\n";
  echo "- Импортирует схему таблиц\n";
  echo "- Мигрирует данные из JSON файлов\n";
  echo "- Создаст резервную копию JSON файлов\n";
  echo "- Протестирует подключение\n\n";

  echo "Продолжить? (y/N): ";
  $handle = fopen("php://stdin", "r");
  $line = fgets($handle);
  fclose($handle);

  if (trim(strtolower($line)) !== 'y') {
    echo "Операция отменена\n";
    exit(0);
  }

  echo "\n";

  // Выполняем шаги
  $steps = [
    'createDatabase' => 'Создание базы данных',
    'importSchema' => 'Импорт схемы',
    'migrateData' => 'Миграция данных',
    'testConnection' => 'Тестирование подключения',
    'createBackup' => 'Создание резервной копии',
    'createHtaccess' => 'Создание защиты'
  ];

  $success = true;

  foreach ($steps as $function => $description) {
    echo "=== $description ===\n";
    $result = $function();

    if (!$result) {
      $success = false;
      echo "\nОшибка на шаге: $description\n";
      echo "Попробуйте исправить проблему и запустить скрипт снова\n";
      break;
    }

    echo "\n";
  }

  if ($success) {
    echo "=== НАСТРОЙКА ЗАВЕРШЕНА УСПЕШНО! ===\n\n";
    echo "Что дальше:\n";
    echo "1. Проверьте работу сайта\n";
    echo "2. Обновите API файлы для работы с базой данных\n";
    echo "3. Настройте регулярные резервные копии\n";
    echo "4. При необходимости обновите настройки для продакшена\n\n";

    echo "Полезные команды:\n";
    echo "- Тест подключения: php database/test_connection.php\n";
    echo "- Резервная копия: mysqldump -u root -p cherkas_therapy > backup.sql\n";
    echo "- Восстановление: mysql -u root -p cherkas_therapy < backup.sql\n\n";

  } else {
    echo "=== НАСТРОЙКА ЗАВЕРШЕНА С ОШИБКАМИ ===\n";
    echo "Проверьте логи выше и исправьте проблемы\n";
    exit(1);
  }

} catch (Exception $e) {
  echo "Критическая ошибка: " . $e->getMessage() . "\n";
  exit(1);
}



