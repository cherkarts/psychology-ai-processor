<?php
/**
 * Скрипт миграции данных из JSON файлов в MySQL базу данных
 * Запускать после создания схемы базы данных
 */

require_once __DIR__ . '/../includes/Database.php';

class DataMigrator
{
  private $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  /**
   * Мигрировать все данные
   */
  public function migrateAll()
  {
    echo "Начинаем миграцию данных...\n";

    try {
      $this->db->beginTransaction();

      $this->migrateProducts();
      // $this->migrateOrders(); // Пропускаем заказы пока
      $this->migrateMeditations();
      $this->migrateReviews();
      $this->migrateArticles();

      $this->db->commit();
      echo "Миграция завершена успешно!\n";

    } catch (Exception $e) {
      $this->db->rollback();
      echo "Ошибка миграции: " . $e->getMessage() . "\n";
      throw $e;
    }
  }

  /**
   * Мигрировать продукты
   */
  private function migrateProducts()
  {
    echo "Мигрируем продукты...\n";

    $jsonFile = __DIR__ . '/../data/products.json';
    if (!file_exists($jsonFile)) {
      echo "Файл products.json не найден\n";
      return;
    }

    $products = json_decode(file_get_contents($jsonFile), true);
    if (!$products) {
      echo "Ошибка чтения products.json\n";
      return;
    }

    foreach ($products as $product) {
      // Получаем или создаем категорию
      $categoryId = null;
      if (!empty($product['category'])) {
        $categoryId = $this->getOrCreateCategory($product['category']);
      }

      $data = [
        'slug' => $product['slug'] ?? $product['id'],
        'title' => $product['title'],
        'description' => $product['description'],
        'short_description' => $product['short_description'] ?? null,
        'price' => $product['price'] ?? 0,
        'old_price' => $product['old_price'] ?? null,
        'currency' => $product['currency'] ?? 'RUB',
        'category_id' => $categoryId,
        'type' => $this->mapProductType($product['type'] ?? 'digital'),
        'image' => $product['image'] ?? null,
        'gallery' => !empty($product['gallery']) ? json_encode($product['gallery']) : null,
        'features' => !empty($product['features']) ? json_encode($product['features']) : null,
        'content' => $product['content'] ?? null,
        'download_url' => $product['download_url'] ?? null,
        'telegram_required' => isset($product['telegram_required']) && $product['telegram_required'] === true ? 1 : 0,
        'whatsapp_contact' => $product['whatsapp_contact'] ?? null,
        'telegram_contact' => $product['telegram_contact'] ?? null,
        'in_stock' => $product['in_stock'] === true ? 1 : 0,
        'is_featured' => $product['is_featured'] === true ? 1 : 0,
        'tags' => !empty($product['tags']) ? json_encode($product['tags']) : null,
        'meta_title' => $product['meta_title'] ?? null,
        'meta_description' => $product['meta_description'] ?? null
      ];

      // Проверяем, существует ли уже продукт
      $existing = $this->db->fetchOne("SELECT id FROM products WHERE slug = ?", [$data['slug']]);
      if ($existing) {
        echo "Продукт {$data['slug']} уже существует, обновляем...\n";
        $this->db->update('products', $data, 'slug = ?', [$data['slug']]);
      } else {
        echo "Добавляем продукт {$data['slug']}...\n";
        $this->db->insert('products', $data);
      }
    }

    echo "Продукты мигрированы\n";
  }

  /**
   * Мигрировать заказы
   */
  private function migrateOrders()
  {
    echo "Мигрируем заказы...\n";

    $jsonFile = __DIR__ . '/../data/orders.json';
    if (!file_exists($jsonFile)) {
      echo "Файл orders.json не найден\n";
      return;
    }

    $ordersData = json_decode(file_get_contents($jsonFile), true);
    if (!$ordersData || !isset($ordersData['orders'])) {
      echo "Ошибка чтения orders.json\n";
      return;
    }

    foreach ($ordersData['orders'] as $order) {
      // Создаем заказ
      $orderData = [
        'order_number' => $order['id'],
        'email' => $order['email'],
        'name' => 'Клиент', // Добавляем имя по умолчанию
        'status' => $this->mapOrderStatus($order['status']),
        'total_amount' => $order['total'],
        'currency' => 'RUB',
        'payment_method' => $order['payment_method'] ?? 'card',
        'payment_status' => $order['status'] === 'completed' ? 'paid' : 'pending'
      ];

      // Проверяем, существует ли уже заказ
      $existing = $this->db->fetchOne("SELECT id FROM orders WHERE order_number = ?", [$orderData['order_number']]);
      if ($existing) {
        echo "Заказ {$orderData['order_number']} уже существует, пропускаем...\n";
        continue;
      }

      echo "Добавляем заказ {$orderData['order_number']}...\n";
      $orderId = $this->db->insert('orders', $orderData);

      // Добавляем элементы заказа
      if (!empty($order['items'])) {
        foreach ($order['items'] as $item) {
          $itemData = [
            'order_id' => $orderId,
            'product_id' => $item['id'],
            'product_title' => $item['title'],
            'quantity' => $item['quantity'] ?? 1,
            'price' => $item['price'],
            'total_price' => ($item['quantity'] ?? 1) * $item['price']
          ];

          $this->db->insert('order_items', $itemData);
        }
      }
    }

    echo "Заказы мигрированы\n";
  }

  /**
   * Мигрировать медитации
   */
  private function migrateMeditations()
  {
    echo "Мигрируем медитации...\n";

    $jsonFile = __DIR__ . '/../data/meditations.json';
    if (!file_exists($jsonFile)) {
      echo "Файл meditations.json не найден\n";
      return;
    }

    $meditationsData = json_decode(file_get_contents($jsonFile), true);
    if (!$meditationsData) {
      echo "Ошибка чтения meditations.json\n";
      return;
    }

    // Мигрируем категории медитаций
    if (!empty($meditationsData['categories'])) {
      foreach ($meditationsData['categories'] as $category) {
        $categoryData = [
          'slug' => $category['id'],
          'name' => $category['name'],
          'description' => $category['description'] ?? null,
          'icon' => $category['icon'] ?? null,
          'sort_order' => $category['sort_order'] ?? 0
        ];

        $existing = $this->db->fetchOne("SELECT id FROM meditation_categories WHERE slug = ?", [$categoryData['slug']]);
        if (!$existing) {
          $this->db->insert('meditation_categories', $categoryData);
        }
      }
    }

    // Мигрируем медитации
    if (!empty($meditationsData['meditations'])) {
      foreach ($meditationsData['meditations'] as $meditation) {
        $categoryId = null;
        if (!empty($meditation['category'])) {
          $category = $this->db->fetchOne("SELECT id FROM meditation_categories WHERE slug = ?", [$meditation['category']]);
          $categoryId = $category ? $category['id'] : null;
        }

        $meditationData = [
          'slug' => $meditation['id'],
          'title' => $meditation['title'],
          'subtitle' => $meditation['subtitle'] ?? null,
          'category_id' => $categoryId,
          'duration' => $meditation['duration'] ?? null,
          'description' => $meditation['description'] ?? null,
          'audio_file' => $meditation['audio_file'] ?? null,
          'icon' => $meditation['icon'] ?? null,
          'meta_description' => $meditation['meta_description'] ?? null,
          'likes' => $meditation['likes'] ?? 0,
          'favorites' => $meditation['favorites'] ?? 0,
          'is_free' => isset($meditation['is_free']) && $meditation['is_free'] === true ? 1 : 0,
          'telegram_required' => isset($meditation['telegram_required']) && $meditation['telegram_required'] === true ? 1 : 0
        ];

        $existing = $this->db->fetchOne("SELECT id FROM meditations WHERE slug = ?", [$meditationData['slug']]);
        if ($existing) {
          echo "Медитация {$meditationData['slug']} уже существует, обновляем...\n";
          $this->db->update('meditations', $meditationData, 'slug = ?', [$meditationData['slug']]);
        } else {
          echo "Добавляем медитацию {$meditationData['slug']}...\n";
          $this->db->insert('meditations', $meditationData);
        }
      }
    }

    echo "Медитации мигрированы\n";
  }

  /**
   * Мигрировать отзывы
   */
  private function migrateReviews()
  {
    echo "Мигрируем отзывы...\n";

    $jsonFile = __DIR__ . '/../data/reviews.json';
    if (!file_exists($jsonFile)) {
      echo "Файл reviews.json не найден\n";
      return;
    }

    $reviews = json_decode(file_get_contents($jsonFile), true);
    if (!$reviews) {
      echo "Ошибка чтения reviews.json\n";
      return;
    }

    foreach ($reviews as $review) {
      // Проверяем обязательные поля
      if (empty($review['name']) || empty($review['email']) || empty($review['rating']) || empty($review['text'])) {
        echo "Пропускаем отзыв с неполными данными...\n";
        continue;
      }

      $reviewData = [
        'type' => $review['type'] ?? 'text',
        'name' => $review['name'],
        'email' => $review['email'],
        'telegram_username' => $review['telegram_username'] ?? null,
        'telegram_avatar' => $review['telegram_avatar'] ?? null,
        'phone' => $review['phone'] ?? null,
        'rating' => $review['rating'],
        'text' => $review['text'],
        'age' => $review['age'] ?? null,
        'tags' => !empty($review['tags']) ? json_encode($review['tags']) : null,
        'image' => $review['image'] ?? null,
        'image_type' => $review['image_type'] ?? null,
        'video' => $review['video'] ?? null,
        'thumbnail' => $review['thumbnail'] ?? null,
        'status' => $review['status'] ?? 'approved',
        'verification_method' => $review['verification_method'] ?? 'none',
        'verification_status' => isset($review['verification_status']) && $review['verification_status'] === true ? 1 : 0
      ];

      // Проверяем, существует ли уже отзыв
      $existing = $this->db->fetchOne(
        "SELECT id FROM reviews WHERE email = ? AND text = ? AND created_at = ?",
        [$reviewData['email'], $reviewData['text'], $review['created_at'] ?? date('Y-m-d H:i:s')]
      );

      if (!$existing) {
        echo "Добавляем отзыв от {$reviewData['name']}...\n";
        $this->db->insert('reviews', $reviewData);
      } else {
        echo "Отзыв от {$reviewData['name']} уже существует, пропускаем...\n";
      }
    }

    echo "Отзывы мигрированы\n";
  }

  /**
   * Мигрировать статьи
   */
  private function migrateArticles()
  {
    echo "Мигрируем статьи...\n";

    $articlesDir = __DIR__ . '/../articles/articles/';
    if (!is_dir($articlesDir)) {
      echo "Директория статей не найдена\n";
      return;
    }

    $jsonFiles = glob($articlesDir . '*.json');
    foreach ($jsonFiles as $jsonFile) {
      $articleData = json_decode(file_get_contents($jsonFile), true);
      if (!$articleData) {
        echo "Ошибка чтения файла: " . basename($jsonFile) . "\n";
        continue;
      }

      $categoryId = null;
      if (!empty($articleData['category'])) {
        $categoryId = $this->getOrCreateArticleCategory($articleData['category']);
      }

      $article = [
        'slug' => $articleData['slug'] ?? pathinfo($jsonFile, PATHINFO_FILENAME),
        'title' => $articleData['title'],
        'excerpt' => $articleData['excerpt'] ?? null,
        'content' => $articleData['content'],
        'category_id' => $categoryId,
        'author' => $articleData['author'] ?? 'Денис Черкас',
        'meta_title' => $articleData['meta_title'] ?? null,
        'meta_description' => $articleData['meta_description'] ?? null,
        'featured_image' => $articleData['featured_image'] ?? null,
        'is_published' => isset($articleData['is_published']) && $articleData['is_published'] === true ? 1 : 0,
        'published_at' => $articleData['published_at'] ?? date('Y-m-d H:i:s')
      ];

      $existing = $this->db->fetchOne("SELECT id FROM articles WHERE slug = ?", [$article['slug']]);
      if ($existing) {
        echo "Статья {$article['slug']} уже существует, обновляем...\n";
        $this->db->update('articles', $article, 'slug = ?', [$article['slug']]);
      } else {
        echo "Добавляем статью {$article['slug']}...\n";
        $this->db->insert('articles', $article);
      }
    }

    echo "Статьи мигрированы\n";
  }

  /**
   * Получить или создать категорию товара
   */
  private function getOrCreateCategory($categorySlug)
  {
    $category = $this->db->fetchOne("SELECT id FROM product_categories WHERE slug = ?", [$categorySlug]);
    if ($category) {
      return $category['id'];
    }

    // Создаем новую категорию
    $categoryData = [
      'slug' => $categorySlug,
      'name' => ucfirst($categorySlug),
      'description' => 'Категория ' . ucfirst($categorySlug)
    ];

    return $this->db->insert('product_categories', $categoryData);
  }

  /**
   * Получить или создать категорию статьи
   */
  private function getOrCreateArticleCategory($categorySlug)
  {
    $category = $this->db->fetchOne("SELECT id FROM article_categories WHERE slug = ?", [$categorySlug]);
    if ($category) {
      return $category['id'];
    }

    // Создаем новую категорию
    $categoryData = [
      'slug' => $categorySlug,
      'name' => ucfirst($categorySlug),
      'description' => 'Категория ' . ucfirst($categorySlug)
    ];

    return $this->db->insert('article_categories', $categoryData);
  }

  /**
   * Маппинг типов продуктов
   */
  private function mapProductType($type)
  {
    $mapping = [
      'digital' => 'digital',
      'physical' => 'physical',
      'service' => 'service',
      'free' => 'free',
      'discussion' => 'service'
    ];

    return $mapping[$type] ?? 'digital';
  }

  /**
   * Маппинг статусов заказов
   */
  private function mapOrderStatus($status)
  {
    $mapping = [
      'pending' => 'pending',
      'processing' => 'processing',
      'completed' => 'completed',
      'delivered' => 'completed',
      'cancelled' => 'cancelled',
      'refunded' => 'refunded'
    ];

    return $mapping[$status] ?? 'pending';
  }
}

// Запуск миграции
if (php_sapi_name() === 'cli') {
  $migrator = new DataMigrator();
  $migrator->migrateAll();
} else {
  echo "Этот скрипт должен запускаться из командной строки\n";
}
