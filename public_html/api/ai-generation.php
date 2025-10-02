<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../includes/Database.php';
require_once '../includes/Models/AIGenerationTask.php';
require_once '../includes/Models/Article.php';

// Инициализация базы данных
$db = Database::getInstance();
$aiTask = new AIGenerationTask($db);
$article = new Article();

// Получение метода запроса
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

// Проверка авторизации (можно использовать API ключ)
function checkAuth()
{
  $headers = getallheaders();
  $apiKey = $headers['Authorization'] ?? $headers['authorization'] ?? '';

  // Здесь можно добавить проверку API ключа
  // Пока оставляем открытым для тестирования
  return true;
}

// Обработка запросов
try {
  if (!checkAuth()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
  }

  switch ($method) {
    case 'GET':
      if (isset($_GET['task_id'])) {
        // Получить конкретную задачу
        $task = $aiTask->getTaskByApiId($_GET['task_id']);
        if ($task) {
          echo json_encode([
            'success' => true,
            'task' => $task
          ]);
        } else {
          http_response_code(404);
          echo json_encode(['error' => 'Task not found']);
        }
      } else {
        // Получить список задач для обработки
        $tasks = $aiTask->getPendingTasks(10);
        echo json_encode([
          'success' => true,
          'tasks' => $tasks
        ]);
      }
      break;

    case 'POST':
      // Поддержка как JSON, так и form-data
      $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
      if (strpos($contentType, 'application/json') !== false) {
        $input = json_decode(file_get_contents('php://input'), true);
      } else {
        // Form-data
        $input = $_POST;
        // Если result в JSON формате, декодируем
        if (isset($input['result']) && is_string($input['result'])) {
          $input['result'] = json_decode($input['result'], true);
        }
      }

      if (isset($input['action'])) {
        switch ($input['action']) {
          case 'create_category':
            $name = trim($input['name'] ?? '');
            if ($name === '') {
              http_response_code(400);
              echo json_encode(['error' => 'Missing category name']);
              break;
            }
            // Генерация уникального slug
            $base = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $name), '-'));
            if ($base === '') {
              $base = 'category';
            }
            $slug = $base;
            $n = 1;
            while ((int) $db->fetchColumn("SELECT COUNT(*) FROM article_categories WHERE slug = ?", [$slug]) > 0 && $n < 200) {
              $slug = $base . '-' . $n;
              $n++;
            }

            try {
              $newId = $article->createCategory([
                'name' => $name,
                'slug' => $slug,
                'is_active' => 1,
                'sort_order' => 0
              ]);
              echo json_encode(['success' => true, 'id' => $newId, 'name' => $name]);
            } catch (Throwable $e) {
              http_response_code(500);
              echo json_encode(['error' => 'Failed to create category', 'details' => $e->getMessage()]);
            }
            break;
          case 'retry_task':
            // Перевести задачу в pending (для повторной обработки)
            if (!empty($input['task_id'])) {
              $ok = $aiTask->updateTaskStatus($input['task_id'], 'pending', ['error_message' => null]);
              echo json_encode(['success' => (bool) $ok]);
            } else {
              http_response_code(400);
              echo json_encode(['error' => 'Missing task_id']);
            }
            break;
          case 'update_status':
            // Обновление статуса задачи
            if (isset($input['task_id']) && isset($input['status'])) {
              $result = $aiTask->updateTaskStatus(
                $input['task_id'],
                $input['status'],
                $input['additional_data'] ?? []
              );

              if ($result) {
                echo json_encode(['success' => true]);
              } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to update task status']);
              }
            } else {
              http_response_code(400);
              echo json_encode(['error' => 'Missing required parameters']);
            }
            break;

          case 'complete_task':
            // Завершение задачи с результатом
            if (isset($input['task_id']) && isset($input['result'])) {
              // Сначала обновляем статус
              $aiTask->updateTaskStatus($input['task_id'], 'completed');

              // Создаем статью из результата
              $task = $aiTask->getTaskByApiId($input['task_id']);
              if ($task) {
                // Используем title из входящих данных или из задачи
                $title = $input['title'] ?? $task['title'] ?? 'AI Generated Article';

                // Определяем корректную категорию (избегаем нарушения внешнего ключа)
                $categoryId = $task['category_id'] ?? null;
                if ($categoryId) {
                  $catExists = (int) $db->fetchColumn("SELECT COUNT(*) FROM article_categories WHERE id = ?", [$categoryId]);
                  if ($catExists === 0) {
                    $categoryId = null;
                  }
                }

                $articleData = [
                  'title' => $title,
                  'content' => $input['result']['content'] ?? '',
                  'excerpt' => $input['result']['excerpt'] ?? '',
                  'category_id' => $categoryId, // NULL если категории нет
                  'meta_title' => $input['result']['meta_title'] ?? $title,
                  'meta_description' => $input['result']['meta_description'] ?? '',
                  'featured_image' => $input['result']['featured_image'] ?? '',
                  'tags' => $task['keywords'] ?? '[]',
                  'is_published' => false, // По умолчанию в черновики
                  'author' => 'Черкес Денис'
                ];

                // Логируем данные для отладки
                error_log("Article data: " . json_encode($articleData));

                try {
                  $articleId = $article->create($articleData);
                } catch (Throwable $e) {
                  error_log('Article create exception: ' . $e->getMessage());
                  http_response_code(500);
                  echo json_encode(['error' => 'Failed to create article', 'details' => $e->getMessage()]);
                  exit;
                }

                // Логируем результат
                error_log("Article creation result: " . ($articleId ? $articleId : 'false'));

                if ($articleId) {
                  // Обновляем задачу с ID созданной статьи
                  $aiTask->updateTaskStatus($input['task_id'], 'completed', [
                    'generated_article_id' => $articleId
                  ]);

                  echo json_encode([
                    'success' => true,
                    'article_id' => $articleId,
                    'message' => 'Article created successfully'
                  ]);
                } else {
                  http_response_code(500);
                  echo json_encode(['error' => 'Failed to create article - check server logs']);
                }
              } else {
                http_response_code(404);
                echo json_encode(['error' => 'Task not found']);
              }
            } else {
              http_response_code(400);
              echo json_encode(['error' => 'Missing required parameters']);
            }
            break;

          case 'fail_task':
            // Обработка ошибки задачи
            if (isset($input['task_id']) && isset($input['error'])) {
              $aiTask->updateTaskStatus($input['task_id'], 'failed', [
                'error_message' => $input['error']
              ]);

              echo json_encode(['success' => true]);
            } else {
              http_response_code(400);
              echo json_encode(['error' => 'Missing required parameters']);
            }
            break;

          default:
            http_response_code(400);
            echo json_encode(['error' => 'Unknown action']);
            break;
        }
      } else {
        // Создание новой задачи
        $requiredFields = ['title', 'topic'];
        foreach ($requiredFields as $field) {
          if (!isset($input[$field]) || empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Missing required field: {$field}"]);
            exit;
          }
        }

        $result = $aiTask->createTask($input);
        if ($result['success']) {
          echo json_encode([
            'success' => true,
            'task_id' => $result['api_task_id']
          ]);
        } else {
          http_response_code(500);
          echo json_encode(['error' => $result['message']]);
        }
      }
      break;

    case 'PUT':
      $input = json_decode(file_get_contents('php://input'), true);

      if (isset($input['task_id']) && isset($input['data'])) {
        // Обновление задачи
        $task = $aiTask->getTaskByApiId($input['task_id']);
        if ($task) {
          // Здесь можно добавить логику обновления задачи
          echo json_encode(['success' => true]);
        } else {
          http_response_code(404);
          echo json_encode(['error' => 'Task not found']);
        }
      } else {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required parameters']);
      }
      break;

    case 'DELETE':
      if (isset($_GET['task_id'])) {
        $result = $aiTask->deleteTask($_GET['task_id']);
        if ($result) {
          echo json_encode(['success' => true]);
        } else {
          http_response_code(500);
          echo json_encode(['error' => 'Failed to delete task']);
        }
      } else {
        http_response_code(400);
        echo json_encode(['error' => 'Missing task_id parameter']);
      }
      break;

    default:
      http_response_code(405);
      echo json_encode(['error' => 'Method not allowed']);
      break;
  }

} catch (Exception $e) {
  // Возвращаем подробности ошибки для диагностики (можно скрыть в проде)
  error_log("AI Generation API Error: " . $e->getMessage());
  http_response_code(500);
  echo json_encode([
    'error' => 'Internal server error',
    'details' => $e->getMessage()
  ]);
}
