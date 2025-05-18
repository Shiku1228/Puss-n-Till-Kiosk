<?php
require_once 'config.php';

// Get database connection
$conn = getDBConnection();

// Handle different HTTP methods
switch (getRequestMethod()) {
    case 'GET':
        // Get all orders with item details
        $sql = "SELECT o.*, 
                GROUP_CONCAT(od.item_id, ':', od.quantity, ':', od.price SEPARATOR '|') as order_history
                FROM orders o
                LEFT JOIN orderhistory od ON o.id = od.order_id
                GROUP BY o.id
                ORDER BY o.order_date DESC";
        
        $result = $conn->query($sql);
        
        if ($result) {
            $orders = [];
            while ($row = $result->fetch_assoc()) {
                // Process order history string into array
                if ($row['order_history']) {
                    $details = [];
                    $items = explode('|', $row['order_history']);
                    foreach ($items as $item) {
                        list($item_id, $quantity, $price) = explode(':', $item);
                        $details[] = [
                            'item_id' => $item_id,
                            'quantity' => $quantity,
                            'price' => $price
                        ];
                    }
                    $row['order_history'] = $details;
                } else {
                    $row['order_history'] = [];
                }

                $orders[] = $row;
            }

            sendResponse(['status' => 'success', 'data' => $orders]);
        } else {
            sendResponse(['status' => 'error', 'message' => 'Failed to fetch orders'], 500);
        }
        break;

    case 'POST':
        // Create new order
        $data = getRequestBody();

        if (!isset($data['items']) || !is_array($data['items'])) {
            sendResponse(['status' => 'error', 'message' => 'Order items are required'], 400);
        }

        $conn->begin_transaction();

        try {
            // Calculate total amount
            $total_amount = 0;
            foreach ($data['items'] as $item) {
                $total_amount += $item['price'] * $item['quantity'];
            }

            // Insert into 'orders' table
            $sql = "INSERT INTO orders (total_amount, status) VALUES ($total_amount, 'pending')";
            if (!$conn->query($sql)) {
                throw new Exception("Failed to create order");
            }

            $order_id = $conn->insert_id;

            // Insert into 'orderhistory' table for each item
            foreach ($data['items'] as $item) {
                $item_id = intval($item['item_id']);
                $quantity = intval($item['quantity']);
                $price = floatval($item['price']);

                $sql = "INSERT INTO orderhistory (order_id, item_id, quantity, price) 
                        VALUES ($order_id, $item_id, $quantity, $price)";
                
                if (!$conn->query($sql)) {
                    throw new Exception("Failed to add order details");
                }
            }

            $conn->commit();

            sendResponse([
                'status' => 'success',
                'message' => 'Order created successfully',
                'order_id' => $order_id
            ], 201);

        } catch (Exception $e) {
            $conn->rollback();
            sendResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
        break;

    case 'PUT':
        // Update order status
        $data = getRequestBody();
        
        if (!isset($data['id']) || !isset($data['status'])) {
            sendResponse(['status' => 'error', 'message' => 'Order ID and status are required'], 400);
        }

        $id = intval($data['id']);
        $status = $conn->real_escape_string($data['status']);
        
        $sql = "UPDATE orders SET status = '$status' WHERE id = $id";
        
        if ($conn->query($sql)) {
            sendResponse(['status' => 'success', 'message' => 'Order status updated successfully']);
        } else {
            sendResponse(['status' => 'error', 'message' => 'Failed to update order status'], 500);
        }
        break;

    default:
        sendResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
        break;
}

$conn->close();
?>
