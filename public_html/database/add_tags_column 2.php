<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Adding tags column to articles table ===\n";

// Load database configuration
require_once __DIR__ . '/../includes/config.php';

try {
    $db = getAdminDB();
    
    if (!$db) {
        throw new Exception("Failed to connect to database");
    }
    
    echo "✓ Connected to database successfully\n";
    
    // Check if tags column already exists
    $stmt = $db->prepare("DESCRIBE articles");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array('tags', $columns)) {
        echo "✓ Tags column already exists in articles table\n";
    } else {
        echo "Adding tags column to articles table...\n";
        
        // Add the tags column
        $sql = "ALTER TABLE articles ADD COLUMN tags JSON NULL AFTER featured_image";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        
        echo "✓ Successfully added tags column to articles table\n";
    }
    
    // Verify the column was added
    $stmt = $db->prepare("DESCRIBE articles");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nCurrent articles table structure:\n";
    foreach ($columns as $column) {
        $nullable = $column['Null'] === 'YES' ? 'NULL' : 'NOT NULL';
        $default = $column['Default'] ? "DEFAULT '{$column['Default']}'" : '';
        echo "  {$column['Field']} {$column['Type']} {$nullable} {$default}\n";
    }
    
    echo "\n✅ Migration completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    if (isset($db)) {
        $errorInfo = $db->errorInfo();
        if ($errorInfo[0] !== '00000') {
            echo "SQL Error: " . implode(' - ', $errorInfo) . "\n";
        }
    }
    exit(1);
}
?>