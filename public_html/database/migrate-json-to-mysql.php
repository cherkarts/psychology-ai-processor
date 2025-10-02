<?php
/**
 * Скрипт миграции данных из JSON файлов в MySQL базу данных
 * Запускать только один раз для переноса всех данных
 */

require_once __DIR__ . '/../includes/Database.php';

class JsonToMysqlMigrator
{
  private $db;
  private $jsonFiles = [
    'products' => '../data/products.json',
    'reviews' => '../data/reviews.json',
    'meditations' => '../data/meditations.json',
    'orders' => '../data/orders.json',
    'categories' => '../data/categories.json',
    'article_likes' => '../data/article-likes.json',
    'user_article_likes' => '../data/user-article-likes.json'
  ];

  public function __construct()
  {
    $this->db = Database::getInstance();
    $this->db->selectDatabase('cherkas_therapy');
  }

  public function migrateAll()
  {
    echo "Начинаем миграцию данных из JSON в MySQL...\n";

    try {
      // Мигрируем категории товаров
      $this->migrateProductCategories();

      // Мигрируем товары
      $this->migrateProducts();

      // Мигрируем отзывы
      $this->migrateReviews();

      // Мигрируем медитации
      $this->migrateMeditations();

      // Мигрируем заказы
      $this->migrateOrders();

      // Мигрируем лайки статей
      $this->migrateArticleLikes();

      echo "Миграция завершена успешно!\n";

    } catch (Exception $e) {
      echo "Ошибка при миграции: " . $e->getMessage() . "\n";
    }
  }

  private function migrateProductCategories()
  {
    echo "Мигрируем категории товаров...\n";

    $categoriesFile = __DIR__ . '/' . $this->jsonFiles['categories'];
    if (!file_exists($categoriesFile)) {
      echo "Файл категорий не найден, пропускаем...\n";
      return;
    }

    $categories = json_decode(file_get_contents($categoriesFile), true);

    foreach ($categories as $category) {
      $sql = "INSERT IGNORE INTO product_categories (slug, name, description, image, sort_order) 
                    VALUES (?, ?, ?, ?, ?)";

      $stmt = $this->db->prepare($sql);
      $stmt->execute([
        $category['slug'] ?? $category['id'],
        $category['name'],
        $category['description'] ?? null,
        $category['image'] ?? null,
        $category['sort_order'] ?? 0
      ]);
    }

    echo "Категории товаров мигрированы.\n";
  }

  private function migrateProducts()
  {
    echo "Мигрируем товары...\n";

    $productsFile = __DIR__ . '/' . $this->jsonFiles['products'];
    if (!file_exists($productsFile)) {
      echo "Файл товаров не найден, пропускаем...\n";
      return;
    }

    $products = json_decode(file_get_contents($productsFile), true);

    foreach ($products as $product) {
      // Получаем ID категории
      $categoryId = null;
      if (!empty($product['category'])) {
        $stmt = $this->db->prepare("SELECT id FROM product_categories WHERE slug = ?");
        $stmt->execute([$product['category']]);
        $categoryId = $stmt->fetchColumn();
      }

      $sql = "INSERT IGNORE INTO products (
                slug, title, description, short_description, price, old_price, 
                currency, category_id, type, status, image, gallery, features, 
                content, download_url, telegram_required, whatsapp_contact, 
                telegram_contact, in_stock, is_featured, tags, meta_title, 
                meta_description, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

      $stmt = $this->db->prepare($sql);
      $stmt->execute([
        $product['slug'],
        $product['title'],
        $product['description'],
        $product['short_description'] ?? null,
        $product['price'],
        $product['old_price'] ?? null,
        $product['currency'] ?? 'RUB',
        $categoryId,
        $this->mapProductType($product['type']),
        'published',
        $product['image'] ?? null,
        json_encode($product['gallery'] ?? []),
        json_encode($product['features'] ?? []),
        $product['content'] ?? null,
        $product['download_url'] ?? null,
        $product['telegram_required'] ?? false,
        $product['whatsapp_contact'] ?? null,
        $product['telegram_contact'] ?? null,
        $product['in_stock'] ?? true,
        $product['is_featured'] ?? false,
        json_encode($product['tags'] ?? []),
        $product['meta_title'] ?? null,
        $product['meta_description'] ?? null,
        $product['created_at'] ?? date('Y-m-d H:i:s')
      ]);
    }

    echo "Товары мигрированы.\n";
  }

  private function migrateReviews()
  {
    echo "Мигрируем отзывы...\n";

    $reviewsFile = __DIR__ . '/' . $this->jsonFiles['reviews'];
    if (!file_exists($reviewsFile)) {
      echo "Файл отзывов не найден, пропускаем...\n";
      return;
    }

    $reviewsData = json_decode(file_get_contents($reviewsFile), true);
    $reviews = $reviewsData['reviews'] ?? $reviewsData;

    foreach ($reviews as $review) {
      $sql = "INSERT IGNORE INTO reviews (
                type, name, email, rating, text, age, tags, image, image_type, 
                video, thumbnail, status, created_at, verification_method, 
                verification_status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

      $stmt = $this->db->prepare($sql);
      $stmt->execute([
        $review['type'] ?? 'text',
        $review['name'],
        $review['email'] ?? 'anonymous@example.com',
        $review['rating'],
        $review['text'],
        $review['age'] ?? null,
        json_encode($review['tags'] ?? []),
        $review['image'] ?? null,
        $review['imageType'] ?? null,
        $review['video'] ?? null,
        $review['thumbnail'] ?? null,
        'approved', // Все существующие отзывы считаем одобренными
        $review['date'] ? date('Y-m-d H:i:s', strtotime($review['date'])) : date('Y-m-d H:i:s'),
        'none', // Для существующих отзывов не требуем верификации
        true
      ]);
    }

    echo "Отзывы мигрированы.\n";
  }

  private function migrateMeditations()
  {
    echo "Мигрируем медитации...\n";

    $meditationsFile = __DIR__ . '/' . $this->jsonFiles['meditations'];
    if (!file_exists($meditationsFile)) {
      echo "Файл медитаций не найден, пропускаем...\n";
      return;
    }

    $meditations = json_decode(file_get_contents($meditationsFile), true);

    foreach ($meditations as $meditation) {
      // Получаем ID категории медитации
      $categoryId = null;
      if (!empty($meditation['category'])) {
        $stmt = $this->db->prepare("SELECT id FROM meditation_categories WHERE slug = ?");
        $stmt->execute([$meditation['category']]);
        $categoryId = $stmt->fetchColumn();
      }

      $sql = "INSERT IGNORE INTO meditations (
                slug, title, subtitle, category_id, duration, description, 
                audio_file, icon, meta_description, likes, favorites, is_free, 
                telegram_required, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

      $stmt = $this->db->prepare($sql);
      $stmt->execute([
        $meditation['slug'] ?? $meditation['id'],
        $meditation['title'],
        $meditation['subtitle'] ?? null,
        $categoryId,
        $meditation['duration'] ?? null,
        $meditation['description'] ?? null,
        $meditation['audio_file'] ?? null,
        $meditation['icon'] ?? null,
        $meditation['meta_description'] ?? null,
        $meditation['likes'] ?? 0,
        $meditation['favorites'] ?? 0,
        $meditation['is_free'] ?? false,
        $meditation['telegram_required'] ?? false,
        $meditation['created_at'] ?? date('Y-m-d H:i:s')
      ]);
    }

    echo "Медитации мигрированы.\n";
  }

  private function migrateOrders()
  {
    echo "Мигрируем заказы...\n";

    $ordersFile = __DIR__ . '/' . $this->jsonFiles['orders'];
    if (!file_exists($ordersFile)) {
      echo "Файл заказов не найден, пропускаем...\n";
      return;
    }

    $orders = json_decode(file_get_contents($ordersFile), true);

    foreach ($orders as $order) {
      // Создаем заказ
      $sql = "INSERT IGNORE INTO orders (
                order_number, email, phone, name, status, total_amount, 
                currency, payment_method, payment_status, notes, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

      $stmt = $this->db->prepare($sql);
      $stmt->execute([
        $order['order_number'] ?? 'ORD-' . time(),
        $order['email'],
        $order['phone'] ?? null,
        $order['name'],
        $order['status'] ?? 'completed',
        $order['total_amount'],
        $order['currency'] ?? 'RUB',
        $order['payment_method'] ?? null,
        $order['payment_status'] ?? 'paid',
        $order['notes'] ?? null,
        $order['created_at'] ?? date('Y-m-d H:i:s')
      ]);

      $orderId = $this->db->lastInsertId();

      // Добавляем элементы заказа
      if (!empty($order['items'])) {
        foreach ($order['items'] as $item) {
          $sql = "INSERT INTO order_items (
                        order_id, product_id, product_title, quantity, price, total_price
                    ) VALUES (?, ?, ?, ?, ?, ?)";

          $stmt = $this->db->prepare($sql);
          $stmt->execute([
            $orderId,
            $item['product_id'] ?? 1,
            $item['product_title'] ?? 'Товар',
            $item['quantity'] ?? 1,
            $item['price'] ?? 0,
            $item['total_price'] ?? 0
          ]);
        }
      }
    }

    echo "Заказы мигрированы.\n";
  }

  private function migrateArticleLikes()
  {
    echo "Мигрируем лайки статей...\n";

    // Создаем таблицу для лайков статей если её нет
    $sql = "CREATE TABLE IF NOT EXISTS article_likes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            article_id INT NOT NULL,
            user_identifier VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_like (article_id, user_identifier)
        )";
    $this->db->exec($sql);

    // Мигрируем общие лайки
    $likesFile = __DIR__ . '/' . $this->jsonFiles['article_likes'];
    if (file_exists($likesFile)) {
      $likes = json_decode(file_get_contents($likesFile), true);
      foreach ($likes as $articleId => $count) {
        // Здесь можно добавить логику для создания записей лайков
        echo "Статья ID {$articleId} имеет {$count} лайков\n";
      }
    }

    // Мигрируем пользовательские лайки
    $userLikesFile = __DIR__ . '/' . $this->jsonFiles['user_article_likes'];
    if (file_exists($userLikesFile)) {
      $userLikes = json_decode(file_get_contents($userLikesFile), true);
      foreach ($userLikes as $userId => $articleIds) {
        foreach ($articleIds as $articleId) {
          $sql = "INSERT IGNORE INTO article_likes (article_id, user_identifier) VALUES (?, ?)";
          $stmt = $this->db->prepare($sql);
          $stmt->execute([$articleId, $userId]);
        }
      }
    }

    echo "Лайки статей мигрированы.\n";
  }

  private function mapProductType($jsonType)
  {
    $typeMap = [
      'digital' => 'digital',
      'physical' => 'physical',
      'service' => 'service',
      'free' => 'free',
      'discussion' => 'service'
    ];

    return $typeMap[$jsonType] ?? 'digital';
  }
}

// Запуск миграции
if (php_sapi_name() === 'cli') {
  $migrator = new JsonToMysqlMigrator();
  $migrator->migrateAll();
} else {
  echo "Этот скрипт предназначен для запуска из командной строки.\n";
  echo "Используйте: php migrate-json-to-mysql.php\n";
}
?>