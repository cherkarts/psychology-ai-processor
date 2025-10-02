<?php
// Ensure the Database class is available regardless of include context
if (!class_exists('Database')) {
  require_once __DIR__ . '/../Database.php';
}

class PromoCode
{
  private $db;

  public function __construct()
  {
    // Всегда инициализируем подключение через общий класс Database
    // (исключает ситуацию "No database selected" при вызовах из API)
    $this->db = Database::getInstance()->getConnection();
  }

  /**
   * Получить все промокоды
   */
  public function getAll($limit = null, $offset = null)
  {
    $sql = "SELECT * FROM promo_codes ORDER BY created_at DESC";

    if ($limit) {
      $sql .= " LIMIT " . (int) $limit;
      if ($offset) {
        $sql .= " OFFSET " . (int) $offset;
      }
    }

    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Получить промокод по ID
   */
  public function getById($id)
  {
    $stmt = $this->db->prepare("SELECT * FROM promo_codes WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  /**
   * Получить промокод по коду
   */
  public function getByCode($code)
  {
    $stmt = $this->db->prepare("SELECT * FROM promo_codes WHERE code = ? AND is_active = 1");
    $stmt->execute([$code]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  /**
   * Создать новый промокод
   */
  public function create($data)
  {
    $sql = "INSERT INTO promo_codes (code, name, description, type, value, min_amount, max_uses, valid_from, valid_until) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      $data['code'],
      $data['name'],
      $data['description'],
      $data['type'],
      $data['value'],
      $data['min_amount'],
      $data['max_uses'],
      $data['valid_from'] ?: null,
      $data['valid_until'] ?: null
    ]);

    return $this->db->lastInsertId();
  }

  /**
   * Обновить промокод
   */
  public function update($id, $data)
  {
    $sql = "UPDATE promo_codes SET 
                code = ?, name = ?, description = ?, type = ?, value = ?, 
                min_amount = ?, max_uses = ?, valid_from = ?, valid_until = ?, 
                is_active = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?";

    $stmt = $this->db->prepare($sql);
    return $stmt->execute([
      $data['code'],
      $data['name'],
      $data['description'],
      $data['type'],
      $data['value'],
      $data['min_amount'],
      $data['max_uses'],
      $data['valid_from'] ?: null,
      $data['valid_until'] ?: null,
      $data['is_active'],
      $id
    ]);
  }

  /**
   * Удалить промокод
   */
  public function delete($id)
  {
    $stmt = $this->db->prepare("DELETE FROM promo_codes WHERE id = ?");
    return $stmt->execute([$id]);
  }

  /**
   * Проверить валидность промокода
   */
  public function validatePromoCode($code, $cartTotal, $userEmail = null)
  {
    $promo = $this->getByCode($code);

    if (!$promo) {
      return ['valid' => false, 'error' => 'Промокод не найден'];
    }

    // Устанавливаем часовой пояс
    date_default_timezone_set('Europe/Moscow');

    // Проверка срока действия
    $now = new DateTime();
    if ($promo['valid_from'] && new DateTime($promo['valid_from']) > $now) {
      return ['valid' => false, 'error' => 'Промокод еще не активен'];
    }

    if ($promo['valid_until'] && new DateTime($promo['valid_until']) < $now) {
      return ['valid' => false, 'error' => 'Срок действия промокода истек'];
    }

    // Проверка минимальной суммы
    if ($cartTotal < $promo['min_amount']) {
      return [
        'valid' => false,
        'error' => 'Минимальная сумма для применения промокода: ' . number_format($promo['min_amount'], 0, ',', ' ') . ' ₽'
      ];
    }

    // Проверка лимита использования
    if ($promo['max_uses'] && $promo['used_count'] >= $promo['max_uses']) {
      return ['valid' => false, 'error' => 'Лимит использования промокода исчерпан'];
    }

    // Проверка использования одним пользователем (если указан email)
    if ($userEmail) {
      $stmt = $this->db->prepare("
                SELECT COUNT(*) as used_count 
                FROM promo_code_usage 
                WHERE promo_code_id = ? AND user_email = ?
            ");
      $stmt->execute([$promo['id'], $userEmail]);
      $userUsage = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($userUsage['used_count'] > 0) {
        return ['valid' => false, 'error' => 'Вы уже использовали этот промокод'];
      }
    }

    return ['valid' => true, 'promo' => $promo];
  }

  /**
   * Применить промокод
   */
  public function applyPromoCode($code, $cartTotal, $orderId = null, $userEmail = null)
  {
    $validation = $this->validatePromoCode($code, $cartTotal, $userEmail);

    if (!$validation['valid']) {
      return $validation;
    }

    $promo = $validation['promo'];

    // Рассчитываем скидку
    $discount = 0;
    if ($promo['type'] === 'percentage') {
      $discount = round($cartTotal * $promo['value'] / 100);
    } else {
      $discount = $promo['value'];
    }

    // Не даем скидку больше суммы заказа
    $discount = min($discount, $cartTotal);
    $finalTotal = $cartTotal - $discount;

    // Записываем использование промокода
    $this->recordUsage($promo['id'], $orderId, $userEmail, $discount, $cartTotal);

    // Увеличиваем счетчик использования
    $this->incrementUsageCount($promo['id']);

    return [
      'valid' => true,
      'promo_code' => $code,
      'discount' => $discount,
      'final_total' => $finalTotal,
      'description' => $promo['description']
    ];
  }

  /**
   * Записать использование промокода
   */
  private function recordUsage($promoId, $orderId, $userEmail, $discount, $orderTotal)
  {
    $sql = "INSERT INTO promo_code_usage (promo_code_id, order_id, user_email, discount_amount, order_total) 
                VALUES (?, ?, ?, ?, ?)";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$promoId, $orderId, $userEmail, $discount, $orderTotal]);
  }

  /**
   * Увеличить счетчик использования
   */
  private function incrementUsageCount($promoId)
  {
    $sql = "UPDATE promo_codes SET used_count = used_count + 1 WHERE id = ?";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$promoId]);
  }

  /**
   * Получить статистику использования промокодов
   */
  public function getUsageStats($promoId = null)
  {
    if ($promoId) {
      $sql = "SELECT 
                        pc.code,
                        pc.name,
                        pc.used_count,
                        pc.max_uses,
                        COALESCE(SUM(pcu.discount_amount), 0) as total_discount,
                        COUNT(pcu.id) as total_uses
                    FROM promo_codes pc
                    LEFT JOIN promo_code_usage pcu ON pc.id = pcu.promo_code_id
                    WHERE pc.id = ?
                    GROUP BY pc.id";

      $stmt = $this->db->prepare($sql);
      $stmt->execute([$promoId]);
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
      $sql = "SELECT 
                        pc.code,
                        pc.name,
                        pc.used_count,
                        pc.max_uses,
                        COALESCE(SUM(pcu.discount_amount), 0) as total_discount,
                        COUNT(pcu.id) as total_uses
                    FROM promo_codes pc
                    LEFT JOIN promo_code_usage pcu ON pc.id = pcu.promo_code_id
                    GROUP BY pc.id
                    ORDER BY pc.created_at DESC";

      $stmt = $this->db->prepare($sql);
      $stmt->execute();
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
  }

  /**
   * Получить количество промокодов
   */
  public function getCount()
  {
    $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM promo_codes");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['count'];
  }

  /**
   * Проверить валидность промокода с учетом времени
   */
  public function isValidPromo($promo)
  {
    // Устанавливаем часовой пояс
    date_default_timezone_set('Europe/Moscow');
    $now = new DateTime();

    // Проверяем активность
    if (!$promo['is_active']) {
      return false;
    }

    // Проверяем дату начала
    if ($promo['valid_from']) {
      $validFrom = new DateTime($promo['valid_from']);
      if ($validFrom > $now) {
        return false; // Промокод еще не начался
      }
    }

    // Проверяем дату окончания
    if ($promo['valid_until']) {
      $validUntil = new DateTime($promo['valid_until']);
      if ($validUntil < $now) {
        return false; // Промокод истек
      }
    }

    // Проверяем лимит использования
    if ($promo['max_uses'] && $promo['used_count'] >= $promo['max_uses']) {
      return false; // Лимит исчерпан
    }

    return true;
  }
}
?>