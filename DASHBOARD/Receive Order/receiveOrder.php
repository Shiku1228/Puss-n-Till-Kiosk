<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../../DATABASE/Database.php';

// Handle API (POST) requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    try {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        $db->beginTransaction();
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            throw new Exception('Invalid JSON data');
        }
        if (!isset($data['orderDetails']) || !isset($data['totalPrice'])) {
            throw new Exception('Missing required fields');
        }
        $orderDetailsJson = json_encode($data['orderDetails']);
        $paymentMethod = isset($data['paymentMethod']) ? $data['paymentMethod'] : 'cash';
        $stmt = $conn->prepare("INSERT INTO orders (staffID, OrderDetails, totalPrice, orderTime, status, payment_status, payment_method) VALUES (?, ?, ?, NOW(), 'pending', 'pending', ?)");
        $stmt->bind_param("isds", $_SESSION['staffID'], $orderDetailsJson, $data['totalPrice'], $paymentMethod);
        if (!$stmt->execute()) {
            throw new Exception("Failed to insert order: " . $stmt->error);
        }
        $orderID = $conn->insert_id;
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO orderhistory (OrderID, orderStatus, Timestamps, totalPrice, OrderDetails) VALUES (?, 'pending', NOW(), ?, ?)");
        $stmt->bind_param("ids", $orderID, $data['totalPrice'], $orderDetailsJson);
        if (!$stmt->execute()) {
            throw new Exception("Failed to insert order history: " . $stmt->error);
        }
        $stmt->close();
        foreach ($data['orderDetails'] as $itemName => $item) {
            $qty = intval($item['quantity']);
            $stmt = $conn->prepare("UPDATE menuitem SET Quantity = Quantity - ? WHERE Name = ?");
            $stmt->bind_param("is", $qty, $itemName);
            if (!$stmt->execute()) {
                throw new Exception("Failed to update stock for $itemName: " . $stmt->error);
            }
            $stmt->close();
            $stmt = $conn->prepare("SELECT ItemID FROM menuitem WHERE Name = ?");
            $stmt->bind_param("s", $itemName);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $itemID = $row ? $row['ItemID'] : null;
            $stmt->close();
            if ($itemID) {
                $type = 'out';
                $referenceType = 'order';
                $notes = 'Order #' . $orderID;
                $stmt = $conn->prepare("INSERT INTO stock_history (ItemID, Quantity, Type, ReferenceID, ReferenceType, Notes) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iissss", $itemID, $qty, $type, $orderID, $referenceType, $notes);
                if (!$stmt->execute()) {
                    throw new Exception("Failed to insert stock history: " . $stmt->error);
                }
                $stmt->close();
            }
        }
        $cashSales = $paymentMethod === 'cash' ? $data['totalPrice'] : 0;
        $cardSales = $paymentMethod === 'card' ? $data['totalPrice'] : 0;
        $mobileSales = $paymentMethod === 'mobile' ? $data['totalPrice'] : 0;
        $stmt = $conn->prepare("INSERT INTO daily_sales (Date, TotalSales, TotalOrders, CashSales, CardSales, MobileSales) VALUES (CURDATE(), ?, 1, ?, ?, ?) ON DUPLICATE KEY UPDATE TotalSales = TotalSales + VALUES(TotalSales), TotalOrders = TotalOrders + 1, CashSales = CashSales + VALUES(CashSales), CardSales = CardSales + VALUES(CardSales), MobileSales = MobileSales + VALUES(MobileSales)");
        $stmt->bind_param("dddd", $data['totalPrice'], $cashSales, $cardSales, $mobileSales);
        if (!$stmt->execute()) {
            throw new Exception("Failed to update daily sales: " . $stmt->error);
        }
        $stmt->close();
        $db->commit();
        echo json_encode([
            'success' => true,
            'message' => 'Order received successfully',
            'orderID' => $orderID
        ]);
    } catch (Exception $e) {
        if (isset($db)) {
            $db->rollback();
        }
        http_response_code(500);
        echo json_encode([
            'error' => $e->getMessage()
        ]);
    }
    exit;
}
// If not POST, show the HTML page as normal
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard | Receive Order</title>
    <link rel="stylesheet" href="receiveOrder.css">
</head>
<body>
    <div class="container">
        <nav>
            <ul class="navbar">
                <li><a href="../Home/homepage_staff.php" id="home">Home</a></li>
                <li id="receiveOrder"><a href="receiveOrder.php" id="receive">Receive Order</a></li>
                <li><a href="../Order History/orderHistory.php" id="history">Order History</a></li>
                <li><a href="../Records/records.php" id="records">Records</a></li>
                <li><a href="../logout.php" class="btn">Logout</a></li>
            </ul>
        </nav>
        <h1>Receive Orders Panel</h1>
        <p>This is the panel where you will see the order queue</p>
        <!-- Display order queue -->
        
        <div class="orders-list">
            <!-- Orders will be dynamically inserted here by receive.js -->
        </div>
    </div>
    <script src="receive.js"></script>
</body>
</html>
