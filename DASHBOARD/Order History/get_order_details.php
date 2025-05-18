<?php
session_start();
include '../../Log in Staff/connect.php';

// Check if staff is logged in
if (!isset($_SESSION['StaffID'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get history ID from request
$history_id = isset($_GET['history_id']) ? (int)$_GET['history_id'] : 0;

if ($history_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid history ID']);
    exit();
}

// Get order details
$sql = "SELECT 
            HistoryID,
            OrderID,
            Timestamps,
            totalPrice,
            orderStatus,
            OrderDetails
        FROM orderhistory 
        WHERE HistoryID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $history_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $order = $result->fetch_assoc();
    
    // Decode order details JSON
    $order['items'] = json_decode($order['OrderDetails'], true);
    unset($order['OrderDetails']); // Remove the raw JSON
    
    header('Content-Type: application/json');
    echo json_encode($order);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Order not found']);
}

$stmt->close();
$conn->close(); 