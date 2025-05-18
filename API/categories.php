<?php
require_once 'config.php';

// Get database connection
$conn = getDBConnection();

// Handle different HTTP methods
switch (getRequestMethod()) {
    case 'GET':
        // Get all categories
        $sql = "SELECT * FROM categories ORDER BY name ASC";
        $result = $conn->query($sql);
        
        if ($result) {
            $categories = [];
            while ($row = $result->fetch_assoc()) {
                $categories[] = $row;
            }
            sendResponse(['status' => 'success', 'data' => $categories]);
        } else {
            sendResponse(['status' => 'error', 'message' => 'Failed to fetch categories'], 500);
        }
        break;

    case 'POST':
        // Add new category
        $data = getRequestBody();
        
        if (!isset($data['name'])) {
            sendResponse(['status' => 'error', 'message' => 'Category name is required'], 400);
        }

        $name = $conn->real_escape_string($data['name']);
        $description = isset($data['description']) ? $conn->real_escape_string($data['description']) : '';

        $sql = "INSERT INTO categories (name, description) VALUES ('$name', '$description')";
        
        if ($conn->query($sql)) {
            sendResponse([
                'status' => 'success',
                'message' => 'Category added successfully',
                'id' => $conn->insert_id
            ], 201);
        } else {
            sendResponse(['status' => 'error', 'message' => 'Failed to add category'], 500);
        }
        break;

    case 'PUT':
        // Update category
        $data = getRequestBody();
        
        if (!isset($data['id'])) {
            sendResponse(['status' => 'error', 'message' => 'Category ID is required'], 400);
        }

        $id = intval($data['id']);
        $updates = [];
        
        if (isset($data['name'])) {
            $updates[] = "name = '" . $conn->real_escape_string($data['name']) . "'";
        }
        if (isset($data['description'])) {
            $updates[] = "description = '" . $conn->real_escape_string($data['description']) . "'";
        }

        if (empty($updates)) {
            sendResponse(['status' => 'error', 'message' => 'No fields to update'], 400);
        }

        $sql = "UPDATE categories SET " . implode(', ', $updates) . " WHERE id = $id";
        
        if ($conn->query($sql)) {
            sendResponse(['status' => 'success', 'message' => 'Category updated successfully']);
        } else {
            sendResponse(['status' => 'error', 'message' => 'Failed to update category'], 500);
        }
        break;

    case 'DELETE':
        // Delete category
        $data = getRequestBody();
        
        if (!isset($data['id'])) {
            sendResponse(['status' => 'error', 'message' => 'Category ID is required'], 400);
        }

        $id = intval($data['id']);

        // Check if category is in use
        $check_sql = "SELECT COUNT(*) as count FROM menuitem WHERE category_id = $id";
        $check_result = $conn->query($check_sql);
        $count = $check_result->fetch_assoc()['count'];

        if ($count > 0) {
            sendResponse(['status' => 'error', 'message' => 'Cannot delete category that is in use by menu items'], 400);
        }

        $sql = "DELETE FROM categories WHERE id = $id";
        
        if ($conn->query($sql)) {
            sendResponse(['status' => 'success', 'message' => 'Category deleted successfully']);
        } else {
            sendResponse(['status' => 'error', 'message' => 'Failed to delete category'], 500);
        }
        break;

    default:
        sendResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
        break;
}

$conn->close();
?> 