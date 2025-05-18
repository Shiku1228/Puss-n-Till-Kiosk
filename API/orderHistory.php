<?php
require_once 'config.php';

// Get database connection
$conn = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT HistoryID, OrderID, orderStatus, Timestamps, totalPrice FROM orderhistory ORDER BY Timestamps DESC";
    $result = $conn->query($sql);

    if ($result) {
        $orderHistory = [];
        while ($row = $result->fetch_assoc()) {
            $orderHistory[] = $row;
        }
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'data' => $orderHistory]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to fetch order history']);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}

$conn->close();
?>
