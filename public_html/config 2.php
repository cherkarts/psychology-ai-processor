<?php
// Конфигурационный файл для продакшена
// Создан автоматически: 2025-09-04 17:00:28

return [
    // Настройки базы данных
    'database' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'dbname' => 'cherk146_charkas-therapy',
        'username' => 'cherk146_charkas-therapy',
        'password' => 'YhCn5R4hnhDL9cF7WxDg',
        'charset' => 'utf8mb4',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ],

    // Настройки окружения
    'environment' => 'production',

    // Telegram настройки (оставьте как есть)
    'telegram' => [
        'bot_token' => '7657713367:AAEDdQSD1K1g8ckRI-R-ePB7s1AtXc4OuyE',
        'chat_id' => '-1002418481743'
    ],

    // Email настройки (оставьте как есть)
    'email' => [
        'to' => 'cherkarts.denis@gmail.com',
        'subject' => 'Новая заявка с сайта',
        'from' => 'cherkarts.denis@gmail.com',
        'reply_to' => 'cherkarts.denis@gmail.com'
    ],

    // Настройки безопасности
    'security' => [
        'csrf_token_name' => 'cherkas_csrf_token',
        'honeypot_field' => 'website'
    ],

    // Настройки сайта
    'site' => [
        'name' => 'Психолог Денис Черкас',
        'url' => 'https://cherkas-therapy.ru',
        'description' => 'Консультации психолога: онлайн, от 15 мин бесплатно до 50 мин - 2500₽ с поддержкой.'
    ]
];
