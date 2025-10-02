<?php
session_start();
require_once 'includes/functions.php';

// Проверка режима обслуживания
if (isMaintenanceMode() && !isAdminAccess()) {
    header('Location: /maintenance.php');
    exit;
}

// Перенаправляем на ЧПУ URL если нужно
redirectToSeoUrl();

$meta = generateMetaTags('services');
$schema = generateSchemaMarkup('person');
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

    <link rel="canonical" href="https://cherkas-therapy.ru/services.php" />
    <meta property="og:locale" content="ru_RU" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="<?= e($meta['title']) ?>" />
    <meta property="og:description" content="<?= e($meta['description']) ?>" />
    <meta property="og:url" content="https://cherkas-therapy.ru/services.php" />
    <meta property="og:site_name" content="Психолог Денис Черкас" />
    <meta property="og:image" content="https://cherkas-therapy.ru/image/23-1.jpg" />
    <meta property="og:image:width" content="1920" />
    <meta property="og:image:height" content="1080" />
    <meta property="og:image:type" content="image/jpeg" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?= e($meta['title']) ?>" />
    <meta name="twitter:image" content="https://cherkas-therapy.ru/image/23-1.jpg" />

    <!-- CSRF токен для отправки форм -->
    <meta name="csrf-token" content="<?= e(generateCSRFToken()) ?>" />

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Психологические консультации",
        "description": "<?= e($meta['description']) ?>",
        "provider": {
            "@type": "Person",
            "name": "Денис Черкас",
            "jobTitle": "Психолог"
        },
        "serviceType": "Психологическая консультация",
        "areaServed": "Россия",
        "availableChannel": {
            "@type": "ServiceChannel",
            "serviceUrl": "https://cherkas-therapy.ru/services.php"
        }
    }
    </script>

    <!-- Новые стили -->
    <link rel="stylesheet" href="/css/new-components.css" />
    <link rel="stylesheet" href="/css/new-homepage.css?v=7.6" />
    <link rel="stylesheet" href="/css/fancybox.css" />
    <link rel="stylesheet" href="/css/font.css" />
    <link rel="stylesheet" href="/css/pages.css" />
    <link rel="stylesheet" href="/css/header-unification.css" />



    <meta name="msapplication-TileColor" content="#ffffff" />
    <meta name="msapplication-TileImage" content="favicon/ms-icon.png" />
    <meta name="theme-color" content="#ffffff" />
    <meta name="yandex-verification" content="abe245cbb3b37351" />
</head>

<body class="page">
    <?php include 'includes/new-header.php'; ?>

    <!-- Обновленная Hero секция -->
    <section class="services-hero">
        <div class="wrapper">
            <div class="services-hero__content">
                <div class="services-hero__badge">
                    <span>Профессиональная помощь</span>
                </div>
                <h1 class="services-hero__title md-main-title">
                    <span class="services-hero__title-accent">УСЛУГИ</span> ПСИХОЛОГА
                </h1>
                <p class="services-hero__subtitle">
                    Индивидуальный подход к каждому клиенту. Помогаю преодолеть трудности
                    и найти внутреннюю гармонию через профессиональную психологическую поддержку.
                </p>
                <div class="services-hero__stats">
                    <div class="hero-stat">
                        <span class="hero-stat__number">100+</span>
                        <span class="hero-stat__label">Довольных клиентов</span>
                    </div>
                    <div class="hero-stat">
                        <span class="hero-stat__number">8</span>
                        <span class="hero-stat__label">Лет опыта</span>
                    </div>
                    <div class="hero-stat">
                        <span class="hero-stat__number">95%</span>
                        <span class="hero-stat__label">Успешных случаев</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="services-hero__decoration">
            <div class="hero-shape hero-shape--1"></div>
            <div class="hero-shape hero-shape--2"></div>
            <div class="hero-shape hero-shape--3"></div>
        </div>
    </section>

    <!-- Секция преимуществ -->
    <section class="services-benefits">
        <div class="wrapper">
            <div class="services-benefits__container">
                <h2 class="services-benefits__title">Почему выбирают меня</h2>
                <div class="benefits-grid">
                    <div class="benefit-item">
                        <div class="benefit-item__icon">
                            <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
                                <circle cx="24" cy="24" r="24" fill="#6a7e9f" opacity="0.1" />
                                <path
                                    d="M24 12C17.373 12 12 17.373 12 24s5.373 12 12 12 12-5.373 12-12S30.627 12 24 12zm0 22c-5.514 0-10-4.486-10-10s4.486-10 10-10 10 4.486 10 10-4.486 10-10 10z"
                                    fill="#6a7e9f" />
                                <path
                                    d="M24 18c-3.314 0-6 2.686-6 6s2.686 6 6 6 6-2.686 6-6-2.686-6-6-6zm0 10c-2.206 0-4-1.794-4-4s1.794-4 4-4 4 1.794 4 4-1.794 4-4 4z"
                                    fill="#6a7e9f" />
                            </svg>
                        </div>
                        <h3 class="benefit-item__title">Индивидуальный подход</h3>
                        <p class="benefit-item__text">
                            Каждый клиент уникален. Подбираю методики и техники,
                            которые подходят именно вам и вашей ситуации.
                        </p>
                    </div>

                    <div class="benefit-item">
                        <div class="benefit-item__icon">
                            <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
                                <circle cx="24" cy="24" r="24" fill="#d2afa0" opacity="0.1" />
                                <path
                                    d="M24 4C12.954 4 4 12.954 4 24s8.954 20 20 20 20-8.954 20-20S35.046 4 24 4zm0 36c-8.837 0-16-7.163-16-16S15.163 8 24 8s16 7.163 16 16-7.163 16-16 16z"
                                    fill="#d2afa0" />
                                <path d="M20 16h8v2h-8v-2zm0 4h8v2h-8v-2zm0 4h8v2h-8v-2z" fill="#d2afa0" />
                            </svg>
                        </div>
                        <h3 class="benefit-item__title">Индивидуальный подбор методик</h3>
                        <p class="benefit-item__text">
                            Использую гибкий подход, подбирая техники и методы,
                            которые наиболее эффективны для вашей конкретной ситуации.
                        </p>
                    </div>

                    <div class="benefit-item">
                        <div class="benefit-item__icon">
                            <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
                                <circle cx="24" cy="24" r="24" fill="#6a7e9f" opacity="0.1" />
                                <path
                                    d="M24 4C12.954 4 4 12.954 4 24s8.954 20 20 20 20-8.954 20-20S35.046 4 24 4zm0 36c-8.837 0-16-7.163-16-16S15.163 8 24 8s16 7.163 16 16-7.163 16-16 16z"
                                    fill="#6a7e9f" />
                                <path
                                    d="M24 12c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm0 22c-5.514 0-10-4.486-10-10s4.486-10 10-10 10 4.486 10 10-4.486 10-10 10z"
                                    fill="#6a7e9f" />
                                <path
                                    d="M24 16c-4.418 0-8 3.582-8 8s3.582 8 8 8 8-3.582 8-8-3.582-8-8-8zm0 14c-3.314 0-6-2.686-6-6s2.686-6 6-6 6 2.686 6 6-2.686 6-6 6z"
                                    fill="#6a7e9f" />
                            </svg>
                        </div>
                        <h3 class="benefit-item__title">Конфиденциальность</h3>
                        <p class="benefit-item__text">
                            Полная анонимность и конфиденциальность. Ваши личные
                            переживания остаются между нами.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Основные услуги -->
    <section class="services-list">
        <div class="wrapper">
            <div class="services-list__header">
                <h2 class="services-list__title">Основные направления работы</h2>
                <p class="services-list__subtitle">
                    Выберите направление, которое соответствует вашей ситуации
                </p>
            </div>
            <div class="services-list__container">

                <div class="service-card">
                    <div class="service-card__icon">
                        <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
                            <path
                                d="M20 4C11.163 4 4 11.163 4 20s7.163 16 16 16 16-7.163 16-16S28.837 4 20 4zm0 28c-6.627 0-12-5.373-12-12S13.373 8 20 8s12 5.373 12 12-5.373 12-12 12z"
                                fill="white" />
                            <path d="M16 14h8v2h-8v-2zm0 4h8v2h-8v-2zm0 4h8v2h-8v-2z" fill="white" />
                        </svg>
                    </div>
                    <div class="service-card__content">
                        <h3 class="service-card__title">Работа с зависимостями</h3>
                        <p class="service-card__description">
                            Помощь в преодолении различных видов зависимостей: алкогольной,
                            наркотической, игровой, пищевой. Индивидуальный план выздоровления
                            и поддержка на каждом этапе.
                        </p>
                        <ul class="service-card__features">
                            <li>Диагностика типа зависимости</li>
                            <li>Разработка стратегии выздоровления</li>
                            <li>Работа с триггерами и тягой</li>
                            <li>Поддержка семьи зависимого</li>
                        </ul>
                    </div>
                </div>

                <div class="service-card">
                    <div class="service-card__icon">
                        <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
                            <path
                                d="M20 4C11.163 4 4 11.163 4 20s7.163 16 16 16 16-7.163 16-16S28.837 4 20 4zm0 28c-6.627 0-12-5.373-12-12S13.373 8 20 8s12 5.373 12 12-5.373 12-12 12z"
                                fill="white" />
                            <path d="M16 14h8v2h-8v-2zm0 4h8v2h-8v-2zm0 4h8v2h-8v-2z" fill="white" />
                        </svg>
                    </div>
                    <div class="service-card__content">
                        <h3 class="service-card__title">Созависимость</h3>
                        <p class="service-card__description">
                            Работа с созависимыми отношениями, восстановление здоровых границ,
                            развитие самооценки и независимости. Помощь в построении
                            здоровых отношений.
                        </p>
                        <ul class="service-card__features">
                            <li>Выявление паттернов созависимости</li>
                            <li>Установление здоровых границ</li>
                            <li>Развитие самооценки</li>
                            <li>Построение здоровых отношений</li>
                        </ul>
                    </div>
                </div>

                <div class="service-card">
                    <div class="service-card__icon">
                        <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
                            <path
                                d="M20 4C11.163 4 4 11.163 4 20s7.163 16 16 16 16-7.163 16-16S28.837 4 20 4zm0 28c-6.627 0-12-5.373-12-12S13.373 8 20 8s12 5.373 12 12-5.373 12-12 12z"
                                fill="white" />
                            <path d="M16 14h8v2h-8v-2zm0 4h8v2h-8v-2zm0 4h8v2h-8v-2z" fill="white" />
                        </svg>
                    </div>
                    <div class="service-card__content">
                        <h3 class="service-card__title">Тревожность и страхи</h3>
                        <p class="service-card__description">
                            Помощь в преодолении тревожности, панических атак, фобий.
                            Обучение техникам релаксации и управления эмоциями.
                            Работа с причинами тревожности.
                        </p>
                        <ul class="service-card__features">
                            <li>Диагностика тревожных расстройств</li>
                            <li>Техники релаксации и дыхания</li>
                            <li>Когнитивно-поведенческая терапия</li>
                            <li>Работа с паническими атаками</li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Первая CTA секция -->
    <section class="services-cta services-cta--primary">
        <div class="wrapper">
            <div class="services-cta__content">
                <div class="services-cta__icon">
                    <svg width="64" height="64" viewBox="0 0 64 64" fill="none">
                        <circle cx="32" cy="32" r="32" fill="#6a7e9f" opacity="0.1" />
                        <path
                            d="M32 8C18.745 8 8 18.745 8 32s10.745 24 24 24 24-10.745 24-24S45.255 8 32 8zm0 44c-11.046 0-20-8.954-20-20S20.954 12 32 12s20 8.954 20 20-8.954 20-20 20z"
                            fill="#6a7e9f" />
                        <path
                            d="M32 20c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm0 20c-4.418 0-8-3.582-8-8s3.582-8 8-8 8 3.582 8 8-3.582 8-8 8z"
                            fill="#6a7e9f" />
                    </svg>
                </div>
                <h2 class="services-cta__title">Нужна помощь в выборе услуги?</h2>
                <p class="services-cta__text">
                    Свяжитесь со мной для бесплатной консультации и подбора подходящего направления работы.
                    Вместе найдем решение вашей проблемы.
                </p>
                <button class="services-cta__btn md-main-color-btn" popupOpen="call-back-popup"
                    data-form-source="Услуги: Нужна помощь в выборе услуги?">
                    <span>ПОЛУЧИТЬ КОНСУЛЬТАЦИЮ</span>
                    <img src="image/phone.svg" alt="" />
                </button>
            </div>
        </div>
    </section>

    <!-- Дополнительные услуги -->
    <section class="services-list services-list--secondary">
        <div class="wrapper">
            <div class="services-list__container">

                <div class="service-card">
                    <div class="service-card__icon">
                        <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
                            <path
                                d="M20 4C11.163 4 4 11.163 4 20s7.163 16 16 16 16-7.163 16-16S28.837 4 20 4zm0 28c-6.627 0-12-5.373-12-12S13.373 8 20 8s12 5.373 12 12-5.373 12-12 12z"
                                fill="white" />
                            <path d="M16 14h8v2h-8v-2zm0 4h8v2h-8v-2zm0 4h8v2h-8v-2z" fill="white" />
                        </svg>
                    </div>
                    <div class="service-card__content">
                        <h3 class="service-card__title">Сложности в отношениях</h3>
                        <p class="service-card__description">
                            Помощь в решении проблем в отношениях: конфликты, недопонимание,
                            потеря близости. Работа с коммуникацией и эмоциональной связью.
                        </p>
                        <ul class="service-card__features">
                            <li>Улучшение коммуникации</li>
                            <li>Разрешение конфликтов</li>
                            <li>Восстановление близости</li>
                            <li>Построение доверия</li>
                        </ul>
                    </div>
                </div>

                <div class="service-card">
                    <div class="service-card__icon">
                        <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
                            <path
                                d="M20 4C11.163 4 4 11.163 4 20s7.163 16 16 16 16-7.163 16-16S28.837 4 20 4zm0 28c-6.627 0-12-5.373-12-12S13.373 8 20 8s12 5.373 12 12-5.373 12-12 12z"
                                fill="white" />
                            <path d="M16 14h8v2h-8v-2zm0 4h8v2h-8v-2zm0 4h8v2h-8v-2z" fill="white" />
                        </svg>
                    </div>
                    <div class="service-card__content">
                        <h3 class="service-card__title">Стресс и выгорание</h3>
                        <p class="service-card__description">
                            Помощь в преодолении стресса, эмоционального выгорания,
                            хронической усталости. Восстановление ресурсов и
                            обучение техникам самопомощи.
                        </p>
                        <ul class="service-card__features">
                            <li>Диагностика уровня стресса</li>
                            <li>Техники управления стрессом</li>
                            <li>Восстановление ресурсов</li>
                            <li>Профилактика выгорания</li>
                        </ul>
                    </div>
                </div>

                <div class="service-card">
                    <div class="service-card__icon">
                        <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
                            <path
                                d="M20 4C11.163 4 4 11.163 4 20s7.163 16 16 16 16-7.163 16-16S28.837 4 20 4zm0 28c-6.627 0-12-5.373-12-12S13.373 8 20 8s12 5.373 12 12-5.373 12-12 12z"
                                fill="white" />
                            <path d="M16 14h8v2h-8v-2zm0 4h8v2h-8v-2zm0 4h8v2h-8v-2z" fill="white" />
                        </svg>
                    </div>
                    <div class="service-card__content">
                        <h3 class="service-card__title">Самооценка и уверенность</h3>
                        <p class="service-card__description">
                            Работа с низкой самооценкой, неуверенностью в себе,
                            перфекционизмом. Развитие здорового отношения к себе
                            и своим достижениям.
                        </p>
                        <ul class="service-card__features">
                            <li>Повышение самооценки</li>
                            <li>Развитие уверенности</li>
                            <li>Работа с перфекционизмом</li>
                            <li>Принятие себя</li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Специализированные услуги -->
    <section class="services-list services-list--specialized">
        <div class="wrapper">
            <div class="services-list__header">
                <h2 class="services-list__title">Специализированная помощь</h2>
                <p class="services-list__subtitle">
                    Работа с глубокими психологическими проблемами и травмами
                </p>
            </div>
            <div class="services-list__container">

                <div class="service-card service-card--specialized">
                    <div class="service-card__icon">
                        <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
                            <path
                                d="M20 4C11.163 4 4 11.163 4 20s7.163 16 16 16 16-7.163 16-16S28.837 4 20 4zm0 28c-6.627 0-12-5.373-12-12S13.373 8 20 8s12 5.373 12 12-5.373 12-12 12z"
                                fill="white" />
                            <path d="M16 14h8v2h-8v-2zm0 4h8v2h-8v-2zm0 4h8v2h-8v-2z" fill="white" />
                        </svg>
                    </div>
                    <div class="service-card__content">
                        <h3 class="service-card__title">Работа с травмой</h3>
                        <p class="service-card__description">
                            Помощь в преодолении психологических травм, посттравматического
                            стрессового расстройства. Безопасная работа с болезненными
                            воспоминаниями и эмоциями.
                        </p>
                        <ul class="service-card__features">
                            <li>Диагностика травматических переживаний</li>
                            <li>Техники работы с травмой</li>
                            <li>Восстановление чувства безопасности</li>
                            <li>Интеграция травматического опыта</li>
                        </ul>
                    </div>
                </div>

                <div class="service-card service-card--specialized">
                    <div class="service-card__icon">
                        <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
                            <path
                                d="M20 4C11.163 4 4 11.163 4 20s7.163 16 16 16 16-7.163 16-16S28.837 4 20 4zm0 28c-6.627 0-12-5.373-12-12S13.373 8 20 8s12 5.373 12 12-5.373 12-12 12z"
                                fill="white" />
                            <path d="M16 14h8v2h-8v-2zm0 4h8v2h-8v-2zm0 4h8v2h-8v-2z" fill="white" />
                        </svg>
                    </div>
                    <div class="service-card__content">
                        <h3 class="service-card__title">Депрессия и апатия</h3>
                        <p class="service-card__description">
                            Помощь в преодолении депрессии, апатии, потери интереса к жизни.
                            Восстановление энергии, мотивации и радости жизни.
                            Работа с негативными мыслями.
                        </p>
                        <ul class="service-card__features">
                            <li>Диагностика депрессивных состояний</li>
                            <li>Когнитивно-поведенческая терапия</li>
                            <li>Восстановление мотивации</li>
                            <li>Развитие позитивного мышления</li>
                        </ul>
                    </div>
                </div>

                <div class="service-card service-card--specialized">
                    <div class="service-card__icon">
                        <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
                            <path
                                d="M20 4C11.163 4 4 11.163 4 20s7.163 16 16 16 16-7.163 16-16S28.837 4 20 4zm0 28c-6.627 0-12-5.373-12-12S13.373 8 20 8s12 5.373 12 12-5.373 12-12 12z"
                                fill="white" />
                            <path d="M16 14h8v2h-8v-2zm0 4h8v2h-8v-2zm0 4h8v2h-8v-2z" fill="white" />
                        </svg>
                    </div>
                    <div class="service-card__content">
                        <h3 class="service-card__title">Кризисные ситуации</h3>
                        <p class="service-card__description">
                            Поддержка в кризисных ситуациях: развод, потеря близкого,
                            увольнение, переезд. Помощь в адаптации к изменениям
                            и поиске новых смыслов.
                        </p>
                        <ul class="service-card__features">
                            <li>Экстренная психологическая поддержка</li>
                            <li>Помощь в адаптации к изменениям</li>
                            <li>Поиск новых смыслов и целей</li>
                            <li>Восстановление после потерь</li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Финальная CTA секция -->
    <section class="services-cta services-cta--final">
        <div class="wrapper">
            <div class="services-cta__content">
                <div class="services-cta__icon">
                    <svg width="64" height="64" viewBox="0 0 64 64" fill="none">
                        <circle cx="32" cy="32" r="32" fill="#d2afa0" opacity="0.1" />
                        <path
                            d="M32 8C18.745 8 8 18.745 8 32s10.745 24 24 24 24-10.745 24-24S45.255 8 32 8zm0 44c-11.046 0-20-8.954-20-20S20.954 12 32 12s20 8.954 20 20-8.954 20-20 20z"
                            fill="#d2afa0" />
                        <path
                            d="M32 20c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm0 20c-4.418 0-8-3.582-8-8s3.582-8 8-8 8 3.582 8 8-3.582 8-8 8z"
                            fill="#d2afa0" />
                    </svg>
                </div>
                <h2 class="services-cta__title">Готовы начать работу над собой?</h2>
                <p class="services-cta__text">
                    Запишитесь на бесплатную консультацию и получите персональные рекомендации.
                    Первый шаг к изменениям - это обращение за помощью.
                </p>
                <button class="services-cta__btn md-main-color-btn" popupOpen="call-back-popup"
                    data-form-source="Услуги: Готовы начать работу над собой?">
                    <span>ЗАПИСАТЬСЯ НА КОНСУЛЬТАЦИЮ</span>
                    <img src="image/phone.svg" alt="" />
                </button>
            </div>
        </div>
    </section>

    <?php include 'includes/new-footer.php'; ?>

    <script src="js/main.js"></script>
    <script src="js/new-components.js"></script>
    <script src="js/fancybox.umd.js"></script>
    <script src="js/script.js"></script>
    <script src="js/jquery.maskedinput.min.js"></script>
    <script src="js/form-handler.js?v=1.5"></script>
    <script src="js/new-homepage.js?v=3.1"></script>
    <script>
        // Инициализация Fancybox
        if (typeof Fancybox !== 'undefined') {
            Fancybox.bind('[data-fancybox]');
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