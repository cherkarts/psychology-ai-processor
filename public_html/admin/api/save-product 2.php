<?php
// API для сохранения товара
// Полностью отключаем все выводы
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 0);

// Включаем буферизацию вывода
ob_start();

// Отключаем вывод предупреждений
ini_set('display_startup_errors', 0);

session_start();
header('Content-Type: application/json; charset=utf-8');

// Проверка авторизации
if (!isset($_SESSION['admin_user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Неавторизован'], JSON_UNESCAPED_UNICODE);
    exit();
}

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Метод не разрешен'], JSON_UNESCAPED_UNICODE);
    exit();
}

try {
    // Получение данных (поддержка как JSON, так и FormData)
    $input = [];

    // Проверяем, это FormData или JSON
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (strpos($contentType, 'multipart/form-data') !== false) {
        // FormData
        $input = $_POST;
    } else {
        // JSON
        $input = json_decode(file_get_contents('php://input'), true);
    }

    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'Неверные данные'], JSON_UNESCAPED_UNICODE);
        exit();
    }

    // Отладочная информация
    error_log("=== SAVE PRODUCT DEBUG ===");
    error_log("FILES: " . print_r($_FILES, true));
    error_log("POST: " . print_r($_POST, true));
    error_log("INPUT: " . print_r($input, true));

    $id = isset($input['id']) ? (int) $input['id'] : null;
    $title = trim($input['title'] ?? '');
    $description = trim($input['description'] ?? '');
    $shortDescription = trim($input['short_description'] ?? '');
    $price = isset($input['price']) ? (float) $input['price'] : 0;
    $oldPrice = isset($input['old_price']) && $input['old_price'] !== '' ? (float) $input['old_price'] : null;
    $currency = trim($input['currency'] ?? 'RUB');
    $type = trim($input['type'] ?? 'digital');
    $status = trim($input['status'] ?? 'active');
    $inStock = isset($input['in_stock']) ? (bool) $input['in_stock'] : true;
    $quantity = isset($input['quantity']) ? (int) $input['quantity'] : 0;
    $categoryId = isset($input['category_id']) && $input['category_id'] !== '' ? (int) $input['category_id'] : null;
    $isActive = isset($input['is_active']) ? (bool) $input['is_active'] : true;
    $sortOrder = isset($input['sort_order']) ? (int) $input['sort_order'] : 0;

    // Флаги удаления файлов
    $removeMainImage = isset($input['remove_main_image']) && $input['remove_main_image'] === '1';
    $removeGalleryItems = isset($input['remove_gallery_item']) ? explode(',', $input['remove_gallery_item']) : [];
    $removeDownloadFile = isset($input['remove_download_file']) && $input['remove_download_file'] === '1';

    // Ярлыки товара
    $badges = isset($input['badges']) && is_array($input['badges']) ? array_map('intval', $input['badges']) : [];
    error_log("Badges received: " . print_r($badges, true));

    // Обработка загрузки файлов
    $image = null;
    $gallery = [];
    $downloadFilePath = null;

    // Обработка основного изображения
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../uploads/products/';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                throw new Exception('Не удалось создать папку для загрузки изображений');
            }
        }

        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $safeBase = preg_replace('~[^a-z0-9\-_.]+~i', '-', pathinfo($_FILES['image']['name'], PATHINFO_FILENAME));
        $fileName = 'product_' . $safeBase . '_' . date('YmdHis') . '_' . substr(sha1(random_bytes(8)), 0, 8) . '.' . $ext;
        $uploadPath = rtrim($uploadDir, '/') . '/' . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
            $image = '/uploads/products/' . $fileName;
            error_log("Main image uploaded: " . $image);
        } else {
            error_log("Failed to upload main image");
        }
    }

    // Обработка галереи
    if (isset($_FILES['gallery'])) {
        $uploadDir = __DIR__ . '/../../uploads/products/gallery/';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                throw new Exception('Не удалось создать папку для галереи');
            }
        }

        $galleryFiles = [];

        // Проверяем структуру $_FILES['gallery']
        if (is_array($_FILES['gallery']['name'])) {
            // Множественные файлы (gallery[])
            for ($i = 0; $i < count($_FILES['gallery']['name']); $i++) {
                if ($_FILES['gallery']['error'][$i] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($_FILES['gallery']['name'][$i], PATHINFO_EXTENSION);
                    $safeBase = preg_replace('~[^a-z0-9\-_.]+~i', '-', pathinfo($_FILES['gallery']['name'][$i], PATHINFO_FILENAME));
                    $fileName = 'gallery_' . $safeBase . '_' . date('YmdHis') . '_' . substr(sha1(random_bytes(8)), 0, 8) . '.' . $ext;
                    $uploadPath = rtrim($uploadDir, '/') . '/' . $fileName;

                    if (move_uploaded_file($_FILES['gallery']['tmp_name'][$i], $uploadPath)) {
                        $galleryFiles[] = '/uploads/products/gallery/' . $fileName;
                    }
                }
            }
        } else {
            // Один файл
            if ($_FILES['gallery']['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['gallery']['name'], PATHINFO_EXTENSION);
                $safeBase = preg_replace('~[^a-z0-9\-_.]+~i', '-', pathinfo($_FILES['gallery']['name'], PATHINFO_FILENAME));
                $fileName = 'gallery_' . $safeBase . '_' . date('YmdHis') . '_' . substr(sha1(random_bytes(8)), 0, 8) . '.' . $ext;
                $uploadPath = rtrim($uploadDir, '/') . '/' . $fileName;

                if (move_uploaded_file($_FILES['gallery']['tmp_name'], $uploadPath)) {
                    $galleryFiles[] = '/uploads/products/gallery/' . $fileName;
                }
            }
        }

        $gallery = $galleryFiles;
    }

    // Обработка файла для скачивания
    if (isset($_FILES['download_file']) && $_FILES['download_file']['error'] === UPLOAD_ERR_OK) {
        $filesDir = __DIR__ . '/../../uploads/products/files/';
        if (!is_dir($filesDir)) {
            if (!mkdir($filesDir, 0755, true)) {
                throw new Exception('Не удалось создать папку для файлов');
            }
        }
        $ext = pathinfo($_FILES['download_file']['name'], PATHINFO_EXTENSION);
        $safeBase = preg_replace('~[^a-z0-9\-_.]+~i', '-', pathinfo($_FILES['download_file']['name'], PATHINFO_FILENAME));
        $filename = 'file_' . $safeBase . '_' . date('YmdHis') . '_' . substr(sha1(random_bytes(8)), 0, 8) . '.' . $ext;
        $dest = rtrim($filesDir, '/') . '/' . $filename;
        if (move_uploaded_file($_FILES['download_file']['tmp_name'], $dest)) {
            $downloadFilePath = '/uploads/products/files/' . $filename;
            error_log("Download file uploaded: " . $downloadFilePath);
        } else {
            error_log("Failed to upload download file");
        }
    }

    // Отладочная информация о файлах
    error_log("=== FILE PROCESSING RESULT ===");
    error_log("Main image: " . ($image ?: 'null'));
    error_log("Gallery: " . print_r($gallery, true));
    error_log("Download file: " . ($downloadFilePath ?: 'null'));

    // Генерация slug из названия
    function generateSlug($title)
    {
        // Транслитерация кириллицы в латиницу
        $translit = [
            'а' => 'a',
            'б' => 'b',
            'в' => 'v',
            'г' => 'g',
            'д' => 'd',
            'е' => 'e',
            'ё' => 'yo',
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
            'ц' => 'ts',
            'ч' => 'ch',
            'ш' => 'sh',
            'щ' => 'sch',
            'ъ' => '',
            'ы' => 'y',
            'ь' => '',
            'э' => 'e',
            'ю' => 'yu',
            'я' => 'ya',
            'А' => 'A',
            'Б' => 'B',
            'В' => 'V',
            'Г' => 'G',
            'Д' => 'D',
            'Е' => 'E',
            'Ё' => 'Yo',
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
            'Ц' => 'Ts',
            'Ч' => 'Ch',
            'Ш' => 'Sh',
            'Щ' => 'Sch',
            'Ъ' => '',
            'Ы' => 'Y',
            'Ь' => '',
            'Э' => 'E',
            'Ю' => 'Yu',
            'Я' => 'Ya'
        ];

        $slug = strtr($title, $translit);
        $slug = strtolower($slug);
        $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');

        return $slug ?: 'product-' . time();
    }

    $slug = generateSlug($title);

    // Проверка уникальности slug
    function generateUniqueSlug($pdo, $slug, $excludeId = null)
    {
        $originalSlug = $slug;
        $counter = 1;

        while (true) {
            $sql = "SELECT COUNT(*) FROM products WHERE slug = ?";
            $params = [$slug];

            if ($excludeId) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $count = $stmt->fetchColumn();

            if ($count == 0) {
                return $slug;
            }

            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
    }

    // Валидация
    if (empty($title)) {
        echo json_encode(['success' => false, 'message' => 'Название обязательно'], JSON_UNESCAPED_UNICODE);
        exit();
    }

    if ($price < 0) {
        echo json_encode(['success' => false, 'message' => 'Цена не может быть отрицательной'], JSON_UNESCAPED_UNICODE);
        exit();
    }

    // Подключение к БД
    $config = require '../../config.php';

    $pdo = new PDO(
        "mysql:host=" . $config['database']['host'] . ";dbname=" . $config['database']['dbname'],
        $config['database']['username'],
        $config['database']['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    // Определяем доступные колонки
    $columns = $pdo->query("SHOW COLUMNS FROM products")->fetchAll(PDO::FETCH_COLUMN);
    $has = function ($col) use ($columns) {
        return in_array($col, $columns, true);
    };
    $imageCol = $has('image') ? 'image' : ($has('featured_image') ? 'featured_image' : null);
    $statusCol = $has('is_active') ? 'is_active' : ($has('is_published') ? 'is_published' : ($has('active') ? 'active' : null));

    // Проверяем наличие колонки download_url, если нет - создаем
    if (!$has('download_url')) {
        try {
            $pdo->exec("ALTER TABLE products ADD COLUMN download_url VARCHAR(500) NULL");
        } catch (Exception $e) {
            // Игнорируем ошибку если колонка уже существует
        }
    }

    // Проверяем наличие колонки gallery, если нет - создаем
    if (!$has('gallery')) {
        try {
            $pdo->exec("ALTER TABLE products ADD COLUMN gallery TEXT NULL");
        } catch (Exception $e) {
            // Игнорируем ошибку если колонка уже существует
        }
    }

    // Генерируем уникальный slug
    if ($has('slug')) {
        $slug = generateUniqueSlug($pdo, $slug, $id);
    }

    if ($id) {
        // Обновление существующего товара (динамически)
        $set = [];
        $params = [];
        if ($has('title')) {
            $set[] = 'title = ?';
            $params[] = $title;
        }
        if ($has('slug')) {
            $set[] = 'slug = ?';
            $params[] = $slug;
        }
        if ($has('description')) {
            $set[] = 'description = ?';
            $params[] = $description;
        }
        if ($has('short_description')) {
            $set[] = 'short_description = ?';
            $params[] = $shortDescription;
        }
        if ($has('price')) {
            $set[] = 'price = ?';
            $params[] = $price;
        }
        if ($has('old_price')) {
            $set[] = 'old_price = ?';
            $params[] = $oldPrice;
        }
        if ($has('currency')) {
            $set[] = 'currency = ?';
            $params[] = $currency;
        }
        if ($has('type')) {
            $set[] = 'type = ?';
            $params[] = $type;
        }
        if ($has('status')) {
            $set[] = 'status = ?';
            $params[] = $status;
        }
        if ($has('in_stock')) {
            $set[] = 'in_stock = ?';
            $params[] = $inStock ? 1 : 0;
        }
        if ($has('quantity')) {
            $set[] = 'quantity = ?';
            $params[] = $quantity;
        }
        if ($has('category_id')) {
            $set[] = 'category_id = ?';
            $params[] = $categoryId;
        }
        if ($imageCol) {
            if ($removeMainImage) {
                $set[] = "$imageCol = NULL";
            } elseif ($image !== null) {
                $set[] = "$imageCol = ?";
                $params[] = $image;
            }
        }
        if ($has('gallery')) {
            if (!empty($removeGalleryItems) || !empty($gallery)) {
                // Получаем текущую галерею
                $currentGallery = [];
                if ($id) {
                    $stmt = $pdo->prepare("SELECT gallery FROM products WHERE id = ?");
                    $stmt->execute([$id]);
                    $currentGalleryData = $stmt->fetchColumn();
                    if ($currentGalleryData) {
                        $currentGallery = json_decode($currentGalleryData, true) ?: [];
                    }
                }

                // Удаляем элементы по индексам
                foreach ($removeGalleryItems as $index) {
                    if (isset($currentGallery[$index])) {
                        unset($currentGallery[$index]);
                    }
                }

                // Добавляем новые файлы
                $currentGallery = array_merge($currentGallery, $gallery);

                $set[] = 'gallery = ?';
                $params[] = json_encode(array_values($currentGallery));
            }
        }
        if ($has('download_url')) {
            if ($removeDownloadFile) {
                $set[] = 'download_url = NULL';
            } elseif ($downloadFilePath !== null) {
                $set[] = 'download_url = ?';
                $params[] = $downloadFilePath;
            }
        }
        if ($statusCol !== null) {
            $set[] = "$statusCol = ?";
            $params[] = $isActive ? 1 : 0;
        }
        if ($has('sort_order')) {
            $set[] = 'sort_order = ?';
            $params[] = $sortOrder;
        }
        if ($has('updated_at')) {
            $set[] = 'updated_at = CURRENT_TIMESTAMP';
        }
        if (empty($set)) {
            throw new Exception('Нет полей для обновления');
        }
        $sql = 'UPDATE products SET ' . implode(', ', $set) . ' WHERE id = ?';
        $params[] = $id;
        error_log("Update SQL: " . $sql);
        error_log("Update params: " . print_r($params, true));
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // Обновляем ярлыки товара
        updateProductBadges($pdo, $id, $badges);

        error_log("Product updated successfully. ID: " . $id);
        echo json_encode([
            'success' => true,
            'message' => 'Товар обновлен',
            'id' => $id
        ], JSON_UNESCAPED_UNICODE);
    } else {
        // Создание нового товара (динамически)
        $fields = [];
        $placeholders = [];
        $values = [];
        if ($has('title')) {
            $fields[] = 'title';
            $placeholders[] = '?';
            $values[] = $title;
        }
        if ($has('slug')) {
            $fields[] = 'slug';
            $placeholders[] = '?';
            $values[] = $slug;
        }
        if ($has('description')) {
            $fields[] = 'description';
            $placeholders[] = '?';
            $values[] = $description;
        }
        if ($has('short_description')) {
            $fields[] = 'short_description';
            $placeholders[] = '?';
            $values[] = $shortDescription;
        }
        if ($has('price')) {
            $fields[] = 'price';
            $placeholders[] = '?';
            $values[] = $price;
        }
        if ($has('old_price')) {
            $fields[] = 'old_price';
            $placeholders[] = '?';
            $values[] = $oldPrice;
        }
        if ($has('currency')) {
            $fields[] = 'currency';
            $placeholders[] = '?';
            $values[] = $currency;
        }
        if ($has('type')) {
            $fields[] = 'type';
            $placeholders[] = '?';
            $values[] = $type;
        }
        if ($has('status')) {
            $fields[] = 'status';
            $placeholders[] = '?';
            $values[] = $status;
        }
        if ($has('in_stock')) {
            $fields[] = 'in_stock';
            $placeholders[] = '?';
            $values[] = $inStock ? 1 : 0;
        }
        if ($has('quantity')) {
            $fields[] = 'quantity';
            $placeholders[] = '?';
            $values[] = $quantity;
        }
        if ($has('category_id')) {
            $fields[] = 'category_id';
            $placeholders[] = '?';
            $values[] = $categoryId;
        }
        if ($imageCol && $image !== null) {
            $fields[] = $imageCol;
            $placeholders[] = '?';
            $values[] = $image;
        }
        if ($has('gallery') && !empty($gallery)) {
            $fields[] = 'gallery';
            $placeholders[] = '?';
            $values[] = json_encode($gallery);
        }
        if ($has('download_url') && $downloadFilePath !== null) {
            $fields[] = 'download_url';
            $placeholders[] = '?';
            $values[] = $downloadFilePath;
        }
        if ($statusCol !== null) {
            $fields[] = $statusCol;
            $placeholders[] = '?';
            $values[] = $isActive ? 1 : 0;
        }
        if ($has('sort_order')) {
            $fields[] = 'sort_order';
            $placeholders[] = '?';
            $values[] = $sortOrder;
        }
        $sql = 'INSERT INTO products (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $placeholders) . ')';
        error_log("Insert SQL: " . $sql);
        error_log("Insert values: " . print_r($values, true));
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);

        $newId = $pdo->lastInsertId();

        // Добавляем ярлыки для нового товара
        updateProductBadges($pdo, $newId, $badges);

        error_log("Product created successfully. ID: " . $newId);
        echo json_encode([
            'success' => true,
            'message' => 'Товар создан',
            'id' => $newId
        ], JSON_UNESCAPED_UNICODE);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Ошибка: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

// Функция обновления ярлыков товара
function updateProductBadges($pdo, $productId, $badgeIds)
{
    try {
        // Удаляем все существующие ярлыки товара
        $stmt = $pdo->prepare("DELETE FROM product_badge_relations WHERE product_id = ?");
        $stmt->execute([$productId]);

        // Добавляем новые ярлыки
        if (!empty($badgeIds)) {
            $stmt = $pdo->prepare("INSERT INTO product_badge_relations (product_id, badge_id) VALUES (?, ?)");
            foreach ($badgeIds as $badgeId) {
                $stmt->execute([$productId, $badgeId]);
            }
        }

        error_log("Product badges updated for product ID: " . $productId . ", badges: " . implode(',', $badgeIds));
    } catch (Exception $e) {
        error_log("Error updating product badges: " . $e->getMessage());
        // Не прерываем выполнение, так как это не критическая ошибка
    }
}

// Очищаем буфер вывода перед отправкой JSON
ob_clean();
?>