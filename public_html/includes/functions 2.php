<?php
// Общие функции для сайта

// Автозагрузка Composer (для getID3)
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// Загрузка конфигурации
function getConfig()
{
    static $config = null;
    if ($config === null) {
        $config = require_once __DIR__ . '/../config.php';
    }
    return $config;
}

// Функция для подключения к базе данных
function getDB()
{
    static $pdo = null;
    if ($pdo === null) {
        try {
            $config = getConfig();

            // Собираем DSN ТОЛЬКО через TCP. Никаких локальных сокетов.
            $host = $config['database']['host'];
            $port = $config['database']['port'] ?? 3306;
            $dbname = $config['database']['dbname'];
            $charset = trim($config['database']['charset'] ?? '');

            $dsn = "mysql:host={$host};port={$port};dbname={$dbname}";
            if ($charset !== '') {
                $dsn .= ";charset={$charset}";
            }

            $options = $config['database']['options'] ?? [];
            $pdo = new PDO($dsn, $config['database']['username'], $config['database']['password'], $options);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            // Устанавливаем кодировку только если указана
            if ($charset !== '') {
                try {
                    $pdo->exec("SET NAMES {$charset}");
                } catch (Throwable $t) { /* ignore */
                }
            }

            // Явно выбираем базу данных
            $pdo->exec("USE `{$dbname}`");
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw $e;
        }
    }
    return $pdo;
}

// Генерация CSRF токена
function generateCSRFToken()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Проверка CSRF токена
function verifyCSRFToken($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Валидация телефона
function validatePhone($phone)
{
    $phone = preg_replace('/[^0-9]/', '', $phone);
    return strlen($phone) >= 10 && strlen($phone) <= 15;
}

// Нормализация телефона
function normalizePhone($phone)
{
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($phone) === 11 && $phone[0] === '8') {
        $phone = '7' . substr($phone, 1);
    }
    return $phone;
}

// Проверка режима обслуживания
function isMaintenanceMode()
{
    // Сначала проверяем файл (приоритет)
    $maintenanceFile = __DIR__ . '/../maintenance.flag';
    if (file_exists($maintenanceFile)) {
        return true;
    }

    // Затем проверяем базу данных
    try {
        $settings = getSystemSettings();
        return !empty($settings['maintenance_mode']);
    } catch (Exception $e) {
        error_log("Error checking maintenance mode: " . $e->getMessage());
        return false;
    }
}

// Получение сообщения режима обслуживания
function getMaintenanceMessage()
{
    // Сначала проверяем файл (приоритет)
    $messageFile = __DIR__ . '/../maintenance.message';
    if (file_exists($messageFile)) {
        return file_get_contents($messageFile);
    }

    // Затем проверяем базу данных
    try {
        $settings = getSystemSettings();
        return $settings['maintenance_message'] ?? 'Сайт временно недоступен по техническим причинам.';
    } catch (Exception $e) {
        error_log("Error getting maintenance message: " . $e->getMessage());
        return 'Сайт временно недоступен по техническим причинам.';
    }
}

// Проверка доступа администратора
function isAdminAccess()
{
    // Проверяем, что пользователь авторизован в админке
    return isset($_SESSION['admin_user_id']) && !empty($_SESSION['admin_user_id']);
}

// Логирование
function logAction($action, $data = [])
{
    $log = [
        'timestamp' => date('Y-m-d H:i:s'),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'action' => $action,
        'data' => $data
    ];

    $logFile = dirname(__DIR__) . '/logs/actions.log';
    $logDir = dirname($logFile);

    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    file_put_contents($logFile, json_encode($log, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND | LOCK_EX);
}

// Отправка в Telegram
function sendToTelegram($message, $config = null)
{
    if ($config === null) {
        $config = getConfig();
    }

    // Отладочная информация
    error_log("MAIN TELEGRAM: Config loaded, bot_token: " . substr($config['telegram']['bot_token'] ?? 'MISSING', 0, 10) . "...");
    error_log("MAIN TELEGRAM: chat_id: " . ($config['telegram']['chat_id'] ?? 'MISSING'));

    $url = "https://api.telegram.org/bot{$config['telegram']['bot_token']}/sendMessage";
    $params = [
        'chat_id' => $config['telegram']['chat_id'],
        'text' => $message,
        'parse_mode' => 'HTML'
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $params,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true
    ]);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Логируем результат
    error_log("Telegram API response: HTTP $httpCode, Response: $response, Error: $error");

    if ($error) {
        error_log("Telegram cURL error: $error");
        return false;
    }

    if ($httpCode !== 200) {
        error_log("Telegram API error: HTTP $httpCode, Response: $response");
        return false;
    }

    $responseData = json_decode($response, true);
    if (!$responseData || !$responseData['ok']) {
        error_log("Telegram API error: " . ($responseData['description'] ?? 'Unknown error'));
        return false;
    }

    return true;
}

/**
 * Генерирует SEO-friendly slug из строки
 * @param string $string Исходная строка
 * @param string $separator Разделитель (по умолчанию '-')
 * @return string
 */
function generateSlug($string, $separator = '-')
{
    // Убираем HTML теги
    $string = strip_tags($string);

    // Транслитерация кириллицы
    $converter = array(
        'а' => 'a',
        'б' => 'b',
        'в' => 'v',
        'г' => 'g',
        'д' => 'd',
        'е' => 'e',
        'ё' => 'e',
        'ж' => 'zh',
        'з' => 'z',
        'и' => 'i',
        'й' => 'y',
        'к' => 'k',
        'л' => 'l',
        'м' => 'm',
        'н' => 'n',
        'о' => 'o',
        'п' => 'p',
        'р' => 'r',
        'с' => 's',
        'т' => 't',
        'у' => 'u',
        'ф' => 'f',
        'х' => 'h',
        'ц' => 'c',
        'ч' => 'ch',
        'ш' => 'sh',
        'щ' => 'sch',
        'ь' => '',
        'ы' => 'y',
        'ъ' => '',
        'э' => 'e',
        'ю' => 'yu',
        'я' => 'ya',

        'А' => 'A',
        'Б' => 'B',
        'В' => 'V',
        'Г' => 'G',
        'Д' => 'D',
        'Е' => 'E',
        'Ё' => 'E',
        'Ж' => 'Zh',
        'З' => 'Z',
        'И' => 'I',
        'Й' => 'Y',
        'К' => 'K',
        'Л' => 'L',
        'М' => 'M',
        'Н' => 'N',
        'О' => 'O',
        'П' => 'P',
        'Р' => 'R',
        'С' => 'S',
        'Т' => 'T',
        'У' => 'U',
        'Ф' => 'F',
        'Х' => 'H',
        'Ц' => 'C',
        'Ч' => 'Ch',
        'Ш' => 'Sh',
        'Щ' => 'Sch',
        'Ь' => '',
        'Ы' => 'Y',
        'Ъ' => '',
        'Э' => 'E',
        'Ю' => 'Yu',
        'Я' => 'Ya'
    );

    // Применяем транслитерацию
    $string = strtr($string, $converter);

    // Приводим к нижнему регистру
    $string = mb_strtolower($string, 'UTF-8');

    // Заменяем все символы кроме букв и цифр на разделитель
    $string = preg_replace('/[^a-z0-9\-]/', $separator, $string);

    // Убираем множественные разделители
    $string = preg_replace('/' . preg_quote($separator, '/') . '+/', $separator, $string);

    // Убираем разделители в начале и конце
    $string = trim($string, $separator);

    // Если строка пустая, возвращаем 'untitled'
    if (empty($string)) {
        $string = 'untitled';
    }

    return $string;
}

/**
 * Проверяет уникальность slug в базе данных
 * @param string $slug Проверяемый slug
 * @param string $table Таблица для проверки
 * @param int $excludeId ID записи для исключения (при обновлении)
 * @return bool
 */
function isSlugUnique($slug, $table, $excludeId = null)
{
    try {
        $config = getConfig();
        $host = $config['database']['host'];
        $port = $config['database']['port'] ?? 3306;
        $dbname = $config['database']['dbname'];
        $charset = trim($config['database']['charset'] ?? '');

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname}";
        if ($charset !== '') {
            $dsn .= ";charset={$charset}";
        }

        $pdo = new PDO(
            $dsn,
            $config['database']['username'],
            $config['database']['password'],
            $config['database']['options']
        );

        $sql = "SELECT COUNT(*) FROM `$table` WHERE slug = ?";
        $params = [$slug];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $count = $stmt->fetchColumn();

        return $count === 0;
    } catch (Exception $e) {
        error_log("Error checking slug uniqueness: " . $e->getMessage());
        return false;
    }
}

/**
 * Генерирует уникальный slug
 * @param string $string Исходная строка
 * @param string $table Таблица для проверки
 * @param int $excludeId ID записи для исключения
 * @return string
 */
function generateUniqueSlug($string, $table, $excludeId = null)
{
    $baseSlug = generateSlug($string);
    $slug = $baseSlug;
    $counter = 1;

    while (!isSlugUnique($slug, $table, $excludeId)) {
        $slug = $baseSlug . '-' . $counter;
        $counter++;
    }

    return $slug;
}

/**
 * Получает URL продукта
 * @param string $slug
 * @return string
 */
function getProductUrl($slug)
{
    return "/product/$slug/";
}

/**
 * Получает URL статьи
 * @param string $slug
 * @return string
 */
function getArticleUrl($slug)
{
    return "/statya/" . urlencode($slug);
}

/**
 * Получает URL категории
 * @param string $slug
 * @return string
 */
function getCategoryUrl($slug)
{
    return "/category/$slug/";
}

/**
 * Получает URL категории статей
 * @param string $slug
 * @return string
 */
function getArticleCategoryUrl($slug)
{
    return "/articles/category/$slug/";
}

/**
 * Получает URL медитации
 * @param string $slug
 * @return string
 */
function getMeditationUrl($slug)
{
    return "/meditation/$slug/";
}

/**
 * Получает URL услуги
 * @param string $slug
 * @return string
 */
function getServiceUrl($slug)
{
    return "/service/$slug/";
}

/**
 * Получает текущий URL
 * @return string
 */
function getCurrentUrl()
{
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $uri = $_SERVER['REQUEST_URI'];
    return $protocol . '://' . $host . $uri;
}

/**
 * Парсит URL для определения типа страницы и slug
 * @return array|null
 */
function parseUrl()
{
    $requestUri = $_SERVER['REQUEST_URI'];
    $path = parse_url($requestUri, PHP_URL_PATH);

    // Проверяем, что parse_url вернул валидный путь
    if ($path === false || $path === null) {
        return null;
    }

    // Убираем trailing slash
    $path = rtrim($path, '/');

    // Разбиваем путь на части
    $parts = explode('/', trim($path, '/'));

    if (empty($parts[0])) {
        return null;
    }

    $result = [];

    switch ($parts[0]) {
        case 'product':
            $result['type'] = 'product';
            $result['slug'] = $parts[1] ?? null;
            break;

        case 'article':
            $result['type'] = 'article';
            $result['slug'] = $parts[1] ?? null;
            break;

        case 'category':
            $result['type'] = 'category';
            $result['slug'] = $parts[1] ?? null;
            break;

        case 'articles':
            if (isset($parts[1]) && $parts[1] === 'category') {
                $result['type'] = 'article_category';
                $result['slug'] = $parts[2] ?? null;
            } else {
                $result['type'] = 'articles_list';
            }
            break;

        case 'meditation':
            $result['type'] = 'meditation';
            $result['slug'] = $parts[1] ?? null;
            break;

        case 'meditations':
            if (isset($parts[1]) && $parts[1] === 'category') {
                $result['type'] = 'meditation_category';
                $result['slug'] = $parts[2] ?? null;
            } else {
                $result['type'] = 'meditations_list';
            }
            break;

        case 'service':
            $result['type'] = 'service';
            $result['slug'] = $parts[1] ?? null;
            break;

        default:
            // Проверяем, существует ли файл
            $filePath = $parts[0] . '.php';
            if (file_exists($filePath)) {
                $result['type'] = 'page';
                $result['slug'] = $parts[0];
            } else {
                $result['type'] = '404';
            }
            break;
    }

    return $result;
}

/**
 * Перенаправляет на ЧПУ URL если нужно
 */
function redirectToSeoUrl()
{
    $requestUri = $_SERVER['REQUEST_URI'];

    // Если это уже ЧПУ URL, не перенаправляем
    if (preg_match('/^\/(product|article|category|articles|meditation|meditations|service)\//', $requestUri)) {
        return;
    }

    // Проверяем GET параметры
    if (isset($_GET['slug'])) {
        $slug = $_GET['slug'];

        // Определяем тип по контексту
        if (strpos($requestUri, 'product.php') !== false) {
            $newUrl = getProductUrl($slug);
        } elseif (strpos($requestUri, 'article.php') !== false) {
            $newUrl = getArticleUrl($slug);
        } elseif (strpos($requestUri, 'meditations.php') !== false) {
            $newUrl = getMeditationUrl($slug);
        } else {
            return;
        }

        // Перенаправляем на ЧПУ URL
        header('Location: ' . $newUrl, true, 301);
        exit;
    }

    if (isset($_GET['category'])) {
        $category = $_GET['category'];

        if (strpos($requestUri, 'shop.php') !== false) {
            $newUrl = getCategoryUrl($category);
        } elseif (strpos($requestUri, 'articles.php') !== false) {
            $newUrl = getArticleCategoryUrl($category);
        } else {
            return;
        }

        header('Location: ' . $newUrl, true, 301);
        exit;
    }
}

// Audio duration function - использует только getID3
function getAudioDuration($filePath)
{
    // Проверяем, что файл существует
    if (!file_exists($filePath)) {
        return 0;
    }

    // Используем только getID3 (работает на любом shared-хостинге)
    if (class_exists('getID3')) {
        try {
            $getID3 = new getID3();
            $fileInfo = $getID3->analyze($filePath);

            if (isset($fileInfo['playtime_seconds']) && $fileInfo['playtime_seconds'] > 0) {
                error_log("getID3 duration for $filePath: {$fileInfo['playtime_seconds']} seconds");
                return intval($fileInfo['playtime_seconds']);
            }
        } catch (Exception $e) {
            error_log("getID3 error for $filePath: " . $e->getMessage());
        }
    }

    error_log("Could not determine duration for $filePath");
    return 0;
}

// Генерация мета-тегов для SEO
function generateMetaTags($page = 'home')
{
    $config = getConfig();
    $meta = [
        'home' => [
            'title' => 'Онлайн-психолог Денис Черкас – зависимости, созависимость, тревожность',
            'description' => 'Консультации психолога: онлайн, от 15 мин бесплатно до 50 мин - 2500₽ с поддержкой.',
            'keywords' => 'психолог, консультации, Денис Черкас, онлайн терапия, зависимости, созависимость'
        ],
        'services' => [
            'title' => 'Услуги психолога Дениса Черкаса - Зависимости, созависимость, тревожность',
            'description' => 'Профессиональная помощь при зависимостях, созависимости, тревожности. Онлайн консультации психолога.',
            'keywords' => 'психолог услуги, зависимости, созависимость, тревожность, онлайн консультации'
        ],
        'articles' => [
            'title' => 'Статьи психолога Дениса Черкаса - Полезные материалы о психологии',
            'description' => 'Статьи и материалы по психологии, зависимостям, созависимости. Полезные советы от профессионального психолога.',
            'keywords' => 'статьи психолога, психология, зависимости, советы психолога'
        ],
        'meditations' => [
            'title' => 'Аудио медитации от психолога Дениса Черкаса - Расслабление и спокойствие',
            'description' => 'Профессиональные аудио медитации для снятия стресса, тревоги, улучшения сна. Медитации для детей и взрослых.',
            'keywords' => 'аудио медитации, медитация, релаксация, снятие стресса, медитации для сна, детские медитации'
        ]
    ];

    return $meta[$page] ?? $meta['home'];
}

// Безопасный вывод данных
function e($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Генерация Schema.org разметки
function generateSchemaMarkup($type = 'person')
{
    $config = getConfig();

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => $type
    ];

    if ($type === 'person') {
        $schema = array_merge($schema, [
            'name' => 'Денис Черкас',
            'jobTitle' => 'Психолог',
            'description' => 'Специалист по зависимостям и созависимости',
            'image' => $config['site']['url'] . '/image/445-1.png',
            'knowsAbout' => ['Addiction', 'Codependency', 'Anxiety', 'Psychology'],
            'sameAs' => [
                'https://t.me/cherkas_therapy',
                'https://wa.me/79936202951'
            ],
            'telephone' => '+79936202951',
            'email' => 'cherkarts.denis@gmail.com'
        ]);
    }

    return json_encode($schema, JSON_UNESCAPED_UNICODE);
}

// Функции для работы с настройками системы
function getSystemSettings()
{
    try {
        // Используем getAdminDB() из админки, если доступна
        if (function_exists('getAdminDB')) {
            $db = getAdminDB();
        } else {
            $db = getDB();
            $cfg = getConfig();
            $db->exec("USE `{$cfg['database']['dbname']}`");
        }
        $stmt = $db->query("SELECT setting_key, setting_value, setting_type FROM settings");
        $settings = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $key = $row['setting_key'];
            $value = $row['setting_value'];
            $type = $row['setting_type'];

            // Преобразуем значение в соответствии с типом
            switch ($type) {
                case 'integer':
                    $settings[$key] = intval($value);
                    break;
                case 'boolean':
                    $settings[$key] = $value === '1' || $value === 'true';
                    break;
                case 'json':
                    $settings[$key] = json_decode($value, true);
                    break;
                default:
                    $settings[$key] = $value;
            }
        }

        return $settings;
    } catch (PDOException $e) {
        error_log("Error loading settings: " . $e->getMessage());
        return [];
    }
}

function saveSettings($data)
{
    try {
        // Используем getAdminDB() из админки, если доступна
        if (function_exists('getAdminDB')) {
            $db = getAdminDB();
        } else {
            $db = getDB();
            $cfg = getConfig();
            $db->exec("USE `{$cfg['database']['dbname']}`");
        }

        // Начинаем транзакцию
        $db->beginTransaction();

        // Подготавливаем запрос для обновления настроек
        $stmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");

        foreach ($data as $key => $value) {
            // Санитизируем значение
            if (is_string($value)) {
                $value = sanitizeInput($value);
            }

            // Преобразуем boolean значения
            if (is_bool($value)) {
                $value = $value ? '1' : '0';
            }

            // Выполняем обновление
            $stmt->execute([$value, $key]);
        }

        // Подтверждаем транзакцию
        $db->commit();

        // Логируем действие, если функция доступна
        if (function_exists('logAdminActivity')) {
            logAdminActivity('update', 'System settings updated');
        }
        return ['success' => true, 'message' => 'Настройки успешно сохранены'];

    } catch (PDOException $e) {
        // Откатываем транзакцию в случае ошибки
        if (isset($db) && $db instanceof PDO) {
            $db->rollBack();
        }
        error_log("Error saving settings: " . $e->getMessage());
        return ['success' => false, 'message' => 'Ошибка сохранения настроек: ' . $e->getMessage()];
    }
}

// Функция для получения настроек контактов
function getContactSettings()
{
    $settings = getSystemSettings();

    return [
        'email' => $settings['contact_email'] ?? 'info@cherkas-therapy.ru',
        'phone' => $settings['contact_phone'] ?? '+7 (993) 620-29-51',
        'whatsapp' => $settings['whatsapp_number'] ?? '+7 (993) 620-29-51',
        'telegram_username' => $settings['telegram_username'] ?? 'Cherkas_therapy',
        'telegram_url' => $settings['telegram_url'] ?? 'https://t.me/Cherkas_therapy',
        'telegram_channel' => $settings['telegram_channel'] ?? 'taterapia',
        'telegram_channel_url' => $settings['telegram_channel_url'] ?? 'https://t.me/taterapia'
    ];
}

// Функция для получения настроек Яндекс Метрики
function getYandexMetrikaSettings()
{
    $settings = getSystemSettings();

    return [
        'enabled' => !empty($settings['yandex_metrika_enabled']),
        'id' => $settings['yandex_metrika_id'] ?? '',
        'webvisor' => ($settings['yandex_metrika_webvisor'] ?? '1') === '1',
        'clickmap' => ($settings['yandex_metrika_clickmap'] ?? '1') === '1',
        'track_hash' => ($settings['yandex_metrika_track_hash'] ?? '1') === '1',
        'track_links' => ($settings['yandex_metrika_track_links'] ?? '1') === '1',
        'accurate_track_bounce' => ($settings['yandex_metrika_accurate_track_bounce'] ?? '1') === '1',
        'defer' => ($settings['yandex_metrika_defer'] ?? '1') === '1',
        'ecommerce' => ($settings['yandex_metrika_ecommerce'] ?? '1') === '1',
        'custom_events' => $settings['yandex_metrika_custom_events'] ?? '',
        'debug' => ($settings['yandex_metrika_debug'] ?? '0') === '1'
    ];
}

// Функция для генерации кода Яндекс Метрики
function generateYandexMetrikaCode()
{
    $metrika = getYandexMetrikaSettings();

    if (!$metrika['enabled'] || empty($metrika['id'])) {
        return '';
    }

    $id = $metrika['id'];
    $params = [];

    // Добавляем параметры в зависимости от настроек
    if ($metrika['webvisor']) {
        $params[] = 'webvisor: true';
    }
    if ($metrika['clickmap']) {
        $params[] = 'clickmap: true';
    }
    if ($metrika['track_hash']) {
        $params[] = 'trackHash: true';
    }
    if ($metrika['track_links']) {
        $params[] = 'trackLinks: true';
    }
    if ($metrika['accurate_track_bounce']) {
        $params[] = 'accurateTrackBounce: true';
    }
    if ($metrika['defer']) {
        $params[] = 'defer: true';
    }
    if ($metrika['ecommerce']) {
        $params[] = 'ecommerce: true';
    }

    $paramsStr = !empty($params) ? ', {' . implode(', ', $params) . '}' : '';

    $code = "
<!-- Yandex.Metrika counter -->
<script type=\"text/javascript\" >
   (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
   m[i].l=1*new Date();
   for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
   k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
   (window, document, \"script\", \"https://mc.yandex.ru/metrika/tag.js?id={$id}\", \"ym\");

   ym({$id}, \"init\"{$paramsStr});
</script>
<noscript><div><img src=\"https://mc.yandex.ru/watch/{$id}\" style=\"position:absolute; left:-9999px;\" alt=\"\" /></div></noscript>
<!-- /Yandex.Metrika counter -->";

    // Добавляем пользовательские события, если они есть
    if (!empty($metrika['custom_events'])) {
        $code .= "\n<script>\n" . $metrika['custom_events'] . "\n</script>";
    }

    // Добавляем отладочную информацию, если включен режим отладки
    if ($metrika['debug']) {
        $code .= "\n<script>
console.log('Yandex Metrika Debug: Counter ID = {$id}');
console.log('Yandex Metrika Debug: Settings = " . json_encode($metrika) . "');
</script>";
    }

    return $code;
}

// Функция для отслеживания покупок в Яндекс Метрике
function trackYandexMetrikaPurchase($orderId, $total, $currency = 'RUB', $items = [])
{
    $metrika = getYandexMetrikaSettings();

    if (!$metrika['enabled'] || empty($metrika['id']) || !$metrika['ecommerce']) {
        return '';
    }

    $id = $metrika['id'];
    $itemsJson = json_encode($items);

    return "
<script>
ym({$id}, 'reachGoal', 'purchase', {
    order_id: '{$orderId}',
    order_price: {$total},
    currency: '{$currency}',
    goods: {$itemsJson}
});
</script>";
}

// Функция для отслеживания добавления товара в корзину
function trackYandexMetrikaAddToCart($productId, $productName, $price, $quantity = 1)
{
    $metrika = getYandexMetrikaSettings();

    if (!$metrika['enabled'] || empty($metrika['id']) || !$metrika['ecommerce']) {
        return '';
    }

    $id = $metrika['id'];

    return "
<script>
ym({$id}, 'reachGoal', 'add_to_cart', {
    product_id: '{$productId}',
    product_name: '{$productName}',
    price: {$price},
    quantity: {$quantity}
});
</script>";
}

// Функция для отслеживания просмотра товара
function trackYandexMetrikaProductView($productId, $productName, $price, $category = '')
{
    $metrika = getYandexMetrikaSettings();

    if (!$metrika['enabled'] || empty($metrika['id']) || !$metrika['ecommerce']) {
        return '';
    }

    $id = $metrika['id'];
    $categoryParam = !empty($category) ? ", category: '{$category}'" : '';

    return "
<script>
ym({$id}, 'reachGoal', 'product_view', {
    product_id: '{$productId}',
    product_name: '{$productName}',
    price: {$price}{$categoryParam}
});
</script>";
}

// Отправка email
function sendEmail($message, $config = null)
{
    if ($config === null) {
        $config = getConfig();
    }

    try {
        // Простая отправка email через mail() для тестирования
        $to = $config['email']['to'] ?? 'admin@example.com';
        $subject = 'Новая заявка с сайта';
        $headers = 'From: ' . ($config['email']['from'] ?? 'noreply@example.com') . "\r\n";
        $headers .= 'Content-Type: text/html; charset=UTF-8' . "\r\n";

        $result = mail($to, $subject, $message, $headers);

        if ($result) {
            logAction('email_sent_success', ['to' => $to]);
            return true;
        } else {
            logAction('email_sent_failed', ['to' => $to, 'error' => 'mail() function failed']);
            return false;
        }
    } catch (Exception $e) {
        logAction('email_sent_error', ['error' => $e->getMessage()]);
        return false;
    }
}

/**
 * Генерирует ЧПУ ссылку с учетом окружения
 * @param string $type Тип ссылки: 'tovar', 'statya', 'kategoriya', 'page'
 * @param string $slug Slug или путь
 * @return string Готовая ссылка
 */
function generateChpuUrl($type, $slug)
{
    $isLocal = (strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false) ||
        (strpos(gethostname(), 'MacBook') !== false) ||
        (strpos(__DIR__, 'xampp') !== false);

    if ($isLocal) {
        // Локально используем test-chpu.php
        switch ($type) {
            case 'tovar':
                return '/test-chpu.php/tovar/' . $slug;
            case 'statya':
                return '/test-chpu.php/statya/' . $slug;
            case 'kategoriya':
                return '/test-chpu.php/kategoriya/' . $slug;
            case 'page':
                return '/test-chpu.php' . $slug;
            default:
                return '/test-chpu.php' . $slug;
        }
    } else {
        // На продакшене используем прямые ЧПУ
        switch ($type) {
            case 'tovar':
                return '/tovar/' . $slug;
            case 'statya':
                return '/article.php?slug=' . $slug;
            case 'kategoriya':
                return '/kategoriya/' . $slug;
            case 'page':
                return $slug;
            default:
                return $slug;
        }
    }
}

/**
 * Генерирует ссылку на страницу специализации
 * @param string $page Страница: 'dependencies', 'codependency', 'anxiety', 'relationships'
 * @return string Готовая ссылка
 */
function generateSpecializationUrl($page)
{
    $isLocal = (strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false) ||
        (strpos(gethostname(), 'MacBook') !== false) ||
        (strpos(__DIR__, 'xampp') !== false);

    $chpuMap = [
        'dependencies' => '/rabota-s-zavisimostyami',
        'codependency' => '/sozavisimost',
        'anxiety' => '/trevozhnost-i-strahi',
        'relationships' => '/slozhnosti-v-otnosheniyah'
    ];

    $chpuPath = $chpuMap[$page] ?? '/';

    if ($isLocal) {
        return '/test-chpu.php' . $chpuPath;
    } else {
        return $chpuPath;
    }
}