<?php
/**
 * Класс для работы с товарами/продуктами
 */
class Product
{
  private $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  /**
   * Получить все продукты
   */
  public function getAll($filters = [])
  {
    $sql = "SELECT p.*, pc.name as category_name 
                FROM products p 
                LEFT JOIN product_categories pc ON p.category_id = pc.id 
                WHERE (p.status IS NULL OR p.status = '' OR p.status = 'active') 
                  AND (p.in_stock = 1 OR p.in_stock IS NULL OR p.type IN ('digital','free','service'))
                  AND (p.is_active = 1 OR p.is_active IS NULL)";

    $params = [];

    if (!empty($filters['category'])) {
      $sql .= " AND pc.slug = ?";
      $params[] = $filters['category'];
    }

    if (!empty($filters['type'])) {
      $sql .= " AND p.type = ?";
      $params[] = $filters['type'];
    }

    if (isset($filters['is_featured'])) {
      $sql .= " AND p.is_featured = ?";
      $params[] = $filters['is_featured'];
    }

    $sql .= " ORDER BY p.is_featured DESC, p.created_at DESC";

    // Используем кэширование (15 минут) для списка продуктов
    $products = $this->db->fetchAllCached($sql, $params, 900);

    // Ensure JSON-ish fields are properly decoded for all products
    foreach ($products as &$product) {
      if (!empty($product['gallery'])) {
        $gallery = json_decode($product['gallery'], true);
        if (is_array($gallery)) {
          $product['gallery'] = $gallery;
        } else {
          $product['gallery'] = [];
        }
      } else {
        $product['gallery'] = [];
      }

      // Загружаем ярлыки для каждого товара
      $product['badges'] = $this->getProductBadges($product['id']);

      // Normalize features to array of human-readable strings for grids
      if (!empty($product['features'])) {
        $features = is_array($product['features']) ? $product['features'] : (json_decode($product['features'], true) ?: $product['features']);
        $normalized = [];
        if (is_array($features)) {
          foreach ($features as $f) {
            if (is_array($f)) {
              $name = trim($f['name'] ?? '');
              $value = trim($f['value'] ?? '');
              $normalized[] = trim($name . ($value !== '' ? (': ' . $value) : ''));
            } else {
              $normalized[] = (string) $f;
            }
          }
        } else {
          $normalized[] = (string) $features;
        }
        $product['features'] = array_values(array_filter($normalized, fn($s) => $s !== ''));
      } else {
        $product['features'] = [];
      }

      // Normalize tags to array for search/filters
      if (!empty($product['tags'])) {
        if (is_string($product['tags'])) {
          $decoded = json_decode($product['tags'], true);
          if (is_array($decoded)) {
            $product['tags'] = $decoded;
          } else {
            $product['tags'] = array_values(array_filter(array_map('trim', explode(',', $product['tags'])), fn($v) => $v !== ''));
          }
        }
      } else {
        $product['tags'] = [];
      }
    }

    return $products;
  }

  /**
   * Получить продукт по slug
   */
  public function getBySlug($slug)
  {
    $sql = "SELECT p.*, pc.name as category_name, pc.slug as category_slug 
                FROM products p 
                LEFT JOIN product_categories pc ON p.category_id = pc.id 
                WHERE p.slug = ?";

    $product = $this->db->fetchOne($sql, [$slug]);

    if (!$product) {
      return null;
    }

    // Ensure gallery is properly decoded
    if (!empty($product['gallery'])) {
      $gallery = json_decode($product['gallery'], true);
      if (is_array($gallery)) {
        $product['gallery'] = $gallery;
      } else {
        $product['gallery'] = [];
      }
    } else {
      $product['gallery'] = [];
    }

    // Загружаем ярлыки товара
    $product['badges'] = $this->getProductBadges($product['id']);

    return $product;
  }

  /**
   * Получить продукт по ID
   */
  public function getById($id)
  {
    $sql = "SELECT p.*, pc.name as category_name, pc.slug as category_slug 
                FROM products p 
                LEFT JOIN product_categories pc ON p.category_id = pc.id 
                WHERE p.id = ?";

    $product = $this->db->fetchOne($sql, [$id]);

    // Ensure gallery is properly decoded
    if (!empty($product['gallery'])) {
      $gallery = json_decode($product['gallery'], true);
      if (is_array($gallery)) {
        $product['gallery'] = $gallery;
      } else {
        $product['gallery'] = [];
      }
    } else {
      $product['gallery'] = [];
    }

    return $product;
  }

  /**
   * Получить избранные продукты
   */
  public function getFeatured($limit = 6)
  {
    $sql = "SELECT p.*, pc.name as category_name 
                FROM products p 
                LEFT JOIN product_categories pc ON p.category_id = pc.id 
                WHERE p.is_featured = 1 AND p.in_stock = 1 
                ORDER BY p.created_at DESC 
                LIMIT ?";

    return $this->db->fetchAll($sql, [$limit]);
  }

  /**
   * Поиск продуктов
   */
  public function search($query, $limit = 20)
  {
    $sql = "SELECT p.*, pc.name as category_name 
                FROM products p 
                LEFT JOIN product_categories pc ON p.category_id = pc.id 
                WHERE p.in_stock = 1 
                AND (p.title LIKE ? OR p.description LIKE ? OR p.short_description LIKE ?) 
                ORDER BY p.is_featured DESC, p.created_at DESC 
                LIMIT ?";

    $searchTerm = "%$query%";
    return $this->db->fetchAll($sql, [$searchTerm, $searchTerm, $searchTerm, $limit]);
  }

  /**
   * Получить продукты по категории
   */
  public function getByCategory($categorySlug, $limit = null)
  {
    $sql = "SELECT p.*, pc.name as category_name 
                FROM products p 
                LEFT JOIN product_categories pc ON p.category_id = pc.id 
                WHERE pc.slug = ? AND p.in_stock = 1 
                ORDER BY p.is_featured DESC, p.created_at DESC";

    if ($limit) {
      $sql .= " LIMIT ?";
      return $this->db->fetchAll($sql, [$categorySlug, $limit]);
    }

    return $this->db->fetchAll($sql, [$categorySlug]);
  }

  /**
   * Создать новый продукт
   */
  public function create($data)
  {
    // Обработка JSON полей
    if (!empty($data['gallery']) && is_array($data['gallery'])) {
      $data['gallery'] = json_encode($data['gallery']);
    }

    if (!empty($data['features']) && is_array($data['features'])) {
      $data['features'] = json_encode($data['features']);
    }

    if (!empty($data['tags']) && is_array($data['tags'])) {
      $data['tags'] = json_encode($data['tags']);
    }

    $productId = $this->db->insert('products', $data);

    // Логируем действие
    $this->db->logActivity('product_created', 'product', $productId);

    return $productId;
  }

  /**
   * Обновить продукт
   */
  public function update($id, $data)
  {
    // Обработка JSON полей
    if (!empty($data['gallery']) && is_array($data['gallery'])) {
      $data['gallery'] = json_encode($data['gallery']);
    }

    if (!empty($data['features']) && is_array($data['features'])) {
      $data['features'] = json_encode($data['features']);
    }

    if (!empty($data['tags']) && is_array($data['tags'])) {
      $data['tags'] = json_encode($data['tags']);
    }

    $result = $this->db->update('products', $data, 'id = ?', [$id]);

    // Логируем действие
    $this->db->logActivity('product_updated', 'product', $id);

    return $result;
  }

  /**
   * Удалить продукт
   */
  public function delete($id)
  {
    $result = $this->db->delete('products', 'id = ?', [$id]);

    // Логируем действие
    $this->db->logActivity('product_deleted', 'product', $id);

    return $result;
  }

  /**
   * Получить все категории
   */
  public function getCategories()
  {
    return $this->db->fetchAll("SELECT * FROM product_categories WHERE is_active = 1 ORDER BY sort_order, name");
  }

  /**
   * Получить категорию по slug
   */
  public function getCategoryBySlug($slug)
  {
    return $this->db->fetchOne("SELECT * FROM product_categories WHERE slug = ? AND is_active = 1", [$slug]);
  }

  /**
   * Создать категорию
   */
  public function createCategory($data)
  {
    $categoryId = $this->db->insert('product_categories', $data);

    // Логируем действие
    $this->db->logActivity('category_created', 'product_category', $categoryId);

    return $categoryId;
  }

  /**
   * Обновить категорию
   */
  public function updateCategory($id, $data)
  {
    $result = $this->db->update('product_categories', $data, 'id = ?', [$id]);

    // Логируем действие
    $this->db->logActivity('category_updated', 'product_category', $id);

    return $result;
  }

  /**
   * Удалить категорию
   */
  public function deleteCategory($id)
  {
    $result = $this->db->delete('product_categories', 'id = ?', [$id]);

    // Логируем действие
    $this->db->logActivity('category_deleted', 'product_category', $id);

    return $result;
  }

  /**
   * Получить ярлыки товара
   */
  public function getProductBadges($productId)
  {
    $sql = "SELECT pb.id, pb.name, pb.slug, pb.color, pb.background_color
            FROM product_badge_relations pbr
            JOIN product_badges pb ON pbr.badge_id = pb.id
            WHERE pbr.product_id = ? AND pb.is_active = 1
            ORDER BY pb.sort_order, pb.name";

    return $this->db->fetchAll($sql, [$productId]);
  }
}

