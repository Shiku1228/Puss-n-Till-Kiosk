<?php
require_once 'config.php';

// Get database connection
$conn = getDBConnection();

// Handle different HTTP methods
switch (getRequestMethod()) {
    case 'GET':
        $data = $_GET;
        
        if (!isset($data['type'])) {
            sendResponse(['status' => 'error', 'message' => 'Statistics type is required'], 400);
        }

        switch ($data['type']) {
            case 'daily_sales':
                // Get daily sales for the last 7 days
                $sql = "SELECT 
                        DATE(order_date) as date,
                        COUNT(*) as total_orders,
                        SUM(total_amount) as total_sales
                        FROM orders 
                        WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                        GROUP BY DATE(order_date)
                        ORDER BY date DESC";
                break;

            case 'top_items':
                // Get top selling items
                $limit = isset($data['limit']) ? intval($data['limit']) : 10;
                $sql = "SELECT 
                        m.name,
                        SUM(od.quantity) as total_quantity,
                        SUM(od.quantity * od.price) as total_revenue
                        FROM orderhistory od
                        JOIN menuitem m ON od.item_id = m.id
                        GROUP BY m.id
                        ORDER BY total_quantity DESC
                        LIMIT $limit";
                break;

            case 'category_sales':
                // Get sales by category
                $sql = "SELECT 
                        c.name as category,
                        COUNT(DISTINCT o.id) as total_orders,
                        SUM(od.quantity * od.price) as total_revenue
                        FROM orders o
                        JOIN orderhistory od ON o.id = od.order_id
                        JOIN menuitem m ON od.item_id = m.id
                        JOIN categories c ON m.category_id = c.id
                        GROUP BY c.id
                        ORDER BY total_revenue DESC";
                break;

            case 'hourly_sales':
                // Get sales by hour of day
                $sql = "SELECT 
                        HOUR(order_date) as hour,
                        COUNT(*) as total_orders,
                        SUM(total_amount) as total_sales
                        FROM orders
                        GROUP BY HOUR(order_date)
                        ORDER BY hour";
                break;

            default:
                sendResponse(['status' => 'error', 'message' => 'Invalid statistics type'], 400);
                break;
        }

        $result = $conn->query($sql);
        
        if ($result) {
            $statistics = [];
            while ($row = $result->fetch_assoc()) {
                $statistics[] = $row;
            }
            sendResponse(['status' => 'success', 'data' => $statistics]);
        } else {
            sendResponse(['status' => 'error', 'message' => 'Failed to fetch statistics'], 500);
        }
        break;

    default:
        sendResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
        break;
}

$conn->close();
?> 