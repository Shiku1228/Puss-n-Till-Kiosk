<?php
session_start();
include '../../Log in Staff/connect.php';

// Check if staff is logged in
if (!isset($_SESSION['StaffID'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get daily sales data
$sql = "SELECT 
            DATE(Timestamps) as date,
            COUNT(*) as total_orders,
            SUM(totalPrice) as total_sales
        FROM orderhistory 
        WHERE orderStatus = 'approved'
        GROUP BY DATE(Timestamps)
        ORDER BY date DESC";

$result = $conn->query($sql);
$sales_data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sales_data[] = [
            'date' => $row['date'],
            'total_orders' => (int)$row['total_orders'],
            'total_sales' => (float)$row['total_sales']
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($sales_data);
$conn->close(); 