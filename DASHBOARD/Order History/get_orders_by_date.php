<?php
session_start();
include '../../Log in Staff/connect.php';

// Set timezone to Asia/Manila
date_default_timezone_set('Asia/Manila');

// Check if staff is logged in
if (!isset($_SESSION['StaffID'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get date from request, ensure it's in the correct timezone
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
error_log("Current server time: " . date('Y-m-d H:i:s'));
error_log("Fetching orders for date: " . $date);

// First check what's in the database
$check_sql = "SELECT * FROM orderhistory LIMIT 5";
$check_result = $conn->query($check_sql);
error_log("Sample orders in database:");
while ($row = $check_result->fetch_assoc()) {
    error_log("Order: " . json_encode($row));
}
    
// Get orders for the specific date
$sql = "SELECT 
            HistoryID,
            OrderID,
            Timestamps,
            totalPrice,
            orderStatus,
            OrderDetails
        FROM orderhistory 
        WHERE DATE(Timestamps) = ?
        AND orderStatus IN ('approved', 'declined')
        ORDER BY Timestamps DESC";

error_log("Executing main query for date: " . $date);

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
$total_sales = 0;
$total_approved_orders = 0;

if ($result && $result->num_rows > 0) {
    error_log("Found " . $result->num_rows . " orders for date: " . $date);
    while ($row = $result->fetch_assoc()) {
        // Only process orders for the exact date
        $orderDate = date('Y-m-d', strtotime($row['Timestamps']));
        if ($orderDate === $date) {
            $orders[] = [
                'HistoryID' => $row['HistoryID'],
                'OrderID' => $row['OrderID'],
                'Timestamps' => $row['Timestamps'],
                'totalPrice' => $row['totalPrice'],
                'orderStatus' => $row['orderStatus'],
                'OrderDetails' => $row['OrderDetails']
            ];
            if ($row['orderStatus'] === 'approved') {
                $total_sales += $row['totalPrice'];
                $total_approved_orders++;
            }
        }
    }
} else {
    error_log("No orders found for date: " . $date);
}

$response = [
    'date' => $date,
    'total_orders' => $total_approved_orders,
    'total_sales' => $total_sales,
    'orders' => $orders
];

error_log("Sending response: " . json_encode($response));
header('Content-Type: application/json');
echo json_encode($response);

$stmt->close();
$conn->close(); 