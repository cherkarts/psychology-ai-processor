<?php
require_once '../config.php';

try {
  $pdo = new PDO(
    "mysql:host=" . $config['database']['host'] . ";dbname=" . $config['database']['dbname'],
    $config['database']['username'],
    $config['database']['password'],
    $config['database']['options']
  );

  $stmt = $pdo->query("SELECT id, title, image, gallery FROM products WHERE gallery IS NOT NULL AND gallery != '' LIMIT 5");
  $products = $stmt->fetchAll();

  echo "<h2>Товары с галереей:</h2>";
  foreach ($products as $product) {
    echo "<h3>ID: {$product['id']} - {$product['title']}</h3>";
    echo "<p><strong>Изображение:</strong> {$product['image']}</p>";
    echo "<p><strong>Галерея:</strong> {$product['gallery']}</p>";

    if ($product['gallery']) {
      $gallery = json_decode($product['gallery'], true);
      if ($gallery) {
        echo "<p><strong>Галерея (массив):</strong></p>";
        echo "<ul>";
        foreach ($gallery as $img) {
          echo "<li><a href='{$img}' target='_blank'>{$img}</a></li>";
        }
        echo "</ul>";
      }
    }
    echo "<hr>";
  }

} catch (Exception $e) {
  echo "Ошибка: " . $e->getMessage();
}
?>