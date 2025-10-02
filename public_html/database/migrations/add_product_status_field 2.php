<?php
/**
 * Database migration script to add status field to products table
 * 
 * This migration adds a status field to the products table to track whether
 * a product is published or in draft status, similar to the articles table.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../../admin/includes/config.php';

echo "Starting migration to add status field to products table...\n";

try {
    $db = getAdminDB();
    
    if (!$db) {
        throw new Exception("Failed to connect to database");
    }
    
    // Check if the column already exists
    $stmt = $db->prepare("SHOW COLUMNS FROM products LIKE 'status'");
    $stmt->execute();
    $columnExists = $stmt->fetch();
    
    if ($columnExists) {
        echo "Status column already exists in products table. Skipping migration.\n";
        exit(0);
    }
    
    // Add the status column
    echo "Adding status column to products table...\n";
    $stmt = $db->prepare("ALTER TABLE products ADD COLUMN status ENUM('draft', 'published') DEFAULT 'draft' AFTER type");
    $stmt->execute();
    
    // Add index for the status column
    echo "Adding index for status column...\n";
    $stmt = $db->prepare("ALTER TABLE products ADD INDEX idx_status (status)");
    $stmt->execute();
    
    // Update existing products to have 'published' status by default
    echo "Setting existing products to 'published' status...\n";
    $stmt = $db->prepare("UPDATE products SET status = 'published'");
    $stmt->execute();
    
    echo "Migration completed successfully!\n";
    echo "Added status column to products table with default value 'draft'\n";
    echo "All existing products have been set to 'published' status\n";
    
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>