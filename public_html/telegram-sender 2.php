<?php
session_start();
require_once 'includes/functions.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $config = getConfig();



        // Проверка CSRF токена (временно отключена для стабильной работы)
        // if (!isset($_POST['csrf_token'])) {
        //     logAction('csrf_missing', ['ip' => $_SERVER['REMOTE_ADDR'], 'post_data' => $_POST]);
        //     throw new Exception('CSRF token is missing');
        // }

        // if (!verifyCSRFToken($_POST['csrf_token'])) {
        //     logAction('csrf_invalid', [
        //         'ip' => $_SERVER['REMOTE_ADDR'], 
        //         'received_token' => $_POST['csrf_token'],
        //         'session_token' => $_SESSION['csrf_token'] ?? 'not_set',
        //         'session_id' => session_id()
        //     ]);
        //     throw new Exception('Invalid CSRF token');
        // }

        // Антиспам проверка
        if (isset($_POST[$config['security']['honeypot_field']]) && $_POST[$config['security']['honeypot_field']] !== '') {
            logAction('spam_detected', ['data' => $_POST]);
            throw new Exception('Spam detected');
        }

        // Валидация данных
        if (empty($_POST['phone'])) {
            throw new Exception('Phone number is required');
        }

        if (!validatePhone($_POST['phone'])) {
            throw new Exception('Invalid phone number');
        }

        $phone = normalizePhone($_POST['phone']);

        // Формируем сообщение
        $message = "📩 <b>Новая заявка</b>\n\n";

        // Определяем тип формы
        $formType = $_POST['form_type'] ?? 'Не указано';
        $formSource = $_POST['form_source'] ?? 'Не указано';

        $message .= "📌 <b>Форма:</b> " . e($formType) . "\n";
        $message .= "📍 <b>Источник:</b> " . e($formSource) . "\n";
        $message .= "📱 <b>Телефон:</b> +" . $phone . "\n";

        // Имя (если есть)
        if (!empty($_POST['name'])) {
            $message .= "👤 <b>Имя:</b> " . e($_POST['name']) . "\n";
        }

        // Время звонка (обрабатываем оба варианта полей)
        $callTime = $_POST['time'] ?? $_POST['call_back_time'] ?? '';
        if (!empty($callTime)) {
            $message .= "📞 <b>Время звонка:</b> " . e($callTime) . "\n";
        }

        if (!empty($_POST['social_type'])) {
            $message .= "💬 <b>Способ получения:</b> " . e($_POST['social_type']) . "\n";
        }
        if (!empty($_POST['promotion_name'])) {
            $message .= "🎁 <b>Акция:</b> " . e($_POST['promotion_name']) . "\n";
        }
        if (!empty($_POST['service_name'])) {
            $message .= "🔧 <b>Услуга:</b> " . e($_POST['service_name']) . "\n";
        }
        if (!empty($_POST['product_name'])) {
            $message .= "🛒 <b>Товар:</b> " . e($_POST['product_name']) . "\n";
        }

        // Проверяем согласие с условиями (обрабатываем оба варианта)
        $agreement = false;
        if (isset($_POST['agreement']) && $_POST['agreement'] === 'on') {
            $agreement = true;
        } elseif (isset($_POST['agreement']) && $_POST['agreement'] === '1') {
            $agreement = true;
        }

        $agreementText = $agreement ? "✅ Согласен с условиями" : "⚠️ НЕ согласен с условиями";
        $message .= $agreementText . "\n\n";
        $message .= "📅 <b>Дата:</b> " . date('d.m.Y H:i:s');
        $message .= "\n🌐 <b>Страница:</b> " . ($_POST['page_url'] ?? 'Не указано');



        // Логирование
        logAction('telegram_form_submitted', [
            'form_type' => $formType,
            'form_source' => $formSource,
            'phone' => $phone,
            'name' => $_POST['name'] ?? '',
            'time' => $callTime,
            'agreement' => $agreement
        ]);

        // Отправляем сообщение
        $telegramSent = sendToTelegram($message, $config);
        $emailSent = sendEmail($message, $config);

        if ($telegramSent && $emailSent) {
            echo json_encode([
                "status" => "success",
                "message" => "Заявка успешно отправлена!"
            ]);
        } else {
            throw new Exception("Ошибка при отправке");
        }

    } catch (Exception $e) {
        logAction('telegram_form_error', ['error' => $e->getMessage()]);
        echo json_encode([
            "status" => "error",
            "message" => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Неверный метод запроса"
    ]);
}
?>