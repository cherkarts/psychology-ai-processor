<?php
// Подключаем конфигурацию админки
if (file_exists(__DIR__ . '/../admin/includes/config.php')) {
    require_once __DIR__ . '/../admin/includes/config.php';
}

// Функция для подключения к базе данных админки
if (!function_exists('getAdminDB')) {
    function getAdminDB()
    {
        try {
            $config = require_once __DIR__ . '/config.php';
            $dbConfig = $config['database'];

            $pdo = new PDO(
                "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}",
                $dbConfig['username'],
                $dbConfig['password'],
                $dbConfig['options']
            );
            return $pdo;
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            return null;
        }
    }
}

// Функции для работы со статьями

if (!function_exists('getArticleById')) {
    function getArticleById($slug)
    {
        global $db;
        if (!$db) {
            $db = getAdminDB();
        }

        if (!$db) {
            return false;
        }

        // В админке показываем ВСЕ статьи, включая черновики
        $stmt = $db->prepare("SELECT * FROM articles WHERE slug = ?");
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

if (!function_exists('saveArticle')) {
    function saveArticle($data, $files = [])
    {
        global $db;
        if (!$db) {
            $db = getAdminDB();
        }

        if (!$db) {
            return ['success' => false, 'message' => 'Ошибка подключения к базе данных'];
        }

        try {
            $title = trim($data['title'] ?? '');
            $content = $data['content'] ?? '';
            $excerpt = $data['excerpt'] ?? '';
            // support both 'category' and 'category_id'
            $categoryId = $data['category_id'] ?? ($data['category'] ?? null);
            $categoryId = ($categoryId === '' ? null : $categoryId);
            $author = $data['author'] ?? 'Администратор';
            // status or checkbox
            $status = $data['status'] ?? null;
            $isPublished = isset($data['is_published']) ? (int) (bool) $data['is_published'] : ($status === 'published' ? 1 : 0);
            // published_at from provided date if any
            $publishedAt = null;
            if ($isPublished) {
                if (!empty($data['date'])) {
                    $ts = strtotime($data['date']);
                    $publishedAt = $ts ? date('Y-m-d H:i:s', $ts) : date('Y-m-d H:i:s');
                } else {
                    $publishedAt = date('Y-m-d H:i:s');
                }
            }

            // Meta fields and tags
            $metaTitle = $data['meta_title'] ?? null;
            $metaDescription = $data['meta_description'] ?? null;
            // tags and meta_keywords can both feed into tags JSON
            $tagsInput = $data['tags'] ?? '';
            $metaKeywordsInput = $data['meta_keywords'] ?? '';
            $tagItems = [];
            if (is_string($tagsInput) && trim($tagsInput) !== '') {
                $tagItems = array_map('trim', explode(',', $tagsInput));
            }
            if (is_string($metaKeywordsInput) && trim($metaKeywordsInput) !== '') {
                $kw = array_map('trim', explode(',', $metaKeywordsInput));
                $tagItems = array_merge($tagItems, $kw);
            }
            $tagItems = array_values(array_unique(array_filter($tagItems, fn($v) => $v !== '')));
            $tags = !empty($tagItems) ? json_encode($tagItems, JSON_UNESCAPED_UNICODE) : null;

            if (empty($title)) {
                return ['success' => false, 'message' => 'Название статьи обязательно'];
            }

            // Accept provided slug or generate
            $slug = trim($data['slug'] ?? '');
            if ($slug === '') {
                $slug = createSlug($title);
            }

            // Проверяем уникальность slug
            $stmt = $db->prepare("SELECT id FROM articles WHERE slug = ? AND id != ?");
            $stmt->execute([$slug, $data['article_id'] ?? 0]);

            if ($stmt->rowCount() > 0) {
                $slug = $slug . '-' . time();
            }

            // Handle featured image upload (optional)
            $featuredImage = null;
            if (isset($files['featured_image']) && is_array($files['featured_image']) && $files['featured_image']['error'] === UPLOAD_ERR_OK) {
                $file = $files['featured_image'];
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $maxSize = 5 * 1024 * 1024;
                if (in_array($file['type'], $allowedTypes) && $file['size'] <= $maxSize) {
                    $uploadDir = __DIR__ . '/../uploads/articles';
                    if (!is_dir($uploadDir)) {
                        @mkdir($uploadDir, 0755, true);
                    }
                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $name = 'article_' . uniqid() . '.' . $ext;
                    $dest = $uploadDir . '/' . $name;
                    if (@move_uploaded_file($file['tmp_name'], $dest)) {
                        $featuredImage = '/uploads/articles/' . $name;
                    }
                }
            }

            if (!empty($data['article_id'])) {
                // Обновление существующей статьи
                // preserve existing featured_image if not re-uploaded
                if ($featuredImage === null) {
                    $imgStmt = $db->prepare("SELECT featured_image FROM articles WHERE id = ? OR slug = ?");
                    $imgStmt->execute([$data['article_id'], $data['article_id']]);
                    $row = $imgStmt->fetch(PDO::FETCH_ASSOC);
                    if ($row) {
                        $featuredImage = $row['featured_image'] ?? null;
                    }
                }

                $stmt = $db->prepare("
                    UPDATE articles 
                    SET title = ?, slug = ?, content = ?, excerpt = ?, category_id = ?, author = ?, is_published = ?, published_at = ?, meta_title = ?, meta_description = ?, featured_image = ?, tags = ?, updated_at = NOW()
                    WHERE id = ? OR slug = ?
                ");
                $stmt->execute([$title, $slug, $content, $excerpt, $categoryId, $author, $isPublished, $publishedAt, $metaTitle, $metaDescription, $featuredImage, $tags, $data['article_id'], $data['article_id']]);

                return ['success' => true, 'message' => 'Статья успешно обновлена', 'slug' => $slug];
            } else {
                // Создание новой статьи
                $stmt = $db->prepare("
                    INSERT INTO articles (title, slug, content, excerpt, category_id, author, is_published, published_at, meta_title, meta_description, featured_image, tags, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ");
                $stmt->execute([$title, $slug, $content, $excerpt, $categoryId, $author, $isPublished, $publishedAt, $metaTitle, $metaDescription, $featuredImage, $tags]);

                return ['success' => true, 'message' => 'Статья успешно создана', 'slug' => $slug];
            }

        } catch (Exception $e) {
            error_log("Error saving article: " . $e->getMessage());
            return ['success' => false, 'message' => 'Ошибка при сохранении статьи: ' . $e->getMessage()];
        }
    }
}

if (!function_exists('getArticleCategories')) {
    function getArticleCategories()
    {
        // Сначала пробуем получить из БД реальные категории (id => name)
        try {
            $db = getAdminDB();
            if ($db) {
                $stmt = $db->query("SELECT id, name FROM article_categories WHERE is_active = 1 ORDER BY sort_order, name");
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($rows) {
                    $categories = [];
                    foreach ($rows as $row) {
                        $categories[$row['id']] = $row['name'];
                    }
                    return $categories;
                }
            }
        } catch (Exception $e) {
            // ignore and fallback
        }

        // Фолбэк: статический список (значения будут не id)
        return [
            0 => 'Психология',
            -1 => 'Самопомощь',
            -2 => 'Медитация',
            -3 => 'Здоровье',
            -4 => 'Развитие'
        ];
    }
}

// Функции для работы с товарами

if (!function_exists('getProductById')) {
    function getProductById($id)
    {
        global $db;
        if (!$db) {
            $db = getAdminDB();
        }

        if (!$db) {
            return false;
        }

        $stmt = $db->prepare("SELECT * FROM products WHERE id = ? OR slug = ?");
        $stmt->execute([$id, $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

if (!function_exists('saveProduct')) {
    function saveProduct($data, $files = [])
    {
        global $db;
        if (!$db) {
            $db = getAdminDB();
        }

        if (!$db) {
            return ['success' => false, 'message' => 'Ошибка подключения к базе данных'];
        }

        try {
            $title = trim($data['title'] ?? '');
            $description = $data['description'] ?? '';
            $shortDescription = $data['short_description'] ?? '';
            $content = $data['content'] ?? '';
            // Normalize price: support comma as decimal separator
            $priceRaw = isset($data['price']) ? (string) $data['price'] : '0';
            $price = floatval(str_replace(',', '.', $priceRaw));
            $type = $data['type'] ?? 'digital';
            $categoryId = isset($data['category']) && $data['category'] !== '' ? intval($data['category']) : null;
            // status / stock
            $status = $data['status'] ?? 'draft';
            $inStock = isset($data['in_stock']) ? (int) (bool) $data['in_stock'] : 1;
            $quantity = isset($data['quantity']) ? intval($data['quantity']) : 0;

            // Features (name/value pairs) → JSON
            $featuresJson = null;
            if (!empty($data['features_name']) && is_array($data['features_name'])) {
                $names = $data['features_name'];
                $values = $data['features_value'] ?? [];
                $pairs = [];
                $len = max(count($names), count($values));
                for ($i = 0; $i < $len; $i++) {
                    $n = isset($names[$i]) ? trim((string) $names[$i]) : '';
                    $v = isset($values[$i]) ? trim((string) $values[$i]) : '';
                    if ($n === '' && $v === '')
                        continue;
                    $pairs[] = ['name' => $n, 'value' => $v];
                }
                if (!empty($pairs)) {
                    $featuresJson = json_encode($pairs, JSON_UNESCAPED_UNICODE);
                }
            } elseif (!empty($data['features']) && is_array($data['features'])) {
                // Backward compatibility: list of strings
                $features = array_values(array_filter(array_map('trim', $data['features']), fn($v) => $v !== ''));
                if (!empty($features)) {
                    $pairs = [];
                    foreach ($features as $f) {
                        if (strpos($f, ':') !== false) {
                            [$n, $v] = array_map('trim', explode(':', $f, 2));
                            $pairs[] = ['name' => $n, 'value' => $v];
                        } else {
                            $pairs[] = ['name' => $f, 'value' => ''];
                        }
                    }
                    $featuresJson = json_encode($pairs, JSON_UNESCAPED_UNICODE);
                }
            }

            // Tags (comma separated) → JSON
            $tagsJson = null;
            if (!empty($data['tags'])) {
                $tags = array_values(array_filter(array_map('trim', explode(',', $data['tags'])), fn($v) => $v !== ''));
                if (!empty($tags)) {
                    $tagsJson = json_encode($tags, JSON_UNESCAPED_UNICODE);
                }
            }

            if (empty($title)) {
                return ['success' => false, 'message' => 'Название товара обязательно'];
            }

            // Генерируем slug из названия или из поля
            $slugInput = trim($data['slug'] ?? '');
            $slug = $slugInput !== '' ? $slugInput : createSlug($title);

            // Проверяем уникальность slug
            $stmt = $db->prepare("SELECT id FROM products WHERE slug = ? AND id != ?");
            $stmt->execute([$slug, $data['product_id'] ?? 0]);

            if ($stmt->rowCount() > 0) {
                $slug = $slug . '-' . time();
            }

            // Upload helpers
            $uploadProductsDir = __DIR__ . '/../uploads/products';
            if (!is_dir($uploadProductsDir)) {
                @mkdir($uploadProductsDir, 0755, true);
            }
            $imagePath = null;
            if (isset($files['image']) && is_array($files['image']) && $files['image']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (in_array($files['image']['type'], $allowed)) {
                    $ext = pathinfo($files['image']['name'], PATHINFO_EXTENSION);
                    $name = 'product_' . uniqid() . '.' . $ext;
                    if (@move_uploaded_file($files['image']['tmp_name'], $uploadProductsDir . '/' . $name)) {
                        $imagePath = '/uploads/products/' . $name;
                    }
                }
            }

            $downloadPath = null;
            if (isset($files['download_file']) && is_array($files['download_file']) && $files['download_file']['error'] === UPLOAD_ERR_OK) {
                $filesDir = __DIR__ . '/../uploads/products/files';
                if (!is_dir($filesDir)) {
                    @mkdir($filesDir, 0755, true);
                }
                $ext = pathinfo($files['download_file']['name'], PATHINFO_EXTENSION);
                $name = 'file_' . uniqid() . '.' . $ext;
                if (@move_uploaded_file($files['download_file']['tmp_name'], $filesDir . '/' . $name)) {
                    $downloadPath = '/uploads/products/files/' . $name;
                }
            }

            // Gallery upload (images/videos)
            $galleryJson = null;
            $newGallery = [];
            if (isset($files['gallery']) && isset($files['gallery']['name']) && is_array($files['gallery']['name'])) {
                $galleryDir = __DIR__ . '/../uploads/products/gallery';
                if (!is_dir($galleryDir)) {
                    @mkdir($galleryDir, 0755, true);
                }
                $count = count($files['gallery']['name']);
                for ($i = 0; $i < $count; $i++) {
                    if (($files['gallery']['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK)
                        continue;
                    $mime = $files['gallery']['type'][$i] ?? '';
                    $ext = pathinfo($files['gallery']['name'][$i], PATHINFO_EXTENSION);
                    $name = 'gallery_' . uniqid() . '.' . $ext;
                    if (@move_uploaded_file($files['gallery']['tmp_name'][$i], $galleryDir . '/' . $name)) {
                        $newGallery[] = [
                            'path' => '/uploads/products/gallery/' . $name,
                            'type' => strpos($mime, 'video/') === 0 ? 'video' : 'image'
                        ];
                    }
                }
            }

            // Existing gallery handling (removals)
            $existingGallery = [];
            if (!empty($data['existing_gallery'])) {
                $existingGallery = json_decode($data['existing_gallery'], true) ?: [];
            }
            if (!empty($data['remove_gallery']) && is_array($data['remove_gallery'])) {
                $idxs = array_map('intval', $data['remove_gallery']);
                rsort($idxs);
                foreach ($idxs as $idx) {
                    if (isset($existingGallery[$idx])) {
                        unset($existingGallery[$idx]);
                    }
                }
                $existingGallery = array_values($existingGallery);
            }
            if (!empty($newGallery)) {
                $existingGallery = array_merge($existingGallery, $newGallery);
            }
            if ($existingGallery !== []) {
                $galleryJson = json_encode($existingGallery, JSON_UNESCAPED_UNICODE);
            } elseif (isset($data['existing_gallery'])) {
                $galleryJson = json_encode([]); // явное очищение
            }

            if (isset($data['product_id']) && !empty($data['product_id'])) {
                // Обновление существующего товара
                $set = [
                    'title = ?',
                    'slug = ?',
                    'short_description = ?',
                    'description = ?',
                    'type = ?',
                    'status = ?',
                    'category_id = ?',
                    'price = ?',
                    'in_stock = ?',
                    'quantity = ?',
                    'updated_at = NOW()'
                ];
                $params = [
                    $title,
                    $slug,
                    $shortDescription,
                    $description,
                    $type,
                    $status,
                    $categoryId,
                    $price,
                    $inStock,
                    $quantity
                ];

                if (isset($data['remove_image']) && $data['remove_image'] == '1') {
                    $set[] = 'image = NULL';
                } elseif ($imagePath !== null) {
                    $set[] = 'image = ?';
                    $params[] = $imagePath;
                }

                if (isset($data['remove_file']) && $data['remove_file'] == '1') {
                    $set[] = 'download_file = NULL';
                } elseif ($downloadPath !== null) {
                    $set[] = 'download_file = ?';
                    $params[] = $downloadPath;
                }

                if ($galleryJson !== null) {
                    $set[] = 'gallery = ?';
                    $params[] = $galleryJson;
                }
                if ($featuresJson !== null) {
                    $set[] = 'features = ?';
                    $params[] = $featuresJson;
                }
                if ($tagsJson !== null) {
                    $set[] = 'tags = ?';
                    $params[] = $tagsJson;
                }

                $sql = 'UPDATE products SET ' . implode(', ', $set) . ' WHERE id = ? OR slug = ?';
                $params[] = $data['product_id'];
                $params[] = $data['product_id'];

                $stmt = $db->prepare($sql);
                $stmt->execute($params);

                return ['success' => true, 'message' => 'Товар успешно обновлен', 'slug' => $slug];
            } else {
                // Создание нового товара
                $stmt = $db->prepare("
                    INSERT INTO products (title, slug, short_description, description, type, status, category_id, price, in_stock, quantity, image, download_file, gallery, features, tags, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ");
                $stmt->execute([
                    $title,
                    $slug,
                    $shortDescription,
                    $description,
                    $type,
                    $status,
                    $categoryId,
                    $price,
                    $inStock,
                    $quantity,
                    $imagePath,
                    $downloadPath,
                    $galleryJson,
                    $featuresJson,
                    $tagsJson
                ]);

                return ['success' => true, 'message' => 'Товар успешно создан', 'slug' => $slug];
            }

        } catch (Exception $e) {
            error_log("Error saving product: " . $e->getMessage());
            return ['success' => false, 'message' => 'Ошибка при сохранении товара: ' . $e->getMessage()];
        }
    }
}

if (!function_exists('getProductCategories')) {
    function getProductCategories()
    {
        global $db;
        if (!$db) {
            $db = getAdminDB();
        }

        if (!$db) {
            return [];
        }

        try {
            $stmt = $db->query("SELECT * FROM product_categories WHERE is_active = 1 ORDER BY sort_order, name");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            // Если таблицы нет, возвращаем пустой массив
            return [];
        }
    }
}

// Функции для работы с отзывами

if (!function_exists('getReviews')) {
    function getReviews($page = 1, $status = 'all', $search = '')
    {
        global $db;
        if (!$db) {
            $db = getAdminDB();
        }

        if (!$db) {
            return ['items' => [], 'stats' => [], 'pagination' => []];
        }

        try {
            $itemsPerPage = 20;
            $offset = ($page - 1) * $itemsPerPage;

            $conditions = [];
            $params = [];

            if ($status !== 'all') {
                $conditions[] = "status = ?";
                $params[] = $status;
            }

            if (!empty($search)) {
                $conditions[] = "(name LIKE ? OR content LIKE ? OR email LIKE ?)";
                $searchTerm = "%{$search}%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            $whereClause = empty($conditions) ? '' : 'WHERE ' . implode(' AND ', $conditions);

            // Get total count
            $countSql = "SELECT COUNT(*) as total FROM reviews " . $whereClause;
            $stmt = $db->prepare($countSql);
            $stmt->execute($params);
            $totalItems = $stmt->fetch()['total'];

            // Get reviews
            $sql = "SELECT * FROM reviews " . $whereClause . " ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $params[] = $itemsPerPage;
            $params[] = $offset;
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $reviews = $stmt->fetchAll();

            // Get stats
            $statsQueries = [
                'total' => "SELECT COUNT(*) as count FROM reviews",
                'pending' => "SELECT COUNT(*) as count FROM reviews WHERE status = 'pending'",
                'approved' => "SELECT COUNT(*) as count FROM reviews WHERE status = 'approved'",
                'rejected' => "SELECT COUNT(*) as count FROM reviews WHERE status = 'rejected'"
            ];

            $stats = [];
            foreach ($statsQueries as $key => $query) {
                $stmt = $db->query($query);
                $stats[$key] = $stmt->fetch()['count'];
            }

            return [
                'items' => $reviews,
                'stats' => $stats,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => ceil($totalItems / $itemsPerPage),
                    'total_items' => $totalItems,
                    'items_per_page' => $itemsPerPage
                ]
            ];

        } catch (Exception $e) {
            error_log("Error getting reviews: " . $e->getMessage());
            return ['items' => [], 'stats' => [], 'pagination' => []];
        }
    }
}

if (!function_exists('handleReviewAction')) {
    function handleReviewAction($data)
    {
        // Отладочная информация
        $debugFile = __DIR__ . '/../debug-reviews.log';
        $debugMessage = "[" . date('Y-m-d H:i:s') . "] handleReviewAction called with data: " . json_encode($data) . PHP_EOL;
        file_put_contents($debugFile, $debugMessage, FILE_APPEND | LOCK_EX);

        error_log("handleReviewAction called with data: " . json_encode($data));

        $action = $data['action'] ?? '';
        $reviewId = $data['review_id'] ?? '';

        $debugMessage = "[" . date('Y-m-d H:i:s') . "] Action: '$action', Review ID: '$reviewId'" . PHP_EOL;
        file_put_contents($debugFile, $debugMessage, FILE_APPEND | LOCK_EX);

        error_log("Action: '$action', Review ID: '$reviewId'");

        if (empty($reviewId)) {
            $debugMessage = "[" . date('Y-m-d H:i:s') . "] Review ID is empty, returning error" . PHP_EOL;
            file_put_contents($debugFile, $debugMessage, FILE_APPEND | LOCK_EX);
            return ['success' => false, 'message' => 'Review ID is required.'];
        }

        global $db;
        if (!$db) {
            $db = getAdminDB();
        }

        $debugMessage = "[" . date('Y-m-d H:i:s') . "] Database connection: " . ($db ? 'SUCCESS' : 'FAILED') . PHP_EOL;
        file_put_contents($debugFile, $debugMessage, FILE_APPEND | LOCK_EX);

        if ($db) {
            try {
                $debugMessage = "[" . date('Y-m-d H:i:s') . "] Processing action: '$action'" . PHP_EOL;
                file_put_contents($debugFile, $debugMessage, FILE_APPEND | LOCK_EX);
                error_log("Processing action: '$action'");
                switch ($action) {
                    case 'update_status':
                        $status = $data['status'] ?? '';
                        if (!in_array($status, ['pending', 'approved', 'rejected'])) {
                            return ['success' => false, 'message' => 'Invalid status.'];
                        }

                        $stmt = $db->prepare("UPDATE reviews SET status = ?, updated_at = NOW() WHERE id = ?");
                        $stmt->execute([$status, $reviewId]);

                        return ['success' => true, 'message' => "Review {$status} successfully."];

                    case 'delete':
                        $stmt = $db->prepare("DELETE FROM reviews WHERE id = ?");
                        $stmt->execute([$reviewId]);

                        return ['success' => true, 'message' => 'Review deleted successfully.'];

                    case 'approve':
                        $debugMessage = "[" . date('Y-m-d H:i:s') . "] Processing case 'approve' for review ID: $reviewId" . PHP_EOL;
                        file_put_contents($debugFile, $debugMessage, FILE_APPEND | LOCK_EX);

                        $stmt = $db->prepare("UPDATE reviews SET status = 'approved', updated_at = NOW() WHERE id = ?");
                        $stmt->execute([$reviewId]);

                        $debugMessage = "[" . date('Y-m-d H:i:s') . "] Review approved successfully" . PHP_EOL;
                        file_put_contents($debugFile, $debugMessage, FILE_APPEND | LOCK_EX);

                        return ['success' => true, 'message' => 'Отзыв одобрен успешно.'];

                    case 'reject':
                        $stmt = $db->prepare("UPDATE reviews SET status = 'rejected', updated_at = NOW() WHERE id = ?");
                        $stmt->execute([$reviewId]);

                        return ['success' => true, 'message' => 'Отзыв отклонен успешно.'];

                    case 'edit':
                        // Редактирование отзыва
                        $name = trim($data['name'] ?? '');
                        $email = trim($data['email'] ?? '');
                        $rating = intval($data['rating'] ?? 5);
                        $content = trim($data['content'] ?? '');
                        $status = $data['status'] ?? 'pending';

                        if (empty($name) || empty($content)) {
                            return ['success' => false, 'message' => 'Имя и содержание обязательны'];
                        }

                        // Определяем корректное имя колонки для текста отзыва: content или text
                        $contentColumn = 'content';
                        try {
                            $chk = $db->query("SHOW COLUMNS FROM reviews LIKE 'content'");
                            if (!$chk || !$chk->fetch()) {
                                $contentColumn = 'text';
                            }
                        } catch (Exception $e) {
                            // если SHOW COLUMNS недоступен, используем content по умолчанию
                        }

                        $sql = "UPDATE reviews SET name = ?, email = ?, rating = ?, {$contentColumn} = ?, status = ?, updated_at = NOW() WHERE id = ?";
                        $stmt = $db->prepare($sql);
                        $stmt->execute([$name, $email, $rating, $content, $status, $reviewId]);

                        return ['success' => true, 'message' => 'Отзыв обновлен'];

                    default:
                        $debugMessage = "[" . date('Y-m-d H:i:s') . "] Unknown action: '$action'" . PHP_EOL;
                        file_put_contents($debugFile, $debugMessage, FILE_APPEND | LOCK_EX);
                        error_log("Unknown action: '$action'");
                        return ['success' => false, 'message' => 'Invalid action.'];
                }
            } catch (PDOException $e) {
                error_log("Review action failed: " . $e->getMessage());
                return ['success' => false, 'message' => 'Database error occurred.', 'error' => $e->getMessage()];
            }
        } else {
            return ['success' => false, 'message' => 'Database connection failed.'];
        }
    }
}

if (!function_exists('createSlug')) {
    function createSlug($string)
    {
        $string = mb_strtolower($string, 'UTF-8');
        $string = str_replace(
            ['а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я'],
            ['a', 'b', 'v', 'g', 'd', 'e', 'e', 'zh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', '', 'y', '', 'e', 'yu', 'ya'],
            $string
        );
        $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
        $string = preg_replace('/[\s-]+/', '-', $string);
        $string = trim($string, '-');
        return $string;
    }
}
?>