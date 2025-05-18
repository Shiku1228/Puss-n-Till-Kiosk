<?php
session_start();
include '../../Log in Staff/connect.php';

// Check if staff is logged in
if (!isset($_SESSION['StaffID'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Check table structure
$tables = ['orders', 'orderhistory'];
$structure = [];

foreach ($tables as $table) {
    $result = $conn->query("DESCRIBE $table");
    $structure[$table] = [];
    while ($row = $result->fetch_assoc()) {
        $structure[$table][] = $row;
    }
}

// Check recent orders
$orders_sql = "SELECT * FROM orders ORDER BY OrderID DESC LIMIT 5";
$orders_result = $conn->query($orders_sql);
$recent_orders = [];
while ($row = $orders_result->fetch_assoc()) {
    $recent_orders[] = $row;
}

// Check recent order history
$history_sql = "SELECT * FROM orderhistory ORDER BY HistoryID DESC LIMIT 5";
$history_result = $conn->query($history_sql);
$recent_history = [];
while ($row = $history_result->fetch_assoc()) {
    $recent_history[] = $row;
}

$response = [
    'structure' => $structure,
    'recent_orders' => $recent_orders,
    'recent_history' => $recent_history
];

header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);

$conn->close(); 