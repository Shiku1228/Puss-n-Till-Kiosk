<?php
require_once 'config.php';

// Get database connection
$conn = getDBConnection();

// Handle different HTTP methods
switch (getRequestMethod()) {
    case 'GET':
        // Get all menu items
        $sql = "SELECT * FROM menuitem";
        $result = $conn->query($sql);
        
        if ($result) {
            $menu = [];
            while ($row = $result->fetch_assoc()) {
                $menu[] = $row;
            }
            sendResponse(['status' => 'success', 'data' => $menu]);
        } else {
            sendResponse(['status' => 'error', 'message' => 'Failed to fetch menu items'], 500);
        }
        break;

    case 'POST':
        // Add new menu item
        $data = getRequestBody();
        
        // Validate required fields
        if (!isset($data['name']) || !isset($data['price']) || !isset($data['category'])) {
            sendResponse(['status' => 'error', 'message' => 'Missing required fields'], 400);
        }

        $name = $conn->real_escape_string($data['name']);
        $price = floatval($data['price']);
        $category = $conn->real_escape_string($data['category']);
        $description = isset($data['description']) ? $conn->real_escape_string($data['description']) : '';

        $sql = "INSERT INTO menu (name, price, category, description) VALUES ('$name', $price, '$category', '$description')";
        
        if ($conn->query($sql)) {
            sendResponse([
                'status' => 'success',
                'message' => 'Menu item added successfully',
                'id' => $conn->insert_id
            ], 201);
        } else {
            sendResponse(['status' => 'error', 'message' => 'Failed to add menu item'], 500);
        }
        break;

    case 'PUT':
        // Update menu item
        $data = getRequestBody();
        
        if (!isset($data['id'])) {
            sendResponse(['status' => 'error', 'message' => 'Menu item ID is required'], 400);
        }

        $id = intval($data['id']);
        $updates = [];
        
        if (isset($data['name'])) {
            $updates[] = "name = '" . $conn->real_escape_string($data['name']) . "'";
        }
        if (isset($data['price'])) {
            $updates[] = "price = " . floatval($data['price']);
        }
        if (isset($data['category'])) {
            $updates[] = "category = '" . $conn->real_escape_string($data['category']) . "'";
        }
        if (isset($data['description'])) {
            $updates[] = "description = '" . $conn->real_escape_string($data['description']) . "'";
        }

        if (empty($updates)) {
            sendResponse(['status' => 'error', 'message' => 'No fields to update'], 400);
        }

        $sql = "UPDATE menu SET " . implode(', ', $updates) . " WHERE id = $id";
        
        if ($conn->query($sql)) {
            sendResponse(['status' => 'success', 'message' => 'Menu item updated successfully']);
        } else {
            sendResponse(['status' => 'error', 'message' => 'Failed to update menu item'], 500);
        }
        break;

    case 'DELETE':
        // Delete menu item
        $data = getRequestBody();
        
        if (!isset($data['id'])) {
            sendResponse(['status' => 'error', 'message' => 'Menu item ID is required'], 400);
        }

        $id = intval($data['id']);
        $sql = "DELETE FROM menu WHERE id = $id";
        
        if ($conn->query($sql)) {
            sendResponse(['status' => 'success', 'message' => 'Menu item deleted successfully']);
        } else {
            sendResponse(['status' => 'error', 'message' => 'Failed to delete menu item'], 500);
        }
        break;

    default:
        sendResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
        break;
}

$conn->close();
?> 