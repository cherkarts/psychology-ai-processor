<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');

require_once __DIR__ . '/../includes/functions.php';

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Проверяем CSRF токен
$csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? '');
if (!verifyCSRFToken($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

try {
    // Получаем данные формы
    $formData = $_POST;

    // Тип формы
    $formType = isset($formData['form_type']) ? trim($formData['form_type']) : 'Контактная форма';

    // Валидация
    $errors = [];
    if (mb_strtolower($formType) === mb_strtolower('Скачать файл')) {
        $deliveryMethod = trim($formData['delivery_method'] ?? '');
        $contactInfo = trim($formData['contact_info'] ?? '');
        if ($deliveryMethod === '') {
            $errors[] = 'Выберите способ получения';
        }
        if ($contactInfo === '') {
            $errors[] = 'Укажите контакт для отправки файла';
        }
    } else {
        if (empty($formData['phone'])) {
            $errors[] = 'Номер телефона обязателен';
        }
        if (empty($formData['name'])) {
            $errors[] = 'Имя обязательно';
        }
        $digits = preg_replace('/[^0-9]/', '', $formData['phone'] ?? '');
        if ($digits !== '' && strlen($digits) < 10) {
            $errors[] = 'Неверный формат номера телефона';
        }
    }

    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
        exit;
    }

    // Подготавливаем данные
    $name = e(trim($formData['name'] ?? 'Не указано'));
    $phone = e(trim($formData['phone'] ?? 'Не указано'));
    $userMessage = e(trim($formData['message'] ?? ''));
    // Приводим время звонка к человеко-читаемой метке
    $timeValue = trim($formData['time'] ?? '');
    $timeLabel = trim($formData['time_label'] ?? '');
    if ($timeLabel === '') {
        $timeMap = [
            'now' => 'Перезвоните сейчас',
            'morning' => 'Утром (9-12)',
            'afternoon' => 'Днем (12-18)',
            'evening' => 'Вечером (18-22)'
        ];
        $timeLabel = $timeMap[$timeValue] ?? ($timeValue ?: 'Не указано');
    }
    $time = e($timeLabel);
    // Нормализуем username Телеграма на сервере (доп. защита)
    $deliveryMethod = e(trim($formData['delivery_method'] ?? ''));
    $contactInfoRaw = trim($formData['contact_info'] ?? '');
    if ($deliveryMethod === 'telegram') {
        $username = trim($formData['telegram_username'] ?? $contactInfoRaw);
        // Запрещаем кириллицу
        if (preg_match('/[А-Яа-яЁё]/u', $username)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Username должен содержать только латиницу, цифры и _']);
            exit;
        }
        $username = preg_replace('/^@+/', '', $username);
        $username = preg_replace('/[^a-zA-Z0-9_]/', '', $username);
        $contactInfo = '@' . $username;
    } else {
        $contactInfo = e($contactInfoRaw);
    }
    $referrer = isset($_SERVER['HTTP_REFERER']) ? e($_SERVER['HTTP_REFERER']) : 'Прямой переход';

    // Формируем HTML сообщение (parse_mode HTML в sendToTelegram)
    $message = "🔔 <b>Новая заявка с сайта</b>\n\n";
    $message .= "📋 <b>Тип формы:</b> " . e($formType) . "\n";
    if (!empty($formData['form_source'])) {
        $message .= "🧭 <b>Источник формы:</b> " . e($formData['form_source']) . "\n";
    }
    if (mb_strtolower($formType) === mb_strtolower('Скачать файл')) {
        $message .= "📘 <b>Материал:</b> Как подготовиться к консультации\n";
        if ($deliveryMethod) {
            $message .= "🚚 <b>Способ получения:</b> {$deliveryMethod}\n";
        }
        if ($contactInfo) {
            $message .= "👤 <b>Контакт:</b> {$contactInfo}\n";
        }
    } else {
        $message .= "👤 <b>Имя:</b> {$name}\n";
        $message .= "📞 <b>Телефон:</b> {$phone}\n";
        $message .= "⏰ <b>Время звонка:</b> {$time}\n";
        if (!empty($userMessage)) {
            $message .= "💬 <b>Сообщение:</b> {$userMessage}\n";
        }
    }
    $message .= "🌐 <b>Источник:</b> {$referrer}\n";
    $message .= "📅 <b>Дата:</b> " . date('d.m.Y H:i:s') . "\n";
    $message .= "🖥️ <b>IP:</b> " . e($_SERVER['REMOTE_ADDR']) . "\n";

    // Отправляем в Telegram
    $telegramResult = sendToTelegram($message);

    // Логируем заявку
    logContactAction('contact_form', [
        'name' => $name,
        'phone' => $phone,
        'time' => $time,
        'message' => $userMessage,
        'form_type' => $formType,
        'delivery_method' => $deliveryMethod,
        'contact_info' => $contactInfo,
        'telegram_sent' => $telegramResult
    ]);

    // Возвращаем успешный ответ
    echo json_encode([
        'success' => true,
        'message' => 'Спасибо! Мы свяжемся с вами в ближайшее время.',
        'telegram_sent' => $telegramResult
    ]);

} catch (Exception $e) {
    error_log('Contact form error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Произошла ошибка. Попробуйте еще раз.'
    ]);
}

// Отправка в Telegram выполняется общей функцией sendToTelegram() из includes/functions.php

/**
 * Логирование действий
 */
function logContactAction($action, $data)
{
    $logFile = '../logs/actions.log';
    $logDir = dirname($logFile);

    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'action' => $action,
        'data' => $data,
        'ip' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
    ];

    file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);
}
?>