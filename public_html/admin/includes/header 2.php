<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? sanitizeOutput($pageTitle) . ' - ' : ''; ?>Панель администратора - Черкас
        Терапия</title>

    <!-- Admin CSS -->
    <link rel="stylesheet" href="assets/css/admin.css">

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Редакторы: TinyMCE отключен. CKEditor подключается локально на нужных страницах. -->

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../favicon.ico">

    <meta name="robots" content="noindex, nofollow">
</head>

<body class="admin-body">
    <!-- Notification Container -->
    <div id="notificationContainer" class="notification-container"></div>
    <!-- Admin Header -->
    <header class="admin-header">
        <div class="admin-header-content">
            <div class="admin-logo">
                <a href="index.php">
                    <i class="fas fa-brain"></i>
                    <span>Панель администратора</span>
                </a>
            </div>

            <nav class="admin-nav">
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="index.php"
                            class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Панель управления</span>
                        </a>
                    </li>

                    <?php if (hasPermission('reviews')): ?>
                        <li class="nav-item">
                            <a href="reviews.php"
                                class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reviews.php' ? 'active' : ''; ?>">
                                <i class="fas fa-star"></i>
                                <span>Отзывы</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="product-reviews.php"
                                class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'product-reviews.php' ? 'active' : ''; ?>">
                                <i class="fas fa-star-half-alt"></i>
                                <span>Отзывы товаров</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (hasPermission('comments')): ?>
                        <li class="nav-item">
                            <a href="comments.php"
                                class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'comments.php' ? 'active' : ''; ?>">
                                <i class="fas fa-comments"></i>
                                <span>Комментарии</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (hasPermission('articles')): ?>
                        <li class="nav-item">
                            <a href="articles.php"
                                class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'articles.php' ? 'active' : ''; ?>">
                                <i class="fas fa-newspaper"></i>
                                <span>Статьи</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="article-categories.php"
                                class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'article-categories.php' ? 'active' : ''; ?>">
                                <i class="fas fa-tags"></i>
                                <span>Категории статей</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="ai-article-generator.php"
                                class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'ai-article-generator.php' ? 'active' : ''; ?>">
                                <i class="fas fa-robot"></i>
                                <span>AI Генератор</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="logs.php"
                                class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'logs.php' ? 'active' : ''; ?>">
                                <i class="fas fa-clipboard-list"></i>
                                <span>Логи</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (hasPermission('products')): ?>
                        <li class="nav-item">
                            <a href="products-v4.php"
                                class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products-v4.php' ? 'active' : ''; ?>">
                                <i class="fas fa-box"></i>
                                <span>Товары (v4.0)</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="product-categories.php"
                                class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'product-categories.php' ? 'active' : ''; ?>">
                                <i class="fas fa-tags"></i>
                                <span>Категории товаров</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (hasPermission('meditations')): ?>
                        <li class="nav-item">
                            <a href="meditations.php"
                                class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'meditations.php' ? 'active' : ''; ?>">
                                <i class="fas fa-om"></i>
                                <span>Медитации</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (hasPermission('orders')): ?>
                        <li class="nav-item">
                            <a href="orders.php"
                                class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>">
                                <i class="fas fa-shopping-cart"></i>
                                <span>Заказы</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (hasPermission('promos') || (getCurrentUser()['role'] ?? '') === 'admin'): ?>
                        <li class="nav-item">
                            <a href="promos.php"
                                class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'promos.php' ? 'active' : ''; ?>">
                                <i class="fas fa-tag"></i>
                                <span>Промокоды</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (hasPermission('promos') || (getCurrentUser()['role'] ?? '') === 'admin'): ?>
                        <li class="nav-item">
                            <a href="spotlights.php"
                                class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'spotlights.php' ? 'active' : ''; ?>">
                                <i class="fas fa-bullhorn"></i>
                                <span>Витрина</span>
                            </a>
                        </li>
                    <?php endif; ?>


                </ul>
            </nav>

            <div class="admin-user-menu">
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <i class="fas fa-chevron-down dropdown-arrow"></i>
                </div>

                <div class="user-dropdown">
                    <a href="../" class="dropdown-item" target="_blank">
                        <i class="fas fa-external-link-alt"></i>
                        Перейти на сайт
                    </a>
                    <a href="profile.php" class="dropdown-item">
                        <i class="fas fa-user-cog"></i>
                        Профиль
                    </a>
                    <a href="settings.php" class="dropdown-item">
                        <i class="fas fa-cog"></i>
                        Настройки
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="logout.php" class="dropdown-item">
                        <i class="fas fa-sign-out-alt"></i>
                        Выйти
                    </a>
                </div>
            </div>

            <!-- Mobile menu toggle -->
            <button class="mobile-menu-toggle" id="mobileMenuToggle">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>

    <!-- Mobile sidebar overlay -->
    <div class="mobile-sidebar-overlay" id="mobileSidebarOverlay"></div>

    <!-- Mobile sidebar -->
    <aside class="mobile-sidebar" id="mobileSidebar">
        <div class="mobile-sidebar-header">
            <div class="admin-logo">
                <i class="fas fa-brain"></i>
                <span>Панель администратора</span>
            </div>
            <button class="mobile-sidebar-close" id="mobileSidebarClose">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <nav class="mobile-sidebar-nav">
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="index.php" class="nav-link">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Панель управления</span>
                    </a>
                </li>

                <?php if (hasPermission('reviews')): ?>
                    <li class="nav-item">
                        <a href="reviews.php" class="nav-link">
                            <i class="fas fa-star"></i>
                            <span>Отзывы</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="product-reviews.php" class="nav-link">
                            <i class="fas fa-star-half-alt"></i>
                            <span>Отзывы товаров</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (hasPermission('comments')): ?>
                    <li class="nav-item">
                        <a href="comments.php" class="nav-link">
                            <i class="fas fa-comments"></i>
                            <span>Комментарии</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (hasPermission('articles')): ?>
                    <li class="nav-item">
                        <a href="articles.php" class="nav-link">
                            <i class="fas fa-newspaper"></i>
                            <span>Статьи</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (hasPermission('products')): ?>
                    <li class="nav-item">
                        <a href="products.php" class="nav-link">
                            <i class="fas fa-box"></i>
                            <span>Товары</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="product-categories.php" class="nav-link">
                            <i class="fas fa-tags"></i>
                            <span>Категории товаров</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (hasPermission('promos')): ?>
                    <li class="nav-item">
                        <a href="promos.php" class="nav-link">
                            <i class="fas fa-tag"></i>
                            <span>Промокоды</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (hasPermission('meditations')): ?>
                    <li class="nav-item">
                        <a href="meditations.php" class="nav-link">
                            <i class="fas fa-om"></i>
                            <span>Медитации</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (hasPermission('orders')): ?>
                    <li class="nav-item">
                        <a href="orders.php" class="nav-link">
                            <i class="fas fa-shopping-cart"></i>
                            <span>Заказы</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (hasPermission('users')): ?>
                    <li class="nav-item">
                        <a href="users.php" class="nav-link">
                            <i class="fas fa-users"></i>
                            <span>Пользователи</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <div class="mobile-user-section">
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo sanitizeOutput(getCurrentUser()['name'] ?? 'Admin'); ?></span>
                </div>
                <div class="user-actions">
                    <a href="../" target="_blank">Перейти на сайт</a>
                    <a href="profile.php">Профиль</a>
                    <a href="settings.php">Настройки</a>
                    <a href="logout.php">Выйти</a>
                </div>
            </div>
        </nav>
    </aside>

    <!-- Main content area -->
    <main class="admin-main">
        <div class="admin-content">
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php
                    echo sanitizeOutput($_SESSION['success_message']);
                    unset($_SESSION['success_message']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php
                    echo sanitizeOutput($_SESSION['error_message']);
                    unset($_SESSION['error_message']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['warning_message'])): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php
                    echo sanitizeOutput($_SESSION['warning_message']);
                    unset($_SESSION['warning_message']);
                    ?>
                </div>
            <?php endif; ?>
            <!-- Main content area -->
            <main class="admin-main">
                <div class="admin-content">
                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?php
                            echo sanitizeOutput($_SESSION['success_message']);
                            unset($_SESSION['success_message']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php
                            echo sanitizeOutput($_SESSION['error_message']);
                            unset($_SESSION['error_message']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['warning_message'])): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <?php
                            echo sanitizeOutput($_SESSION['warning_message']);
                            unset($_SESSION['warning_message']);
                            ?>
                        </div>
                    <?php endif; ?>