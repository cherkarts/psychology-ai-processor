<?php
// CSRF токен уже генерируется в template.php
?>
<header class="header">
    <div class="header__top">
        <div class="container">
            <div class="header__content">
                <div class="header__logo">
                    <a href="/">
                        <img src="/image/logo.png" alt="Психолог Денис Черкас">
                    </a>
                </div>

                <div class="header__info">
                    <div class="header__schedule">
                        <span>Приемы Online</span>
                        <span>Пн-Пт: 9-22</span>
                    </div>
                </div>

                <div class="header__social">
                    <p>Задайте вопрос, <strong>я онлайн</strong></p>
                    <div class="social-links">
                        <a href="https://wa.me/+79936202951" target="_blank" aria-label="WhatsApp">
                            <img src="/image/whats-app.png" alt="WhatsApp">
                        </a>
                        <a href="<?php echo getContactSettings()['telegram_url']; ?>" target="_blank"
                            aria-label="Telegram">
                            <img src="/image/telegram.png" alt="Telegram">
                        </a>
                    </div>
                </div>

                <?php
                $uri = $_SERVER['REQUEST_URI'] ?? '';
                $path = parse_url($uri, PHP_URL_PATH) ?: '/';
                // Показываем иконку корзины в магазине, карточках товара, на странице корзины и оформлении заказа
                $showCart = (
                    strpos($path, '/shop') !== false ||
                    strpos($path, '/product') !== false ||
                    strpos($path, '/tovar') !== false ||
                    strpos($path, '/cart') !== false ||
                    strpos($path, '/checkout') !== false
                );

                // Отладочная информация
                echo "<!-- DEBUG: URI = $uri, Path = $path, ShowCart = " . ($showCart ? 'true' : 'false') . " -->";

                if ($showCart):
                    ?>
                    <div class="header__cart">
                        <a href="/cart.php" class="cart-link">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path
                                    d="M9 22C9.55228 22 10 21.5523 10 21C10 20.4477 9.55228 20 9 20C8.44772 20 8 20.4477 8 21C8 21.5523 8.44772 22 9 22Z"
                                    stroke="currentColor" stroke-width="2" />
                                <path
                                    d="M20 22C20.5523 22 21 21.5523 21 21C21 20.4477 20.5523 20 20 20C19.4477 20 19 20.4477 19 21C19 21.5523 19.4477 22 20 22Z"
                                    stroke="currentColor" stroke-width="2" />
                                <path
                                    d="M1 1H5L7.68 14.39C7.77144 14.8504 8.02191 15.264 8.38755 15.5583C8.75318 15.8526 9.2107 16.009 9.68 16H19.4C19.8693 16.009 20.3268 15.8526 20.6925 15.5583C21.0581 15.264 21.3086 14.8504 21.4 14.39L23 6H6"
                                    stroke="currentColor" stroke-width="2" />
                            </svg>
                            <span class="cart-counter">0</span>
                        </a>
                    </div>
                <?php endif; ?>

                <div class="header__contacts">
                    <a
                        href="tel:<?php echo str_replace([' ', '(', ')', '-'], '', getContactSettings()['phone']); ?>"><?php echo getContactSettings()['phone']; ?></a>
                    <button class="call-back-btn" data-popup="call-back-popup">
                        Заказать звонок сейчас
                    </button>
                </div>

                <!-- Мобильная контактная информация в шапке -->
                <div class="header__mobile-contacts">
                    <div class="header__mobile-contact-item">
                        <span>Пн-Пт: 9-22</span>
                    </div>
                    <div class="header__mobile-phone">
                        <a
                            href="tel:<?php echo str_replace([' ', '(', ')', '-'], '', getContactSettings()['phone']); ?>"><?php echo getContactSettings()['phone']; ?></a>
                    </div>
                </div>

                <button class="header__menu-btn" aria-label="Меню">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
    </div>

    <nav class="header__nav">
        <div class="container">
            <button class="header__nav-close" aria-label="Закрыть меню">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </button>

            <div class="nav-wrap">
                <ul class="nav">
                    <?php $isHome = ($path === '/' || $path === '/index.php'); ?>
                    <li class="nav-item"><a href="/" class="nav-link<?= $isHome ? ' active' : '' ?>">Главная</a></li>
                    <li class="nav-item"><a href="/services"
                            class="nav-link<?= basename($_SERVER['PHP_SELF']) == 'services.php' ? ' active' : '' ?>">Услуги</a>
                    </li>
                    <li class="nav-item"><a href="/about"
                            class="nav-link<?= basename($_SERVER['PHP_SELF']) == 'about.php' ? ' active' : '' ?>">Обо
                            мне</a></li>
                    <li class="nav-item"><a href="/reviews"
                            class="nav-link<?= basename($_SERVER['PHP_SELF']) == 'reviews.php' ? ' active' : '' ?>">Отзывы</a>
                    </li>
                    <li class="nav-item"><a href="/prices"
                            class="nav-link<?= basename($_SERVER['PHP_SELF']) == 'prices.php' ? ' active' : '' ?>">Цены</a>
                    </li>
                    <?php $isArticles = (basename($_SERVER['PHP_SELF']) == 'articles.php' || basename($_SERVER['PHP_SELF']) == 'article.php'); ?>
                    <li class="nav-item"><a href="/articles"
                            class="nav-link<?= $isArticles ? ' active' : '' ?>">Статьи</a></li>
                    <li class="nav-item"><a href="/meditations"
                            class="nav-link<?= basename($_SERVER['PHP_SELF']) == 'meditations.php' ? ' active' : '' ?>">Медитации</a>
                    </li>
                    <!-- Временно скрыто на время настройки -->
                    <!-- <li class="nav-item"><a href="/shop"
                            class="nav-link<?= basename($_SERVER['PHP_SELF']) == 'shop.php' ? ' active' : '' ?>">Магазин</a>
                    </li> -->
                    <li class="nav-item"><a href="/contact"
                            class="nav-link<?= basename($_SERVER['PHP_SELF']) == 'contact.php' ? ' active' : '' ?>">Контакты</a>
                    </li>
                </ul>
            </div>

            <!-- Мобильная контактная информация в меню -->
            <div class="header__nav-contacts">
                <div class="header__nav-contact-item">
                    <div class="header__nav-contact-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                            <path
                                d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"
                                fill="currentColor" />
                        </svg>
                    </div>
                    <span>Приемы Online</span>
                </div>
                <div class="header__nav-contact-item">
                    <div class="header__nav-contact-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                            <path
                                d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"
                                fill="currentColor" />
                        </svg>
                    </div>
                    <span>Пн-Пт: 9-22</span>
                </div>
                <div class="header__nav-phone">
                    <a
                        href="tel:<?php echo str_replace([' ', '(', ')', '-'], '', getContactSettings()['phone']); ?>"><?php echo getContactSettings()['phone']; ?></a>
                </div>
                <button class="header__nav-call-btn" data-popup="call-back-popup">
                    Заказать звонок сейчас
                </button>
            </div>
        </div>
    </nav>
</header>