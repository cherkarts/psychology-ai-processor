<?php
// Simple one-off migration to add download_file column to products
// Usage: open in browser http://localhost/database/migrations/add_download_file_field.php

require_once __DIR__ . '/../../includes/Database.php';

header('Content-Type: text/plain; charset=utf-8');

try {
  $db = Database::getInstance();
  $pdo = $db->getPdo();

  // Check if table products exists
  $stmt = $pdo->query("SHOW TABLES LIKE 'products'");
  if (!$stmt->fetch()) {
    echo "Table 'products' not found.\n";
    exit;
  }

  // Check existing columns
  $columns = [];
  $colsStmt = $pdo->query("SHOW COLUMNS FROM products");
  foreach ($colsStmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
    $columns[$col['Field']] = true;
  }

  $altered = false;

  if (!isset($columns['download_file'])) {
    $pdo->exec("ALTER TABLE products ADD COLUMN download_file VARCHAR(255) NULL AFTER image");
    echo "Added column download_file.\n";
    $altered = true;
  } else {
    echo "Column download_file already exists.\n";
  }

  if (!isset($columns['gallery'])) {
    $pdo->exec("ALTER TABLE products ADD COLUMN gallery JSON NULL AFTER download_file");
    echo "Added column gallery (JSON).\n";
    $altered = true;
  }

  if (!isset($columns['features'])) {
    $pdo->exec("ALTER TABLE products ADD COLUMN features JSON NULL AFTER gallery");
    echo "Added column features (JSON).\n";
    $altered = true;
  }

  if (!isset($columns['tags'])) {
    $pdo->exec("ALTER TABLE products ADD COLUMN tags JSON NULL AFTER features");
    echo "Added column tags (JSON).\n";
    $altered = true;
  }

  if ($altered) {
    echo "Migration completed.\n";
  } else {
    echo "Nothing to migrate.\n";
  }
} catch (Throwable $e) {
  http_response_code(500);
  echo "Migration failed: " . $e->getMessage() . "\n";
}




