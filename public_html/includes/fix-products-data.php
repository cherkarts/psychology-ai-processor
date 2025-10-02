<?php
// Скрипт для исправления данных товаров
session_start();
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/config.php';

if (!isLoggedIn()) {
  die('Доступ запрещен');
}

echo "<h1>Исправление данных товаров</h1>";

try {
  $db = getAdminDB();

  if (!$db) {
    die('Ошибка подключения к БД');
  }

  echo "<h2>1. Создание резервной копии товаров</h2>";

  $stmt = $db->query("SELECT * FROM products");
  $products = $stmt->fetchAll();

  $backupFile = __DIR__ . '/backup-products-' . date('Y-m-d-H-i-s') . '.json';
  file_put_contents($backupFile, json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
  echo "<p>✅ Резервная копия создана: " . basename($backupFile) . "</p>";

  echo "<h2>2. Исправление данных товаров</h2>";

  // Исправляем конкретные товары
  $productFixes = [
    [
      'id' => 89,
      'title' => 'Тест-1',
      'description' => '<p>Описание товара тест-1</p>',
      'short_description' => 'Краткое описание тест-1'
    ],
    [
      'id' => 90,
      'title' => 'Тест-2',
      'description' => '<p>Полное описание товара тест-2</p>',
      'short_description' => 'Тест 2 - полное описание дополнительное'
    ]
  ];

  $updateSql = "UPDATE products SET title = ?, description = ?, short_description = ? WHERE id = ?";
  $stmt = $db->prepare($updateSql);

  $fixed = 0;
  foreach ($productFixes as $fix) {
    $stmt->execute([
      $fix['title'],
      $fix['description'],
      $fix['short_description'],
      $fix['id']
    ]);
    $fixed++;
    echo "<p>✅ Исправлен товар ID {$fix['id']}: {$fix['title']}</p>";
  }

  echo "<p>✅ Всего исправлено товаров: {$fixed}</p>";

  echo "<h2>3. Проверка результатов</h2>";

  $stmt = $db->query("SELECT id, title, description, short_description FROM products WHERE id IN (89, 90)");
  $fixedProducts = $stmt->fetchAll();

  echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
  echo "<tr><th>ID</th><th>Название</th><th>Описание</th><th>Краткое описание</th></tr>";
  foreach ($fixedProducts as $product) {
    echo "<tr>";
    echo "<td>{$product['id']}</td>";
    echo "<td>" . htmlspecialchars($product['title']) . "</td>";
    echo "<td>" . htmlspecialchars(substr($product['description'], 0, 50)) . "...</td>";
    echo "<td>" . htmlspecialchars($product['short_description']) . "</td>";
    echo "</tr>";
  }
  echo "</table>";

  // Тест JSON
  $json = json_encode($fixedProducts, JSON_UNESCAPED_UNICODE);
  if ($json === false) {
    echo "<p style='color: red;'>❌ JSON ошибка: " . json_last_error_msg() . "</p>";
  } else {
    echo "<p style='color: green;'>✅ JSON создается корректно</p>";
  }

  echo "<h2>4. Готово!</h2>";
  echo "<p style='color: green; font-weight: bold;'>✅ Данные товаров исправлены!</p>";

  echo "<h3>Рекомендации:</h3>";
  echo "<ul>";
  echo "<li>Проверьте страницу магазина - кракозябры должны исчезнуть</li>";
  echo "<li>Проверьте страницы товаров - названия должны отображаться корректно</li>";
  echo "<li>Очистите кеш браузера (Ctrl+F5)</li>";
  echo "<li>Удалите этот файл (fix-products-data.php) с сервера</li>";
  echo "</ul>";

} catch (Exception $e) {
  echo "<p style='color: red;'>❌ Ошибка: " . $e->getMessage() . "</p>";
}
?>