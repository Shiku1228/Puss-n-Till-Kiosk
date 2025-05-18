<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../../DATABASE/Database.php';

// Check if staff is logged in
if (!isset($_SESSION['staffID'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized (staffID not set in session)']);
    exit;
}

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Fetch pending orders with staff details
    $stmt = $conn->prepare("
        SELECT o.*, s.firstName, s.lastName 
        FROM orders o
        JOIN staff s ON o.staffID = s.staffID
        WHERE o.status = 'pending'
        ORDER BY o.orderTime ASC
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = [
            'OrderID' => $row['OrderID'],
            'orderTime' => $row['orderTime'],
            'totalPrice' => $row['totalPrice'],
            'OrderDetails' => $row['OrderDetails'],
            'payment_method' => $row['payment_method'],
            'staffName' => $row['firstName'] . ' ' . $row['lastName']
        ];
    }
    echo json_encode($orders);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
