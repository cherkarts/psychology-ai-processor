<?php
/**
 * Класс для работы со статьями
 */
class Article
{
  private $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  /**
   * Получить все статьи с фильтрами
   */
  public function getAll($filters = [])
  {
    $sql = "SELECT a.*, ac.name as category_name, ac.slug as category_slug 
                FROM articles a 
                LEFT JOIN article_categories ac ON a.category_id = ac.id 
                WHERE 1=1";
    $params = [];

    if (!empty($filters['category'])) {
      $sql .= " AND ac.slug = ?";
      $params[] = $filters['category'];
    }

    if (isset($filters['is_published'])) {
      $sql .= " AND a.is_published = ?";
      $params[] = $filters['is_published'];
    }

    $sql .= " ORDER BY a.created_at DESC";

    if (!empty($filters['limit'])) {
      $sql .= " LIMIT ?";
      $params[] = $filters['limit'];
    }

    return $this->db->fetchAll($sql, $params);
  }

  /**
   * Получить опубликованные статьи
   */
  public function getPublished($limit = null)
  {
    $sql = "SELECT a.*, ac.name as category_name, ac.slug as category_slug 
                FROM articles a 
                LEFT JOIN article_categories ac ON a.category_id = ac.id 
                WHERE a.is_published = 1 
                ORDER BY a.published_at DESC, a.created_at DESC";

    if ($limit) {
      $sql .= " LIMIT ?";
      return $this->db->fetchAll($sql, [$limit]);
    }

    return $this->db->fetchAll($sql);
  }

  /**
   * Получить статью по slug
   */
  public function getBySlug($slug)
  {
    $sql = "SELECT a.*, ac.name as category_name, ac.slug as category_slug 
                FROM articles a 
                LEFT JOIN article_categories ac ON a.category_id = ac.id 
                WHERE a.slug = ? AND a.is_published = 1";

    return $this->db->fetchOne($sql, [$slug]);
  }

  /**
   * Получить статью по ID
   */
  public function getById($id)
  {
    $sql = "SELECT a.*, ac.name as category_name, ac.slug as category_slug 
                FROM articles a 
                LEFT JOIN article_categories ac ON a.category_id = ac.id 
                WHERE a.id = ?";

    return $this->db->fetchOne($sql, [$id]);
  }

  /**
   * Получить статьи по категории
   */
  public function getByCategory($categorySlug, $limit = null)
  {
    $sql = "SELECT a.*, ac.name as category_name 
                FROM articles a 
                LEFT JOIN article_categories ac ON a.category_id = ac.id 
                WHERE ac.slug = ? AND a.is_published = 1 
                ORDER BY a.published_at DESC, a.created_at DESC";

    if ($limit) {
      $sql .= " LIMIT ?";
      return $this->db->fetchAll($sql, [$categorySlug, $limit]);
    }

    return $this->db->fetchAll($sql, [$categorySlug]);
  }

  /**
   * Создать новую статью
   */
  public function create($data)
  {
    // Обработка JSON полей
    if (!empty($data['tags']) && is_array($data['tags'])) {
      $data['tags'] = json_encode($data['tags']);
    }

    // Если статья публикуется, устанавливаем дату публикации
    if (!empty($data['is_published']) && $data['is_published']) {
      $data['published_at'] = date('Y-m-d H:i:s');
    }

    // Гарантируем наличие slug (NOT NULL + UNIQUE в БД)
    $title = trim($data['title'] ?? '');
    $slug = trim($data['slug'] ?? '');

    if ($slug === '') {
      // Пытаемся использовать глобальные хелперы, если доступны
      if (function_exists('generateUniqueSlug')) {
        $slug = generateUniqueSlug($title, 'articles');
      } else {
        // Локальная генерация slug + проверка уникальности
        $baseSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $title), '-'));
        if ($baseSlug === '') {
          $baseSlug = 'article';
        }

        $slug = $baseSlug;
        $counter = 1;
        // Проверяем уникальность в БД
        while (true) {
          $exists = $this->db->fetchColumn('SELECT COUNT(*) FROM articles WHERE slug = ?', [$slug]);
          if ((int) $exists === 0) {
            break;
          }
          $slug = $baseSlug . '-' . $counter;
          $counter++;
          if ($counter > 100) {
            // Форсируем уникальность, чтобы не зациклиться
            $slug = $baseSlug . '-' . uniqid();
            break;
          }
        }
      }
    }

    $data['slug'] = $slug;

    // Автор по умолчанию
    if (empty($data['author'])) {
      $data['author'] = 'Черкес Денис';
    }

    $articleId = $this->db->insert('articles', $data);

    // Логируем действие
    $this->db->logActivity('article_created', 'article', $articleId);

    return $articleId;
  }

  /**
   * Обновить статью
   */
  public function update($id, $data)
  {
    // Обработка JSON полей
    if (!empty($data['tags']) && is_array($data['tags'])) {
      $data['tags'] = json_encode($data['tags']);
    }

    // Если статья публикуется впервые, устанавливаем дату публикации
    if (!empty($data['is_published']) && $data['is_published']) {
      $currentArticle = $this->getById($id);
      if (!$currentArticle['published_at']) {
        $data['published_at'] = date('Y-m-d H:i:s');
      }
    }

    $result = $this->db->update('articles', $data, 'id = ?', [$id]);

    // Логируем действие
    $this->db->logActivity('article_updated', 'article', $id);

    return $result;
  }

  /**
   * Удалить статью
   */
  public function delete($id)
  {
    $result = $this->db->delete('articles', 'id = ?', [$id]);

    // Логируем действие
    $this->db->logActivity('article_deleted', 'article', $id);

    return $result;
  }

  /**
   * Опубликовать статью
   */
  public function publish($id)
  {
    $data = [
      'is_published' => 1,
      'published_at' => date('Y-m-d H:i:s')
    ];

    $result = $this->update($id, $data);

    // Логируем действие
    $this->db->logActivity('article_published', 'article', $id);

    return $result;
  }

  /**
   * Снять статью с публикации
   */
  public function unpublish($id)
  {
    $data = [
      'is_published' => 0,
      'published_at' => null
    ];

    $result = $this->update($id, $data);

    // Логируем действие
    $this->db->logActivity('article_unpublished', 'article', $id);

    return $result;
  }

  /**
   * Получить все категории статей
   */
  public function getCategories()
  {
    return $this->db->fetchAll("SELECT * FROM article_categories WHERE is_active = 1 ORDER BY sort_order, name");
  }

  /**
   * Получить категорию по slug
   */
  public function getCategoryBySlug($slug)
  {
    return $this->db->fetchOne("SELECT * FROM article_categories WHERE slug = ? AND is_active = 1", [$slug]);
  }

  /**
   * Создать категорию статей
   */
  public function createCategory($data)
  {
    $categoryId = $this->db->insert('article_categories', $data);

    // Логируем действие
    $this->db->logActivity('article_category_created', 'article_category', $categoryId);

    return $categoryId;
  }

  /**
   * Обновить категорию статей
   */
  public function updateCategory($id, $data)
  {
    $result = $this->db->update('article_categories', $data, 'id = ?', [$id]);

    // Логируем действие
    $this->db->logActivity('article_category_updated', 'article_category', $id);

    return $result;
  }

  /**
   * Удалить категорию статей
   */
  public function deleteCategory($id)
  {
    $result = $this->db->delete('article_categories', 'id = ?', [$id]);

    // Логируем действие
    $this->db->logActivity('article_category_deleted', 'article_category', $id);

    return $result;
  }

  /**
   * Поиск статей
   */
  public function search($query, $limit = 20)
  {
    $sql = "SELECT a.*, ac.name as category_name 
                FROM articles a 
                LEFT JOIN article_categories ac ON a.category_id = ac.id 
                WHERE a.is_published = 1 
                AND (a.title LIKE ? OR a.excerpt LIKE ? OR a.content LIKE ?) 
                ORDER BY a.published_at DESC, a.created_at DESC 
                LIMIT ?";

    $searchTerm = "%$query%";
    return $this->db->fetchAll($sql, [$searchTerm, $searchTerm, $searchTerm, $limit]);
  }

  /**
   * Получить статистику статей
   */
  public function getStats()
  {
    $sql = "SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN is_published = 1 THEN 1 END) as published_count,
                    COUNT(CASE WHEN is_published = 0 THEN 1 END) as draft_count
                FROM articles";

    return $this->db->fetchOne($sql);
  }

  /**
   * Получить последние статьи
   */
  public function getRecent($limit = 10)
  {
    $sql = "SELECT a.*, ac.name as category_name 
                FROM articles a 
                LEFT JOIN article_categories ac ON a.category_id = ac.id 
                WHERE a.is_published = 1 
                ORDER BY a.published_at DESC, a.created_at DESC 
                LIMIT ?";

    return $this->db->fetchAll($sql, [$limit]);
  }

  /**
   * Получить связанные статьи
   */
  public function getRelated($articleId, $limit = 5)
  {
    $currentArticle = $this->getById($articleId);
    if (!$currentArticle) {
      return [];
    }

    $sql = "SELECT a.*, ac.name as category_name 
                FROM articles a 
                LEFT JOIN article_categories ac ON a.category_id = ac.id 
                WHERE a.is_published = 1 
                AND a.id != ? 
                AND a.category_id = ? 
                ORDER BY a.published_at DESC 
                LIMIT ?";

    return $this->db->fetchAll($sql, [$articleId, $currentArticle['category_id'], $limit]);
  }

  /**
   * Увеличить счетчик просмотров (если нужно)
   */
  public function incrementViews($id)
  {
    // Можно добавить поле views в таблицу articles
    // $sql = "UPDATE articles SET views = views + 1 WHERE id = ?";
    // return $this->db->execute($sql, [$id]);
    return true;
  }

  /**
   * Получить количество лайков статьи
   */
  public function getLikesCount($articleId)
  {
    $sql = "SELECT COUNT(*) FROM article_likes WHERE article_id = ?";
    return $this->db->fetchColumn($sql, [$articleId]);
  }

  /**
   * Проверить, лайкнул ли пользователь статью
   */
  public function hasUserLiked($articleId, $userIdentifier)
  {
    $sql = "SELECT COUNT(*) FROM article_likes WHERE article_id = ? AND user_identifier = ?";
    $count = $this->db->fetchColumn($sql, [$articleId, $userIdentifier]);
    return $count > 0;
  }

  /**
   * Добавить лайк статье
   */
  public function addLike($articleId, $userIdentifier)
  {
    $sql = "INSERT IGNORE INTO article_likes (article_id, user_identifier) VALUES (?, ?)";
    return $this->db->execute($sql, [$articleId, $userIdentifier]);
  }

  /**
   * Убрать лайк со статьи
   */
  public function removeLike($articleId, $userIdentifier)
  {
    $sql = "DELETE FROM article_likes WHERE article_id = ? AND user_identifier = ?";
    return $this->db->execute($sql, [$articleId, $userIdentifier]);
  }
}
?>