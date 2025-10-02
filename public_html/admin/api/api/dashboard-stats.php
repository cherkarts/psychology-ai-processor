<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';

// Require authentication
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Неавторизован']);
    exit();
}

// Set JSON header
header('Content-Type: application/json');

try {
    $db = getAdminDB();
    $stats = [];
    
    if ($db) {
        // Get reviews count
        $stmt = $db->query("SELECT COUNT(*) as count FROM reviews");
        $stats['reviews'] = $stmt->fetch()['count'] ?? 0;
        
        // Get articles count
        $stmt = $db->query("SELECT COUNT(*) as count FROM articles WHERE is_published = 1");
        $stats['articles'] = $stmt->fetch()['count'] ?? 0;
        
        // Get products count
        $stmt = $db->query("SELECT COUNT(*) as count FROM products WHERE in_stock = 1");
        $stats['products'] = $stmt->fetch()['count'] ?? 0;
        
        // Get orders count
        $stmt = $db->query("SELECT COUNT(*) as count FROM orders WHERE status != 'cancelled'");
        $stats['orders'] = $stmt->fetch()['count'] ?? 0;
        
        // Get pending reviews count
        $stmt = $db->query("SELECT COUNT(*) as count FROM reviews WHERE status = 'pending'");
        $stats['pending_reviews'] = $stmt->fetch()['count'] ?? 0;
        
        // Get today's orders
        $stmt = $db->query("SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE()");
        $stats['today_orders'] = $stmt->fetch()['count'] ?? 0;
        
        // Get this month's revenue
        $stmt = $db->query("SELECT COALESCE(SUM(total_amount), 0) as revenue FROM orders WHERE status = 'completed' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
        $stats['monthly_revenue'] = $stmt->fetch()['revenue'] ?? 0;
        
    } else {
        // Database connection failed - return zeros
        $stats = [
            'reviews' => 0,
            'articles' => 0,
            'products' => 0,
            'orders' => 0,
            'pending_reviews' => 0,
            'today_orders' => 0,
            'monthly_revenue' => 0
        ];
    }
    
    // Additional calculated stats
    $stats['total_content'] = $stats['articles'] + $stats['products'];
    $stats['pending_items'] = $stats['pending_reviews'];
    
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
    
} catch (Exception $e) {
    error_log("Dashboard stats error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Ошибка загрузки статистики панели',
        'stats' => [
            'reviews' => 0,
            'articles' => 0,
            'products' => 0,
            'orders' => 0,
            'pending_reviews' => 0,
            'today_orders' => 0,
            'monthly_revenue' => 0
        ]
    ]);
}
?>