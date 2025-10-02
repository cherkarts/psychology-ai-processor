<?php
/**
 * Скрипт для обновления всех файлов, которые используют JSON файлы
 * Заменяет обращения к JSON на обращения к базе данных
 */

require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Models/Product.php';
require_once __DIR__ . '/../includes/Models/Review.php';
require_once __DIR__ . '/../includes/Models/Meditation.php';
require_once __DIR__ . '/../includes/Models/Article.php';
require_once __DIR__ . '/../includes/Models/Order.php';

class JsonToMysqlUpdater
{
  private $db;
  private $filesToUpdate = [
    // API файлы
    'api/products-db.php',
    'api/reviews-db.php',
    'api/meditations-db.php',
    'api/articles.php',
    'api/add-to-cart.php',
    'api/my-purchases.php',

    // Админ файлы
    'admin/products.php',
    'admin/reviews.php',
    'admin/meditations.php',
    'admin/articles.php',
    'admin/orders.php',
    'admin/product-edit.php',
    'admin/article-edit.php',

    // Основные файлы
    'shop.php',
    'reviews.php',
    'meditations.php',
    'articles.php',
    'product.php',
    'article.php',
    'cart.php',
    'checkout.php'
  ];

  public function __construct()
  {
    $this->db = Database::getInstance();
    $this->db->selectDatabase('cherkas_therapy');
  }

  public function updateAllFiles()
  {
    echo "Начинаем обновление файлов для перехода с JSON на MySQL...\n";

    foreach ($this->filesToUpdate as $file) {
      $this->updateFile($file);
    }

    echo "Обновление файлов завершено!\n";
  }

  private function updateFile($filePath)
  {
    $fullPath = __DIR__ . '/../' . $filePath;

    if (!file_exists($fullPath)) {
      echo "Файл {$filePath} не найден, пропускаем...\n";
      return;
    }

    echo "Обновляем файл: {$filePath}\n";

    $content = file_get_contents($fullPath);
    $originalContent = $content;

    // Обновляем зависимости
    $content = $this->updateDependencies($content);

    // Обновляем код работы с данными
    $content = $this->updateDataAccess($content, $filePath);

    // Сохраняем изменения
    if ($content !== $originalContent) {
      file_put_contents($fullPath, $content);
      echo "  ✓ Файл обновлен\n";
    } else {
      echo "  - Изменений не требуется\n";
    }
  }

  private function updateDependencies($content)
  {
    // Добавляем необходимые зависимости в начало файла
    $dependencies = [
      'require_once \'../includes/Database.php\';',
      'require_once \'../includes/Models/Product.php\';',
      'require_once \'../includes/Models/Review.php\';',
      'require_once \'../includes/Models/Meditation.php\';',
      'require_once \'../includes/Models/Article.php\';',
      'require_once \'../includes/Models/Order.php\';'
    ];

    // Проверяем, есть ли уже зависимости
    foreach ($dependencies as $dep) {
      if (strpos($content, $dep) === false) {
        // Добавляем после <?php
        $content = preg_replace('/<\?php/', "<?php\n" . $dep, $content, 1);
      }
    }

    return $content;
  }

  private function updateDataAccess($content, $filePath)
  {
    // Заменяем загрузку JSON файлов на работу с моделями
    $replacements = [
      // Загрузка JSON файлов
      '/\$productsFile = .*?\.json.*?;/' => '$productModel = new Product();',
      '/\$reviewsFile = .*?\.json.*?;/' => '$reviewModel = new Review();',
      '/\$meditationsFile = .*?\.json.*?;/' => '$meditationModel = new Meditation();',
      '/\$ordersFile = .*?\.json.*?;/' => '$orderModel = new Order();',
      '/\$articlesFile = .*?\.json.*?;/' => '$articleModel = new Article();',

      // Чтение JSON файлов
      '/json_decode\(file_get_contents\(\$productsFile\), true\)/' => '$productModel->getAll()',
      '/json_decode\(file_get_contents\(\$reviewsFile\), true\)/' => '$reviewModel->getApproved()',
      '/json_decode\(file_get_contents\(\$meditationsFile\), true\)/' => '$meditationModel->getAll()',
      '/json_decode\(file_get_contents\(\$ordersFile\), true\)/' => '$orderModel->getAll()',
      '/json_decode\(file_get_contents\(\$articlesFile\), true\)/' => '$articleModel->getPublished()',

      // Запись в JSON файлы
      '/file_put_contents\(\$productsFile, json_encode\(.*?\)\)/' => '$productModel->update($id, $data)',
      '/file_put_contents\(\$reviewsFile, json_encode\(.*?\)\)/' => '$reviewModel->create($data)',
      '/file_put_contents\(\$meditationsFile, json_encode\(.*?\)\)/' => '$meditationModel->update($id, $data)',
      '/file_put_contents\(\$ordersFile, json_encode\(.*?\)\)/' => '$orderModel->create($data)',
      '/file_put_contents\(\$articlesFile, json_encode\(.*?\)\)/' => '$articleModel->create($data)',
    ];

    foreach ($replacements as $pattern => $replacement) {
      $content = preg_replace($pattern, $replacement, $content);
    }

    // Обновляем конкретные методы в зависимости от файла
    $content = $this->updateSpecificMethods($content, $filePath);

    return $content;
  }

  private function updateSpecificMethods($content, $filePath)
  {
    // Обновляем методы в зависимости от типа файла
    if (strpos($filePath, 'api/') === 0) {
      $content = $this->updateApiMethods($content);
    } elseif (strpos($filePath, 'admin/') === 0) {
      $content = $this->updateAdminMethods($content);
    } else {
      $content = $this->updateFrontendMethods($content);
    }

    return $content;
  }

  private function updateApiMethods($content)
  {
    // Обновляем API методы
    $replacements = [
      // Получение продуктов
      '/function getProducts\(\)/' => 'function getProducts() {
                $productModel = new Product();
                return $productModel->getAll();',

      // Получение отзывов
      '/function getReviews\(\)/' => 'function getReviews() {
                $reviewModel = new Review();
                return $reviewModel->getApproved();',

      // Получение медитаций
      '/function getMeditations\(\)/' => 'function getMeditations() {
                $meditationModel = new Meditation();
                return $meditationModel->getAll();',

      // Создание отзыва
      '/function createReview\(.*?\)/' => 'function createReview($data) {
                $reviewModel = new Review();
                return $reviewModel->create($data);',
    ];

    foreach ($replacements as $pattern => $replacement) {
      $content = preg_replace($pattern, $replacement, $content);
    }

    return $content;
  }

  private function updateAdminMethods($content)
  {
    // Обновляем админ методы
    $replacements = [
      // Загрузка данных для админки
      '/\$products = json_decode\(file_get_contents\(\$productsFile\), true\);/' => '$productModel = new Product();
                $products = $productModel->getAll();',

      '/\$reviews = json_decode\(file_get_contents\(\$reviewsFile\), true\);/' => '$reviewModel = new Review();
                $reviews = $reviewModel->getAll();',

      '/\$meditations = json_decode\(file_get_contents\(\$meditationsFile\), true\);/' => '$meditationModel = new Meditation();
                $meditations = $meditationModel->getAll();',

      '/\$orders = json_decode\(file_get_contents\(\$ordersFile\), true\);/' => '$orderModel = new Order();
                $orders = $orderModel->getAll();',

      // Сохранение данных
      '/file_put_contents\(\$productsFile, json_encode\(\$products\)\);/' => '$productModel->update($id, $data);',
      '/file_put_contents\(\$reviewsFile, json_encode\(\$reviews\)\);/' => '$reviewModel->update($id, $data);',
      '/file_put_contents\(\$meditationsFile, json_encode\(\$meditations\)\);/' => '$meditationModel->update($id, $data);',
      '/file_put_contents\(\$ordersFile, json_encode\(\$orders\)\);/' => '$orderModel->update($id, $data);',
    ];

    foreach ($replacements as $pattern => $replacement) {
      $content = preg_replace($pattern, $replacement, $content);
    }

    return $content;
  }

  private function updateFrontendMethods($content)
  {
    // Обновляем фронтенд методы
    $replacements = [
      // Загрузка данных для отображения
      '/\$products = json_decode\(file_get_contents\(.*?products\.json.*?\), true\);/' => '$productModel = new Product();
                $products = $productModel->getAll();',

      '/\$reviews = json_decode\(file_get_contents\(.*?reviews\.json.*?\), true\);/' => '$reviewModel = new Review();
                $reviews = $reviewModel->getApproved();',

      '/\$meditations = json_decode\(file_get_contents\(.*?meditations\.json.*?\), true\);/' => '$meditationModel = new Meditation();
                $meditations = $meditationModel->getAll();',

      '/\$articles = json_decode\(file_get_contents\(.*?articles\.json.*?\), true\);/' => '$articleModel = new Article();
                $articles = $articleModel->getPublished();',
    ];

    foreach ($replacements as $pattern => $replacement) {
      $content = preg_replace($pattern, $replacement, $content);
    }

    return $content;
  }

  public function createBackup()
  {
    echo "Создаем резервную копию файлов...\n";

    $backupDir = __DIR__ . '/backup_' . date('Y-m-d_H-i-s');
    mkdir($backupDir, 0755, true);

    foreach ($this->filesToUpdate as $file) {
      $sourcePath = __DIR__ . '/../' . $file;
      $backupPath = $backupDir . '/' . str_replace('/', '_', $file);

      if (file_exists($sourcePath)) {
        copy($sourcePath, $backupPath);
        echo "  ✓ Резервная копия: {$file}\n";
      }
    }

    echo "Резервная копия создана в: {$backupDir}\n";
    return $backupDir;
  }
}

// Запуск обновления
if (php_sapi_name() === 'cli') {
  $updater = new JsonToMysqlUpdater();

  // Создаем резервную копию
  $backupDir = $updater->createBackup();

  // Обновляем файлы
  $updater->updateAllFiles();

  echo "\nОбновление завершено!\n";
  echo "Резервная копия сохранена в: {$backupDir}\n";
  echo "Теперь все файлы используют базу данных вместо JSON.\n";
} else {
  echo "Этот скрипт предназначен для запуска из командной строки.\n";
  echo "Используйте: php update-json-references.php\n";
}
?>