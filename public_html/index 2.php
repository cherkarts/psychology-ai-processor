<?php
session_start();
require_once 'includes/functions.php';

// Проверка режима обслуживания
if (isMaintenanceMode() && !isAdminAccess()) {
    header('Location: /maintenance.php');
    exit;
}

// Включаем кэширование для статической главной страницы (10 минут)
$cacheTime = 600; // 10 минут
header("Cache-Control: public, max-age={$cacheTime}");
header("Expires: " . gmdate('D, d M Y H:i:s', time() + $cacheTime) . ' GMT');
header("Last-Modified: " . gmdate('D, d M Y H:i:s', filemtime(__FILE__)) . ' GMT');

$meta = [
    'title' => 'Онлайн-психолог Денис Черкас – зависимости, созависимость, тревожность',
    'description' => 'Консультации психолога: онлайн, от 15 мин бесплатно до 50 мин - 2500₽ с поддержкой.',
    'keywords' => 'психолог, консультации, Денис Черкас, онлайн терапия'
];
$schema = generateSchemaMarkup('person');
?>
<!DOCTYPE html>
<html class="js" lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($meta['title']) ?></title>
    <meta name="description" content="<?= e($meta['description']) ?>">
    <meta name="keywords" content="<?= e($meta['keywords']) ?>">

    <!-- Open Graph -->
    <meta property="og:title" content="Психолог Денис Черкас">
    <meta property="og:description" content="Консультации психолога: от бесплатного знакомства до полной сессии">
    <meta property="og:image" content="https://cherkas-therapy.ru/image/23-1.jpg">
    <meta property="og:url" content="https://cherkas-therapy.ru">

    <!-- CSRF токен -->
    <meta name="csrf-token" content="<?= e(generateCSRFToken()) ?>">

    <!-- Стили -->
    <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css">
    <link rel="stylesheet" href="css/new-homepage.css?v=7.6">
    <link rel="stylesheet" href="css/fancybox.css">
    <link rel="stylesheet" href="css/header-unification.css">

    <!-- Шрифты -->
    <link rel="preload" href="fonts/Inter/Inter-Regular.woff" as="font" type="font/woff" crossorigin>
    <link rel="preload" href="fonts/Inter/Inter-Bold.woff" as="font" type="font/woff" crossorigin>

    <script type="application/ld+json"><?= $schema ?></script>
</head>

<body class="new-homepage">
    <!-- Header -->
    <header class="header">
        <div class="header__top">
            <div class="container">
                <div class="header__content">
                    <div class="header__logo">
                        <a href="/">
                            <img src="image/logo.png" alt="Психолог Денис Черкас">
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
                                <img src="image/whats-app.png" alt="WhatsApp">
                            </a>
                            <a href="<?php echo getContactSettings()['telegram_url']; ?>" target="_blank"
                                aria-label="Telegram">
                                <img src="image/telegram.png" alt="Telegram">
                            </a>
                        </div>
                    </div>

                    <!-- <div class="header__cart">
                        <a href="/cart.php" class="cart-link">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M9 22C9.55228 22 10 21.5523 10 21C10 20.4477 9.55228 20 9 20C8.44772 20 8 20.4477 8 21C8 21.5523 8.44772 22 9 22Z" stroke="currentColor" stroke-width="2"/>
                                <path d="M20 22C20.5523 22 21 21.5523 21 21C21 20.4477 20.5523 20 20 20C19.4477 20 19 20.4477 19 21C19 21.5523 19.4477 22 20 22Z" stroke="currentColor" stroke-width="2"/>
                                <path d="M1 1H5L7.68 14.39C7.77144 14.8504 8.02191 15.264 8.38755 15.5583C8.75318 15.8526 9.2107 16.009 9.68 16H19.4C19.8693 16.009 20.3268 15.8526 20.6925 15.5583C21.0581 15.264 21.3086 14.8504 21.4 14.39L23 6H6" stroke="currentColor" stroke-width="2"/>
                            </svg>
                            <span class="cart-counter">0</span>
                        </a>
                    </div> -->

                    <div class="header__contacts">
                        <!-- <p>Звоните <strong>Пн-Пт: 9-22</strong></p> -->
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



                <ul class="nav">
                    <li><a href="/" class="nav-link active">Главная</a></li>
                    <li><a href="/services" class="nav-link">Услуги</a></li>
                    <li><a href="/about" class="nav-link">Обо мне</a></li>
                    <li><a href="/reviews" class="nav-link">Отзывы</a></li>
                    <li><a href="/prices" class="nav-link">Цены</a></li>
                    <li><a href="/articles" class="nav-link">Статьи</a></li>
                    <li><a href="/meditations" class="nav-link">Медитации</a></li>
                    <!-- Временно скрыто на время настройки -->
                    <!-- <li><a href="/shop" class="nav-link">Магазин</a></li> -->
                    <li><a href="/contact" class="nav-link">Контакты</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero__bg">
            <img src="image/23-1.jpg" alt="Денис Черкас" class="hero__bg-image" loading="eager">
        </div>
        <div class="container">
            <div class="hero__content">
                <div class="hero__text">
                    <h1 class="hero__title">
                        <span> ПСИХОЛОГ</span><br>
                        ДЕНИС<br>
                        ЧЕРКАС
                    </h1>
                    <p class="hero__subtitle">
                        Квалифицированный психолог-коуч с опытом < 5 лет </p>
                            <div class="hero__cta">
                                <button class="btn btn--primary" data-popup="consultation-popup">
                                    <span>ЗАПИСАТЬСЯ НА КОНСУЛЬТАЦИЮ</span>
                                    <img src="image/phone.svg" alt="">
                                </button>
                                <p class="hero__cta-text">
                                    Перезвоню и проконсультирую через 5 минут
                                </p>
                            </div>
                </div>
            </div>
        </div>
    </section>



    <!-- Advantages Section -->
    <section class="advantages">
        <div class="container">
            <h2 class="section-title">
                <span class="title-accent">ПРЕИМУЩЕСТВА</span> РАБОТЫ СО МНОЙ
            </h2>
            <div class="advantages__carousel swiper">
                <div class="swiper-wrapper">
                    <div class="swiper-slide">
                        <div class="advantage-card">
                            <div class="advantage-card__blur"></div>
                            <img src="image/2-1.png" alt="Специализация на сложных темах" class="advantage-card__img"
                                loading="lazy">
                            <div class="advantage-card__content">
                                <h3>Специализация на сложных темах</h3>
                                <p>Глубокий опыт в работе с созависимостью и зависимостью – помогу найти выход.</p>
                                <button class="advantage-card__btn" data-popup="consultation-popup">
                                    <span>ЗАПИСАТЬСЯ</span>
                                    <svg width="19" height="19" viewBox="0 0 19 19" fill="none">
                                        <path
                                            d="M17.1646 8.3086L0.884766 8.30859L0.884766 10.0781L17.1646 10.0781L17.1646 8.3086Z" />
                                        <path
                                            d="M14.0596 13.5751L12.8083 12.3238L15.9115 9.22063L12.8083 6.11748L14.0596 4.86621L18.414 9.22063L14.0596 13.5751Z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="advantage-card">
                            <div class="advantage-card__blur"></div>
                            <img src="image/2-2.png" alt="Индивидуальный подход" class="advantage-card__img"
                                loading="lazy">
                            <div class="advantage-card__content">
                                <h3>Индивидуальный подход</h3>
                                <p>Уникальные методы под ваши цели и обстоятельства.</p>
                                <button class="advantage-card__btn" data-popup="consultation-popup">
                                    <span>ЗАПИСАТЬСЯ</span>
                                    <svg width="19" height="19" viewBox="0 0 19 19" fill="none">
                                        <path
                                            d="M17.1646 8.3086L0.884766 8.30859L0.884766 10.0781L17.1646 10.0781L17.1646 8.3086Z" />
                                        <path
                                            d="M14.0596 13.5751L12.8083 12.3238L15.9115 9.22063L12.8083 6.11748L14.0596 4.86621L18.414 9.22063L14.0596 13.5751Z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="advantage-card">
                            <div class="advantage-card__blur"></div>
                            <img src="image/2-3.png" alt="Конфиденциальность" class="advantage-card__img"
                                loading="lazy">
                            <div class="advantage-card__content">
                                <h3>Конфиденциальность</h3>
                                <p>Безопасное пространство – все остается между нами.</p>
                                <button class="advantage-card__btn" data-popup="consultation-popup">
                                    <span>ЗАПИСАТЬСЯ</span>
                                    <svg width="19" height="19" viewBox="0 0 19 19" fill="none">
                                        <path
                                            d="M17.1646 8.3086L0.884766 8.30859L0.884766 10.0781L17.1646 10.0781L17.1646 8.3086Z" />
                                        <path
                                            d="M14.0596 13.5751L12.8083 12.3238L15.9115 9.22063L12.8083 6.11748L14.0596 4.86621L18.414 9.22063L14.0596 13.5751Z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="advantage-card">
                            <div class="advantage-card__blur"></div>
                            <img src="image/2-4.png" alt="Поддержка" class="advantage-card__img" loading="lazy">
                            <div class="advantage-card__content">
                                <h3>Поддержка</h3>
                                <p>Сопровождаю вас к изменениям и новым ресурсам.</p>
                                <button class="advantage-card__btn" data-popup="consultation-popup">
                                    <span>ЗАПИСАТЬСЯ</span>
                                    <svg width="19" height="19" viewBox="0 0 19 19" fill="none">
                                        <path
                                            d="M17.1646 8.3086L0.884766 8.30859L0.884766 10.0781L17.1646 10.0781L17.1646 8.3086Z" />
                                        <path
                                            d="M14.0596 13.5751L12.8083 12.3238L15.9115 9.22063L12.8083 6.11748L14.0596 4.86621L18.414 9.22063L14.0596 13.5751Z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="advantage-card">
                            <div class="advantage-card__blur"></div>
                            <img src="image/2-5.png" alt="Прозрачность" class="advantage-card__img" loading="lazy">
                            <div class="advantage-card__content">
                                <h3>Прозрачность</h3>
                                <p>Фиксированная цена на полгода, четкие правила.</p>
                                <button class="advantage-card__btn" data-popup="consultation-popup">
                                    <span>ЗАПИСАТЬСЯ</span>
                                    <svg width="19" height="19" viewBox="0 0 19 19" fill="none">
                                        <path
                                            d="M17.1646 8.3086L0.884766 8.30859L0.884766 10.0781L17.1646 10.0781L17.1646 8.3086Z" />
                                        <path
                                            d="M14.0596 13.5751L12.8083 12.3238L15.9115 9.22063L12.8083 6.11748L14.0596 4.86621L18.414 9.22063L14.0596 13.5751Z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="advantage-card">
                            <div class="advantage-card__blur"></div>
                            <img src="image/2-6.png" alt="Результат" class="advantage-card__img" loading="lazy">
                            <div class="advantage-card__content">
                                <h3>Результат</h3>
                                <p>Конкретные решения для отношений, самооценки, тревоги.</p>
                                <button class="advantage-card__btn" data-popup="consultation-popup">
                                    <span>ЗАПИСАТЬСЯ</span>
                                    <svg width="19" height="19" viewBox="0 0 19 19" fill="none">
                                        <path
                                            d="M17.1646 8.3086L0.884766 8.30859L0.884766 10.0781L17.1646 10.0781L17.1646 8.3086Z" />
                                        <path
                                            d="M14.0596 13.5751L12.8083 12.3238L15.9115 9.22063L12.8083 6.11748L14.0596 4.86621L18.414 9.22063L14.0596 13.5751Z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="advantage-card">
                            <div class="advantage-card__blur"></div>
                            <img src="image/2-7.png" alt="Доступность" class="advantage-card__img" loading="lazy">
                            <div class="advantage-card__content">
                                <h3>Доступность</h3>
                                <p>Всегда на связи для вопросов и записи.</p>
                                <button class="advantage-card__btn" data-popup="consultation-popup">
                                    <span>ЗАПИСАТЬСЯ</span>
                                    <svg width="19" height="19" viewBox="0 0 19 19" fill="none">
                                        <path
                                            d="M17.1646 8.3086L0.884766 8.30859L0.884766 10.0781L17.1646 10.0781L17.1646 8.3086Z" />
                                        <path
                                            d="M14.0596 13.5751L12.8083 12.3238L15.9115 9.22063L12.8083 6.11748L14.0596 4.86621L18.414 9.22063L14.0596 13.5751Z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="slider-btn slider-prev-btn">
                    <img src="image/slider-prev.svg" alt="">
                </div>
                <div class="slider-btn slider-next-btn">
                    <img src="image/slider-next.svg" alt="">
                </div>
                <div class="slider-pagination swiper-pagination"></div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about">
        <div class="about__bg" style="background: url('image/bg-1.jpg');"></div>
        <div class="container">
            <div class="about__content">
                <div class="about__text">
                    <h2 class="section-title">ОБО <span class="title-accent">МНЕ</span></h2>
                    <h3 class="about__subtitle">
                        Помогу найти выход из сложных жизненных ситуаций, особенно если вы
                        столкнулись с зависимостью или созависимостью. Работаю с этими
                        темами чаще всего, но готов помочь и в других конфликтах, убирая
                        страхи и помогая обрести силы для достижения ваших целей.
                    </h3>
                    <div class="about__description">
                        <p>
                            Я не сторонник долгих разговоров ради разговоров — мне важны
                            реальные результаты. Поэтому в работе с зависимыми, созависимыми
                            и другими клиентами использую разные методы психотерапии,
                            подбирая подход, который работает именно для вас.
                        </p>
                        <span>
                            В прошлом я сам был наркоманом и алкоголиком, прошел этот путь
                            и знаю, каково быть зависимым. Эти личные трансформации помогают
                            мне глубже понимать вас и поддерживать на пути к изменениям.
                        </span>
                    </div>
                </div>
                <div class="about__image">
                    <img src="image/445-1.png" loading="lazy" alt="Денис Черкас">
                </div>
            </div>
        </div>
    </section>

    <!-- Stats & Certificates Section -->
    <section class="stats-certificates">
        <div class="container">
            <div class="stats-certificates__content">
                <div class="stats">
                    <div class="stat-item">
                        <div class="stat-number">6</div>
                        <div class="stat-text">
                            <strong>лет психологической</strong><br>
                            практики
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">8</div>
                        <div class="stat-text">
                            <strong>лет обучения</strong><br>
                            психологии
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">62+</div>
                        <div class="stat-text">
                            <strong>клиентам помог</strong> выйти<br>
                            на новый уровень жизни
                        </div>
                    </div>
                </div>
                <div class="certificates swiper">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide">
                            <a href="image/d1.png" data-fancybox="certificates">
                                <img src="image/d1.png" loading="lazy" alt="Диплом бакалавра психологии">
                            </a>
                        </div>
                        <div class="swiper-slide">
                            <a href="image/d2.png" data-fancybox="certificates">
                                <img src="image/d2.png" loading="lazy" alt="Диплом бакалавра психологии">
                            </a>
                        </div>
                        <div class="swiper-slide">
                            <a href="image/s1.png" data-fancybox="certificates">
                                <img src="image/s1.png" loading="lazy"
                                    alt="Свидетельство консультативного члена профессиональной психотерапевтической лиги">
                            </a>
                        </div>
                    </div>
                    <div class="slider-btn slider-prev-btn">
                        <img src="image/slider-prev.svg" alt="">
                    </div>
                    <div class="slider-pagination swiper-pagination"></div>
                    <div class="slider-btn slider-next-btn">
                        <img src="image/slider-next.svg" alt="">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Requests Section -->
    <section class="requests" style="background: url('image/group-252.jpg');">
        <div class="container">
            <h2 class="section-title">ЗАПРОСЫ с которыми я работаю</h2>
            <p class="section-subtitle"> помимо зависимости и созависимости</p>

            <div class="requests__grid">
                <div class="request-card">
                    <div class="request-card__img">
                        <img src="image/1.jpg" alt="Сложности в отношениях" loading="lazy">
                    </div>
                    <div class="request-card__content">
                        <h3>СЛОЖНОСТИ В ОТНОШЕНИЯХ</h3>
                        <p>Конфликты с близкими. Ощущение одиночества. Трудности в общении или недоверие. Эмоциональная
                            дистанция. Страх быть отвергнутым или непонятым.</p>
                        <div class="request-card__bottom">
                            <button class="btn btn--primary" data-popup="consultation-popup">
                                <span>Оставить заявку</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="request-card">
                    <div class="request-card__img">
                        <img src="image/2.jpg" alt="Тревожность и страхи" loading="lazy">
                    </div>
                    <div class="request-card__content">
                        <h3>ТРЕВОЖНОСТЬ И СТРАХИ</h3>
                        <p>Постоянное беспокойство. Страх перед будущим. Панические мысли или навязчивые идеи.
                            Напряжение в теле. Неуверенность в себе и своих силах.</p>
                        <div class="request-card__bottom">
                            <button class="btn btn--primary" data-popup="consultation-popup">
                                <span>Оставить заявку</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="request-card">
                    <div class="request-card__img">
                        <img src="image/3.jpg" alt="Выбор и принятие решения" loading="lazy">
                    </div>
                    <div class="request-card__content">
                        <h3>ВЫБОР И ПРИНЯТИЕ РЕШЕНИЯ</h3>
                        <p>Сомнения в своих решениях. Страх сделать неверный шаг. Зависание между вариантами. Тревога о
                            последствиях. Неясность, куда двигаться дальше.</p>
                        <div class="request-card__bottom">
                            <button class="btn btn--primary" data-popup="consultation-popup">
                                <span>Оставить заявку</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="request-card">
                    <div class="request-card__img">
                        <img src="image/4.jpg" alt="Эмоциональное выгорание" loading="lazy">
                    </div>
                    <div class="request-card__content">
                        <h3>ЭМОЦИОНАЛЬНОЕ ВЫГОРАНИЕ</h3>
                        <p>Усталость от работы или дел. Потеря интереса к жизни. Раздражение или апатия. Чувство
                            опустошения. Трудно найти силы на каждый день.</p>
                        <div class="request-card__bottom">
                            <button class="btn btn--primary" data-popup="consultation-popup">
                                <span>Оставить заявку</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="request-card">
                    <div class="request-card__img">
                        <img src="image/1-1.jpg" alt="Самооценка" loading="lazy">
                    </div>
                    <div class="request-card__content">
                        <h3>САМООЦЕНКА</h3>
                        <p>Неуверенность в себе. Сравнение с другими. Чувство, что вы недостаточно хороши. Страх
                            критики. Трудно ценить свои достижения.</p>
                        <div class="request-card__bottom">
                            <button class="btn btn--primary" data-popup="consultation-popup">
                                <span>Оставить заявку</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="request-card">
                    <div class="request-card__img">
                        <img src="image/2-1.jpg" alt="Отношения с мамой или папой" loading="lazy">
                    </div>
                    <div class="request-card__content">
                        <h3>ОТНОШЕНИЯ С МАМОЙ ИЛИ ПАПОЙ</h3>
                        <p>Напряжение с родителями. Чувство вины или обиды. Трудности в понимании друг друга.
                            Зависимость от их мнения. Конфликты или дистанция.</p>
                        <div class="request-card__bottom">
                            <button class="btn btn--primary" data-popup="consultation-popup">
                                <span>Оставить заявку</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="request-card">
                    <div class="request-card__img">
                        <img src="image/3-1.jpg" alt="Жизненный кризис" loading="lazy">
                    </div>
                    <div class="request-card__content">
                        <h3>ЖИЗНЕННЫЙ КРИЗИС</h3>
                        <p>Потеря смысла в жизни. Ощущение тупика. Эмоциональный спад или растерянность. Страх перемен.
                            Не знаете, как двигаться дальше.</p>
                        <div class="request-card__bottom">
                            <button class="btn btn--primary" data-popup="consultation-popup">
                                <span>Оставить заявку</span>
                            </button>

                        </div>
                    </div>
                </div>
                <div class="request-card">
                    <div class="request-card__img">
                        <img src="image/4-1.jpg" alt="Стресс и напряжение" loading="lazy">
                    </div>
                    <div class="request-card__content">
                        <h3>СТРЕСС И НАПРЯЖЕНИЕ</h3>
                        <p>Постоянное напряжение в теле. Трудности с расслаблением. Раздражительность и нервозность.
                            Проблемы со сном. Ощущение, что вы на пределе.</p>
                        <div class="request-card__bottom">
                            <button class="btn btn--primary" data-popup="consultation-popup">
                                <span>Оставить заявку</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Кнопка "Показать еще" -->
            <div class="requests__show-more">
                <button class="btn btn--secondary show-more-btn" id="showMoreRequests">
                    <span>Показать еще 4</span>
                </button>
            </div>
        </div>
    </section>

    <!-- Results Section -->
    <section class="results">
        <div class="container">
            <!-- Мобильный заголовок -->
            <div class="results__mobile-header">
                <h2 class="section-title">УЖЕ ПОСЛЕ ПЕРВОЙ СЕССИИ</h2>
            </div>

            <div class="results__content">
                <div class="results__text">
                    <h2 class="section-title">УЖЕ ПОСЛЕ ПЕРВОЙ СЕССИИ</h2>
                    <p class="results__subtitle">Вы почувствуете облегчение и увидите пути решения вашей проблемы. Мы
                        вместе найдем ресурсы для изменений и составим план действий.</p>
                    <div class="results__benefits">
                        <div class="results__benefits-desktop">
                            <div class="result-benefit">
                                <div class="result-benefit__icon">01</div>
                                <div class="result-benefit__text">Найдете в себе ресурсы</div>
                            </div>
                            <div class="result-benefit">
                                <div class="result-benefit__icon">02</div>
                                <div class="result-benefit__text">Увидите пути выхода из ситуации</div>
                            </div>
                            <div class="result-benefit">
                                <div class="result-benefit__icon">03</div>
                                <div class="result-benefit__text">Поймете себя и свои желания</div>
                            </div>
                            <div class="result-benefit">
                                <div class="result-benefit__icon">04</div>
                                <div class="result-benefit__text">Улучшите качество жизни</div>
                            </div>
                            <div class="result-benefit">
                                <div class="result-benefit__icon">05</div>
                                <div class="result-benefit__text">Улучшите отношения с близкими</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Мобильная карусель (вне блока results__text) -->
                <div class="results__benefits-mobile swiper">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide">
                            <div class="result-benefit">
                                <div class="result-benefit__icon">01</div>
                                <div class="result-benefit__text">Найдете в себе ресурсы</div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="result-benefit">
                                <div class="result-benefit__icon">02</div>
                                <div class="result-benefit__text">Увидите пути выхода из ситуации</div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="result-benefit">
                                <div class="result-benefit__icon">03</div>
                                <div class="result-benefit__text">Поймете себя и свои желания</div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="result-benefit">
                                <div class="result-benefit__icon">04</div>
                                <div class="result-benefit__text">Улучшите качество жизни</div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="result-benefit">
                                <div class="result-benefit__icon">05</div>
                                <div class="result-benefit__text">Улучшите отношения с близкими</div>
                            </div>
                        </div>
                    </div>
                    <!-- Пагинация для мобильной карусели -->
                    <div class="swiper-pagination"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="pricing">
        <div class="container">
            <h2 class="section-title">СТОИМОСТЬ</h2>
            <p class="section-subtitle">консультаций, есть 3 тарифа</p>
            <div class="pricing__grid">
                <div class="pricing-card">
                    <h3>ЗНАКОМСТВО</h3>
                    <ul>
                        <li>Встреча онлайн по Telegram или WhatsApp</li>
                        <li>Длительность 15 минут</li>
                        <li>Знакомство и определение запроса</li>
                        <li>Подбираем время</li>
                    </ul>
                    <div class="pricing__price">бесплатно</div>
                    <button class="btn btn--primary" data-popup="consultation-popup">
                        ЗАПИСАТЬСЯ НА КОНСУЛЬТАЦИЮ
                    </button>
                </div>
                <div class="pricing-card">
                    <h3>1 КОНСУЛЬТАЦИЯ</h3>
                    <ul>
                        <li>Встреча онлайн по Telegram или WhatsApp</li>
                        <li>Длительность 50 минут</li>
                        <li>Проработка запроса</li>
                        <li>Возможны домашние задания</li>
                    </ul>
                    <div class="pricing__price">2500 р</div>
                    <button class="btn btn--primary" data-popup="consultation-popup">
                        ЗАПИСАТЬСЯ НА КОНСУЛЬТАЦИЮ
                    </button>
                </div>
                <div class="pricing-card pricing-card--featured">
                    <h3>3 КОНСУЛЬТАЦИИ</h3>
                    <ul>
                        <li>Встреча онлайн по Telegram или WhatsApp</li>
                        <li>Длительность 50 минут</li>
                        <li>Знакомство и определение запроса</li>
                        <li>Возможны домашние задания</li>
                    </ul>
                    <div class="pricing__price">7000 р</div>
                    <button class="btn btn--primary" data-popup="consultation-popup">
                        ЗАПИСАТЬСЯ НА КОНСУЛЬТАЦИЮ
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Download Section -->
    <section class="download">
        <div class="container">
            <div class="download__content">
                <div class="download__text">
                    <h2>ДЛЯ ВАС</h2>
                    <p>скачайте бесплатно файл</p>
                    <p class="download__title">"Как вам подготовиться к консультации психолога"</p>
                    <p class="download__question">Выберите куда вам отправить?</p>
                    <form class="download__form" method="post">
                        <select name="delivery_method" class="download__select" id="delivery-method" required>
                            <option value="">Выберите способ получения</option>
                            <option value="whatsapp">Получить в WhatsApp</option>
                            <option value="telegram">Получить в Telegram</option>
                            <option value="email">Получить на email</option>
                        </select>

                        <!-- WhatsApp поле -->
                        <div class="form-field-group" id="whatsapp-field" style="display: none;">
                            <label class="form-field-label">Ваш номер телефона для WhatsApp</label>
                            <input type="tel" name="whatsapp_phone" class="download__input"
                                placeholder="+7 (___) ___-__-__">
                        </div>

                        <!-- Telegram поле -->
                        <div class="form-field-group" id="telegram-field" style="display: none;">
                            <label class="form-field-label">Ваш username в Telegram</label>
                            <input type="text" name="telegram_username" class="download__input" placeholder="@username">
                        </div>

                        <!-- Email поле -->
                        <div class="form-field-group" id="email-field" style="display: none;">
                            <label class="form-field-label">Ваш email адрес</label>
                            <input type="email" name="email" class="download__input" placeholder="example@email.com">
                        </div>

                        <input type="hidden" name="form_type" value="Скачать файл">
                        <input type="hidden" name="form_source" value="Главная: Для вас">
                        <button type="submit" class="download__btn">
                            Получить сейчас
                            <img src="image/download.svg" alt="">
                        </button>
                        <label class="download__checkbox">
                            <input type="checkbox" checked required>
                            <span>Согласен с <a href="privacy-policy.php" target="_blank" class="privacy-policy-link"
                                    data-popup="privacy-policy-popup">политикой конфиденциальности</a></span>
                        </label>
                    </form>
                </div>
                <div class="download__image">
                    <img src="image/07.png" alt="Пособие">
                </div>
            </div>
        </div>
    </section>

    <!-- Reviews Section -->
    <section class="reviews">
        <div class="container">
            <h2 class="section-title">ОТЗЫВЫ</h2>
            <p class="section-subtitle">и истории трансформации жизней людей</p>

            <!-- Reviews Images Slider -->
            <div class="reviews__img-slider img-slider">
                <div class="img-slider__container md-standart-slider" singleslider_js="">
                    <div class="swiper-container swiper-container-initialized swiper-container-horizontal">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide">
                                <a href="image/1.png" data-fancybox="img-review" class="img-slider__slide">
                                    <img src="image/1.png" alt="Отзыв 1" loading="lazy">
                                </a>
                            </div>
                            <div class="swiper-slide">
                                <a href="image/2.png" data-fancybox="img-review" class="img-slider__slide">
                                    <img src="image/2.png" alt="Отзыв 2" loading="lazy">
                                </a>
                            </div>
                            <div class="swiper-slide">
                                <a href="image/3.png" data-fancybox="img-review" class="img-slider__slide">
                                    <img src="image/3.png" alt="Отзыв 3" loading="lazy">
                                </a>
                            </div>
                            <div class="swiper-slide">
                                <a href="image/4.png" data-fancybox="img-review" class="img-slider__slide">
                                    <img src="image/4.png" alt="Отзыв 4" loading="lazy">
                                </a>
                            </div>
                            <div class="swiper-slide">
                                <a href="image/5.png" data-fancybox="img-review" class="img-slider__slide">
                                    <img src="image/5.png" alt="Отзыв 5" loading="lazy">
                                </a>
                            </div>
                        </div>
                        <span class="swiper-notification" aria-live="assertive" aria-atomic="true"></span>
                    </div>
                    <div class="slider-btn slider-prev-btn" tabindex="0" role="button" aria-label="Previous slide">
                        <img src="image/slider-prev.svg" alt="">
                    </div>
                    <div class="slider-pagination swiper-pagination-bullets swiper-pagination-clickable">
                        <span class="swiper-pagination-bullet" tabindex="0" role="button"
                            aria-label="Go to slide 1"></span>
                        <span class="swiper-pagination-bullet" tabindex="0" role="button"
                            aria-label="Go to slide 2"></span>
                        <span class="swiper-pagination-bullet" tabindex="0" role="button"
                            aria-label="Go to slide 3"></span>
                        <span class="swiper-pagination-bullet" tabindex="0" role="button"
                            aria-label="Go to slide 4"></span>
                        <span class="swiper-pagination-bullet" tabindex="0" role="button"
                            aria-label="Go to slide 5"></span>
                    </div>
                    <div class="slider-btn slider-next-btn" tabindex="0" role="button" aria-label="Next slide">
                        <img src="image/slider-next.svg" alt="">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq">
        <div class="container">
            <h2 class="section-title">ВОПРОС-ОТВЕТ</h2>
            <p class="section-subtitle">ТОП-8 самых популярных вопросов перед консультацией</p>
            <div class="faq__grid">
                <div class="faq-item">
                    <button class="faq__question">
                        Вы можете помочь моему сыну/мужу/отцу перестать употреблять?
                        <span class="faq__icon">+</span>
                    </button>
                    <div class="faq__answer">
                        <p>Да, я специализируюсь на работе с зависимостями и могу помочь как самому зависимому, так и
                            его близким. Важно понимать, что изменения возможны только при желании самого человека.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq__question">
                        Сколько стоит консультация?
                        <span class="faq__icon">+</span>
                    </button>
                    <div class="faq__answer">
                        <p>Первая консультация (знакомство) - бесплатно, 15 минут. Полная консультация - 2500 рублей, 50
                            минут. Пакет из 3 консультаций - 7000 рублей.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq__question">
                        Как проходит консультация?
                        <span class="faq__icon">+</span>
                    </button>
                    <div class="faq__answer">
                        <p>Консультации проходят онлайн через Telegram или WhatsApp. Мы обсуждаем вашу ситуацию,
                            определяем цели и работаем над их достижением.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq__question">
                        Конфиденциально ли это?
                        <span class="faq__icon">+</span>
                    </button>
                    <div class="faq__answer">
                        <p>Да, все консультации строго конфиденциальны. Ваша личная информация не разглашается третьим
                            лицам.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq__question">
                        Сколько времени нужно для результата?
                        <span class="faq__icon">+</span>
                    </button>
                    <div class="faq__answer">
                        <p>Это зависит от сложности проблемы и вашей готовности к изменениям. Некоторые видят улучшения
                            уже после первой консультации, другим нужно больше времени.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq__question">
                        Можно ли работать с детьми?
                        <span class="faq__icon">+</span>
                    </button>
                    <div class="faq__answer">
                        <p>Нет, я не работаю с подростками и детьми. 18+</p>
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq__question">
                        Что если мне не понравится?
                        <span class="faq__icon">+</span>
                    </button>
                    <div class="faq__answer">
                        <p>Первая консультация бесплатная, поэтому вы ничего не теряете. Если не подойду - порекомендую
                            других специалистов.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq__question">
                        Как записаться на консультацию?
                        <span class="faq__icon">+</span>
                    </button>
                    <div class="faq__answer">
                        <p>Заполните форму на сайте или напишите в WhatsApp/Telegram. Я свяжусь с вами в течение часа.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Кнопка "Показать еще вопросы" для мобильных -->
            <div class="faq__show-more">
                <button class="btn btn--secondary show-more-btn" id="showMoreFaq">
                    <span>Показать еще вопросы</span>
                </button>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <div class="cta__content">
                <div class="cta__text">
                    <h2>ЗАПИШИТЕСЬ</h2>
                    <p>на бесплатную консультацию прямо сейчас</p>
                    <form class="cta__form" method="post">
                        <div class="form-group">
                            <label>Ваше имя</label>
                            <input type="text" name="name" class="form-input" placeholder="Введите ваше имя" required>
                        </div>
                        <div class="form-group">
                            <label>В какое время позвонить?</label>
                            <select name="time" class="form-select">
                                <option value="">Выберите удобное время</option>
                                <option value="now">Перезвоните сейчас</option>
                                <option value="morning">Утром (9-12)</option>
                                <option value="afternoon">Днем (12-18)</option>
                                <option value="evening">Вечером (18-22)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Ваш номер телефона</label>
                            <input type="tel" name="phone" class="form-input" placeholder="+7 (___) ___-__-__" required>
                        </div>
                        <input type="hidden" name="form_type" value="Запись на консультацию">
                        <input type="hidden" name="form_source" value="Главная: CTA">
                        <button type="submit" class="btn btn--primary">
                            Записаться на консультацию
                        </button>
                        <label class="form-checkbox">
                            <input type="checkbox" checked required>
                            <span>Согласен с <a href="privacy-policy.php" target="_blank" class="privacy-policy-link"
                                    data-popup="privacy-policy-popup">политикой конфиденциальности</a></span>
                        </label>
                    </form>
                    <p class="cta__social">или напишите, я онлайн</p>
                    <div class="cta__social-links">
                        <a href="https://wa.me/+79936202951" target="_blank" class="social-btn social-btn--whatsapp">
                            <img src="image/whats-app.png" alt="WhatsApp">
                        </a>
                        <a href="<?php echo getContactSettings()['telegram_url']; ?>" target="_blank"
                            class="social-btn social-btn--telegram">
                            <img src="image/telegram.png" alt="Telegram">
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <?php include 'includes/new-footer.php'; ?>

    <!-- Popups -->
    <div class="popup" id="consultation-popup">
        <div class="popup__overlay"></div>
        <div class="popup__content">
            <button class="popup__close" aria-label="Закрыть">×</button>
            <h3>Записаться на консультацию</h3>
            <form class="popup__form" method="post">
                <div class="form-group">
                    <label>Ваше имя</label>
                    <input type="text" name="name" class="form-input" placeholder="Введите ваше имя" required>
                </div>
                <div class="form-group">
                    <label>Ваш телефон</label>
                    <input type="tel" name="phone" class="form-input" placeholder="+7 (___) ___-__-__" required>
                </div>
                <div class="form-group">
                    <label>Выберите время звонка</label>
                    <select name="time" class="form-select">
                        <option value="">Выберите удобное время</option>
                        <option value="now">Перезвоните сейчас</option>
                        <option value="morning">Утром (9-12)</option>
                        <option value="afternoon">Днем (12-18)</option>
                        <option value="evening">Вечером (18-22)</option>
                    </select>
                </div>
                <input type="hidden" name="form_type" value="Попап консультация">
                <input type="hidden" name="form_source" value="Главная: Попап консультация">
                <label class="form-checkbox">
                    <input type="checkbox" checked required>
                    <span>Согласен с <a href="#" class="privacy-policy-link" data-popup="privacy-policy-popup">политикой
                            конфиденциальности</a></span>
                </label>
                <button type="submit" class="btn btn--primary">Записаться</button>
            </form>
        </div>
    </div>

    <div class="popup" id="call-back-popup">
        <div class="popup__overlay"></div>
        <div class="popup__content">
            <button class="popup__close" aria-label="Закрыть">×</button>
            <h3>Заказать звонок</h3>
            <form class="popup__form" method="post">
                <div class="form-group">
                    <label>Ваше имя</label>
                    <input type="text" name="name" class="form-input" placeholder="Введите ваше имя" required>
                </div>
                <div class="form-group">
                    <label>Ваш телефон</label>
                    <input type="tel" name="phone" class="form-input" placeholder="+7 (___) ___-__-__" required>
                </div>
                <div class="form-group">
                    <label>Удобное время для звонка</label>
                    <select name="time" class="form-select">
                        <option value="">Выберите удобное время</option>
                        <option value="now">Сейчас</option>
                        <option value="morning">Утром (9-12)</option>
                        <option value="afternoon">Днем (12-18)</option>
                        <option value="evening">Вечером (18-22)</option>
                    </select>
                </div>
                <input type="hidden" name="form_type" value="Заказать звонок">
                <input type="hidden" name="form_source" value="Главная: Попап звонок">
                <label class="form-checkbox">
                    <input type="checkbox" checked required>
                    <span>Согласен с <a href="#" class="privacy-policy-link" data-popup="privacy-policy-popup">политикой
                            конфиденциальности</a></span>
                </label>
                <button type="submit" class="btn btn--primary">Заказать звонок</button>
            </form>
        </div>
    </div>

    <!-- Попап политики конфиденциальности -->
    <div class="popup" id="privacy-policy-popup">
        <div class="popup__overlay"></div>
        <div class="popup__content popup__content--large">
            <button class="popup__close" aria-label="Закрыть">×</button>
            <div class="privacy-policy-popup">
                <h3>Политика конфиденциальности</h3>
                <div class="privacy-policy-popup__content">
                    <p><strong>Дата вступления в силу:</strong> 7 августа 2025 года</p>

                    <h4>1. Общие положения</h4>
                    <p>Настоящая Политика конфиденциальности определяет порядок обработки персональных данных
                        пользователей сайта <a href="https://cherkas-therapy.ru">https://cherkas-therapy.ru</a>,
                        принадлежащего Черкасу Денису Адамовичу.</p>

                    <h4>2. Какие данные мы собираем</h4>
                    <ul>
                        <li>Контактная информация: имя, телефон, email, username в мессенджерах</li>
                        <li>Информация о запросах: тема консультации, предпочтительное время</li>
                        <li>Техническая информация: IP-адрес, данные браузера</li>
                    </ul>

                    <h4>3. Цели обработки</h4>
                    <ul>
                        <li>Обработка заявок на консультации</li>
                        <li>Предоставление психологических услуг</li>
                        <li>Отправка информационных материалов</li>
                        <li>Улучшение качества услуг</li>
                    </ul>

                    <h4>4. Конфиденциальность</h4>
                    <p>Вся информация, полученная в ходе консультаций, является строго конфиденциальной и не подлежит
                        разглашению третьим лицам, за исключением случаев, предусмотренных законодательством РФ.</p>

                    <h4>5. Ваши права</h4>
                    <ul>
                        <li>Получать информацию об обработке данных</li>
                        <li>Требовать уточнения или удаления данных</li>
                        <li>Отзывать согласие на обработку</li>
                        <li>Обжаловать действия в области защиты данных</li>
                    </ul>

                    <h4>6. Контактная информация</h4>
                    <p>По вопросам обработки персональных данных:</p>
                    <ul>
                        <li><strong>Телефон:</strong> <a
                                href="tel:<?php echo str_replace([' ', '(', ')', '-'], '', getContactSettings()['phone']); ?>"><?php echo getContactSettings()['phone']; ?></a>
                        </li>
                        <li><strong>Email:</strong> <a
                                href="mailto:<?php echo getContactSettings()['email']; ?>"><?php echo getContactSettings()['email']; ?></a>
                        </li>
                        <li><strong>Сайт:</strong> <a href="https://cherkas-therapy.ru">https://cherkas-therapy.ru</a>
                        </li>
                    </ul>

                    <p><em>Полная версия политики доступна на странице <a href="privacy-policy.php"
                                target="_blank">Политика конфиденциальности</a></em></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>
    <script src="js/jquery.maskedinput.min.js"></script>
    <script src="js/fancybox.umd.js"></script>
    <script src="js/new-homepage.js?v=3.2"></script>

    <!-- Yandex.Metrika counter -->
    <script type="text/javascript">
        ; (function (m, e, t, r, i, k, a) {
            m[i] =
                m[i] ||
                function () {
                    ; (m[i].a = m[i].a || []).push(arguments)
                }
            m[i].l = 1 * new Date()
            for (var j = 0; j < document.scripts.length; j++) {
                if (document.scripts[j].src === r) {
                    return
                }
            }
            ; (k = e.createElement(t)),
                (a = e.getElementsByTagName(t)[0]),
                (k.async = 1),
                (k.src = r),
                a.parentNode.insertBefore(k, a)
        })(
            window,
            document,
            'script',
            'https://mc.yandex.ru/metrika/tag.js?id=103948722',
            'ym'
        )

        ym(103948722, 'init', {
            ssr: true,
            webvisor: true,
            clickmap: true,
            ecommerce: 'dataLayer',
            accurateTrackBounce: true,
            trackLinks: true,
        })
    </script>
    <noscript>
        <div>
            <img src="https://mc.yandex.ru/watch/103948722" style="position:absolute; left:-9999px;" alt="" />
        </div>
    </noscript>
    <!-- /Yandex.Metrika counter -->
</body>

</html>