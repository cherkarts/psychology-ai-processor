<?php
// Диагностика админки
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Диагностика админки</h1>";

// Проверяем основные файлы
$files_to_check = [
  'includes/config.php',
  'includes/auth.php',
  'includes/header.php',
  'includes/footer.php'
];

echo "<h2>1. Проверка файлов</h2>";
foreach ($files_to_check as $file) {
  if (file_exists(__DIR__ . '/' . $file)) {
    echo "✅ {$file}<br>";
  } else {
    echo "❌ {$file} ОТСУТСТВУЕТ<br>";
  }
}

// Проверяем подключение файлов
echo "<h2>2. Подключение файлов</h2>";
try {
  require_once __DIR__ . '/includes/config.php';
  echo "✅ config.php подключен<br>";
} catch (Exception $e) {
  echo "❌ Ошибка config.php: " . $e->getMessage() . "<br>";
}

try {
  require_once __DIR__ . '/includes/auth.php';
  echo "✅ auth.php подключен<br>";
} catch (Exception $e) {
  echo "❌ Ошибка auth.php: " . $e->getMessage() . "<br>";
}

// Проверяем функции
echo "<h2>3. Проверка функций</h2>";
$functions = ['isLoggedIn', 'getAdminDB', 'loginUser'];
foreach ($functions as $func) {
  if (function_exists($func)) {
    echo "✅ {$func}()<br>";
  } else {
    echo "❌ {$func}() не найдена<br>";
  }
}

// Проверяем БД
echo "<h2>4. Проверка БД</h2>";
if (function_exists('getAdminDB')) {
  try {
    $db = getAdminDB();
    if ($db) {
      echo "✅ Подключение к БД успешно<br>";
      // Тест запроса
      $stmt = $db->query("SELECT COUNT(*) FROM articles");
      $count = $stmt->fetchColumn();
      echo "✅ Статей в БД: {$count}<br>";
    } else {
      echo "❌ getAdminDB() вернул null<br>";
    }
  } catch (Exception $e) {
    echo "❌ Ошибка БД: " . $e->getMessage() . "<br>";
  }
}

echo "<h2>5. Проверка сессии</h2>";
echo "Session ID: " . session_id() . "<br>";
echo "Session status: " . session_status() . "<br>";

echo "<h2>6. Информация о сервере</h2>";
echo "PHP версия: " . phpversion() . "<br>";
echo "Время: " . date('Y-m-d H:i:s') . "<br>";
?>