<?php
require_once 'includes/Models/Order.php';
require_once 'includes/Models/Article.php';
require_once 'includes/Models/Meditation.php';
require_once 'includes/Models/Review.php';
require_once 'includes/Models/Product.php';
require_once 'includes/Database.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/functions.php';
require_once 'includes/products.php';

// Проверка режима обслуживания
if (isMaintenanceMode() && !isAdminAccess()) {
    header('Location: /maintenance.php');
    exit;
}

// Создаем экземпляры классов
$productManager = new ProductManager();
$cart = new Cart();

// Обработка действий с корзиной
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            $productId = $_POST['product_id'] ?? '';
            $quantity = (int) ($_POST['quantity'] ?? 1);
            if ($productId) {
                $cart->addItem($productId, $quantity);
            }
            break;

        case 'update':
            $productId = $_POST['product_id'] ?? '';
            $quantity = (int) ($_POST['quantity'] ?? 0);
            if ($productId) {
                $cart->updateQuantity($productId, $quantity);
            }
            break;

        case 'remove':
            $productId = $_POST['product_id'] ?? '';
            if ($productId) {
                $cart->removeItem($productId);
            }
            break;

        case 'clear':
            $cart->clear();
            break;
    }

    // Редирект для предотвращения повторной отправки формы
    header('Location: /cart.php');
    exit;
}

// Получение товаров в корзине
$cartItems = $cart->getItems();
$cartTotal = $cart->getTotal();

// Мета-данные страницы
$meta = [
    'title' => 'Корзина - Психолог Денис Черкас',
    'description' => 'Ваша корзина с товарами. Оформите заказ на семинары, книги и курсы.',
    'keywords' => 'корзина, заказ, покупка, семинары, книги'
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

    <link rel="canonical" href="https://cherkas-therapy.ru/cart.php" />
    <meta property="og:locale" content="ru_RU" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="<?= e($meta['title']) ?>" />
    <meta property="og:description" content="<?= e($meta['description']) ?>" />
    <meta property="og:url" content="https://cherkas-therapy.ru/cart.php" />
    <meta property="og:site_name" content="Психолог Денис Черкас" />

    <!-- Стили -->
    <link rel="stylesheet" href="/css/new-homepage.css?v=7.6" type="text/css" media="all" />
    <link rel="stylesheet" href="/css/pages.css" type="text/css" media="all" />
    <link rel="stylesheet" href="/css/shop.css?v=2.1" type="text/css" media="all" />
    <link rel="stylesheet" href="/css/cart.css" type="text/css" media="all" />
    <link rel="stylesheet" href="/css/new-components.css" />
    <link rel="stylesheet" href="/css/shop-mobile-header.css?v=1.0" type="text/css" media="all" />
</head>

<body class="cart-page">
    <?php include 'includes/new-header.php'; ?>

    <!-- Main Content Container -->
    <main class="main-content">
        <section class="cart-hero">
            <div class="wrapper">
                <div class="cart-hero__content">
                    <h1 class="cart-hero__title md-main-title">
                        <span style="color: #6a7e9f">КОРЗИНА</span>
                    </h1>
                    <p class="cart-hero__subtitle">
                        Проверьте выбранные товары и оформите заказ
                    </p>
                </div>
            </div>
        </section>

        <section class="cart-content">
            <div class="wrapper">
                <div class="cart-content__container">
                    <?= getCartContent($cartItems, $cartTotal) ?>
                </div>
            </div>
        </section>
    </main>
    <!-- End Main Content Container -->

    <?php include 'includes/new-footer.php'; ?>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/main.js?v=<?php echo time(); ?>"></script>
    <script src="js/new-components.js"></script>
    <script src="js/form-handler.js?v=1.5"></script>
    <script src="js/cart.js?v=<?php echo time(); ?>"></script>
    <script src="js/new-homepage.js?v=3.1"></script>
    <script src="js/shop-mobile-menu.js?v=1.0"></script>

    <script>
        // Инициализация CartManager
        document.addEventListener('DOMContentLoaded', function () {
            new CartManager();
        });

        // Функция применения промокода
        async function applyPromoCode() {
            const promoInput = document.getElementById('promoCodeInput');
            const applyBtn = document.getElementById('applyPromoBtn');
            const messageDiv = document.getElementById('promoMessage');

            const promoCode = promoInput.value.trim();

            if (!promoCode) {
                showPromoMessage('Введите промокод', 'error');
                return;
            }

            // Получаем общую сумму корзины
            const totalAmount = getCartTotal();

            // Показываем загрузку
            applyBtn.disabled = true;
            applyBtn.innerHTML = '<svg class="spinner" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"></circle></svg> Применение...';

            try {
                const response = await fetch('/api/apply-promo.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        promo_code: promoCode,
                        cart_total: totalAmount
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showPromoMessage(data.message, 'success');
                    // Перезагружаем страницу для обновления итогов
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showPromoMessage(data.error, 'error');
                }
            } catch (error) {
                showPromoMessage('Ошибка при применении промокода', 'error');
            } finally {
                // Восстанавливаем кнопку
                applyBtn.disabled = false;
                applyBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9 12L11 14L15 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2"/></svg> Применить';
            }
        }

        // Функция поиска покупок
        async function findPurchases() {
            const emailInput = document.getElementById('purchaseEmailInput');
            const findBtn = document.getElementById('findPurchasesBtn');
            const messageDiv = document.getElementById('purchasesMessage');
            const listDiv = document.getElementById('purchasesList');

            const email = emailInput.value.trim();

            if (!email) {
                showPurchasesMessage('Введите email', 'error');
                return;
            }

            // Показываем загрузку
            findBtn.disabled = true;
            findBtn.innerHTML = '<svg class="spinner" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"></circle></svg> Поиск...';

            try {
                const response = await fetch('/api/my-purchases.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        email: email
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showPurchasesMessage(data.message, 'success');
                    if (data.purchases.length > 0) {
                        displayPurchases(data.purchases);
                    }
                } else {
                    showPurchasesMessage(data.error, 'error');
                }
            } catch (error) {
                showPurchasesMessage('Ошибка при поиске покупок', 'error');
            } finally {
                // Восстанавливаем кнопку
                findBtn.disabled = false;
                findBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M21 21L16.65 16.65" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M11 6C13.7614 6 16 8.23858 16 11C16 13.7614 13.7614 16 11 16C8.23858 16 6 13.7614 6 11C6 8.23858 8.23858 6 11 6Z" stroke="currentColor" stroke-width="2"/></svg> Найти покупки';
            }
        }

        // Функция отображения покупок
        function displayPurchases(purchases) {
            const listDiv = document.getElementById('purchasesList');

            let html = '<div class="purchases-items">';

            purchases.forEach(purchase => {
                const date = new Date(purchase.date).toLocaleDateString('ru-RU');
                const status = getStatusText(purchase.status);

                html += `
                <div class="purchase-item">
                    <div class="purchase-header">
                        <div class="purchase-info">
                            <span class="purchase-id">Заказ #${purchase.order_id}</span>
                            <span class="purchase-date">${date}</span>
                        </div>
                        <span class="purchase-status ${purchase.status}">${status}</span>
                    </div>
                    <div class="purchase-items">`;

                purchase.items.forEach(item => {
                    html += `
                    <div class="purchase-item-product">
                        <span class="product-name">${item.title}</span>
                        <span class="product-quantity">x${item.quantity}</span>
                        <span class="product-price">${item.price} ₽</span>
                    </div>`;
                });

                html += `
                    </div>
                    <div class="purchase-total">
                        <span class="total-label">Итого:</span>
                        <span class="total-amount">${purchase.total} ₽</span>
                    </div>
                </div>`;
            });

            html += '</div>';

            listDiv.innerHTML = html;
            listDiv.style.display = 'block';
        }

        // Вспомогательные функции
        function getCartTotal() {
            const totalElement = document.querySelector('.total-amount');
            if (totalElement) {
                return parseInt(totalElement.textContent.replace(/[^\d]/g, ''));
            }
            return 0;
        }

        function showPromoMessage(message, type) {
            const messageDiv = document.getElementById('promoMessage');
            messageDiv.textContent = message;
            messageDiv.className = `promo-message ${type}`;
            messageDiv.style.display = 'block';

            setTimeout(() => {
                messageDiv.style.display = 'none';
            }, 5000);
        }

        function showPurchasesMessage(message, type) {
            const messageDiv = document.getElementById('purchasesMessage');
            messageDiv.textContent = message;
            messageDiv.className = `purchases-message ${type}`;
            messageDiv.style.display = 'block';

            setTimeout(() => {
                messageDiv.style.display = 'none';
            }, 5000);
        }

        function getStatusText(status) {
            const statuses = {
                'completed': 'Выполнен',
                'processing': 'В обработке',
                'shipped': 'Отправлен',
                'delivered': 'Доставлен',
                'cancelled': 'Отменен'
            };
            return statuses[status] || status;
        }
    </script>

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

<?php
function getCartContent($items, $total)
{
    if (empty($items)) {
        return '
        <div class="cart-empty">
            <div class="cart-empty__icon">
                <svg width="80" height="80" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 22C9.55228 22 10 21.5523 10 21C10 20.4477 9.55228 20 9 20C8.44772 20 8 20.4477 8 21C8 21.5523 8.44772 22 9 22Z" stroke="#6a7e9f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M20 22C20.5523 22 21 21.5523 21 21C21 20.4477 20.5523 20 20 20C19.4477 20 19 20.4477 19 21C19 21.5523 19.4477 22 20 22Z" stroke="#6a7e9f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M1 1H5L7.68 14.39C7.77144 14.8504 8.02191 15.264 8.38755 15.5583C8.75318 15.8526 9.2107 16.009 9.68 16H19.4C19.8693 16.009 20.3268 15.8526 20.6925 15.5583C21.0581 15.264 21.3086 14.8504 21.4 14.39L23 6H6" stroke="#6a7e9f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <h2 class="cart-empty__title">Корзина пуста</h2>
            <p class="cart-empty__text">Добавьте товары в корзину, чтобы оформить заказ</p>
            <a href="/shop.php" class="cart-empty__btn md-main-color-btn">
                <span>Перейти в магазин</span>
            </a>
        </div>

        <!-- Раздел "Мои покупки" для пустой корзины -->
        <div class="my-purchases-section">
            <h3 class="my-purchases-title">Мои покупки</h3>
            <p class="my-purchases-description">Введите email, чтобы посмотреть историю покупок</p>

            <div class="my-purchases-form">
                <input type="email" id="purchaseEmailInput" class="purchase-email-input" placeholder="Ваш email" required>
                <button type="button" id="findPurchasesBtn" class="find-purchases-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M21 21L16.65 16.65" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M11 6C13.7614 6 16 8.23858 16 11C16 13.7614 13.7614 16 11 16C8.23858 16 6 13.7614 6 11C6 8.23858 8.23858 6 11 6Z" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    Найти покупки
                </button>
            </div>

            <div id="purchasesMessage" class="purchases-message" style="display: none;"></div>
            <div id="purchasesList" class="purchases-list" style="display: none;"></div>
        </div>';
    }

    // Получаем примененный промокод из сессии
    $appliedPromo = $_SESSION['applied_promo'] ?? null;
    $discount = $appliedPromo['discount'] ?? 0;
    $finalTotal = $total - $discount;

    $output = '
    <div class="cart-layout">
        <div class="cart-main">
            <div class="cart-header">
                <div class="cart-header__title">
                    <h2>Товары в корзине</h2>
                    <span class="cart-count">' . count($items) . ' ' . getPluralForm(count($items), 'товар', 'товара', 'товаров') . '</span>
                </div>
                <button type="button" class="clear-cart-btn" onclick="clearCart()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 6H5H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M8 6V4C8 3.46957 8.21071 2.96086 8.58579 2.58579C8.96086 2.21071 9.46957 2 10 2H14C14.5304 2 15.0391 2.21071 15.4142 2.58579C15.7893 2.96086 16 3.46957 16 4V6M19 6V20C19 20.5304 18.7893 21.0391 18.4142 21.4142C18.0391 21.7893 17.5304 22 17 22H7C6.46957 22 5.96086 21.7893 5.58579 21.4142C5.21071 21.0391 5 20.5304 5 20V6H19Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Очистить корзину
                </button>
            </div>

            <div class="cart-items">';

    foreach ($items as $item) {
        $product = $item['product'];
        $output .= '
                <div class="cart-item" data-product-id="' . e($product['id']) . '">
                    <div class="cart-item__image">
                        <img src="' . e($product['image']) . '" alt="' . e($product['title']) . '" />
                    </div>

                    <div class="cart-item__content">
                        <div class="cart-item__header">
                            <h3 class="cart-item__title">
                                <a href="/product.php?slug=' . e($product['slug']) . '">' . e($product['title']) . '</a>
                            </h3>
                            <div class="cart-item__category">' . getCategoryName($product['category_name'] ?? $product['category'] ?? '') . '</div>
                        </div>

                        <div class="cart-item__price">
                            <span class="current-price">' . number_format($product['price'], 0, ',', ' ') . ' ₽</span>
                            ' . (!empty($product['old_price']) && $product['old_price'] > $product['price'] ?
            '<span class="old-price">' . number_format($product['old_price'], 0, ',', ' ') . ' ₽</span>' : '') . '
                        </div>
                    </div>

                    <div class="cart-item__quantity">
                        <div class="quantity-controls">
                            <button type="button" class="quantity-btn quantity-btn--minus" data-product-id="' . e($product['id']) . '">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                            <input type="number" class="quantity-input" value="' . $item['quantity'] . '" 
                                   min="1" max="99" data-product-id="' . e($product['id']) . '" />
                            <button type="button" class="quantity-btn quantity-btn--plus" data-product-id="' . e($product['id']) . '">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 5V19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                        </div>
                        <button type="button" class="quantity-confirm-btn" data-product-id="' . e($item['product']['id']) . '" style="display: none;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Подтвердить
                        </button>
                    </div>

                    <div class="cart-item__total">
                        <span class="item-total" data-product-id="' . e($item['product']['id']) . '" data-price="' . $item['product']['price'] . '">' . number_format($item['total'], 0, ',', ' ') . ' ₽</span>
                    </div>

                    <div class="cart-item__actions">
                        <button type="button" class="remove-item-btn" data-product-id="' . e($item['product']['id']) . '">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>
                </div>';
    }

    $output .= '
            </div>
        </div>

        <div class="cart-sidebar">
            <div class="cart-summary">
                <h3 class="cart-summary__title">Итого</h3>

                <div class="cart-summary__items">';

    foreach ($items as $item) {
        $output .= '
                    <div class="summary-item" data-product-id="' . e($item['product']['id']) . '">
                        <div class="summary-item__info">
                            <span class="summary-item__name">' . e($item['product']['title']) . '</span>
                            <span class="summary-item__quantity">x' . $item['quantity'] . '</span>
                        </div>
                        <span class="summary-item__price" data-product-id="' . e($item['product']['id']) . '" data-price="' . $item['product']['price'] . '">' . number_format($item['total'], 0, ',', ' ') . ' ₽</span>
                    </div>';
    }

    $output .= '
                </div>

                <div class="cart-summary__subtotal">
                    <span class="subtotal-label">Подытог:</span>
                    <span class="subtotal-amount">' . number_format($total, 0, ',', ' ') . ' ₽</span>
                </div>';

    // Показываем скидку, если применен промокод
    if ($appliedPromo) {
        $output .= '
                <div class="cart-summary__discount">
                    <span class="discount-label">Скидка (' . e($appliedPromo['code']) . '):</span>
                    <span class="discount-amount">-' . number_format($discount, 0, ',', ' ') . ' ₽</span>
                </div>';
    }

    $output .= '
                <div class="cart-summary__total">
                    <span class="total-label">Общая сумма:</span>
                    <span class="total-amount">' . number_format($finalTotal, 0, ',', ' ') . ' ₽</span>
                </div>

                <!-- Поле для промокода -->
                <div class="promo-code-section">
                    <h4 class="promo-code-title">Промокод</h4>
                    <div class="promo-code-form">
                        <input type="text" id="promoCodeInput" class="promo-code-input" placeholder="Введите промокод" maxlength="20">
                        <button type="button" id="applyPromoBtn" class="apply-promo-btn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9 12L11 14L15 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2"/>
                            </svg>
                            Применить
                        </button>
                    </div>
                    <div id="promoMessage" class="promo-message" style="display: none;"></div>
                    ' . ($appliedPromo ? '<div class="applied-promo">Применен: <strong>' . e($appliedPromo['code']) . '</strong> - ' . e($appliedPromo['description']) . '</div>' : '') . '
                </div>

                <div class="cart-summary__actions">
                    <a href="/shop.php" class="continue-shopping-btn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M19 12H5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 19L5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>Продолжить покупки</span>
                    </a>
                    <a href="/checkout.php" class="checkout-btn md-main-color-btn">
                        <span>Оформить заказ</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 5L19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Раздел "Мои покупки" - отдельно под корзиной -->
    <div class="my-purchases-section">
        <h3 class="my-purchases-title">Мои покупки</h3>
        <p class="my-purchases-description">Введите email, чтобы посмотреть историю покупок</p>

        <div class="my-purchases-form">
            <input type="email" id="purchaseEmailInput" class="purchase-email-input" placeholder="Ваш email" required>
            <button type="button" id="findPurchasesBtn" class="find-purchases-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M21 21L16.65 16.65" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M11 6C13.7614 6 16 8.23858 16 11C16 13.7614 13.7614 16 11 16C8.23858 16 6 13.7614 6 11C6 8.23858 8.23858 6 11 6Z" stroke="currentColor" stroke-width="2"/>
                </svg>
                Найти покупки
            </button>
        </div>

        <div id="purchasesMessage" class="purchases-message" style="display: none;"></div>
        <div id="purchasesList" class="purchases-list" style="display: none;"></div>
    </div>';

    return $output;
}

// Функция для получения названия категории
function getCategoryName($category)
{
    $categories = [
        'seminars' => 'Семинары',
        'books' => 'Книги',
        'courses' => 'Курсы'
    ];

    return $categories[$category] ?? $category;
}

// Функция для склонения слов
function getPluralForm($number, $form1, $form2, $form5)
{
    $number = abs($number) % 100;
    $number1 = $number % 10;
    if ($number > 10 && $number < 20)
        return $form5;
    if ($number1 > 1 && $number1 < 5)
        return $form2;
    if ($number1 == 1)
        return $form1;
    return $form5;
}
?>