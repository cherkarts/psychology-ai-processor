<?php
/**
 * Скрипт для генерации slug'ов для существующих записей
 * Запускается один раз для миграции существующих данных
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config.php';

echo "=== Генерация slug'ов для существующих записей ===\n";

try {
  $config = require __DIR__ . '/../config.php';

  $socket = $config['database']['socket'] ?? null;
  if ($socket) {
    $dsn = "mysql:unix_socket={$socket};dbname={$config['database']['dbname']};charset={$config['database']['charset']}";
  } else {
    $dsn = "mysql:host={$config['database']['host']};dbname={$config['database']['dbname']};charset={$config['database']['charset']}";
  }

  $db = new PDO($dsn, $config['database']['username'], $config['database']['password'], $config['database']['options']);

  if (!$db) {
    throw new Exception("Failed to connect to database");
  }

  echo "✓ Подключение к базе данных успешно\n";

  // Генерация slug'ов для товаров
  echo "\n--- Обработка товаров ---\n";
  $stmt = $db->prepare("SELECT id, title, slug FROM products WHERE slug IS NULL OR slug = ''");
  $stmt->execute();
  $products = $stmt->fetchAll();

  if (empty($products)) {
    echo "✓ Все товары уже имеют slug'и\n";
  } else {
    echo "Найдено товаров без slug'ов: " . count($products) . "\n";

    foreach ($products as $product) {
      $newSlug = generateUniqueSlug($product['title'], 'products', $product['id']);

      $updateStmt = $db->prepare("UPDATE products SET slug = ? WHERE id = ?");
      $updateStmt->execute([$newSlug, $product['id']]);

      echo "  ✓ Товар '{$product['title']}' → slug: {$newSlug}\n";
    }
  }

  // Генерация slug'ов для статей
  echo "\n--- Обработка статей ---\n";
  $stmt = $db->prepare("SELECT id, title, slug FROM articles WHERE slug IS NULL OR slug = ''");
  $stmt->execute();
  $articles = $stmt->fetchAll();

  if (empty($articles)) {
    echo "✓ Все статьи уже имеют slug'и\n";
  } else {
    echo "Найдено статей без slug'ов: " . count($articles) . "\n";

    foreach ($articles as $article) {
      // Генерируем slug вручную
      $baseSlug = generateSlug($article['title']);
      $slug = $baseSlug;
      $counter = 1;

      while (true) {
        $checkStmt = $db->prepare("SELECT COUNT(*) as count FROM articles WHERE slug = ? AND id != ?");
        $checkStmt->execute([$slug, $article['id']]);
        $result = $checkStmt->fetch();

        if ($result['count'] == 0) {
          break;
        }

        $slug = $baseSlug . '-' . $counter;
        $counter++;
      }

      $updateStmt = $db->prepare("UPDATE articles SET slug = ? WHERE id = ?");
      $updateStmt->execute([$slug, $article['id']]);

      echo "  ✓ Статья '{$article['title']}' → slug: {$slug}\n";
    }
  }

  // Генерация slug'ов для медитаций
  echo "\n--- Обработка медитаций ---\n";
  $stmt = $db->prepare("SELECT id, title, slug FROM meditations WHERE slug IS NULL OR slug = ''");
  $stmt->execute();
  $meditations = $stmt->fetchAll();

  if (empty($meditations)) {
    echo "✓ Все медитации уже имеют slug'и\n";
  } else {
    echo "Найдено медитаций без slug'ов: " . count($meditations) . "\n";

    foreach ($meditations as $meditation) {
      // Генерируем slug вручную
      $baseSlug = generateSlug($meditation['title']);
      $slug = $baseSlug;
      $counter = 1;

      while (true) {
        $checkStmt = $db->prepare("SELECT COUNT(*) as count FROM meditations WHERE slug = ? AND id != ?");
        $checkStmt->execute([$slug, $meditation['id']]);
        $result = $checkStmt->fetch();

        if ($result['count'] == 0) {
          break;
        }

        $slug = $baseSlug . '-' . $counter;
        $counter++;
      }

      $updateStmt = $db->prepare("UPDATE meditations SET slug = ? WHERE id = ?");
      $updateStmt->execute([$slug, $meditation['id']]);

      echo "  ✓ Медитация '{$meditation['title']}' → slug: {$slug}\n";
    }
  }

  // Генерация slug'ов для категорий товаров
  echo "\n--- Обработка категорий товаров ---\n";
  $stmt = $db->prepare("SELECT id, name, slug FROM product_categories WHERE slug IS NULL OR slug = ''");
  $stmt->execute();
  $categories = $stmt->fetchAll();

  if (empty($categories)) {
    echo "✓ Все категории товаров уже имеют slug'и\n";
  } else {
    echo "Найдено категорий товаров без slug'ов: " . count($categories) . "\n";

    foreach ($categories as $category) {
      // Генерируем slug вручную, так как функция generateUniqueSlug использует getDB()
      $baseSlug = generateSlug($category['name']);
      $slug = $baseSlug;
      $counter = 1;

      while (true) {
        $checkStmt = $db->prepare("SELECT COUNT(*) as count FROM product_categories WHERE slug = ? AND id != ?");
        $checkStmt->execute([$slug, $category['id']]);
        $result = $checkStmt->fetch();

        if ($result['count'] == 0) {
          break;
        }

        $slug = $baseSlug . '-' . $counter;
        $counter++;
      }

      $updateStmt = $db->prepare("UPDATE product_categories SET slug = ? WHERE id = ?");
      $updateStmt->execute([$slug, $category['id']]);

      echo "  ✓ Категория '{$category['name']}' → slug: {$slug}\n";
    }
  }

  // Генерация slug'ов для категорий статей
  echo "\n--- Обработка категорий статей ---\n";
  $stmt = $db->prepare("SELECT id, name, slug FROM article_categories WHERE slug IS NULL OR slug = ''");
  $stmt->execute();
  $articleCategories = $stmt->fetchAll();

  if (empty($articleCategories)) {
    echo "✓ Все категории статей уже имеют slug'и\n";
  } else {
    echo "Найдено категорий статей без slug'ов: " . count($articleCategories) . "\n";

    foreach ($articleCategories as $category) {
      // Генерируем slug вручную
      $baseSlug = generateSlug($category['name']);
      $slug = $baseSlug;
      $counter = 1;

      while (true) {
        $checkStmt = $db->prepare("SELECT COUNT(*) as count FROM article_categories WHERE slug = ? AND id != ?");
        $checkStmt->execute([$slug, $category['id']]);
        $result = $checkStmt->fetch();

        if ($result['count'] == 0) {
          break;
        }

        $slug = $baseSlug . '-' . $counter;
        $counter++;
      }

      $updateStmt = $db->prepare("UPDATE article_categories SET slug = ? WHERE id = ?");
      $updateStmt->execute([$slug, $category['id']]);

      echo "  ✓ Категория статей '{$category['name']}' → slug: {$slug}\n";
    }
  }

  // Генерация slug'ов для категорий медитаций
  echo "\n--- Обработка категорий медитаций ---\n";
  $stmt = $db->prepare("SELECT id, name, slug FROM meditation_categories WHERE slug IS NULL OR slug = ''");
  $stmt->execute();
  $meditationCategories = $stmt->fetchAll();

  if (empty($meditationCategories)) {
    echo "✓ Все категории медитаций уже имеют slug'и\n";
  } else {
    echo "Найдено категорий медитаций без slug'ов: " . count($meditationCategories) . "\n";

    foreach ($meditationCategories as $category) {
      // Генерируем slug вручную
      $baseSlug = generateSlug($category['name']);
      $slug = $baseSlug;
      $counter = 1;

      while (true) {
        $checkStmt = $db->prepare("SELECT COUNT(*) as count FROM meditation_categories WHERE slug = ? AND id != ?");
        $checkStmt->execute([$slug, $category['id']]);
        $result = $checkStmt->fetch();

        if ($result['count'] == 0) {
          break;
        }

        $slug = $baseSlug . '-' . $counter;
        $counter++;
      }

      $updateStmt = $db->prepare("UPDATE meditation_categories SET slug = ? WHERE id = ?");
      $updateStmt->execute([$slug, $category['id']]);

      echo "  ✓ Категория медитаций '{$category['name']}' → slug: {$slug}\n";
    }
  }

  echo "\n✅ Миграция slug'ов завершена успешно!\n";
  echo "Теперь все записи имеют уникальные ЧПУ-friendly slug'и.\n";

} catch (Exception $e) {
  echo "❌ Ошибка: " . $e->getMessage() . "\n";
  if (isset($db)) {
    $errorInfo = $db->errorInfo();
    if ($errorInfo[0] !== '00000') {
      echo "SQL Error: " . implode(' - ', $errorInfo) . "\n";
    }
  }
  exit(1);
}
?>