<?php
// Добавляем ярлыки к товару ID 105
header('Content-Type: text/plain; charset=utf-8');

echo "=== ADDING BADGES TO PRODUCT 105 ===\n\n";

try {
  $config = require __DIR__ . '/config.php';
  require_once __DIR__ . '/includes/Database.php';

  $db = Database::getInstance();
  $pdo = $db->getConnection();

  echo "Connected to database\n\n";

  // Проверяем товар ID 105
  $stmt = $pdo->prepare("SELECT * FROM products WHERE id = 105");
  $stmt->execute();
  $product = $stmt->fetch();

  if ($product) {
    echo "Product 105 found:\n";
    echo "ID: {$product['id']}\n";
    echo "Title: {$product['title']}\n";
    echo "Status: {$product['status']}\n";
    echo "Type: {$product['type']}\n";
    echo "In Stock: {$product['in_stock']}\n";
    echo "Is Active: " . (isset($product['is_active']) ? $product['is_active'] : 'NULL') . "\n\n";

    // Очищаем существующие ярлыки
    $stmt = $pdo->prepare("DELETE FROM product_badge_relations WHERE product_id = 105");
    $stmt->execute();
    echo "Cleared existing badges\n";

    // Добавляем ярлыки
    $badgeIds = [1, 2, 3]; // Акция, Новый товар, Хит продаж
    $stmt = $pdo->prepare("INSERT INTO product_badge_relations (product_id, badge_id) VALUES (?, ?)");

    foreach ($badgeIds as $badgeId) {
      $stmt->execute([105, $badgeId]);
      echo "Added badge ID: $badgeId\n";
    }

    echo "\nVerifying saved badges...\n";

    // Проверяем сохраненные ярлыки
    $stmt = $pdo->prepare("
            SELECT pbr.badge_id, pb.name, pb.color, pb.background_color
            FROM product_badge_relations pbr
            JOIN product_badges pb ON pbr.badge_id = pb.id
            WHERE pbr.product_id = 105
        ");
    $stmt->execute();
    $savedBadges = $stmt->fetchAll();

    echo "Saved badges for product 105:\n";
    foreach ($savedBadges as $badge) {
      echo "- ID {$badge['badge_id']}: {$badge['name']} (color: {$badge['color']}, bg: {$badge['background_color']})\n";
    }

  } else {
    echo "Product 105 not found!\n";
  }

  echo "\n=== ADDITION COMPLETE ===\n";

} catch (Exception $e) {
  echo "Error: " . $e->getMessage() . "\n";
}
?>