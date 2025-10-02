<?php
// Тестовый скрипт для проверки загрузки файлов
session_start();
require_once __DIR__ . '/includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  echo "<h2>Полученные данные:</h2>";
  echo "<pre>";
  print_r($_POST);
  echo "</pre>";

  echo "<h2>Загруженные файлы:</h2>";
  echo "<pre>";
  print_r($_FILES);
  echo "</pre>";

  // Тестируем загрузку
  if (isset($_FILES['gallery']) && is_array($_FILES['gallery']['name'])) {
    echo "<h3>Обработка галереи:</h3>";
    $uploadDir = '../uploads/products/gallery/';
    if (!is_dir($uploadDir)) {
      mkdir($uploadDir, 0755, true);
    }

    for ($i = 0; $i < count($_FILES['gallery']['name']); $i++) {
      if ($_FILES['gallery']['error'][$i] === UPLOAD_ERR_OK) {
        $fileName = uniqid() . '_' . basename($_FILES['gallery']['name'][$i]);
        $uploadPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['gallery']['tmp_name'][$i], $uploadPath)) {
          echo "<p>Файл {$i} загружен: {$fileName}</p>";
        } else {
          echo "<p>Ошибка загрузки файла {$i}</p>";
        }
      } else {
        echo "<p>Ошибка файла {$i}: " . $_FILES['gallery']['error'][$i] . "</p>";
      }
    }
  }

  exit;
}
?>

<!DOCTYPE html>
<html>

<head>
  <title>Тест загрузки файлов</title>
</head>

<body>
  <h1>Тест загрузки файлов</h1>

  <form method="POST" enctype="multipart/form-data">
    <div>
      <label>Галерея (множественный выбор):</label><br>
      <input type="file" name="gallery[]" multiple accept="image/*">
    </div>
    <br>
    <button type="submit">Загрузить</button>
  </form>
</body>

</html>