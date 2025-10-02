<?php
$rootPath = __DIR__;
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
require_once 'includes/spotlight-render.php';

// Проверка режима обслуживания
if (isMaintenanceMode() && !isAdminAccess()) {
    header('Location: /maintenance.php');
    exit;
}

// Перенаправляем на ЧПУ URL если нужно
redirectToSeoUrl();

// Создаем экземпляр ProductManager
$productManager = new ProductManager();

// Получаем slug товара из URL
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header('Location: /shop.php');
    exit;
}

// Получаем данные товара по slug и список всех товаров для рекомендаций
$product = $productManager->getProductBySlug($slug);
$allProducts = $productManager->getAllProducts();

// Отладочная информация
error_log("=== PRODUCT PAGE DEBUG ===");
error_log("Slug: " . $slug);
error_log("Product data: " . print_r($product, true));

// Исправляем кодировку в данных товара
if ($product) {
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

if (!$product) {
    header('Location: /shop.php');
    exit;
}

// Проверяем наличие акции и таймера
$hasSale = !empty($product['sale_end_date']) && strtotime($product['sale_end_date']) > time();
$saleEndDate = $hasSale ? $product['sale_end_date'] : '';

// Получаем галерею изображений
$gallery = $product['gallery'] ?? [];
error_log("Raw gallery data: " . print_r($gallery, true));
if (!is_array($gallery)) {
    $gallery = json_decode($gallery, true) ?: [];
}
error_log("Processed gallery data: " . print_r($gallery, true));

// Добавляем главное изображение в галерею, если его там нет
error_log("Main image: " . ($product['image'] ?? 'null'));
if (!empty($product['image']) && !in_array($product['image'], $gallery)) {
    array_unshift($gallery, $product['image']); // Добавляем в начало
}

// Если галерея все еще пустая, используем главное изображение
if (empty($gallery) && !empty($product['image'])) {
    $gallery = [$product['image']];
}

// Очистка галереи: убираем несуществующие файлы/пустые элементы
$filtered = [];
foreach ($gallery as $gItem) {
    $path = is_array($gItem) ? ($gItem['path'] ?? '') : $gItem;
    if (!$path) { continue; }
    // Разрешаем абсолютные URL без проверки файловой системы
    if (preg_match('~^https?://~i', $path)) {
        $filtered[] = is_array($gItem) ? $gItem : $path;
        continue;
    }
    // Нормализуем путь (добавляем ведущий слэш для проверки в ФС) и проверяем наличие файла
    $normForFsCheck = '/' . ltrim($path, '/');
    $abs = $_SERVER['DOCUMENT_ROOT'] . $normForFsCheck;
    if (file_exists($abs)) {
        // В данных галереи оставляем путь без ведущего слэша, т.к. в разметке мы добавляем его сами
        $storedPath = ltrim($path, '/');
        if (is_array($gItem)) {
            $gItem['path'] = $storedPath;
            $filtered[] = $gItem;
        } else {
            $filtered[] = $storedPath;
        }
    }
}
$gallery = $filtered;
// Normalize gallery to [{path, type}] for consistent rendering
$normalized = [];
foreach ($gallery as $gItem) {
    $path = is_array($gItem) ? ($gItem['path'] ?? '') : $gItem;
    if (!$path) { continue; }
    $type = 'image';
    if (is_array($gItem) && !empty($gItem['type'])) {
        $type = $gItem['type'];
    } else {
        $ext = strtolower(pathinfo(parse_url($path, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
        if ($ext === 'mp4' || $ext === 'webm' || $ext === 'mov') { $type = 'video'; }
    }
    $normalized[] = ['path' => $path, 'type' => $type];
}
$gallery = $normalized;

// Мета-данные страницы
$meta = [
    'title' => $product['title'] . ' - Магазин психолога Дениса Черкаса',
    'description' => $product['short_description'],
    'keywords' => (function () use ($product) {
        $tags = $product['tags'] ?? null;
        if (is_array($tags)) {
            return implode(', ', $tags);
        } elseif (is_string($tags)) {
            $decoded = json_decode($tags, true);
            return is_array($decoded) ? implode(', ', $decoded) : '';
        }
        return '';
    })()
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

    <!-- CSS Files -->
    <link rel="stylesheet" href="/css/new-components.css" />
    <link rel="stylesheet" href="/css/new-homepage.css?v=7.6" type="text/css" media="all" />
    <link rel="stylesheet" href="/css/pages.css" type="text/css" media="all" />
    <link rel="stylesheet" href="/css/shop.css?v=2.1" type="text/css" media="all" />
    <link rel="stylesheet" href="/css/product.css" />
    <link rel="stylesheet" href="/css/product-gallery-enhanced.css" />
    <link rel="stylesheet" href="/css/shop-mobile-header.css?v=1.0" type="text/css" media="all" />
    
    <!-- Fancybox CSS -->
    <link rel="stylesheet" href="/css/fancybox.css" type="text/css" media="all" />
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />
    <!-- Fancybox Fixes CSS -->
    <link rel="stylesheet" href="/css/fancybox-fixes.css" type="text/css" media="all" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    
    <style>
        /* Принудительные ограничения для wrapper */
        .wrapper {
            max-width: 1200px !important;
            width: 100% !important;
            margin: 0 auto !important;
            box-sizing: border-box !important;
            padding: 0 20px !important;
        }

        /* Специфичные стили для карусели продукта */
        .product-gallery {
            position: relative !important;
            max-width: 100% !important;
            width: 100% !important;
            box-sizing: border-box !important;
            overflow: hidden !important;
        }

        .gallery-main {
            border-radius: 8px !important;
            overflow: hidden !important;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1) !important;
            margin-bottom: 10px !important;
            background: #f8f9fa !important;
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
        }

        .gallery-main .swiper {
            width: 100% !important;
            max-width: 100% !important;
            height: auto !important;
        }

        .gallery-main .swiper-wrapper {
            width: 100% !important;
            max-width: 100% !important;
        }

        .gallery-image-container {
            position: relative !important;
            width: 100% !important;
            max-width: 100% !important;
            height: 250px !important;
            overflow: hidden !important;
            cursor: pointer !important;
            transition: transform 0.3s ease !important;
            box-sizing: border-box !important;
        }

        .gallery-main-image {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            transition: transform 0.3s ease !important;
            max-width: 100% !important;
        }

        /* Красивые превью миниатюр - маленькие квадратики */
        .gallery-thumbnails {
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
            margin-top: 15px !important;
            padding: 0 5px !important;
        }

        .thumbnail-swiper {
            width: 100% !important;
            max-width: 100% !important;
            height: 60px !important;
        }

        .thumbnail-swiper .swiper-wrapper {
            width: 100% !important;
            max-width: 100% !important;
            align-items: center !important;
        }

        .thumbnail-item {
            position: relative !important;
            cursor: pointer !important;
            border-radius: 8px !important;
            overflow: hidden !important;
            border: 2px solid transparent !important;
            transition: all 0.3s ease !important;
            height: 50px !important;
            width: 50px !important;
            opacity: 0.6 !important;
            background: #f8f9fa !important;
            box-sizing: border-box !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
            transform: scale(0.9) !important;
            margin: 0 5px !important;
        }

        .thumbnail-item:hover {
            border-color: #007bff !important;
            opacity: 0.8 !important;
            transform: scale(1.05) !important;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3) !important;
            z-index: 10 !important;
        }

        .thumbnail-item.active {
            border-color: #007bff !important;
            opacity: 1 !important;
            transform: scale(1.1) !important;
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4) !important;
            z-index: 15 !important;
        }

        .thumbnail-item img,
        .thumbnail-item video {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            max-width: 100% !important;
        }

        /* Иконка воспроизведения для видео в превью */
        .thumbnail-play-icon {
            position: absolute !important;
            top: 50% !important;
            left: 50% !important;
            transform: translate(-50%, -50%) !important;
            width: 20px !important;
            height: 20px !important;
            background: rgba(0, 0, 0, 0.8) !important;
            border-radius: 50% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            color: white !important;
            font-size: 8px !important;
            backdrop-filter: blur(3px) !important;
            z-index: 5 !important;
        }

        /* Навигация карусели */
        .swiper-button-next,
        .swiper-button-prev {
            width: 40px !important;
            height: 40px !important;
            background: rgba(255, 255, 255, 0.95) !important;
            border-radius: 50% !important;
            color: #333 !important;
            transition: all 0.3s ease !important;
            backdrop-filter: blur(10px) !important;
            z-index: 10 !important;
        }

        /* Навигация для миниатюр */
        .thumbnail-swiper .swiper-button-next,
        .thumbnail-swiper .swiper-button-prev {
            width: 30px !important;
            height: 30px !important;
            background: rgba(255, 255, 255, 0.9) !important;
            border-radius: 50% !important;
            color: #333 !important;
            font-size: 12px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
        }

        .thumbnail-swiper .swiper-button-next {
            right: 5px !important;
        }

        .thumbnail-swiper .swiper-button-prev {
            left: 5px !important;
        }

        .swiper-button-next,
        .swiper-button-prev {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
        }

        .swiper-button-next:hover,
        .swiper-button-prev:hover {
            background: rgba(255, 255, 255, 1) !important;
            transform: scale(1.1) !important;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2) !important;
        }

        .swiper-button-next::after,
        .swiper-button-prev::after {
            font-size: 14px !important;
            font-weight: bold !important;
            color: #007bff !important;
        }

        /* Пагинация */
        .swiper-pagination {
            bottom: 15px !important;
        }

        .swiper-pagination-bullet {
            width: 8px !important;
            height: 8px !important;
            background: rgba(255, 255, 255, 0.8) !important;
            opacity: 1 !important;
            transition: all 0.3s ease !important;
            margin: 0 3px !important;
        }

        .swiper-pagination-bullet-active {
            background: #007bff !important;
            transform: scale(1.3) !important;
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.4) !important;
        }

        /* Fancybox кнопки */
        .gallery-zoom-btn {
            position: absolute !important;
            top: 15px !important;
            right: 15px !important;
            width: 40px !important;
            height: 40px !important;
            background: rgba(0, 0, 0, 0.8) !important;
            border-radius: 50% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            color: white !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
            opacity: 0 !important;
            backdrop-filter: blur(10px) !important;
            text-decoration: none !important;
            border: none !important;
            z-index: 10 !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3) !important;
        }

        .gallery-image-container:hover .gallery-zoom-btn {
            opacity: 1 !important;
        }

        .gallery-zoom-btn:hover {
            background: rgba(0, 0, 0, 0.95) !important;
            transform: scale(1.1) !important;
            color: white !important;
            text-decoration: none !important;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4) !important;
        }

        .gallery-zoom-btn i {
            font-size: 16px !important;
        }

        /* Клик на изображение для Fancybox */
        .gallery-image-container {
            position: relative !important;
            width: 100% !important;
            max-width: 100% !important;
            height: 250px !important;
            overflow: hidden !important;
            cursor: pointer !important;
            transition: transform 0.3s ease !important;
            box-sizing: border-box !important;
            border-radius: 8px !important;
        }

        .gallery-image-container:hover {
            transform: scale(1.01) !important;
        }

        .gallery-main-image {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            transition: transform 0.3s ease !important;
            max-width: 100% !important;
        }

        .gallery-image-container:hover .gallery-main-image {
            transform: scale(1.05) !important;
        }

        /* Принудительно ограничиваем размеры */
        .product-layout {
            max-width: 100% !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }

        /* Ограничиваем Swiper элементы */
        .swiper-slide {
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
        }

        /* Адаптивность */
        @media (max-width: 768px) {
            .wrapper {
                padding: 0 15px !important;
            }
            
            .gallery-image-container {
                height: 200px !important;
            }
            
            .thumbnail-swiper {
                height: 50px !important;
            }
            
            .thumbnail-item {
                height: 40px !important;
                width: 40px !important;
            }
            
            .swiper-button-next,
            .swiper-button-prev {
                width: 35px !important;
                height: 35px !important;
            }
            
            .swiper-button-next::after,
            .swiper-button-prev::after {
                font-size: 12px !important;
            }
            
            .gallery-zoom-btn {
                width: 35px !important;
                height: 35px !important;
                top: 10px !important;
                right: 10px !important;
            }
            
            .gallery-zoom-btn i {
                font-size: 14px !important;
            }
        }

        @media (max-width: 480px) {
            .wrapper {
                padding: 0 10px !important;
            }
            
            .gallery-image-container {
                height: 160px !important;
            }
            
            .thumbnail-item {
                height: 35px !important;
            }
            
            .swiper-button-next,
            .swiper-button-prev {
                width: 30px !important;
                height: 30px !important;
            }
            
            .swiper-button-next::after,
            .swiper-button-prev::after {
                font-size: 10px !important;
            }
            
            .gallery-zoom-btn {
                width: 30px !important;
                height: 30px !important;
                top: 8px !important;
                right: 8px !important;
            }
            
            .gallery-zoom-btn i {
                font-size: 12px !important;
            }
            
            .thumbnail-play-icon {
                width: 16px !important;
                height: 16px !important;
                font-size: 6px !important;
            }
        }

        /* Улучшенные стили для миниатюр карусели */
        .gallery-thumbnails {
            margin-top: 20px !important;
            padding: 0 10px !important;
        }

        .thumbnail-swiper {
            height: 80px !important;
            margin: 0 auto !important;
        }

        .thumbnail-item {
            position: relative !important;
            cursor: pointer !important;
            border-radius: 12px !important;
            overflow: hidden !important;
            border: 3px solid transparent !important;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
            height: 70px !important;
            opacity: 0.7 !important;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
            box-sizing: border-box !important;
            width: 100% !important;
            max-width: 100% !important;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1) !important;
            transform: scale(0.95) !important;
        }

        .thumbnail-item:hover {
            border-color: #007bff !important;
            opacity: 0.9 !important;
            transform: scale(1.05) translateY(-3px) !important;
            box-shadow: 0 8px 25px rgba(0, 123, 255, 0.3) !important;
            z-index: 10 !important;
        }

        .thumbnail-item.active {
            border-color: #007bff !important;
            opacity: 1 !important;
            transform: scale(1.1) translateY(-2px) !important;
            box-shadow: 0 10px 30px rgba(0, 123, 255, 0.4) !important;
            z-index: 15 !important;
        }

        .thumbnail-item img,
        .thumbnail-item video {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            max-width: 100% !important;
            border-radius: 9px !important;
            transition: transform 0.3s ease !important;
            opacity: 0.8 !important;
        }

        .thumbnail-item.loaded img,
        .thumbnail-item.loaded video {
            opacity: 1 !important;
        }

        .thumbnail-item:hover img,
        .thumbnail-item:hover video {
            transform: scale(1.1) !important;
        }

        /* Анимация загрузки для миниатюр */
        .thumbnail-item:not(.loaded)::before {
            content: '' !important;
            position: absolute !important;
            top: 50% !important;
            left: 50% !important;
            transform: translate(-50%, -50%) !important;
            width: 20px !important;
            height: 20px !important;
            border: 2px solid #e9ecef !important;
            border-top: 2px solid #007bff !important;
            border-radius: 50% !important;
            animation: spin 1s linear infinite !important;
            z-index: 1 !important;
        }

        @keyframes spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }

        /* Улучшенная иконка воспроизведения для видео в превью */
        .thumbnail-play-icon {
            position: absolute !important;
            top: 50% !important;
            left: 50% !important;
            transform: translate(-50%, -50%) !important;
            width: 24px !important;
            height: 24px !important;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.9) 0%, rgba(0, 0, 0, 0.7) 100%) !important;
            border-radius: 50% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            color: white !important;
            font-size: 10px !important;
            backdrop-filter: blur(5px) !important;
            z-index: 5 !important;
            border: 2px solid rgba(255, 255, 255, 0.3) !important;
            transition: all 0.3s ease !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3) !important;
        }

        .thumbnail-item:hover .thumbnail-play-icon {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
            transform: translate(-50%, -50%) scale(1.2) !important;
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4) !important;
        }

        .thumbnail-play-icon i {
            margin-left: 1px !important;
        }

        /* Индикатор типа контента */
        .thumbnail-type-indicator {
            position: absolute !important;
            top: 5px !important;
            right: 5px !important;
            width: 16px !important;
            height: 16px !important;
            border-radius: 50% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 8px !important;
            color: white !important;
            z-index: 6 !important;
            backdrop-filter: blur(3px) !important;
        }

        .thumbnail-type-indicator.video {
            background: linear-gradient(135deg, #ff4757 0%, #ff3742 100%) !important;
            box-shadow: 0 2px 8px rgba(255, 71, 87, 0.4) !important;
        }

        .thumbnail-type-indicator.image {
            background: linear-gradient(135deg, #2ed573 0%, #1e90ff 100%) !important;
            box-shadow: 0 2px 8px rgba(46, 213, 115, 0.4) !important;
        }

        /* Улучшенная навигация карусели */
        .swiper-button-next,
        .swiper-button-prev {
            width: 44px !important;
            height: 44px !important;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.9) 100%) !important;
            border-radius: 50% !important;
            color: #333 !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            backdrop-filter: blur(15px) !important;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15) !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
        }

        .swiper-button-next:hover,
        .swiper-button-prev:hover {
            background: linear-gradient(135deg, rgba(255, 255, 255, 1) 0%, rgba(255, 255, 255, 0.95) 100%) !important;
            transform: scale(1.15) !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2) !important;
        }

        .swiper-button-next::after,
        .swiper-button-prev::after {
            font-size: 16px !important;
            font-weight: bold !important;
            color: #007bff !important;
        }

        /* Улучшенная пагинация */
        .swiper-pagination {
            bottom: 20px !important;
        }

        .swiper-pagination-bullet {
            width: 10px !important;
            height: 10px !important;
            background: rgba(255, 255, 255, 0.8) !important;
            opacity: 1 !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            margin: 0 4px !important;
            border-radius: 50% !important;
        }

        .swiper-pagination-bullet-active {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
            transform: scale(1.4) !important;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.5) !important;
        }

        /* Улучшенные кнопки Fancybox */
        .gallery-zoom-btn {
            position: absolute !important;
            top: 15px !important;
            right: 15px !important;
            width: 44px !important;
            height: 44px !important;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.9) 0%, rgba(0, 0, 0, 0.7) 100%) !important;
            border-radius: 50% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            color: white !important;
            cursor: pointer !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            opacity: 0 !important;
            backdrop-filter: blur(15px) !important;
            text-decoration: none !important;
            border: none !important;
            z-index: 10 !important;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
        }

        .gallery-image-container:hover .gallery-zoom-btn {
            opacity: 1 !important;
            transform: scale(1.1) !important;
        }

        .gallery-zoom-btn:hover {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
            transform: scale(1.2) !important;
            color: white !important;
            text-decoration: none !important;
            box-shadow: 0 10px 30px rgba(0, 123, 255, 0.4) !important;
        }

        .gallery-zoom-btn i {
            font-size: 18px !important;
        }

        /* Улучшенный контейнер изображения */
        .gallery-image-container {
            position: relative !important;
            width: 100% !important;
            max-width: 100% !important;
            height: 300px !important;
            overflow: hidden !important;
            cursor: pointer !important;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            box-sizing: border-box !important;
            border-radius: 12px !important;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1) !important;
        }

        .gallery-image-container:hover {
            transform: scale(1.02) !important;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15) !important;
        }

        .gallery-main-image {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            max-width: 100% !important;
        }

        .gallery-image-container:hover .gallery-main-image {
            transform: scale(1.08) !important;
        }

        /* Адаптивность для миниатюр */
        @media (max-width: 768px) {
            .thumbnail-swiper {
                height: 60px !important;
            }
            
            .thumbnail-item {
                height: 50px !important;
            }
            
            .thumbnail-play-icon {
                width: 20px !important;
                height: 20px !important;
                font-size: 8px !important;
            }
            
            .gallery-image-container {
                height: 250px !important;
            }
            
            .swiper-button-next,
            .swiper-button-prev {
                width: 36px !important;
                height: 36px !important;
            }
        }

        @media (max-width: 480px) {
            .thumbnail-swiper {
                height: 50px !important;
            }
            
            .thumbnail-item {
                height: 40px !important;
            }
            
            .thumbnail-play-icon {
                width: 18px !important;
                height: 18px !important;
                font-size: 7px !important;
            }
            
            .gallery-image-container {
                height: 200px !important;
            }
        }
        
        /* Стили для ярлыков товара */
        .product-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 10px 0 15px 0;
        }
        
        .product-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
        }
        
        .product-badge:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        }
        
        @media (max-width: 768px) {
            .product-badges {
                gap: 6px;
                margin: 8px 0 12px 0;
            }
            
            .product-badge {
                font-size: 11px;
                padding: 3px 6px;
            }
        }
    </style>
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
                        
                        <?php if (!empty($product['badges'])): ?>
                        <div class="product-badges">
                            <?php foreach ($product['badges'] as $badge): ?>
                                <span class="product-badge" style="color: <?= e($badge['color']) ?>; background-color: <?= e($badge['background_color']) ?>;">
                                    <?= e($badge['name']) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        
                        <p class="product-description"><?= e($product['short_description']) ?></p>

                        <div class="product-price-container">
                            <?php if ($product['type'] === 'free'): ?>
                                <span class="product-price product-price--free">Бесплатно</span>
                            <?php else: ?>
                                <span class="product-price"><?= number_format($product['price'], 0, ',', ' ') ?> ₽</span>
                            <?php endif; ?>
                            <?php if (!empty($product['old_price']) && $product['old_price'] > $product['price'] && $product['type'] !== 'free'): ?>
                                <span class="product-price-old"><?= number_format($product['old_price'], 0, ',', ' ') ?> ₽</span>
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

                    <!-- Правая часть - галерея в стиле маркетплейса (Ozon-like) -->
                    <div class="product-gallery ozon-gallery">
                        <!-- Вертикальные миниатюры слева (на десктопе) -->
                        <?php if (count($gallery) > 1): ?>
                        <div class="swiper ozon-thumbs-swiper">
                            <div class="swiper-wrapper">
                                    <?php foreach ($gallery as $index => $item): 
                                        $filePath = is_array($item) ? $item['path'] : $item;
                                        $fileType = is_array($item) ? $item['type'] : (pathinfo($filePath, PATHINFO_EXTENSION) === 'mp4' ? 'video' : 'image');
                                        $isVideo = $fileType === 'video';
                                        
                                        // Проверяем существование файла
                                        if (!file_exists(__DIR__ . '/' . $filePath)) {
                                            $filePath = 'assets/images/no-image.svg';
                                            $isVideo = false;
                                        }
                                    ?>
                                    <div class="swiper-slide" data-index="<?= $index ?>">
                                                <?php if ($isVideo): ?>
                                            <div class="thumbnail-video-placeholder"><i class="fas fa-play"></i></div>
                                                <?php else: ?>
                                            <img src="/<?= e($filePath) ?>" alt="<?= e($product['title']) ?> - миниатюра <?= $index + 1 ?>" class="thumbnail-image" loading="<?= $index < 8 ? 'eager' : 'lazy' ?>" />
                                                <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                    </div>
                                <?php endif; ?>

                        <!-- Большая область просмотра справа -->
                        <div class="gallery-main ozon-main">
                            <div class="swiper ozon-main-swiper">
                                <div class="swiper-wrapper">
                    <?php foreach ($gallery as $index => $item): 
                        $filePath = is_array($item) ? $item['path'] : $item;
                        $fileType = is_array($item) ? $item['type'] : (pathinfo($filePath, PATHINFO_EXTENSION) === 'mp4' ? 'video' : 'image');
                        $isVideo = $fileType === 'video';
                        
                        // Проверяем существование файла
                        if (!file_exists(__DIR__ . '/' . $filePath)) {
                            $filePath = 'assets/images/no-image.svg';
                            $isVideo = false;
                        }
                    ?>
                                        <div class="swiper-slide">
                            <?php if ($isVideo): ?>
                                                <div class="gallery-image-link">
                                                    <video src="/<?= e($filePath) ?>" controls playsinline webkit-playsinline class="gallery-media" preload="auto" controlslist="nodownload noplaybackrate" disablepictureinpicture>
                                                        <source src="/<?= e($filePath) ?>" type="video/mp4">
                                                        Ваш браузер не поддерживает видео.
                                                    </video>
                                </div>
                            <?php else: ?>
                                                <div class="gallery-image-link">
                                                    <img src="/<?= e($filePath) ?>" alt="<?= e($product['title']) ?> - изображение <?= $index + 1 ?>" class="gallery-media" loading="<?= $index === 0 ? 'eager' : 'lazy' ?>" />
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                                <div class="swiper-button-prev"></div>
                                <div class="swiper-button-next"></div>
                                <div class="swiper-pagination"></div>
            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>



        <!-- Описание товара -->
        <section class="product-content">
            <div class="wrapper">
                <div class="content-header">
                    <h2>Описание товара</h2>
                    <div class="content-tabs">
                        <button class="tab-btn active" onclick="switchTab('description')">Описание</button>
                        <button class="tab-btn" onclick="switchTab('features')">Характеристики</button>
                        <button class="tab-btn" onclick="switchTab('reviews')">Отзывы</button>
                    </div>
                </div>
                
                <div class="content-tabs-content">
                    <!-- Вкладка описания -->
                    <div id="description-tab" class="tab-content active">
                        <div class="content-text">
                            <?= $product['description'] ?>
                        </div>
                        
                        <?php 
                        $features = $product['features'] ?? [];
                        if (is_string($features)) { $features = json_decode($features, true) ?: []; }
                        // Normalize features to pairs {name, value}
                        $featurePairs = [];
                        foreach ($features as $f) {
                            if (is_array($f)) {
                                $featurePairs[] = ['name' => trim($f['name'] ?? ''), 'value' => trim($f['value'] ?? '')];
                            } else {
                                $parts = explode(':', (string)$f, 2);
                                $featurePairs[] = ['name' => trim($parts[0] ?? ''), 'value' => trim($parts[1] ?? '')];
                            }
                        }
                        $featurePairs = array_values(array_filter($featurePairs, function($p){ return ($p['name'] !== '' || $p['value'] !== ''); }));
                        if (!empty($featurePairs)):
                        ?>
                            <div class="product-highlights">
                                <h3>Ключевые особенности</h3>
                                <div class="highlights-grid">
                                    <?php foreach (array_slice($featurePairs, 0, 6) as $pair): ?>
                                        <div class="highlight-item">
                                            <i class="fas fa-check-circle"></i>
                                            <span><?= e(trim(($pair['name']?:'') . ($pair['value']!=='' ? (': ' . $pair['value']) : ''))) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php
                            // Spotlight после блока "Ключевые особенности"
                            $unitHtml = get_spotlights_html('product');
                            if (!empty($unitHtml)) {
                                echo '<div class="product-spotlight">' . $unitHtml . '</div>';
                            }
                            ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Вкладка характеристик -->
                    <div id="features-tab" class="tab-content">
                        <div class="features-detailed">
                            <div class="features-section">
                                <h3>Основная информация</h3>
                                <div class="feature-list">
                                    <div class="feature-item">
                                        <span class="feature-label">Категория:</span>
                                        <span class="feature-value"><?= e($product['category_name'] ?? getCategoryName($product['category_slug'] ?? ($product['category'] ?? ''))) ?></span>
                                    </div>
                                    <div class="feature-item">
                                        <span class="feature-label">Тип товара:</span>
                                        <span class="feature-value"><?= getProductTypeName($product['type']) ?></span>
                                    </div>
                                    <div class="feature-item">
                                        <span class="feature-label">Цена:</span>
                                        <span class="feature-value"><?= number_format($product['price'], 0, ',', ' ') ?> ₽</span>
                                    </div>
                                    <?php if (!empty($product['old_price']) && $product['old_price'] > $product['price']): ?>
                                        <div class="feature-item">
                                            <span class="feature-label">Старая цена:</span>
                                            <span class="feature-value old-price"><?= number_format($product['old_price'], 0, ',', ' ') ?> ₽</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <?php 
                            $features = $product['features'] ?? [];
                            if (is_string($features)) { $features = json_decode($features, true) ?: []; }
                            // Normalize to pairs
                            $featurePairs = [];
                            foreach ($features as $f) {
                                if (is_array($f)) {
                                    $featurePairs[] = ['name' => trim($f['name'] ?? ''), 'value' => trim($f['value'] ?? '')];
                                } else {
                                    $parts = explode(':', (string)$f, 2);
                                    $featurePairs[] = ['name' => trim($parts[0] ?? ''), 'value' => trim($parts[1] ?? '')];
                                }
                            }
                            $featurePairs = array_values(array_filter($featurePairs, function($p){ return ($p['name'] !== '' || $p['value'] !== ''); }));
                            if (!empty($featurePairs)):
                            ?>
                                <div class="features-section">
                                    <h3>Детальные характеристики</h3>
                                    <div class="feature-list">
                                        <?php foreach ($featurePairs as $pair): ?>
                                            <div class="feature-item">
                                                <?php if ($pair['name'] !== ''): ?>
                                                    <span class="feature-label"><?= e($pair['name']) ?>:</span>
                                                <?php endif; ?>
                                                <span class="feature-value"><?= e($pair['value']) ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Вкладка отзывов -->
                    <div id="reviews-tab" class="tab-content">
                        <div class="reviews-section">
                            <?php
                            // Подключаем виджет отзывов товаров
                            $productId = $product['id']; // ID товара из базы данных
                            $showTitle = false; // Заголовок уже есть в табе
                            include $rootPath . '/includes/product-reviews-widget.php';
                            ?>
                        </div>
                    </div>
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
    </main>

    <?php include 'includes/new-footer.php'; ?>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="/js/fancybox.umd.js?v=<?php echo time(); ?>"></script>
    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    <script src="/js/main.js?v=<?php echo time(); ?>"></script>
    <script src="/js/new-components.js"></script>
    <script src="/js/form-handler.js?v=1.5"></script>
    <script src="/js/cart.js?v=<?php echo time(); ?>"></script>
    <script src="/js/shop-mobile-menu.js?v=1.0"></script>
    
    <script>
        // Проверка загрузки Fancybox
        window.addEventListener('load', function() {
            console.log('🔍 Проверка загрузки Fancybox после window.load');
            console.log('Fancybox доступен:', typeof Fancybox !== 'undefined');
            if (typeof Fancybox !== 'undefined') {
                console.log('✅ Fancybox успешно загружен');
            } else {
                console.error('❌ Fancybox не загружен');
            }
            
            // Обновляем счетчик корзины
            if (typeof updateCartCounter === 'function') {
                updateCartCounter();
            }
        });
        
        // Обновляем счетчик корзины при загрузке DOM
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, checking cart counter...');
            console.log('updateCartCounter function exists:', typeof updateCartCounter === 'function');
            
            // Проверяем наличие элементов корзины
            const cartContainer = document.querySelector('.header__cart');
            const cartLink = document.querySelector('.cart-link');
            const cartCounter = document.querySelector('.cart-counter');
            
            console.log('Cart container exists:', !!cartContainer);
            console.log('Cart link exists:', !!cartLink);
            console.log('Cart counter exists:', !!cartCounter);
            
            if (cartContainer) {
                console.log('Cart container is visible:', cartContainer.offsetParent !== null);
                console.log('Cart container styles:', window.getComputedStyle(cartContainer).display);
            }
            
            if (typeof updateCartCounter === 'function') {
                console.log('Calling updateCartCounter...');
                updateCartCounter();
            } else {
                console.error('updateCartCounter function not found!');
            }
        });
    </script>

    <script>
        // Глобальные переменные для слайдера
        let currentSlide = 0;
        let totalSlides = <?= count($gallery) ?>;
        let isTransitioning = false;
        let autoPlayInterval = null;
        let autoPlayDelay = 5000; // 5 секунд

        // Функция показа индикатора загрузки
        function showLoading() {
            const slider = document.querySelector('.gallery-slider');
            if (slider) {
                const loading = document.createElement('div');
                loading.className = 'gallery-loading';
                loading.id = 'gallery-loading';
                slider.appendChild(loading);
            }
        }

        // Функция скрытия индикатора загрузки
        function hideLoading() {
            const loading = document.getElementById('gallery-loading');
            if (loading) {
                loading.remove();
            }
        }

        // Функция перехода к слайду с плавной анимацией
        function goToSlide(index) {
            if (isTransitioning) return; // Предотвращаем множественные переходы
            
            if (index < 0) index = totalSlides - 1;
            if (index >= totalSlides) index = 0;
            
            if (index === currentSlide) return; // Не переходим на тот же слайд
            
            isTransitioning = true;
            
            // Показываем индикатор загрузки для видео
            const targetSlide = document.querySelector(`.gallery-slide[data-index="${index}"]`);
            const isVideo = targetSlide && targetSlide.querySelector('video');
            if (isVideo) {
                showLoading();
            }
            
            // Скрываем все слайды с анимацией
            const slides = document.querySelectorAll('.gallery-slide');
            slides.forEach(slide => {
                slide.style.opacity = '0';
                slide.classList.remove('active');
            });
            
            // Показываем нужный слайд с анимацией
            if (targetSlide) {
                targetSlide.classList.add('active');
                setTimeout(() => {
                    targetSlide.style.opacity = '1';
                    hideLoading();
                }, 50);
            }
            
            // Обновляем активную точку
            updateActiveDot(index);
            
            // Обновляем активную миниатюру
            updateActiveThumbnail(index);
            
            // Останавливаем все видео кроме текущего
            stopAllVideosExcept(index);
            
            currentSlide = index;
            
            // Сбрасываем флаг перехода через 300мс
            setTimeout(() => {
                isTransitioning = false;
            }, 300);
            
            // Предзагружаем следующее изображение
            const nextIndex = (index + 1) % totalSlides;
            preloadImage(nextIndex);
            
            console.log('🎯 Переход к слайду:', index);
        }

        // Функция предзагрузки изображения
        function preloadImage(index) {
            const slide = document.querySelector(`.gallery-slide[data-index="${index}"]`);
            if (slide) {
                const img = slide.querySelector('img');
                if (img && !img.complete) {
                    const preloadImg = new Image();
                    preloadImg.src = img.src;
                }
            }
        }

        // Функция запуска автопрокрутки
        function startAutoPlay() {
            if (totalSlides > 1) {
                stopAutoPlay();
                autoPlayInterval = setInterval(() => {
                    changeSlide(1);
                }, autoPlayDelay);
                
                // Обновляем иконку
                const icon = document.getElementById('autoplay-icon');
                if (icon) {
                    icon.className = 'fas fa-pause';
                    icon.parentElement.title = 'Остановить автопрокрутку';
                }
            }
        }

        // Функция остановки автопрокрутки
        function stopAutoPlay() {
            if (autoPlayInterval) {
                clearInterval(autoPlayInterval);
                autoPlayInterval = null;
                
                // Обновляем иконку
                const icon = document.getElementById('autoplay-icon');
                if (icon) {
                    icon.className = 'fas fa-play';
                    icon.parentElement.title = 'Включить автопрокрутку';
                }
            }
        }

        // Функция переключения автопрокрутки
        function toggleAutoPlay() {
            const icon = document.getElementById('autoplay-icon');
            if (autoPlayInterval) {
                stopAutoPlay();
                icon.className = 'fas fa-play';
                icon.parentElement.title = 'Включить автопрокрутку';
            } else {
                startAutoPlay();
                icon.className = 'fas fa-pause';
                icon.parentElement.title = 'Остановить автопрокрутку';
            }
        }

        // Функция смены слайда
        function changeSlide(direction) {
            goToSlide(currentSlide + direction);
        }

        // Функция обновления активной точки
        function updateActiveDot(index) {
            const dots = document.querySelectorAll('.gallery-dot');
            dots.forEach(dot => dot.classList.remove('active'));
            const activeDot = document.querySelector(`.gallery-dot:nth-child(${index + 1})`);
            if (activeDot) {
                activeDot.classList.add('active');
            }
        }
        
        // Функция обновления активной миниатюры
        function updateActiveThumbnail(index) {
            const thumbnails = document.querySelectorAll('.thumbnail-item');
            thumbnails.forEach(thumb => thumb.classList.remove('active'));
            const activeThumbnail = document.querySelector(`.thumbnail-item[data-index="${index}"]`);
            if (activeThumbnail) {
                activeThumbnail.classList.add('active');
                // Прокручиваем к активной миниатюре
                activeThumbnail.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest',
                    inline: 'center'
                });
            }
        }
        
        // Функция остановки всех видео кроме указанного
        function stopAllVideosExcept(activeIndex) {
            const videos = document.querySelectorAll('.gallery-media');
            videos.forEach((video, index) => {
                if (video.tagName === 'VIDEO') {
                    if (index !== activeIndex) {
                    video.pause();
                    video.currentTime = 0;
                    } else {
                        // Автоматически воспроизводим видео на активном слайде
                        video.play().catch(e => {
                            console.log('Автовоспроизведение заблокировано браузером');
                        });
                    }
                }
            });
        }

        // Функция переключения полноэкранного режима
        function toggleFullscreen(button) {
            const video = button.closest('.gallery-item').querySelector('video');
            if (video) {
                if (document.fullscreenElement) {
                    document.exitFullscreen();
                } else {
                    video.requestFullscreen().catch(e => {
                        console.log('Полноэкранный режим не поддерживается');
                    });
                }
            }
        }

        // Обработчик выхода из полноэкранного режима
        document.addEventListener('fullscreenchange', function() {
            if (!document.fullscreenElement) {
                // Выход из полноэкранного режима
                const videos = document.querySelectorAll('video');
                videos.forEach(video => {
                    if (video.paused) {
                        video.currentTime = 0;
                    }
                });
            }
        });

        // Функция принудительной очистки Fancybox
        function forceCleanupFancybox() {
            // Останавливаем все видео
            const videos = document.querySelectorAll('video');
            videos.forEach(video => {
                video.pause();
                video.currentTime = 0;
            });

            // Удаляем все остатки Fancybox
            const fancyboxElements = document.querySelectorAll('.fancybox__container, .fancybox__backdrop, .fancybox__content');
            fancyboxElements.forEach(element => {
                if (element.parentNode) {
                    element.parentNode.removeChild(element);
                }
            });

            // Удаляем классы с body
            document.body.classList.remove('compensate-for-scrollbar', 'with-fancybox');
            
            // Восстанавливаем скролл
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        }

        // Разблокировка скролла страницы (страховка)
        function unlockPageScroll() {
            try {
                const html = document.documentElement;
                const body = document.body;
                html.classList.remove('with-fancybox');
                body.classList.remove('compensate-for-scrollbar', 'with-fancybox');
                body.style.overflow = '';
                body.style.touchAction = '';
                body.style.paddingRight = '';
                body.style.position = '';
                body.style.top = '';
                body.style.width = '';
                html.style.overflow = '';
            } catch (e) {}
        }
        
        // Инициализация Fancybox
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🔍 Проверяем Fancybox...');
            console.log('Fancybox доступен:', typeof Fancybox !== 'undefined');
            
            if (typeof Fancybox !== 'undefined') {
                console.log('🎯 Инициализируем Fancybox...');
                
                // Проверяем наличие элементов с data-fancybox
                const fancyboxElements = document.querySelectorAll('[data-fancybox="gallery"]');
                console.log('📊 Найдено элементов с data-fancybox="gallery":', fancyboxElements.length);
                
                fancyboxElements.forEach((el, index) => {
                    console.log(`Элемент ${index + 1}:`, el.href, 'Тип:', el.getAttribute('data-type') || 'image');
                });
                
                // Глобально убираем лишние эффекты и затемнение контента
                const css = `
                    .fancybox__image, .fancybox__slide.has-video video {opacity:1!important;filter:none!important}
                    .fancybox__content {background:transparent!important}
                    .fancybox__backdrop {background: rgba(0,0,0,0.85)!important}
                `;
                const style = document.createElement('style');
                style.appendChild(document.createTextNode(css));
                document.head.appendChild(style);

                // Собираем элементы галереи без дубликатов swiper
                function collectGalleryItems() {
                    const nodes = Array.from(document.querySelectorAll('.ozon-main-swiper .swiper-slide:not(.swiper-slide-duplicate) .gallery-image-link'));
                    return nodes.map((wrap) => ({
                        src: wrap.querySelector('img')?.getAttribute('src') || wrap.querySelector('video')?.getAttribute('src') || '',
                        type: wrap.querySelector('video') ? 'video' : 'image',
                        caption: ''
                    }));
                }

                // Делегирование: отключаем открытие модалки, включаем зум и переход к нужному слайду
                document.addEventListener('click', function(e) {
                    const link = e.target.closest('.ozon-main-swiper .swiper-slide .gallery-image-link');
                    if (!link) return;
                    e.preventDefault();

                    const mainEl = document.querySelector('.ozon-main-swiper');
                    if (!mainEl || !mainEl.swiper) return;

                    const slideEl = link.closest('.swiper-slide');
                    const allSlides = Array.from(slideEl.parentNode.children).filter(s => !s.classList.contains('swiper-slide-duplicate'));
                    let index = allSlides.indexOf(slideEl);
                    if (index < 0) {
                        // если кликнули по дубликату — переходим к оригиналу по src
                        const src = link.querySelector('img')?.getAttribute('src') || link.querySelector('video')?.getAttribute('src');
                        const orig = allSlides.find(s => {
                            const wrap = s.querySelector('.gallery-image-link');
                            const sSrc = wrap?.querySelector('img')?.getAttribute('src') || wrap?.querySelector('video')?.getAttribute('src');
                            return sSrc === src;
                        });
                        index = Math.max(0, allSlides.indexOf(orig));
                    }

                    mainEl.swiper.slideToLoop(index, 0, false);

                    // Тогглим зум изображения
                    const zoom = mainEl.swiper.zoom;
                    const media = link.querySelector('img,video');
                    if (zoom && media && media.tagName === 'IMG') {
                        if (zoom.scale && zoom.scale !== 1) zoom.out(); else zoom.in(media, 300);
                    }
                });

                // Биндинг по селектору оставляем для других страниц (если есть)
                Fancybox.bind('[data-fancybox="gallery"]', {
                    loop: true,
                    buttons: ['close', 'slideShow', 'fullScreen', 'thumbs'],
                    animationEffect: 'fade',
                    transitionEffect: 'slide',
                    thumbs: {
                        autoStart: false,
                    },
                    arrows: true,
                    navigation: true,
                    showClass: "fancybox-zoomIn",
                    hideClass: "fancybox-zoomOut",
                    keyboard: {
                        Escape: "close",
                        Delete: "close",
                        Backspace: "close",
                        PageUp: "next",
                        PageDown: "prev",
                        ArrowUp: "next",
                        ArrowDown: "prev",
                        ArrowRight: "next",
                        ArrowLeft: "prev"
                    },
                    on: {
                        init: (fancybox) => {
                            console.log('🎯 Fancybox открыт!');
                            // Останавливаем автопрокрутку слайдера при открытии Fancybox
                            stopAutoPlay();
                            // Разрешаем скролл внутри fancybox, запрещаем фриз страницы после закрытия
                            document.body.classList.add('with-fancybox');
                            document.body.style.overflow = '';
                        },
                        done: (fancybox, slide) => {
                            console.log('📷 Слайд загружен:', slide.src);
                        },
                        destroy: (fancybox) => {
                            console.log('🔒 Fancybox закрыт');
                            // Принудительная очистка
                            forceCleanupFancybox();
                            
                            // Возобновляем автопрокрутку слайдера при закрытии Fancybox
                            setTimeout(() => {
                                startAutoPlay();
                            }, 1000);
                            // Восстанавливаем скролл страницы
                            document.body.style.overflow = '';
                            document.body.classList.remove('compensate-for-scrollbar', 'with-fancybox');
                        }
                    }
                });
                console.log('✅ Fancybox инициализирован');
            } else {
                console.error('❌ Fancybox не загружен');
            }
        });



        // Инициализация при загрузке страницы
        document.addEventListener('DOMContentLoaded', function () {
            console.log('✅ Страница продукта загружена успешно');

            // Проверяем работу Fancybox
            if (typeof Fancybox !== 'undefined') {
                console.log('✅ Fancybox доступен');
            } else {
                console.warn('⚠️ Fancybox не найден');
            }

            // Инициализация Swiper (главная галерея + миниатюры)
            try {
                const thumbsEl = document.querySelector('.ozon-thumbs-swiper');
                let thumbsSwiper = null;
                if (thumbsEl) {
                    thumbsSwiper = new Swiper('.ozon-thumbs-swiper', {
                        direction: window.innerWidth >= 768 ? 'vertical' : 'horizontal',
                        slidesPerView: 'auto',
                        spaceBetween: 8,
                        freeMode: true,
                        watchSlidesProgress: true,
                        mousewheel: true,
                        breakpoints: {
                            0: { direction: 'horizontal' },
                            768: { direction: 'vertical' }
                        }
                    });
                }

                new Swiper('.ozon-main-swiper', {
                    spaceBetween: 10,
                    loop: <?= count($gallery) > 1 ? 'true' : 'false' ?>,
                    autoHeight: true,
                    navigation: {
                        nextEl: '.ozon-main-swiper .swiper-button-next',
                        prevEl: '.ozon-main-swiper .swiper-button-prev',
                    },
                    pagination: {
                        el: '.ozon-main-swiper .swiper-pagination',
                        clickable: true,
                    },
                    keyboard: { enabled: true },
                    zoom: { maxRatio: 2, toggle: false },
                    thumbs: thumbsSwiper ? { swiper: thumbsSwiper } : undefined,
                });
            } catch (e) {
                console.warn('Swiper init failed', e);
            }

            // Обновляем счетчик корзины
            if (typeof updateCartCountFromServer === 'function') {
                updateCartCountFromServer();
            }

            // Добавляем поддержку клавиатурной навигации
            document.addEventListener('keydown', function(e) {
                if (totalSlides > 1) {
                    switch(e.key) {
                        case 'ArrowLeft':
                            e.preventDefault();
                            changeSlide(-1);
                            break;
                        case 'ArrowRight':
                            e.preventDefault();
                            changeSlide(1);
                            break;
                        case 'Home':
                            e.preventDefault();
                            goToSlide(0);
                            break;
                        case 'End':
                            e.preventDefault();
                            goToSlide(totalSlides - 1);
                            break;
                        case 'Escape':
                            // Принудительная очистка при нажатии Escape
                            forceCleanupFancybox();
                            break;
                    }
                }
            });

            // Добавляем поддержку свайпов для мобильных устройств
            let touchStartX = 0;
            let touchEndX = 0;

            const gallerySlider = document.querySelector('.gallery-slider');
            if (gallerySlider) {
                gallerySlider.addEventListener('touchstart', function(e) {
                    touchStartX = e.changedTouches[0].screenX;
                    stopAutoPlay(); // Останавливаем автопрокрутку при касании
                });

                gallerySlider.addEventListener('touchend', function(e) {
                    touchEndX = e.changedTouches[0].screenX;
                    handleSwipe();
                    startAutoPlay(); // Возобновляем автопрокрутку
                });

                // Останавливаем автопрокрутку при наведении мыши
                gallerySlider.addEventListener('mouseenter', stopAutoPlay);
                gallerySlider.addEventListener('mouseleave', startAutoPlay);
            }

            function handleSwipe() {
                const swipeThreshold = 50;
                const diff = touchStartX - touchEndX;

                if (Math.abs(diff) > swipeThreshold) {
                    if (diff > 0) {
                        // Свайп влево - следующий слайд
                        changeSlide(1);
                    } else {
                        // Свайп вправо - предыдущий слайд
                        changeSlide(-1);
                    }
                }
            }

            // Финальная проверка всех компонентов
            console.log('=== ФИНАЛЬНАЯ ПРОВЕРКА ===');
            console.log('Fancybox:', typeof Fancybox !== 'undefined' ? '✅ Загружен' : '❌ Не загружен');
            console.log('Слайдер:', '✅ Инициализирован');
            console.log('Клавиатурная навигация:', '✅ Включена');
            console.log('Свайпы:', '✅ Включены');
            
            // Проверяем наличие кликабельных элементов
            const fancyboxLinks = document.querySelectorAll('[data-fancybox="gallery"]');
            console.log('Элементы с data-fancybox="gallery":', fancyboxLinks.length);
            fancyboxLinks.forEach((link, index) => {
                console.log(`Ссылка ${index + 1}:`, link.href, 'Тип:', link.getAttribute('data-type') || 'image');
            });
            
            // Добавляем обработчик кликов для отладки
            document.addEventListener('click', function(e) {
                if (e.target.closest('[data-fancybox="gallery"]')) {
                    const link = e.target.closest('[data-fancybox="gallery"]');
                    console.log('🎯 Клик по элементу Fancybox:', link.href);
                }
            });
            
            console.log('========================');

            // Запускаем автопрокрутку через 3 секунды после загрузки
            setTimeout(() => {
                startAutoPlay();
            }, 3000);

            // Очистка при закрытии страницы
            window.addEventListener('beforeunload', function() {
                stopAutoPlay();
                const videos = document.querySelectorAll('video');
                videos.forEach(video => {
                    video.pause();
                    video.currentTime = 0;
                });
                unlockPageScroll();
            });

            // Очистка при потере фокуса окна
            window.addEventListener('blur', function() {
                // Останавливаем автопрокрутку при потере фокуса
                stopAutoPlay();
                unlockPageScroll();
            });

            // Возобновление при получении фокуса
            window.addEventListener('focus', function() {
                // Возобновляем автопрокрутку при получении фокуса
                setTimeout(() => {
                    startAutoPlay();
                }, 1000);
                unlockPageScroll();
            });
        });

        // Функция переключения вкладок
        function switchTab(tabName) {
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => content.classList.remove('active'));

            const tabButtons = document.querySelectorAll('.tab-btn');
            tabButtons.forEach(btn => btn.classList.remove('active'));

            const activeTab = document.getElementById(tabName + '-tab');
            if (activeTab) {
                activeTab.classList.add('active');
            }

            const activeButton = document.querySelector(`[onclick="switchTab('${tabName}')"]`);
            if (activeButton) {
                activeButton.classList.add('active');
            }
        }

    </script>
    
    <?php echo generateYandexMetrikaCode(); ?>
    
    <?php 
    // Отслеживание просмотра товара
    if (isset($product)) {
        echo trackYandexMetrikaProductView(
            $product['id'], 
            $product['title'], 
            $product['price'] ?? 0,
            $product['category'] ?? ''
        );
    }
    ?>
</body>
</html>

<?php
// Функция для получения кнопок товара
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
                    Скачать можно только при наличии подписки на <a href="' . getContactSettings()['telegram_channel_url'] . '" target="_blank">Telegram канал</a>
                </div>';

        case 'discussion':
            // 2) Товар для обсуждения - кнопки Telegram и WhatsApp
            return '
                <a href="' . getContactSettings()['telegram_channel_url'] . '" class="product-btn product-btn--primary" target="_blank">
                    📱 Telegram
                </a>
                <a href="https://wa.me/' . e(str_replace('+', '', $product['whatsapp_contact'] ?? '79936202951')) . '?text=Здравствуйте! Интересует ' . urlencode($product['title']) . '" class="product-btn product-btn--secondary" target="_blank">
                    💬 WhatsApp
                </a>
                <div class="contact-note">Обсудить детали</div>';

        case 'service':
            // Услуга: подпись и кнопки мессенджеров (цену показывает общий блок сверху)
            $tg = getContactSettings()['telegram_url'] . '?text=' . urlencode('Здравствуйте! У вас на сайте нашёл услугу "' . ($product['title'] ?? '') . '" и хотел бы уточнить детали.');
            $wa = 'https://wa.me/' . e(str_replace('+', '', $product['whatsapp_contact'] ?? '79936202951')) . '?text=' . urlencode('Здравствуйте! У вас на сайте нашёл услугу "' . ($product['title'] ?? '') . '" и хотел бы уточнить детали.');
            return '
                <div class="service-note">Узнать подробности</div>
                <div class="service-buttons">
                    <a href="' . $wa . '" target="_blank" class="product-btn product-btn--whatsapp">WhatsApp</a>
                    <a href="' . $tg . '" target="_blank" class="product-btn product-btn--telegram">Telegram</a>
                </div>';

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
    $currentCategory = $currentProduct['category_name'] ?? $currentProduct['category'] ?? '';

    foreach ($allProducts as $product) {
        $productCategory = $product['category_name'] ?? $product['category'] ?? '';
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
                <a href="/tovar/' . e($product['slug']) . '">
                    <img src="/' . e($displayImage) . '" alt="' . e($product['title']) . '" />
                </a>
            </div>
            <div class="product-card__content">
                <h3 class="product-card__title"><a href="/tovar/' . e($product['slug']) . '">' . e($product['title']) . '</a></h3>
                <div class="product-card__price-container" style="margin-top:auto; margin-bottom:10px;">
                    ' . (($product['type'] === 'free') ?
            '<span class="product-card__price product-card__price--free">Бесплатно</span>' :
            '<span class="product-card__price">' . number_format($product['price'], 0, ',', ' ') . ' ₽</span>'
        ) . '
                </div>
                <div class="product-card__buttons">
                    <a href="/tovar/' . e($product['slug']) . '" class="product-card__btn product-card__btn--primary">
                        Подробнее
                    </a>
                </div>
            </div>
        </div>';
    }

    return $output;
}
?>