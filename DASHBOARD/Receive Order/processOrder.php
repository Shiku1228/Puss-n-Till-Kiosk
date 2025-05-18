<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../../DATABASE/Database.php';

// Set timezone
date_default_timezone_set('Asia/Manila');

// Check if staff is logged in
if (!isset($_SESSION['staffID'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['orderId']) || !isset($data['status'])) {
        throw new Exception('Missing required fields');
    }
    
    $orderId = $data['orderId'];
    $status = strtolower($data['status']); // Convert to lowercase
    
    // Validate status
    $validStatuses = ['approved', 'declined', 'cancelled'];
    if (!in_array($status, $validStatuses)) {
        throw new Exception('Invalid status value');
    }
    
    // Start transaction
    $db->beginTransaction();
    
    // Update order status
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE OrderID = ?");
    $stmt->bind_param("si", $status, $orderId);
    if (!$stmt->execute()) {
        throw new Exception("Failed to update order status: " . $stmt->error);
    }
    $stmt->close();
    
    // Get order details for history
    $stmt = $conn->prepare("SELECT OrderDetails, totalPrice, payment_method FROM orders WHERE OrderID = ?");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();
    
    error_log("Order details retrieved: " . json_encode($order));
    
    // Insert into order history
    $notes = 'Processed by staff ID: ' . $_SESSION['staffID'];
    error_log("Attempting to insert order history - OrderID: $orderId, Status: $status");
    
    $currentTime = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("INSERT INTO orderhistory (OrderID, orderStatus, Timestamps, totalPrice, OrderDetails, notes) VALUES (?, ?, ?, ?, ?, ?)");
    
    // Log the values being bound
    error_log("Binding values - OrderID: $orderId, Status: $status, Time: $currentTime, Price: {$order['totalPrice']}");
    
    $stmt->bind_param("isssss", $orderId, $status, $currentTime, $order['totalPrice'], $order['OrderDetails'], $notes);
    
    if (!$stmt->execute()) {
        error_log("Failed to insert order history: " . $stmt->error);
        throw new Exception("Failed to insert order history: " . $stmt->error);
    }
    
    // Verify the insertion
    $verify_sql = "SELECT * FROM orderhistory WHERE OrderID = ? ORDER BY HistoryID DESC LIMIT 1";
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bind_param("i", $orderId);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    $inserted_order = $verify_result->fetch_assoc();
    error_log("Verified inserted order: " . json_encode($inserted_order));
    
    $stmt->close();
    $verify_stmt->close();
    
    // If order is declined, restore stock
    if ($status === 'declined') {
        $orderDetails = json_decode($order['OrderDetails'], true);
        foreach ($orderDetails as $itemName => $item) {
            // Update stock
            $qty = intval($item['quantity']);
            $stmt = $conn->prepare("UPDATE menuitem SET Quantity = Quantity + ? WHERE Name = ?");
            $stmt->bind_param("is", $qty, $itemName);
            if (!$stmt->execute()) {
                throw new Exception("Failed to restore stock for $itemName: " . $stmt->error);
            }
            $stmt->close();
            // Get ItemID
            $stmt = $conn->prepare("SELECT ItemID FROM menuitem WHERE Name = ?");
            $stmt->bind_param("s", $itemName);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $itemID = $row ? $row['ItemID'] : null;
            $stmt->close();
            if ($itemID) {
                // Record stock history
                $type = 'in';
                $referenceType = 'order';
                $notes = 'Order #' . $orderId . ' declined - Stock restored';
                $stmt = $conn->prepare("INSERT INTO stock_history (ItemID, Quantity, Type, ReferenceID, ReferenceType, Notes) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iissss", $itemID, $qty, $type, $orderId, $referenceType, $notes);
                if (!$stmt->execute()) {
                    throw new Exception("Failed to insert stock history: " . $stmt->error);
                }
                $stmt->close();
            }
        }
    }
    
    // Commit transaction
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'orderId' => $orderId,
        'status' => $status
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($db)) {
        $db->rollback();
    }
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
?> 