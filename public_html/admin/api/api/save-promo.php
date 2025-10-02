<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['success' => false, 'error' => 'Method not allowed']);
  exit();
}

session_start();

// Проверяем авторизацию
require_once '../includes/auth.php';
if (!isLoggedIn()) {
  http_response_code(401);
  echo json_encode(['success' => false, 'error' => 'Unauthorized']);
  exit();
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
  echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
  exit();
}

// Подключаем модель промокодов
require_once '../../includes/Models/PromoCode.php';

try {
  $promoModel = new PromoCode();

  // Валидация данных
  $errors = [];

  if (empty($data['code'])) {
    $errors[] = 'Код промокода обязателен';
  }

  if (empty($data['name'])) {
    $errors[] = 'Название промокода обязательно';
  }

  if (!isset($data['type']) || !in_array($data['type'], ['percentage', 'fixed'])) {
    $errors[] = 'Неверный тип скидки';
  }

  if (!isset($data['value']) || $data['value'] <= 0) {
    $errors[] = 'Значение скидки должно быть больше 0';
  }

  if ($data['type'] === 'percentage' && $data['value'] > 100) {
    $errors[] = 'Процентная скидка не может быть больше 100%';
  }

  if (isset($data['min_amount']) && $data['min_amount'] < 0) {
    $errors[] = 'Минимальная сумма не может быть отрицательной';
  }

  if (isset($data['max_uses']) && $data['max_uses'] < 0) {
    $errors[] = 'Максимальное количество использований не может быть отрицательным';
  }

  if (!empty($errors)) {
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit();
  }

  // Подготавливаем данные
  $promoData = [
    'code' => strtoupper(trim($data['code'])),
    'name' => trim($data['name']),
    'description' => trim($data['description'] ?? ''),
    'type' => $data['type'],
    'value' => (float) $data['value'],
    'min_amount' => (float) ($data['min_amount'] ?? 0),
    'max_uses' => !empty($data['max_uses']) ? (int) $data['max_uses'] : null,
    'valid_from' => !empty($data['valid_from']) ? $data['valid_from'] : null,
    'valid_until' => !empty($data['valid_until']) ? $data['valid_until'] : null,
    'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : true
  ];

  // Проверяем уникальность кода (если это новый промокод)
  if (empty($data['id'])) {
    $existingPromo = $promoModel->getByCode($promoData['code']);
    if ($existingPromo) {
      echo json_encode(['success' => false, 'error' => 'Промокод с таким кодом уже существует']);
      exit();
    }

    // Создаем новый промокод
    $promoId = $promoModel->create($promoData);
    $message = 'Промокод успешно создан';
  } else {
    // Обновляем существующий промокод
    $existingPromo = $promoModel->getById($data['id']);
    if (!$existingPromo) {
      echo json_encode(['success' => false, 'error' => 'Промокод не найден']);
      exit();
    }

    // Проверяем уникальность кода (если код изменился)
    if ($existingPromo['code'] !== $promoData['code']) {
      $existingPromoWithNewCode = $promoModel->getByCode($promoData['code']);
      if ($existingPromoWithNewCode) {
        echo json_encode(['success' => false, 'error' => 'Промокод с таким кодом уже существует']);
        exit();
      }
    }

    $promoModel->update($data['id'], $promoData);
    $promoId = $data['id'];
    $message = 'Промокод успешно обновлен';
  }

  // Получаем обновленные данные
  $updatedPromo = $promoModel->getById($promoId);

  echo json_encode([
    'success' => true,
    'message' => $message,
    'promo' => $updatedPromo
  ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
  error_log("Error in save-promo.php: " . $e->getMessage());
  echo json_encode(['success' => false, 'error' => 'Произошла ошибка при сохранении промокода']);
}
?>


