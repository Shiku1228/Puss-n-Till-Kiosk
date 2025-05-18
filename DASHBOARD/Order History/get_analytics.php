<?php
session_start();
include '../../Log in Staff/connect.php';

// Check if staff is logged in
if (!isset($_SESSION['StaffID'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get parameters
$timeRange = isset($_GET['time_range']) ? $_GET['time_range'] : 'all';
$category = isset($_GET['category']) ? $_GET['category'] : 'all';

// Build date condition
$dateCondition = '';
if ($timeRange !== 'all') {
    $dateCondition = "AND Timestamps >= DATE_SUB(NOW(), INTERVAL ? DAY)";
}

// Get all menu items for category mapping
$menuItems = [];
$menuResult = $conn->query("SELECT Name, Category, Price FROM menuitem");
if ($menuResult && $menuResult->num_rows > 0) {
    while ($row = $menuResult->fetch_assoc()) {
        $menuItems[$row['Name']] = [
            'category' => $row['Category'],
            'price' => $row['Price']
        ];
    }
}

// Get sales data
$sql = "SELECT OrderDetails, Timestamps FROM orderhistory WHERE orderStatus = 'approved' " . $dateCondition;
$stmt = $conn->prepare($sql);

if ($timeRange !== 'all') {
    $stmt->bind_param("i", $timeRange);
}

$stmt->execute();
$result = $stmt->get_result();

$categoryData = [];
$dailySales = [];
$itemSales = [];
$categoryItems = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $details = json_decode($row['OrderDetails'], true);
        $date = date('Y-m-d', strtotime($row['Timestamps']));
        
        if (is_array($details)) {
            foreach ($details as $itemName => $itemData) {
                $cat = isset($menuItems[$itemName]) ? $menuItems[$itemName]['category'] : 'Uncategorized';
                
                // Skip if category filter is set and doesn't match
                if ($category !== 'all' && $cat !== $category) {
                    continue;
                }
                
                // Update category totals
                if (!isset($categoryData[$cat])) {
                    $categoryData[$cat] = 0;
                }
                $categoryData[$cat] += intval($itemData['quantity']);
                
                // Update item totals and details
                if (!isset($itemSales[$cat][$itemName])) {
                    $itemSales[$cat][$itemName] = [
                        'quantity' => 0,
                        'revenue' => 0,
                        'price' => $menuItems[$itemName]['price'] ?? $itemData['price']
                    ];
                }
                $itemSales[$cat][$itemName]['quantity'] += intval($itemData['quantity']);
                $itemSales[$cat][$itemName]['revenue'] += floatval($itemData['price']) * intval($itemData['quantity']);
                
                // Update daily sales
                if (!isset($dailySales[$date])) {
                    $dailySales[$date] = 0;
                }
                $dailySales[$date] += floatval($itemData['price']) * intval($itemData['quantity']);
            }
        }
    }
}

// Sort categories by total sales
arsort($categoryData);

// Process items data
$topItems = [];
foreach ($itemSales as $cat => $items) {
    // Sort items by quantity sold
    uasort($items, function($a, $b) {
        return $b['quantity'] - $a['quantity'];
    });
    
    // If a specific category is selected, return all items
    // Otherwise, return top 5 items for each category
    $topItems[$cat] = ($category !== 'all' && $cat === $category) ? $items : array_slice($items, 0, 5, true);
}

// Sort daily sales by date
ksort($dailySales);

$response = [
    'categories' => $categoryData,
    'topItems' => $topItems,
    'dailySales' => $dailySales,
    'menuItems' => $menuItems
];

header('Content-Type: application/json');
echo json_encode($response);

$stmt->close();
$conn->close(); 