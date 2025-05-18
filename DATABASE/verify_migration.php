<?php
require_once 'Database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "<h2>Migration Verification Report</h2>";
    
    // Check menu_categories
    $stmt = $conn->query("SELECT COUNT(*) as count FROM menu_categories");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Menu Categories: {$result['count']} categories found</p>";
    
    // Check menuitem table
    $stmt = $conn->query("SELECT COUNT(*) as count FROM menuitem");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Menu Items: {$result['count']} items found</p>";
    
    // Check orders
    $stmt = $conn->query("SELECT COUNT(*) as count FROM orders");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Orders: {$result['count']} orders found</p>";
    
    // Check order history
    $stmt = $conn->query("SELECT COUNT(*) as count FROM orderhistory");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Order History: {$result['count']} records found</p>";
    
    // Check staff
    $stmt = $conn->query("SELECT COUNT(*) as count FROM staff");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Staff: {$result['count']} staff members found</p>";
    
    // Check new tables
    $stmt = $conn->query("SELECT COUNT(*) as count FROM stock_history");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Stock History: {$result['count']} records found</p>";
    
    $stmt = $conn->query("SELECT COUNT(*) as count FROM daily_sales");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Daily Sales: {$result['count']} records found</p>";
    
    // Check for any NULL values in critical fields
    $stmt = $conn->query("SELECT COUNT(*) as count FROM menuitem WHERE CategoryID IS NULL");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Menu Items without Category: {$result['count']} items found</p>";
    
    $stmt = $conn->query("SELECT COUNT(*) as count FROM staff WHERE username IS NULL");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Staff without Username: {$result['count']} staff found</p>";
    
    echo "<h3>Migration Status: " . ($result['count'] == 0 ? "SUCCESS" : "WARNING - Some data needs attention") . "</h3>";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 