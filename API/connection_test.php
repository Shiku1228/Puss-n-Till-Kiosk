<?php
require_once 'config.php';

echo "Testing API Connection\n";
echo "=====================\n\n";

// Test Database Connection
echo "1. Testing Database Connection...\n";
try {
    $conn = getDBConnection();
    echo "✓ Database connection successful!\n\n";
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n\n";
    exit();
}

// Test Menu Table
echo "2. Testing Menu Table...\n";
$sql = "SELECT COUNT(*) as count FROM menuitem";
$result = $conn->query($sql);
if ($result) {
    $row = $result->fetch_assoc();
    echo "✓ Menu table exists with " . $row['count'] . " items\n\n";
} else {
    echo "✗ Error accessing menu table: " . $conn->error . "\n\n";
}

// Test Orders Table
echo "3. Testing Orders Table...\n";
$sql = "SELECT COUNT(*) as count FROM orders";
$result = $conn->query($sql);
if ($result) {
    $row = $result->fetch_assoc();
    echo "✓ Orders table exists with " . $row['count'] . " orders\n\n";
} else {
    echo "✗ Error accessing orders table: " . $conn->error . "\n\n";
}

// Test Order History Table
echo "4. Testing Order History Table...\n";
$sql = "SELECT COUNT(*) as count FROM orderhistory";
$result = $conn->query($sql);
if ($result) {
    $row = $result->fetch_assoc();
    echo "✓ Order history table exists with " . $row['count'] . " items\n\n";
} else {
    echo "✗ Error accessing order history table: " . $conn->error . "\n\n";
}

// Test API Endpoints
echo "5. Testing API Endpoints...\n";

// Test Menu API
echo "   Testing /menu.php...\n";
$menu_url = API_URL . "/menu.php";
$menu_response = @file_get_contents($menu_url);
if ($menu_response !== false) {
    $menu_data = json_decode($menu_response, true);
    if (isset($menu_data['status']) && $menu_data['status'] === 'success') {
        echo "   ✓ Menu API is working\n";
    } else {
        echo "   ✗ Menu API returned error: " . ($menu_data['message'] ?? 'Unknown error') . "\n";
    }
} else {
    echo "   ✗ Could not access Menu API\n";
}

// Test Orders API
echo "   Testing /orders.php...\n";
$orders_url = API_URL . "/orders.php";
$orders_response = @file_get_contents($orders_url);
if ($orders_response !== false) {
    $orders_data = json_decode($orders_response, true);
    if (isset($orders_data['status']) && $orders_data['status'] === 'success') {
        echo "   ✓ Orders API is working\n";
    } else {
        echo "   ✗ Orders API returned error: " . ($orders_data['message'] ?? 'Unknown error') . "\n";
    }
} else {
    echo "   ✗ Could not access Orders API\n";
}

echo "\nTest Complete!\n";
$conn->close();
?> 