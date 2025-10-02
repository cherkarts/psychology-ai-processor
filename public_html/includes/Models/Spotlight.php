<?php
/**
 * Spotlight model for managing site showcase units (neutral naming for DOM)
 */
class Spotlight
{
  /** @var Database */
  private $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  /**
   * Get all spotlights with optional filters
   * @param array $filters
   * @return array
   */
  public function getAll(array $filters = [])
  {
    $sql = "SELECT * FROM spotlights WHERE 1=1";
    $params = [];

    if (isset($filters['is_active'])) {
      $sql .= " AND is_active = ?";
      $params[] = (int) $filters['is_active'];
    }

    if (!empty($filters['context'])) {
      // contexts stored as comma-separated list; use FIND_IN_SET for simplicity
      $sql .= " AND (contexts = 'all' OR FIND_IN_SET(?, contexts))";
      $params[] = $filters['context'];
    }

    if (!empty($filters['group_key'])) {
      $sql .= " AND group_key = ?";
      $params[] = $filters['group_key'];
    }

    // Active-by-date window
    if (!empty($filters['only_current'])) {
      $now = date('Y-m-d H:i:s');
      $sql .= " AND (starts_at IS NULL OR starts_at <= ?) AND (ends_at IS NULL OR ends_at >= ?)";
      $params[] = $now;
      $params[] = $now;
    }

    $sql .= " ORDER BY sort_order ASC, created_at DESC";

    return $this->db->fetchAll($sql, $params) ?: [];
  }

  /**
   * Get active spotlights for a specific context, within active date window
   * @param string $context
   * @param int|null $limit
   * @return array
   */
  public function getActiveByContext(string $context, ?int $limit = null)
  {
    $now = date('Y-m-d H:i:s');
    $sql = "SELECT * FROM spotlights 
                WHERE is_active = 1
                  AND (starts_at IS NULL OR starts_at <= ?)
                  AND (ends_at IS NULL OR ends_at >= ?)
                  AND (contexts = 'all' OR FIND_IN_SET(?, contexts))
                ORDER BY sort_order ASC, created_at DESC";
    $params = [$now, $now, $context];
    if ($limit !== null) {
      $sql .= " LIMIT " . intval($limit);
    }
    return $this->db->fetchAll($sql, $params) ?: [];
  }

  public function getById(int $id)
  {
    return $this->db->fetchOne("SELECT * FROM spotlights WHERE id = ?", [$id]);
  }

  public function create(array $data)
  {
    $sql = "INSERT INTO spotlights 
                (title, body, cta_label, cta_url, media_type, media_url, bg_style, contexts, group_key, sort_order, is_active, starts_at, ends_at, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

    $params = [
      $data['title'] ?? '',
      $data['body'] ?? null,
      $data['cta_label'] ?? null,
      $data['cta_url'] ?? null,
      $data['media_type'] ?? 'none',
      $data['media_url'] ?? null,
      $data['bg_style'] ?? null,
      $data['contexts'] ?? 'all',
      $data['group_key'] ?? null,
      (int) ($data['sort_order'] ?? 0),
      (int) ($data['is_active'] ?? 1),
      $data['starts_at'] ?? null,
      $data['ends_at'] ?? null,
    ];

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $this->db->fetchColumn("SELECT LAST_INSERT_ID()");
  }

  public function update(int $id, array $data)
  {
    $sql = "UPDATE spotlights SET 
                    title = ?,
                    body = ?,
                    cta_label = ?,
                    cta_url = ?,
                    media_type = ?,
                    media_url = ?,
                    bg_style = ?,
                    contexts = ?,
                    group_key = ?,
                    sort_order = ?,
                    is_active = ?,
                    starts_at = ?,
                    ends_at = ?,
                    updated_at = NOW()
                WHERE id = ?";

    $params = [
      $data['title'] ?? '',
      $data['body'] ?? null,
      $data['cta_label'] ?? null,
      $data['cta_url'] ?? null,
      $data['media_type'] ?? 'none',
      $data['media_url'] ?? null,
      $data['bg_style'] ?? null,
      $data['contexts'] ?? 'all',
      $data['group_key'] ?? null,
      (int) ($data['sort_order'] ?? 0),
      (int) ($data['is_active'] ?? 1),
      $data['starts_at'] ?? null,
      $data['ends_at'] ?? null,
      $id
    ];

    $stmt = $this->db->prepare($sql);
    return $stmt->execute($params);
  }

  public function delete(int $id)
  {
    return $this->db->execute("DELETE FROM spotlights WHERE id = ?", [$id]);
  }
}


