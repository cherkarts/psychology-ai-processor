<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Сначала загружаем functions.php для правильного подключения к БД
require_once 'includes/functions.php';

// Затем загружаем остальные файлы
require_once 'includes/Models/Order.php';
require_once 'includes/Models/Article.php';
require_once 'includes/Models/Meditation.php';
require_once 'includes/Models/Review.php';
require_once 'includes/Models/Product.php';
require_once 'includes/Database.php';

// Загружаем данные товаров с исправлением кодировки
try {
    require_once 'includes/products.php';
    $productManager = new ProductManager();

    // Получение всех товаров (без фильтра по категории пока)
    $products = $productManager->getAllProducts() ?: [];

    // Исправляем кодировку в данных товаров
    foreach ($products as &$product) {
        if (isset($product['title'])) {
            $product['title'] = mb_convert_encoding($product['title'], 'UTF-8', 'auto');
        }
        if (isset($product['description'])) {
            $product['description'] = mb_convert_encoding($product['description'], 'UTF-8', 'auto');
        }
        if (isset($product['short_description'])) {
            $product['short_description'] = mb_convert_encoding($product['short_description'], 'UTF-8', 'auto');
        }
        if (isset($product['category_name'])) {
            $product['category_name'] = mb_convert_encoding($product['category_name'], 'UTF-8', 'auto');
        }
    }

    // Отладочная информация
    error_log("Загружено товаров: " . count($products));

} catch (Exception $e) {
    error_log("Ошибка загрузки товаров: " . $e->getMessage());
    $products = [];
}

require_once 'includes/spotlight-render.php';

// Проверка режима обслуживания
if (isMaintenanceMode() && !isAdminAccess()) {
    header('Location: /maintenance.php');
    exit;
}

// Перенаправляем на ЧПУ URL если нужно
redirectToSeoUrl();

// Получение параметров фильтрации и пагинации
$category = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 16; // Количество товаров на странице (4 в ряд)

// Фильтрация по поиску
if ($search) {
    $products = array_filter($products, function ($product) use ($search) {
        return stripos($product['title'], $search) !== false ||
            stripos($product['description'], $search) !== false ||
            stripos($product['short_description'], $search) !== false ||
            stripos(implode(' ', $product['tags']), $search) !== false;
    });
}

// Сортировка
switch ($sort) {
    case 'price_asc':
        usort($products, function ($a, $b) {
            return $a['price'] - $b['price'];
        });
        break;
    case 'price_desc':
        usort($products, function ($a, $b) {
            return $b['price'] - $a['price'];
        });
        break;
    case 'name':
        usort($products, function ($a, $b) {
            return strcmp($a['title'], $b['title']);
        });
        break;
    default:
        // По умолчанию - новые первыми
        usort($products, function ($a, $b) {
            $dateA = isset($a['created_at']) ? strtotime($a['created_at']) : 0;
            $dateB = isset($b['created_at']) ? strtotime($b['created_at']) : 0;
            return $dateB - $dateA;
        });
}

// Получение категорий
$categories = [];
foreach ($productManager->getAllProducts() as $product) {
    if (!empty($product['category_name']) && !in_array($product['category_name'], $categories)) {
        $categories[] = $product['category_name'];
    }
}

// Пагинация
$totalProducts = count($products);
$totalPages = ceil($totalProducts / $perPage);
$page = min($page, max(1, $totalPages)); // Не выходить за пределы количества страниц

// Получение товаров для текущей страницы
$offset = ($page - 1) * $perPage;
$currentPageProducts = array_slice($products, $offset, $perPage);

// Мета-данные страницы
$meta = [
    'title' => 'Магазин - Семинары, книги и курсы от психолога Дениса Черкаса',
    'description' => 'Купить семинары, книги и курсы по психологии. Практические материалы для самопомощи и развития.',
    'keywords' => 'купить семинар, книги по психологии, курсы психолога, самопомощь'
];

// Функция для построения URL с фильтрами
function buildFilterUrl($search, $category, $sort)
{
    $params = [];
    if ($search)
        $params[] = 'search=' . urlencode($search);
    if ($category)
        $params[] = 'category=' . urlencode($category);
    if ($sort !== 'newest')
        $params[] = 'sort=' . urlencode($sort);

    return $params ? '?' . implode('&', $params) : '?';
}

// Функция для генерации кнопок товара в зависимости от типа
function getProductButtons($product)
{
    $type = $product['type'] ?? 'digital';

    switch ($type) {
        case 'free':
            return '<a href="' . "/tovar/" . urlencode($product['slug']) . '" class="product-card__btn product-card__btn--primary">Подробнее</a>';
        case 'discussion':
            return '<a href="' . "/tovar/" . urlencode($product['slug']) . '" class="product-card__btn product-card__btn--primary">Подробнее</a>';
        case 'service':
            return '<a href="' . "/tovar/" . urlencode($product['slug']) . '" class="product-card__btn product-card__btn--primary">Подробнее</a>';
        case 'digital':
        case 'physical':
        default:
            return '
                <button class="product-card__btn product-card__btn--primary buy-now-btn" data-product-id="' . e($product['id']) . '">КУПИТЬ СЕЙЧАС</button>
                <a href="' . "/tovar/" . urlencode($product['slug']) . '" class="product-card__btn product-card__btn--secondary">ПОДРОБНЕЕ</a>';
    }
}

// Функция для отображения сетки товаров
function getProductsGrid($products)
{
    // Отладочная информация
    error_log("getProductsGrid вызвана с " . count($products) . " товарами");

    if (empty($products)) {
        error_log("getProductsGrid: товары пустые");
        return '<p class="no-products">Товары не найдены. Попробуйте изменить параметры поиска.</p>';
    }

    $output = '';
    $index = 0;
    foreach ($products as $product) {
        $discount = 0;
        if (!empty($product['old_price']) && $product['old_price'] > $product['price']) {
            $discount = round((($product['old_price'] - $product['price']) / $product['old_price']) * 100);
        }

        // Ограничиваем описание до 130 символов
        $description = '';
        if (!empty($product['short_description'])) {
            $description = strlen($product['short_description']) > 130 ?
                substr($product['short_description'], 0, 130) . '...' :
                $product['short_description'];
        } elseif (!empty($product['description'])) {
            $description = strlen($product['description']) > 130 ?
                substr($product['description'], 0, 130) . '...' :
                $product['description'];
        }

        // Проверяем наличие акции и таймера
        $hasSale = !empty($product['sale_end_date']) && strtotime($product['sale_end_date']) > time();
        $saleEndDate = $hasSale ? $product['sale_end_date'] : '';

        // Определяем изображение для отображения
        $displayImage = '';
        if (!empty($product['image']) && file_exists(__DIR__ . '/' . $product['image'])) {
            $displayImage = $product['image'];
        } else {
            // Используем заглушку если изображения нет
            $displayImage = 'assets/images/no-image.svg';
        }

        $output .= '
        <div class="product-card">
            <div class="product-card__image">
                <a href="' . "/tovar/" . urlencode($product['slug']) . '">
                    <img src="' . e($displayImage) . '" alt="' . e($product['title']) . '" />
                </a>
                ' . ($discount > 0 ? '<div class="product-card__badge sale">-' . $discount . '%</div>' : '') . '
                ' . ($product['is_featured'] ? '<div class="product-card__badge popular">Популярное</div>' : '') . '
                ' . (!empty($product['is_new']) ? '<div class="product-card__badge new">Новинка</div>' : '') . '
                ' . (!empty($product['badges']) ? '
                <div class="product-card__badges-overlay">
                    ' . implode('', array_map(function ($badge) {
            return '<span class="product-badge" style="color: ' . e($badge['color']) . '; background-color: ' . e($badge['background_color']) . ';">' . e($badge['name']) . '</span>';
        }, $product['badges'])) . '
                </div>' : '') . '
            </div>

            <div class="product-card__content">
                <h3 class="product-card__title">' . e($product['title']) . '</h3>
                
                <p class="product-card__description">' . e($description) . '</p>

                <div class="product-card__features">
                ' . (!empty($product['features']) ? implode('', array_map(function ($feature) {
            return '<li>' . e($feature) . '</li>';
        }, array_slice(is_array($product['features']) ? $product['features'] : (json_decode($product['features'], true) ?: []), 0, 2))) : '') . '
                </div>

                <div class="product-card__footer">
                    <div class="product-card__price-container">
                        ' . (($product['type'] === 'free') ?
            '<span class="product-card__price product-card__price--free">Бесплатно</span>' :
            '<span class="product-card__price">' . number_format($product['price'], 0, ',', ' ') . ' ₽</span>'
        ) . '
                        ' . (!empty($product['old_price']) && $product['old_price'] > $product['price'] && $product['type'] !== 'free' ?
            '<span class="product-card__price-old">' . number_format($product['old_price'], 0, ',', ' ') . ' ₽</span>' : '') . '
                    </div>

                    ' . ($hasSale ? '
                    <div class="product-card__timer" data-end-date="' . $saleEndDate . '">
                        <div class="timer-label">Акция заканчивается:</div>
                        <div class="timer-display">
                            <span class="timer-days">00</span>д 
                            <span class="timer-hours">00</span>ч 
                            <span class="timer-minutes">00</span>м
                        </div>
                    </div>' : '') . '

                    <div class="product-card__buttons">
                        ' . getProductButtons($product) . '
                    </div>
                </div>
            </div>
        </div>';

        // Вставляем узкий полноширинный блок-витрину после каждой 6-й карточки
        $index++;
        if ($index % 6 === 0) {
            $unitHtml = get_spotlights_html('shop');
            if (!empty($unitHtml)) {
                $output .= '<div class="grid-spacer" style="grid-column: 1 / -1;">' . $unitHtml . '</div>';
            }
        }
    }

    return $output;
}

// Функция для получения названия категории
function getCategoryName($category)
{
    $categories = [
        'seminars' => 'Семинары',
        'books' => 'Книги',
        'courses' => 'Курсы',
        'meditations' => 'Медитации',
        'groups' => 'Группы'
    ];

    return $categories[$category] ?? $category;
}

// Функция для пагинации
function getPagination($currentPage, $totalPages, $search, $category, $sort)
{
    if ($totalPages <= 1)
        return '';

    $output = '<section class="pagination-section"><div class="wrapper"><div class="pagination">';

    // Кнопка "Предыдущая"
    if ($currentPage > 1) {
        $prevUrl = buildPaginationUrl($currentPage - 1, $search, $category, $sort);
        $output .= '<a href="' . $prevUrl . '" class="pagination__prev">← Предыдущая</a>';
    }

    // Номера страниц
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);

    if ($start > 1) {
        $output .= '<a href="' . buildPaginationUrl(1, $search, $category, $sort) . '" class="pagination__page">1</a>';
        if ($start > 2) {
            $output .= '<span class="pagination__dots">...</span>';
        }
    }

    for ($i = $start; $i <= $end; $i++) {
        if ($i == $currentPage) {
            $output .= '<span class="pagination__page pagination__page--active">' . $i . '</span>';
        } else {
            $output .= '<a href="' . buildPaginationUrl($i, $search, $category, $sort) . '" class="pagination__page">' . $i . '</a>';
        }
    }

    if ($end < $totalPages) {
        if ($end < $totalPages - 1) {
            $output .= '<span class="pagination__dots">...</span>';
        }
        $output .= '<a href="' . buildPaginationUrl($totalPages, $search, $category, $sort) . '" class="pagination__page">' . $totalPages . '</a>';
    }

    // Кнопка "Следующая"
    if ($currentPage < $totalPages) {
        $nextUrl = buildPaginationUrl($currentPage + 1, $search, $category, $sort);
        $output .= '<a href="' . $nextUrl . '" class="pagination__next">Следующая →</a>';
    }

    $output .= '</div></div></section>';

    return $output;
}

// Функция для построения URL пагинации
function buildPaginationUrl($page, $search, $category, $sort)
{
    $params = ['page=' . $page];
    if ($search)
        $params[] = 'search=' . urlencode($search);
    if ($category)
        $params[] = 'category=' . urlencode($category);
    if ($sort !== 'newest')
        $params[] = 'sort=' . urlencode($sort);

    return '?' . implode('&', $params);
}
?>
<!DOCTYPE html>
<html class="js" lang="ru">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title><?= e($meta['title']) ?></title>
    <meta content="<?= e($meta['description']) ?>" name="description" />
    <meta content="<?= e($meta['keywords']) ?>" name="keywords" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta content="telephone=no" name="format-detection" />
    <meta name="HandheldFriendly" content="true" />
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1" />

    <link rel="canonical" href="https://cherkas-therapy.ru/shop.php" />
    <meta property="og:locale" content="ru_RU" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="<?= e($meta['title']) ?>" />
    <meta property="og:description" content="<?= e($meta['description']) ?>" />
    <meta property="og:url" content="https://cherkas-therapy.ru/shop.php" />
    <meta property="og:site_name" content="Психолог Денис Черкас" />
    <meta property="og:image" content="https://cherkas-therapy.ru/image/shop-bg.jpg" />
    <meta property="og:image:width" content="1920" />
    <meta property="og:image:height" content="1080" />
    <meta property="og:image:type" content="image/jpeg" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?= e($meta['title']) ?>" />
    <meta name="twitter:image" content="https://cherkas-therapy.ru/image/shop-bg.jpg" />

    <!-- CSRF токен для AJAX-отправки форм в попапах -->
    <meta name="csrf-token" content="<?= e(generateCSRFToken()) ?>" />

    <!-- Стили -->
    <link rel="stylesheet" href="/css/new-components.css" />
    <link rel="stylesheet" href="/css/new-homepage.css?v=7.6" type="text/css" media="all" />
    <link rel="stylesheet" href="/css/pages.css" type="text/css" media="all" />
    <link rel="stylesheet" href="/css/shop-enhanced.css?v=2.3" type="text/css" media="all" />
    <link rel="stylesheet" href="/css/shop-filters-fix.css?v=2.0" type="text/css" media="all" />
    <link rel="stylesheet" href="/css/shop.css?v=2.1" type="text/css" media="all" />
    <link rel="stylesheet" href="/css/shop-mobile-header.css?v=1.0" type="text/css" media="all" />
    <link rel="stylesheet" href="/css/header-unification.css" />
</head>

<body class="shop-page">
    <?php include 'includes/new-header.php'; ?>

    <!-- Main Content Container -->
    <main class="main-content">
        <section class="shop-hero">
            <div class="wrapper">
                <div class="first__content">
                    <div class="first__text">
                        <h1 class="first__title md-main-title">
                            <span style="color: #6a7e9f">МАГАЗИН</span><br />
                            <span style="color: #d2afa0">МАТЕРИАЛОВ</span>
                        </h1>
                        <p class="first__subtitle">
                            Семинары, книги и курсы для самопомощи и развития.
                            Практические материалы от профессионального психолога.
                        </p>
                    </div>
                    <div class="first__form">
                        <div class="form-title">
                            <p>Найдите нужный<br />материал для<br />развития</p>
                        </div>
                        <form class="form search-form" method="GET">
                            <div class="search-container">
                                <input type="text" name="search" class="form-input search-input"
                                    placeholder="Поиск товаров..." value="<?= e($search) ?>" />
                                <button type="submit" class="form-btn search-btn md-main-color-btn">
                                    <span>Найти</span>
                                </button>
                                <?php if ($search || $category || $sort !== 'newest'): ?>
                                    <a href="?" class="form-btn clear-btn">
                                        <span>Стереть</span>
                                    </a>
                                <?php endif; ?>
                            </div>
                            <?php if ($category): ?>
                                <input type="hidden" name="category" value="<?= e($category) ?>" />
                            <?php endif; ?>
                            <?php if ($sort !== 'newest'): ?>
                                <input type="hidden" name="sort" value="<?= e($sort) ?>" />
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <section class="shop-categories">
            <div class="wrapper">
                <h2 class="advantages__title md-main-title">
                    <span style="color: #6a7e9f">КАТЕГОРИИ</span> ТОВАРОВ
                </h2>
                <div class="filters__slider md-standart-slider" filtersslider_js="">
                    <div class="slider-hint">
                        <span>Листайте влево/вправо</span>
                        <svg width="13" height="14" viewBox="0 0 13 14" fill="none" xmlns="Диагностика страницы настроек

Проверка авторизации

✅ Функции аутентификации загружены

✅ Пользователь авторизован: admin (admin)

✅ Есть права администратора

Проверка конфигурации

✅ Конфигурация загружена

Проверка функций

✅ Основные функции загружены

✅ Функция getSystemSettings существует

✅ Настройки загружены (31 настроек)

Текущие настройки:

Array
(
    [site_title] => Терапия у Черкаса
    [site_description] => 
    [admin_email] => 
    [contact_phone] => +7 (993) 620-29-51
    [session_timeout] => 60
    [max_login_attempts] => 3
    [lockout_time] => 15
    [require_strong_passwords] => 
    [max_file_size] => 10
    [allowed_image_types] => jpg,jpeg,png,gif,webp
    [allowed_audio_types] => mp3,wav,ogg,m4a
    [smtp_host] => 
    [smtp_port] => 587
    [smtp_username] => 
    [smtp_password] => 
    [smtp_secure] => 
    [articles_per_page] => 10
    [reviews_per_page] => 12
    [maintenance_mode] => 
    [maintenance_message] => Сайт временно недоступен по техническим причинам.
    [contact_email] => info@cherkas-therapy.ru
    [whatsapp_number] => +7 (993) 620-29-51
    [telegram_username] => Cherkas_therapy
    [telegram_url] => https://t.me/Cherkas_therapy
    [telegram_channel] => taterapia
    [telegram_channel_url] => https://t.me/taterapia
    [security_session_timeout] => 60
    [security_max_login_attempts] => 3
    [security_lockout_time] => 15
    [security_require_strong_passwords] => 
    [backup_frequency] => daily
)
✅ Функция saveSettings существует

Проверка базы данных

✅ Подключение к базе данных успешно

✅ Таблица settings существует

Структура таблицы settings:

Array
(
    [0] => Array
        (
            [Field] => id
            [Type] => int(11)
            [Null] => NO
            [Key] => PRI
            [Default] => 
            [Extra] => auto_increment
        )

    [1] => Array
        (
            [Field] => setting_key
            [Type] => varchar(255)
            [Null] => NO
            [Key] => UNI
            [Default] => 
            [Extra] => 
        )

    [2] => Array
        (
            [Field] => setting_value
            [Type] => text
            [Null] => YES
            [Key] => 
            [Default] => 
            [Extra] => 
        )

    [3] => Array
        (
            [Field] => setting_type
            [Type] => enum('string','integer','boolean','json')
            [Null] => YES
            [Key] => 
            [Default] => string
            [Extra] => 
        )

    [4] => Array
        (
            [Field] => description
            [Type] => text
            [Null] => YES
            [Key] => 
            [Default] => 
            [Extra] => 
        )

    [5] => Array
        (
            [Field] => created_at
            [Type] => timestamp
            [Null] => NO
            [Key] => 
            [Default] => current_timestamp()
            [Extra] => 
        )

    [6] => Array
        (
            [Field] => updated_at
            [Type] => timestamp
            [Null] => NO
            [Key] => 
            [Default] => current_timestamp()
            [Extra] => on update current_timestamp()
        )

)
Данные из таблицы settings:

Array
(
    [0] => Array
        (
            [id] => 1
            [setting_key] => site_title
            [setting_value] => Терапия у Черкаса
            [setting_type] => string
            [description] => Название сайта
            [created_at] => 2025-08-27 08:40:15
            [updated_at] => 2025-08-27 08:47:29
        )

    [1] => Array
        (
            [id] => 2
            [setting_key] => site_description
            [setting_value] => 
            [setting_type] => string
            [description] => Описание сайта
            [created_at] => 2025-08-27 08:40:15
            [updated_at] => 2025-08-27 08:42:25
        )

    [2] => Array
        (
            [id] => 3
            [setting_key] => admin_email
            [setting_value] => 
            [setting_type] => string
            [description] => Email администратора
            [created_at] => 2025-08-27 08:40:15
            [updated_at] => 2025-08-27 08:47:29
        )

    [3] => Array
        (
            [id] => 4
            [setting_key] => contact_phone
            [setting_value] => +7 (993) 620-29-51
            [setting_type] => string
            [description] => Контактный телефон
            [created_at] => 2025-08-27 08:40:15
            [updated_at] => 2025-08-27 10:00:22
        )

    [4] => Array
        (
            [id] => 5
            [setting_key] => session_timeout
            [setting_value] => 60
            [setting_type] => integer
            [description] => Время сессии в минутах
            [created_at] => 2025-08-27 08:40:15
            [updated_at] => 2025-08-27 08:47:29
        )

)
Проверка CSRF токенов

✅ CSRF токен сгенерирован: 7a7a186c89...

Проверка токена: ✅ Успешно

Проверка функций санитизации

✅ Функция sanitizeOutput существует

Тест sanitizeOutput: <script>alert(" test")</script>

                            ✅ Функция sanitizeInput существует

                            Текущая сессия

                            Array
                            (
                            [csrf_token] => 7a7a186c894f796f53c9caf98ab67e9f630769720284470ed5c347a768844269
                            [cart] => Array
                            (
                            [0] => Array
                            (
                            [id] => 88
                            [title] => капрал
                            [price] => 12345.00
                            [image] => /uploads/products/product_68b12ea899b34.jpg
                            [slug] => kapral
                            [quantity] => 2
                            )

                            )

                            [failed_attempts] => Array
                            (
                            )

                            [applied_promo] => Array
                            (
                            [code] => TATA
                            [discount] => 2469
                            [description] => Скидка 10% по промокоду TATA
                            )

                            [admin_user] => Array
                            (
                            [username] => admin
                            [name] => Administrator
                            [role] => admin
                            [permissions] => Array
                            (
                            [0] => all
                            )

                            [login_time] => 1756476658
                            )

                            [admin_last_activity] => 1756481794
                            )
                            Рекомендации

                            Отключить режим обслуживания
                            Попробовать открыть настройки снова
                            Вернуться в админкуhttp://www.w3.org/2000/svg">
                            <path
                                d="M11.7603 5.82319C11.5048 5.82319 11.2697 5.9028 11.0724 6.03549C10.904 5.54602 10.4425 5.19514 9.90269 5.19514C9.64728 5.19514 9.41218 5.27475 9.21482 5.40744C9.04647 4.91797 8.58499 4.56708 8.04514 4.56708C7.81875 4.56708 7.60687 4.629 7.42692 4.73515V2.67702C7.42692 1.9841 6.87255 1.41797 6.18758 1.41797C5.5026 1.41797 4.94824 1.98115 4.94824 2.67702V8.02285L3.87143 7.20314C3.2329 6.71662 2.32734 6.78149 1.76136 7.35647C1.39856 7.72504 1.39856 8.32361 1.76136 8.68924L6.17016 13.1682C6.6955 13.7019 7.39789 13.9967 8.14092 13.9967H9.59213C11.47 13.9967 12.9967 12.4458 12.9967 10.538V7.0793C12.9996 6.38637 12.4423 5.82319 11.7603 5.82319ZM12.3785 10.541C12.3785 12.1008 11.1275 13.3716 9.59213 13.3716H8.14092C7.56043 13.1416 7.01767 13.1416 6.60843 12.7259L2.19963 8.24695C2.07773 8.12311 2.07773 7.92555 2.19963 7.80171C2.39119 7.6071 2.6466 7.50685 2.90492 7.50685C3.1139 7.50685 3.32577 7.57172 3.49992 7.70735L5.07014 8.90449C5.16302 8.97525 5.29072 8.98705 5.39521 8.93397C5.4997 8.8809 5.56645 8.7718 5.56645 8.65385V2.67702C5.56645 2.32909 5.84509 2.04897 6.18467 2.04897C6.52426 2.04897 6.80289 2.33204 6.80289 2.67702V7.3948C6.80289 7.56877 6.94221 7.7103 7.11345 7.7103C7.2847 7.7103 7.42401 7.56877 7.42401 7.3948V5.82319C7.42401 5.47525 7.70265 5.19514 8.04223 5.19514C8.38182 5.19514 8.66045 5.4782 8.66045 5.82319V7.3948C8.66045 7.56877 8.79977 7.7103 8.97101 7.7103C9.14226 7.7103 9.28157 7.56877 9.28157 7.3948V6.45124C9.28157 6.10331 9.56021 5.82319 9.89979 5.82319C10.2394 5.82319 10.518 6.10626 10.518 6.45124V7.3948C10.518 7.56877 10.6573 7.7103 10.8286 7.7103C10.9998 7.7103 11.1391 7.56877 11.1391 7.3948V7.0793C11.1391 6.73136 11.4178 6.45124 11.7573 6.45124C12.0969 6.45124 12.3756 6.73431 12.3756 7.0793L12.3785 10.541Z"
                                fill="#1C1C1C"></path>
                            <path
                                d="M11.3053 2.21146L12.3705 1.10573L11.3053 0L11.1573 0.153328L11.97 0.99663H8.0459V1.21483H11.97L11.1573 2.05813L11.3053 2.21146Z"
                                fill="#31B939"></path>
                            <path
                                d="M1.06519 0L0 1.10573L1.06519 2.21146L1.21322 2.05813L0.400536 1.21483H4.32463V0.99663H0.400536L1.21322 0.153328L1.06519 0Z"
                                fill="#31B939"></path>
                        </svg>
                    </div>
                    <div class="swiper-container">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide">
                                <a href="<?= buildFilterUrl($search, '', $sort) ?>"
                                    class="filters-item<?= empty($category) ? ' active' : '' ?>">Все</a>
                            </div>
                            <?php foreach ($categories as $cat): ?>
                                <?php $catName = getCategoryName($cat); ?>
                                <?php $isActive = $category === $cat ? ' active' : ''; ?>
                                <div class="swiper-slide">
                                    <a href="<?= buildFilterUrl($search, $cat, $sort) ?>"
                                        class="filters-item<?= $isActive ?>"><?= $catName ?></a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="slider-prev-btn slider-btn swiper-button-disabled">
                        <img src="/image/slider-prev.svg" alt="">
                    </div>
                    <div class="slider-next-btn slider-btn">
                        <img src="/image/slider-next.svg" alt="">
                    </div>
                </div>
            </div>
        </section>

        <section class="shop-filters">
            <div class="wrapper">
                <div class="shop-filters__container">
                    <form class="filters-form" method="GET">
                        <div class="filter-group">
                            <select name="category" id="category">
                                <option value="">Все категории</option>
                                <?php foreach ($categories as $cat): ?>
                                    <?php $selected = $category === $cat ? ' selected' : ''; ?>
                                    <?php $catName = getCategoryName($cat); ?>
                                    <option value="<?= $cat ?>" <?= $selected ?>><?= $catName ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <select name="sort" id="sort">
                                <option value="newest" <?= $sort === 'newest' ? ' selected' : '' ?>>Сначала новые</option>
                                <option value="price_asc" <?= $sort === 'price_asc' ? ' selected' : '' ?>>По цене
                                    (возрастание)</option>
                                <option value="price_desc" <?= $sort === 'price_desc' ? ' selected' : '' ?>>По цене
                                    (убывание)</option>
                                <option value="name" <?= $sort === 'name' ? ' selected' : '' ?>>По названию</option>
                            </select>
                        </div>

                        <?php if ($search): ?>
                            <input type="hidden" name="search" value="<?= e($search) ?>" />
                        <?php endif; ?>

                        <button type="submit" class="filter-btn">Применить</button>
                    </form>
                </div>
            </div>
        </section>

        <section class="shop-products-count">
            <div class="wrapper">
                <p>Найдено <strong><?= $totalProducts ?></strong> товаров</p>
                <?php if ($totalPages > 1): ?>
                    <p class="shop-page-info">Страница <strong><?= $page ?></strong> из <strong><?= $totalPages ?></strong>
                    </p>
                <?php endif; ?>
            </div>
        </section>

        <section class="shop-products">
            <div class="wrapper">
                <div class="shop-products__container">

                    <?= getProductsGrid($currentPageProducts) ?>
                </div>
            </div>
        </section>

        <?php if ($totalPages > 1): ?>
            <?= getPagination($page, $totalPages, $search, $category, $sort) ?>
        <?php endif; ?>
    </main>

    <?php include 'includes/new-footer.php'; ?>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>
    <script src="/js/jquery.maskedinput.min.js"></script>
    <script src="js/main.js?v=<?php echo time(); ?>"></script>
    <script src="js/new-components.js"></script>
    <script src="js/form-handler.js?v=1.5"></script>
    <script src="js/cart.js?v=<?php echo time(); ?>"></script>
    <script src="js/carousel.js?v=<?php echo time(); ?>"></script>
    <script src="js/new-homepage.js?v=3.1"></script>
    <script src="js/shop-mobile-menu.js?v=1.0"></script>

    <script>
        // Обработка кнопки "Купить сейчас"
        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('buy-now-btn')) {
                const productId = e.target.dataset.productId;
                // Добавляем в корзину и переходим к оплате
                addToCart(productId);
                setTimeout(() => {
                    window.location.href = '/checkout.php';
                }, 500);
            }
        });

        // Функция добавления в корзину
        function addToCart(productId) {
            fetch('/api/cart.php?action=add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: 1
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message);
                        // Обновляем счетчик корзины в шапке
                        const cartCounter = document.querySelector('.cart-counter');
                        if (cartCounter && data.cart_count !== undefined) {
                            cartCounter.textContent = data.cart_count;
                            cartCounter.style.display = data.cart_count > 0 ? 'block' : 'none';

                            // Добавляем анимацию
                            cartCounter.style.animation = 'pulse 0.5s ease-in-out';
                            setTimeout(() => {
                                cartCounter.style.animation = '';
                            }, 500);
                        }
                    } else {
                        showNotification('Ошибка: ' + (data.error || 'Неизвестная ошибка'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Ошибка при добавлении в корзину');
                });
        }

        // Функция обновления счетчика корзины
        function updateCartCount(count) {
            const cartCounter = document.querySelector('.cart-counter');
            if (cartCounter) {
                cartCounter.textContent = count;
                cartCounter.style.display = count > 0 ? 'block' : 'none';

                // Добавляем анимацию
                cartCounter.style.animation = 'pulse 0.5s ease-in-out';
                setTimeout(() => {
                    cartCounter.style.animation = '';
                }, 500);
            }
        }

        // Функция показа уведомления
        function showNotification(message) {
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.textContent = message;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #28a745;
                color: white;
                padding: 12px 20px;
                border-radius: 8px;
                z-index: 1000;
                animation: slideIn 0.3s ease;
            `;
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Простая инициализация масок для модальных окон
        function applyMasksToPhoneFields() {
            console.log('🔧 applyMasksToPhoneFields вызвана');

            if (typeof $ === 'undefined') {
                console.log('❌ jQuery недоступен');
                return;
            }

            if (!$.fn.mask) {
                console.log('❌ Плагин маски недоступен');
                return;
            }

            // Применяем маски ко всем полям телефона на странице
            const allPhoneInputs = $('input[type="tel"], input[phoneMask_JS]');
            console.log('📱 Найдено полей телефона:', allPhoneInputs.length);

            allPhoneInputs.each(function (index, input) {
                const $input = $(input);
                console.log('📱 Обрабатываем поле', index + 1, ':', input);

                // Удаляем старую маску если есть
                $input.unmask();

                // Применяем новую маску
                $input.mask('+7 (999) 999-99-99');
                console.log('✅ Маска применена к полю', index + 1);
            });
        }

        // Альтернативная функция для прямого применения масок
        function applyPhoneMaskDirectly() {
            console.log('🔧 applyPhoneMaskDirectly вызвана');

            if (typeof $ === 'undefined') {
                console.log('❌ jQuery недоступен в applyPhoneMaskDirectly');
                return;
            }

            if (!$.fn.mask) {
                console.log('❌ Плагин маски недоступен в applyPhoneMaskDirectly');
                return;
            }

            // Прямое применение маски к полю в модальном окне "Заказать звонок"
            const phoneInput = $('#call-back-popup input[type="tel"]');
            if (phoneInput.length > 0) {
                console.log('📱 Найдено поле телефона в модальном окне "Заказать звонок"');
                phoneInput.unmask();
                phoneInput.mask('+7 (999) 999-99-99');
                console.log('✅ Маска применена к полю в модальном окне "Заказать звонок"');
            } else {
                console.log('❌ Поле телефона в модальном окне "Заказать звонок" не найдено');
            }
        }

        // Простая маска без jQuery (запасной вариант)
        function applySimplePhoneMask() {
            console.log('🔧 applySimplePhoneMask вызвана');

            const phoneInputs = document.querySelectorAll('input[type="tel"], input[phoneMask_JS]');
            console.log('📱 Найдено полей для простой маски:', phoneInputs.length);

            phoneInputs.forEach(function (input, index) {
                console.log('📱 Применяем простую маску к полю', index + 1);

                // Удаляем старые обработчики
                input.removeEventListener('input', input._maskHandler);
                input.removeEventListener('keydown', input._keydownHandler);

                // Создаем новый обработчик
                input._maskHandler = function (e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length > 0) {
                        value = '+7 (' + value.substring(0, 3) + ') ' + value.substring(3, 6) + '-' + value.substring(6, 8) + '-' + value.substring(8, 10);
                    }
                    e.target.value = value;
                };

                input._keydownHandler = function (e) {
                    if (e.key === 'Backspace' && e.target.value.length <= 4) {
                        e.preventDefault();
                        e.target.value = '';
                    }
                };

                // Добавляем обработчики
                input.addEventListener('input', input._maskHandler);
                input.addEventListener('keydown', input._keydownHandler);

                console.log('✅ Простая маска применена к полю', index + 1);
            });
        }

        // Инициализация при загрузке DOM
        $(document).ready(function () {
            console.log('🔧 Инициализация масок на странице магазина');

            // Проверяем доступность jQuery и плагина
            if (typeof $ !== 'undefined') {
                console.log('✅ jQuery доступен');
                if ($.fn.mask) {
                    console.log('✅ Плагин маски доступен');
                } else {
                    console.log('❌ Плагин маски НЕ доступен');
                }
            } else {
                console.log('❌ jQuery НЕ доступен');
            }

            // Применяем маски сразу
            applyMasksToPhoneFields();
            applyPhoneMaskDirectly();
            applySimplePhoneMask();

            // Применяем маски при клике на кнопки модальных окон
            $(document).on('click', '[data-popup], [popupopen]', function () {
                console.log('🔘 Клик по кнопке модального окна');
                setTimeout(applyMasksToPhoneFields, 100);
                setTimeout(applyPhoneMaskDirectly, 100);
                setTimeout(applySimplePhoneMask, 100);
                setTimeout(applyMasksToPhoneFields, 300);
                setTimeout(applyPhoneMaskDirectly, 300);
                setTimeout(applySimplePhoneMask, 300);
                setTimeout(applyMasksToPhoneFields, 500);
                setTimeout(applyPhoneMaskDirectly, 500);
                setTimeout(applySimplePhoneMask, 500);
            });

            // Применяем маски при изменении CSS классов модальных окон
            const observer = new MutationObserver(function (mutations) {
                mutations.forEach(function (mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        const target = mutation.target;
                        if (target.classList.contains('active') || target.classList.contains('open')) {
                            console.log('🔍 Модальное окно открылось через CSS класс');
                            setTimeout(applyMasksToPhoneFields, 100);
                            setTimeout(applyPhoneMaskDirectly, 100);
                            setTimeout(applyMasksToPhoneFields, 300);
                            setTimeout(applyPhoneMaskDirectly, 300);
                        }
                    }
                });
            });

            // Наблюдаем за всеми модальными окнами
            $('.popup').each(function () {
                observer.observe(this, {
                    attributes: true,
                    attributeFilter: ['class']
                });
            });

            console.log('👁️ Наблюдаем за', $('.popup').length, 'модальными окнами');

            // Дополнительные попытки применения масок
            setTimeout(applyMasksToPhoneFields, 1000);
            setTimeout(applyPhoneMaskDirectly, 1000);
            setTimeout(applyMasksToPhoneFields, 2000);
            setTimeout(applyPhoneMaskDirectly, 2000);
            setTimeout(applyMasksToPhoneFields, 3000);
            setTimeout(applyPhoneMaskDirectly, 3000);
        });
    </script>

    <style>
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Стили для ярлыков на карточках товаров */
        .product-card__badges-overlay {
            position: absolute;
            top: 12px;
            left: 12px;
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            z-index: 10;
        }

        .product-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border: none;
            line-height: 1.2;
            position: relative;
            z-index: 10;
        }

        @media (max-width: 768px) {
            .product-card__badges-overlay {
                top: 8px;
                left: 8px;
                gap: 3px;
            }

            .product-badge {
                font-size: 0.7rem;
                padding: 4px 8px;
                border-radius: 15px;
            }
        }
    </style>

    <!-- Yandex.Metrika counter -->
    <script type="text/javascript">
        (function (m, e, t, r, i, k, a) {
            m[i] = m[i] || function () { (m[i].a = m[i].a || []).push(arguments) };
            m[i].l = 1 * new Date();
            for (var j = 0; j < document.scripts.length; j++) { if (document.scripts[j].src === r) { return; } }
            k = e.createElement(t), a = e.getElementsByTagName(t)[0], k.async = 1, k.src = r, a.parentNode.insertBefore(k, a)
        })(window, document, 'script', 'https://mc.yandex.ru/metrika/tag.js?id=103948722', 'ym');

        ym(103948722, 'init', { ssr: true, webvisor: true, clickmap: true, ecommerce: "dataLayer", accurateTrackBounce: true, trackLinks: true });
    </script>
    <noscript>
        <div><img src="https://mc.yandex.ru/watch/103948722" style="position:absolute; left:-9999px;" alt="" /></div>
    </noscript>
    <!-- /Yandex.Metrika counter -->
</body>

</html>