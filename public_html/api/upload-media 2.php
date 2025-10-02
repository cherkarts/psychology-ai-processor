<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Метод не поддерживается']);
    exit;
}

try {
    // Проверяем наличие файла
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Файл не был загружен');
    }

    $file = $_FILES['file'];
    $fileType = $_POST['type'] ?? 'image'; // image, video, thumbnail
    $scope = $_POST['scope'] ?? 'reviews'; // папка назначения: reviews|articles|products|editor
    $allowedScopes = ['reviews', 'articles', 'products', 'editor'];
    if (!in_array($scope, $allowedScopes)) {
        $scope = 'reviews';
    }

    // Проверяем тип файла
    $allowedTypes = [
        'image' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        'video' => ['video/mp4', 'video/avi', 'video/mov', 'video/wmv'],
        'thumbnail' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp']
    ];

    if (!in_array($file['type'], $allowedTypes[$fileType])) {
        throw new Exception('Неподдерживаемый тип файла');
    }

    // Проверяем размер файла
    $maxSizes = [
        'image' => 5 * 1024 * 1024, // 5MB
        'video' => 50 * 1024 * 1024, // 50MB
        'thumbnail' => 2 * 1024 * 1024 // 2MB
    ];

    if ($file['size'] > $maxSizes[$fileType]) {
        throw new Exception('Файл слишком большой');
    }

    // Создаем директорию для загрузок если её нет
    $uploadDir = __DIR__ . '/../uploads/' . $scope . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Генерируем уникальное имя файла
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;

    // Перемещаем файл
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Ошибка при сохранении файла');
    }

    // Возвращаем информацию о файле
    $result = [
        'success' => true,
        'filename' => $filename,
        'filepath' => '/uploads/' . $scope . '/' . $filename,
        'size' => $file['size'],
        'type' => $file['type']
    ];

    echo json_encode($result);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>