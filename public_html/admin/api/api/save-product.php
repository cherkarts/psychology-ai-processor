<?php
// API для сохранения товара
session_start();
header('Content-Type: application/json');

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

// Обработка загрузки файлов
$image = null;
$gallery = [];

// Отладочная информация
error_log("=== UPLOAD DEBUG ===");
error_log("FILES: " . print_r($_FILES, true));
error_log("POST: " . print_r($_POST, true));

// Обработка основного изображения
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../uploads/products/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
    $uploadPath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
        $image = '/uploads/products/' . $fileName;
    }
}

// Обработка галереи
if (isset($_FILES['gallery'])) {
    $uploadDir = '../uploads/products/gallery/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $galleryFiles = [];

    // Проверяем структуру $_FILES['gallery']
    if (is_array($_FILES['gallery']['name'])) {
        // Множественные файлы (gallery[])
        for ($i = 0; $i < count($_FILES['gallery']['name']); $i++) {
            if ($_FILES['gallery']['error'][$i] === UPLOAD_ERR_OK) {
                $fileName = uniqid() . '_' . basename($_FILES['gallery']['name'][$i]);
                $uploadPath = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['gallery']['tmp_name'][$i], $uploadPath)) {
                    $galleryFiles[] = '/uploads/products/gallery/' . $fileName;
                }
            }
        }
    } else {
        // Один файл
        if ($_FILES['gallery']['error'] === UPLOAD_ERR_OK) {
            $fileName = uniqid() . '_' . basename($_FILES['gallery']['name']);
            $uploadPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['gallery']['tmp_name'], $uploadPath)) {
                $galleryFiles[] = '/uploads/products/gallery/' . $fileName;
            }
        }
    }

    $gallery = $galleryFiles;
}

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

try {
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
        if ($imageCol && $image !== null) {
            $set[] = "$imageCol = ?";
            $params[] = $image;
        }
        if ($has('gallery') && !empty($gallery)) {
            $set[] = 'gallery = ?';
            $params[] = json_encode($gallery);
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
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

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
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);

        $newId = $pdo->lastInsertId();

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
?>