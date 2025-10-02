<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/functions.php';
require_once 'includes/products.php';

// Создаем экземпляр ProductManager
$productManager = new ProductManager();

// Получаем slug товара из URL
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header('Location: /shop.php');
    exit;
}

// Получаем данные товара
$product = null;
$allProducts = $productManager->getAllProducts();
foreach ($allProducts as $p) {
    if ($p['slug'] === $slug) {
        $product = $p;
        break;
    }
}

if (!$product) {
    header('Location: /shop.php');
    exit;
}

// Проверяем наличие акции и таймера
$hasSale = !empty($product['sale_end_date']) && strtotime($product['sale_end_date']) > time();
$saleEndDate = $hasSale ? $product['sale_end_date'] : '';

// Получаем галерею изображений
$gallery = $product['gallery'] ?? [];
if (!is_array($gallery)) {
    $gallery = json_decode($gallery, true) ?: [];
}
if (empty($gallery)) {
    $gallery = [$product['image']]; // По умолчанию главное изображение
} else {
    // Проверяем существование файлов и добавляем основное изображение если галерея пустая
    $validGallery = [];
    foreach ($gallery as $image) {
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . $image)) {
            $validGallery[] = $image;
        }
    }
    if (empty($validGallery)) {
        $gallery = [$product['image']];
    } else {
        $gallery = $validGallery;
    }
}

// Мета-данные страницы
$meta = [
    'title' => $product['title'] . ' - Магазин психолога Дениса Черкаса',
    'description' => $product['short_description'],
    'keywords' => implode(', ', is_array($product['tags'] ?? []) ? $product['tags'] : (json_decode($product['tags'] ?? '[]', true) ?: []))
];
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

    <link rel="canonical" href="https://cherkas-therapy.ru/product.php?slug=<?= $slug ?>" />
    <meta property="og:locale" content="ru_RU" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="<?= e($meta['title']) ?>" />
    <meta property="og:description" content="<?= e($meta['description']) ?>" />
    <meta property="og:url" content="https://cherkas-therapy.ru/product.php?slug=<?= $slug ?>" />
    <meta property="og:site_name" content="Психолог Денис Черкас" />
    <meta property="og:image" content="https://cherkas-therapy.ru<?= e($product['image']) ?>" />
    <meta property="og:image:width" content="1920" />
    <meta property="og:image:height" content="1080" />
    <meta property="og:image:type" content="image/jpeg" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?= e($meta['title']) ?>" />
    <meta name="twitter:image" content="https://cherkas-therapy.ru<?= e($product['image']) ?>" />

    <!-- Стили -->
    <link rel="stylesheet" href="/css/new-components.css" />
    <link rel="stylesheet" href="/css/new-homepage.css?v=7.6" type="text/css" media="all" />
    <link rel="stylesheet" href="/css/pages.css" type="text/css" media="all" />
    <link rel="stylesheet" href="/css/shop.css?v=2.1" type="text/css" media="all" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css" />
</head>

<body class="product-page">
    <?php include 'includes/new-header.php'; ?>

    <!-- Main Content Container -->
    <main class="main-content">

        <!-- Хлебные крошки -->
        <section class="breadcrumbs">
            <div class="wrapper">
                <nav class="breadcrumb-nav">
                    <a href="/" class="breadcrumb-item">Главная</a>
                    <span class="breadcrumb-separator">/</span>
                    <a href="/shop.php" class="breadcrumb-item">Магазин</a>
                    <span class="breadcrumb-separator">/</span>
                    <span class="breadcrumb-item active"><?= e($product['title']) ?></span>
                </nav>
            </div>
        </section>

        <section class="product-hero">
            <div class="wrapper">
                <div class="product-layout">
                    <!-- Левая часть - информация о товаре -->
                    <div class="product-info">
                        <h1 class="product-title"><?= e($product['title']) ?></h1>
                        <p class="product-description"><?= e($product['short_description']) ?></p>

                        <div class="product-price-container">
                            <?php if ($product['type'] === 'free'): ?>
                                <span class="product-price product-price--free">Бесплатно</span>
                            <?php else: ?>
                                <span class="product-price"><?= number_format($product['price'], 0, ',', ' ') ?> ₽</span>
                            <?php endif; ?>
                            <?php if (!empty($product['old_price']) && $product['old_price'] > $product['price'] && $product['type'] !== 'free'): ?>
                                <span class="product-price-old"><?= number_format($product['old_price'], 0, ',', ' ') ?>
                                    ₽</span>
                            <?php endif; ?>
                        </div>

                        <?php if ($hasSale): ?>
                            <div class="product-timer" data-end-date="<?= $saleEndDate ?>">
                                <div class="timer-label">Акция заканчивается:</div>
                                <div class="timer-display">
                                    <span class="timer-days">00</span>д
                                    <span class="timer-hours">00</span>ч
                                    <span class="timer-minutes">00</span>м
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="product-buttons">
                            <?= getProductPageButtons($product) ?>
                        </div>
                    </div>

                    <!-- Правая часть - карусель -->
                    <div class="product-gallery">
                        <div class="swiper product-swiper">
                            <div class="swiper-wrapper">
                                <?php foreach ($gallery as $image): ?>
                                    <div class="swiper-slide">
                                        <img src="<?= e($image) ?>" alt="<?= e($product['title']) ?>" />
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="swiper-pagination"></div>
                            <div class="swiper-button-next"></div>
                            <div class="swiper-button-prev"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Характеристики товара -->
        <section class="product-specs">
            <div class="wrapper">
                <h2>Характеристики</h2>
                <div class="specs-grid">
                    <div class="specs-table">
                        <h3>Основная информация</h3>
                        <div class="spec-row">
                            <div class="spec-label">Категория:</div>
                            <div class="spec-value"><?= getCategoryName($product['category']) ?></div>
                        </div>
                        <div class="spec-row">
                            <div class="spec-label">Тип:</div>
                            <div class="spec-value"><?= getProductTypeName($product['type']) ?></div>
                        </div>
                        <div class="spec-row">
                            <div class="spec-label">Цена:</div>
                            <div class="spec-value"><?= number_format($product['price'], 0, ',', ' ') ?> ₽</div>
                        </div>
                        <?php if (!empty($product['old_price']) && $product['old_price'] > $product['price']): ?>
                            <div class="spec-row">
                                <div class="spec-label">Старая цена:</div>
                                <div class="spec-value old-price"><?= number_format($product['old_price'], 0, ',', ' ') ?> ₽
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="specs-table">
                        <h3>Детали</h3>
                        <?php foreach ($product['features'] ?? [] as $feature): ?>
                            <?php $parts = explode(':', $feature, 2); ?>
                            <?php if (count($parts) === 2): ?>
                                <div class="spec-row">
                                    <div class="spec-label"><?= e(trim($parts[0])) ?>:</div>
                                    <div class="spec-value"><?= e(trim($parts[1])) ?></div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- Описание товара -->
        <section class="product-content">
            <div class="wrapper">
                <h2>Описание</h2>
                <div class="content-text">
                    <?= $product['content'] ?>
                </div>
            </div>
        </section>

        <!-- Рекомендуемые товары -->
        <section class="related-products">
            <div class="wrapper">
                <h2>Рекомендуемые товары</h2>
                <div class="products-grid">
                    <?= getRelatedProducts($product, $allProducts) ?>
                </div>
            </div>
        </section>

        <!-- Промоблок -->
        <section class="promo-section">
            <div class="wrapper">
                <div class="promo-block promo-block--primary">
                    <div class="promo-block__content">
                        <h3 class="promo-block__title">Первая консультация всего 1000₽</h3>
                        <p class="promo-block__description">Получите профессиональную помощь психолога по доступной цене
                        </p>
                        <a href="#consultation-popup" class="promo-block__btn">Записаться</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Комментарии -->
        <section class="product-comments">
            <div class="wrapper">
                <h2>Отзывы</h2>
                <div class="comments-container">
                    <p>Отзывы будут добавлены позже</p>
                </div>
            </div>
        </section>

        <script>
            // Инициализация карусели
            const productSwiper = new Swiper(".product-swiper", {
                slidesPerView: 1,
                spaceBetween: 20,
                loop: true,
                pagination: {
                    el: ".swiper-pagination",
                    clickable: true,
                },
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false,
                },
            });

            // Таймер обратного отсчета
            function updateProductTimer() {
                const timer = document.querySelector(".product-timer");
                if (!timer) return;

                const endDate = new Date(timer.dataset.endDate).getTime();
                const now = new Date().getTime();
                const distance = endDate - now;

                if (distance > 0) {
                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));

                    const daysEl = timer.querySelector(".timer-days");
                    const hoursEl = timer.querySelector(".timer-hours");
                    const minutesEl = timer.querySelector(".timer-minutes");

                    if (daysEl) daysEl.textContent = days.toString().padStart(2, "0");
                    if (hoursEl) hoursEl.textContent = hours.toString().padStart(2, "0");
                    if (minutesEl) minutesEl.textContent = minutes.toString().padStart(2, "0");
                } else {
                    timer.style.display = "none";
                }
            }

            if (document.querySelector(".product-timer")) {
                updateProductTimer();
                setInterval(updateProductTimer, 60000);
            }
        </script>

    </main>
    <!-- End Main Content Container -->

    <?php include 'includes/new-footer.php'; ?>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
    <script src="js/main.js?v=<?php echo time(); ?>"></script>
    <script src="js/new-components.js"></script>
    <script src="js/form-handler.js?v=1.5"></script>
    <script src="js/cart.js?v=<?php echo time(); ?>"></script>

    <script>
        // Обработка кнопок товара
        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('buy-now-btn')) {
                const productId = e.target.dataset.productId;
                const productType = e.target.dataset.productType;
                // Переход к оформлению заказа
                window.location.href = '/checkout.php?product_id=' + productId + '&type=' + productType;
            }

            if (e.target.classList.contains('add-to-cart-btn')) {
                const productId = e.target.dataset.productId;
                addToCart(productId);
            }

            if (e.target.classList.contains('download-btn')) {
                const productId = e.target.dataset.productId;
                // Проверка подписки на Telegram канал
                checkTelegramSubscription(productId);
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

        // Функция проверки подписки
        function checkTelegramSubscription(productId) {
            // Логика проверки подписки на Telegram
            alert('Для скачивания необходимо подписаться на Telegram канал!');
            window.open('https://t.me/taterapia', '_blank');
        }

        // Функция показа уведомлений
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
    </style>

</body>

</html>

<?php
function getProductPageButtons($product)
{
    $type = $product['type'] ?? 'digital';

    switch ($type) {
        case 'free':
            // 1) Бесплатный товар - кнопка "Скачать" с подпиской на Telegram
            return '
                <button class="product-btn product-btn--primary download-btn" data-product-id="' . e($product['id']) . '">
                    Скачать
                </button>
                <div class="download-note">
                    Скачать можно только при наличии подписки на <a href="https://t.me/taterapia" target="_blank">Telegram канал</a>
                </div>';

        case 'discussion':
            // 2) Товар для обсуждения - кнопки Telegram и WhatsApp
            return '
                <a href="https://t.me/' . e(str_replace('@', '', $product['telegram_contact'] ?? 'taterapia')) . '" class="product-btn product-btn--primary" target="_blank">
                    📱 Telegram
                </a>
                <a href="https://wa.me/' . e(str_replace('+', '', $product['whatsapp_contact'] ?? '79936202951')) . '?text=Здравствуйте! Интересует ' . urlencode($product['title']) . '" class="product-btn product-btn--secondary" target="_blank">
                    💬 WhatsApp
                </a>
                <div class="contact-note">Обсудить детали</div>';

        case 'digital':
            // 3) Цифровой товар - "В корзину" и "Купить сейчас"
            return '
                <button class="product-btn product-btn--secondary add-to-cart-btn" data-product-id="' . e($product['id']) . '" data-product-type="digital">
                    В КОРЗИНУ
                </button>
                <button class="product-btn product-btn--primary buy-now-btn" data-product-id="' . e($product['id']) . '" data-product-type="digital">
                    КУПИТЬ СЕЙЧАС
                </button>';

        case 'physical':
            // 4) Физический товар - "В корзину" и "Купить сейчас" (с доставкой)
            return '
                <button class="product-btn product-btn--secondary add-to-cart-btn" data-product-id="' . e($product['id']) . '" data-product-type="physical">
                    В КОРЗИНУ
                </button>
                <button class="product-btn product-btn--primary buy-now-btn" data-product-id="' . e($product['id']) . '" data-product-type="physical">
                    КУПИТЬ СЕЙЧАС
                </button>';

        default:
            // По умолчанию - цифровой товар
            return '
                <button class="product-btn product-btn--secondary add-to-cart-btn" data-product-id="' . e($product['id']) . '" data-product-type="digital">
                    В КОРЗИНУ
                </button>
                <button class="product-btn product-btn--primary buy-now-btn" data-product-id="' . e($product['id']) . '" data-product-type="digital">
                    КУПИТЬ СЕЙЧАС
                </button>';
    }
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

// Функция для получения названия типа товара
function getProductTypeName($type)
{
    $types = [
        'free' => 'Бесплатный',
        'discussion' => 'Для обсуждения',
        'digital' => 'Цифровой',
        'physical' => 'Физический'
    ];

    return $types[$type] ?? $type;
}

// Функция для получения рекомендуемых товаров
function getRelatedProducts($currentProduct, $allProducts)
{
    $related = [];
    $currentCategory = $currentProduct['category_name'] ?? $currentProduct['category'];

    foreach ($allProducts as $product) {
        $productCategory = $product['category_name'] ?? $product['category'];
        if ($product['id'] !== $currentProduct['id'] && $productCategory === $currentCategory) {
            $related[] = $product;
            if (count($related) >= 4)
                break;
        }
    }

    if (empty($related)) {
        // Если нет товаров той же категории, берем любые
        foreach ($allProducts as $product) {
            if ($product['id'] !== $currentProduct['id']) {
                $related[] = $product;
                if (count($related) >= 4)
                    break;
            }
        }
    }

    $output = '';
    foreach ($related as $product) {
        $output .= '
        <div class="product-card">
            <div class="product-card__image">
                <img src="' . e($product['image']) . '" alt="' . e($product['title']) . '" />
            </div>
            <div class="product-card__content">
                <h3 class="product-card__title">' . e($product['title']) . '</h3>
                <div class="product-card__price-container">
                    ' . (($product['type'] === 'free') ?
            '<span class="product-card__price product-card__price--free">Бесплатно</span>' :
            '<span class="product-card__price">' . number_format($product['price'], 0, ',', ' ') . ' ₽</span>'
        ) . '
                </div>
                <div class="product-card__buttons">
                    <a href="/product.php?slug=' . e($product['slug']) . '" class="product-card__btn product-card__btn--primary">
                        Подробнее
                    </a>
                </div>
            </div>
        </div>';
    }

    return $output;
}
