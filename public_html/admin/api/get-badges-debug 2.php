<?php
// Диагностический API для выявления проблем
header('Content-Type: text/plain; charset=utf-8');

echo "=== BADGES API DEBUG ===\n\n";

// Включаем все ошибки
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "1. PHP Version: " . phpversion() . "\n";
echo "2. Error reporting: " . error_reporting() . "\n\n";

echo "3. Checking file paths...\n";
$configPath = __DIR__ . '/../../config.php';
$dbPath = __DIR__ . '/../../includes/Database.php';

echo "Config path: $configPath\n";
echo "Config exists: " . (file_exists($configPath) ? 'YES' : 'NO') . "\n";
echo "Config readable: " . (is_readable($configPath) ? 'YES' : 'NO') . "\n";

echo "Database path: $dbPath\n";
echo "Database exists: " . (file_exists($dbPath) ? 'YES' : 'NO') . "\n";
echo "Database readable: " . (is_readable($dbPath) ? 'YES' : 'NO') . "\n\n";

echo "4. Loading config...\n";
try {
  require_once $configPath;
  echo "Config loaded successfully\n";
  echo "Database host: " . $config['database']['host'] . "\n";
  echo "Database name: " . $config['database']['dbname'] . "\n";
  echo "Database user: " . $config['database']['username'] . "\n";
  echo "Database password: " . (empty($config['database']['password']) ? 'EMPTY' : 'SET') . "\n\n";
} catch (Exception $e) {
  echo "Config error: " . $e->getMessage() . "\n";
  exit;
}

echo "5. Loading Database class...\n";
try {
  require_once $dbPath;
  echo "Database class loaded successfully\n\n";
} catch (Exception $e) {
  echo "Database class error: " . $e->getMessage() . "\n";
  exit;
}

echo "6. Creating Database instance...\n";
try {
  $db = Database::getInstance();
  echo "Database instance created successfully\n\n";
} catch (Exception $e) {
  echo "Database instance error: " . $e->getMessage() . "\n";
  exit;
}

echo "7. Getting PDO connection...\n";
try {
  $pdo = $db->getConnection();
  echo "PDO connection established successfully\n\n";
} catch (Exception $e) {
  echo "PDO connection error: " . $e->getMessage() . "\n";
  exit;
}

echo "8. Testing database query...\n";
try {
  $stmt = $pdo->query("SELECT COUNT(*) as count FROM product_badges");
  $result = $stmt->fetch();
  echo "Query successful, badges count: " . $result['count'] . "\n\n";
} catch (Exception $e) {
  echo "Query error: " . $e->getMessage() . "\n";
  exit;
}

echo "9. Getting badges data...\n";
try {
  $stmt = $pdo->query("SELECT * FROM product_badges WHERE is_active = 1 ORDER BY sort_order, name");
  $badges = $stmt->fetchAll();
  echo "Badges retrieved: " . count($badges) . "\n";

  if (count($badges) > 0) {
    echo "First badge: " . json_encode($badges[0]) . "\n";
  }
  echo "\n";
} catch (Exception $e) {
  echo "Badges query error: " . $e->getMessage() . "\n";
  exit;
}

echo "10. Testing JSON encoding...\n";
try {
  $response = [
    'success' => true,
    'badges' => $badges
  ];

  $json = json_encode($response, JSON_UNESCAPED_UNICODE);
  if ($json === false) {
    echo "JSON encoding failed: " . json_last_error_msg() . "\n";
  } else {
    echo "JSON encoding successful, length: " . strlen($json) . " bytes\n";
    echo "JSON preview: " . substr($json, 0, 200) . "...\n";
  }
} catch (Exception $e) {
  echo "JSON encoding error: " . $e->getMessage() . "\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
?>