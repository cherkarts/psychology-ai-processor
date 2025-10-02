<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/functions.php';

$config = getConfig();

// Информация о специалисте для голосовых помощников
$specialistInfo = [
    'name' => 'Денис Черкас',
    'profession' => 'Психолог',
    'specialization' => [
        'Зависимости',
        'Созависимость',
        'Тревожность и страхи',
        'Сложности в отношениях',
        'Стресс и выгорание'
    ],
    'experience' => 'Более 5 лет практики',
    'education' => 'Высшее психологическое образование',
    'personal_story' => 'Бывший зависимый, прошедший путь выздоровления',
    'approach' => 'Интегративный подход, сочетающий когнитивно-поведенческую терапию и методы работы с зависимостями',
    'contact' => [
        'phone' => getContactSettings()['phone'],
        'email' => getContactSettings()['email'],
        'telegram' => getContactSettings()['telegram_url'],
        'whatsapp' => 'https://wa.me/+79936202951'
    ],
    'working_hours' => [
        'days' => 'Понедельник - Пятница',
        'time' => '9:00 - 18:00'
    ],
    'services' => [
        [
            'name' => 'Индивидуальная консультация',
            'duration' => '50 минут',
            'price' => '2500 рублей',
            'description' => 'Персональная работа с психологом в удобном формате'
        ],
        [
            'name' => 'Бесплатная консультация',
            'duration' => '15 минут',
            'price' => 'Бесплатно',
            'description' => 'Краткая консультация для оценки ситуации'
        ]
    ],
    'location' => 'Онлайн консультации',
    'website' => $config['site']['url'],
    'description' => $config['site']['description'],
    'languages' => ['Русский'],
    'certifications' => [
        'Высшее психологическое образование',
        'Специализация по работе с зависимостями',
        'Опыт личного выздоровления'
    ]
];

echo json_encode($specialistInfo, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);