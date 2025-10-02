<?php
/**
 * API для работы с продуктами через базу данных
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Models/Product.php';

$db = Database::getInstance();
$product = new Product();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
  switch ($method) {
    case 'GET':
      handleGet($action, $product);
      break;

    case 'POST':
      handlePost($action, $product);
      break;

    case 'PUT':
      handlePut($action, $product);
      break;

    case 'DELETE':
      handleDelete($action, $product);
      break;

    default:
      http_response_code(405);
      echo json_encode(['error' => 'Method not allowed']);
      break;
  }
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
}

function handleGet($action, $product)
{
  switch ($action) {
    case 'all':
      // Получить все продукты
      $filters = [];

      if (!empty($_GET['category'])) {
        $filters['category'] = $_GET['category'];
      }

      if (!empty($_GET['type'])) {
        $filters['type'] = $_GET['type'];
      }

      if (isset($_GET['featured'])) {
        $filters['is_featured'] = (bool) $_GET['featured'];
      }

      $products = $product->getAll($filters);
      echo json_encode(['success' => true, 'data' => $products]);
      break;

    case 'featured':
      // Получить избранные продукты
      $limit = (int) ($_GET['limit'] ?? 6);
      $products = $product->getFeatured($limit);
      echo json_encode(['success' => true, 'data' => $products]);
      break;

    case 'search':
      // Поиск продуктов
      $query = $_GET['q'] ?? '';
      $limit = (int) ($_GET['limit'] ?? 20);

      if (empty($query)) {
        http_response_code(400);
        echo json_encode(['error' => 'Query parameter is required']);
        return;
      }

      $products = $product->search($query, $limit);
      echo json_encode(['success' => true, 'data' => $products]);
      break;

    case 'categories':
      // Получить все категории
      $categories = $product->getCategories();
      echo json_encode(['success' => true, 'data' => $categories]);
      break;

    case 'by-category':
      // Получить продукты по категории
      $categorySlug = $_GET['category'] ?? '';
      $limit = (int) ($_GET['limit'] ?? null);

      if (empty($categorySlug)) {
        http_response_code(400);
        echo json_encode(['error' => 'Category parameter is required']);
        return;
      }

      $products = $product->getByCategory($categorySlug, $limit);
      echo json_encode(['success' => true, 'data' => $products]);
      break;

    default:
      // Получить конкретный продукт
      if (!empty($action)) {
        $productData = $product->getBySlug($action);
        if ($productData) {
          echo json_encode(['success' => true, 'data' => $productData]);
        } else {
          http_response_code(404);
          echo json_encode(['error' => 'Product not found']);
        }
      } else {
        http_response_code(400);
        echo json_encode(['error' => 'Product slug is required']);
      }
      break;
  }
}

function handlePost($action, $product)
{
  $input = json_decode(file_get_contents('php://input'), true);

  if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON data']);
    return;
  }

  switch ($action) {
    case 'create':
      // Создать новый продукт
      $requiredFields = ['slug', 'title', 'description', 'price'];
      foreach ($requiredFields as $field) {
        if (empty($input[$field])) {
          http_response_code(400);
          echo json_encode(['error' => "Field '$field' is required"]);
          return;
        }
      }

      // Проверяем, не существует ли уже продукт с таким slug
      $existing = $product->getBySlug($input['slug']);
      if ($existing) {
        http_response_code(409);
        echo json_encode(['error' => 'Product with this slug already exists']);
        return;
      }

      $productId = $product->create($input);
      echo json_encode(['success' => true, 'id' => $productId]);
      break;

    case 'category':
      // Создать новую категорию
      if (empty($input['slug']) || empty($input['name'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Slug and name are required']);
        return;
      }

      $categoryId = $product->createCategory($input);
      echo json_encode(['success' => true, 'id' => $categoryId]);
      break;

    default:
      http_response_code(400);
      echo json_encode(['error' => 'Invalid action']);
      break;
  }
}

function handlePut($action, $product)
{
  $input = json_decode(file_get_contents('php://input'), true);

  if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON data']);
    return;
  }

  if (empty($action)) {
    http_response_code(400);
    echo json_encode(['error' => 'Product ID is required']);
    return;
  }

  // Обновить продукт
  $productData = $product->getById($action);
  if (!$productData) {
    http_response_code(404);
    echo json_encode(['error' => 'Product not found']);
    return;
  }

  $result = $product->update($action, $input);
  echo json_encode(['success' => true, 'updated' => $result]);
}

function handleDelete($action, $product)
{
  if (empty($action)) {
    http_response_code(400);
    echo json_encode(['error' => 'Product ID is required']);
    return;
  }

  // Удалить продукт
  $productData = $product->getById($action);
  if (!$productData) {
    http_response_code(404);
    echo json_encode(['error' => 'Product not found']);
    return;
  }

  $result = $product->delete($action);
  echo json_encode(['success' => true, 'deleted' => $result]);
}

