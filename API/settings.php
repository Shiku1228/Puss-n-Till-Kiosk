<?php
require_once 'config.php';

// Get database connection
$conn = getDBConnection();

// Handle different HTTP methods
switch (getRequestMethod()) {
    case 'GET':
        // Get all settings or specific setting
        if (isset($_GET['key'])) {
            $key = $conn->real_escape_string($_GET['key']);
            $sql = "SELECT * FROM settings WHERE setting_key = '$key'";
        } else {
            $sql = "SELECT * FROM settings ORDER BY setting_key ASC";
        }
        
        $result = $conn->query($sql);
        
        if ($result) {
            $settings = [];
            while ($row = $result->fetch_assoc()) {
                $settings[] = $row;
            }
            sendResponse(['status' => 'success', 'data' => $settings]);
        } else {
            sendResponse(['status' => 'error', 'message' => 'Failed to fetch settings'], 500);
        }
        break;

    case 'POST':
        // Add new setting
        $data = getRequestBody();
        
        if (!isset($data['key']) || !isset($data['value'])) {
            sendResponse(['status' => 'error', 'message' => 'Setting key and value are required'], 400);
        }

        $key = $conn->real_escape_string($data['key']);
        $value = $conn->real_escape_string($data['value']);
        $description = isset($data['description']) ? $conn->real_escape_string($data['description']) : '';

        // Check if setting already exists
        $check_sql = "SELECT id FROM settings WHERE setting_key = '$key'";
        $check_result = $conn->query($check_sql);
        
        if ($check_result && $check_result->num_rows > 0) {
            sendResponse(['status' => 'error', 'message' => 'Setting key already exists'], 400);
        }

        $sql = "INSERT INTO settings (setting_key, setting_value, description) VALUES ('$key', '$value', '$description')";
        
        if ($conn->query($sql)) {
            sendResponse([
                'status' => 'success',
                'message' => 'Setting added successfully',
                'id' => $conn->insert_id
            ], 201);
        } else {
            sendResponse(['status' => 'error', 'message' => 'Failed to add setting'], 500);
        }
        break;

    case 'PUT':
        // Update setting
        $data = getRequestBody();
        
        if (!isset($data['key'])) {
            sendResponse(['status' => 'error', 'message' => 'Setting key is required'], 400);
        }

        $key = $conn->real_escape_string($data['key']);
        $updates = [];
        
        if (isset($data['value'])) {
            $updates[] = "setting_value = '" . $conn->real_escape_string($data['value']) . "'";
        }
        if (isset($data['description'])) {
            $updates[] = "description = '" . $conn->real_escape_string($data['description']) . "'";
        }

        if (empty($updates)) {
            sendResponse(['status' => 'error', 'message' => 'No fields to update'], 400);
        }

        $sql = "UPDATE settings SET " . implode(', ', $updates) . " WHERE setting_key = '$key'";
        
        if ($conn->query($sql)) {
            sendResponse(['status' => 'success', 'message' => 'Setting updated successfully']);
        } else {
            sendResponse(['status' => 'error', 'message' => 'Failed to update setting'], 500);
        }
        break;

    case 'DELETE':
        // Delete setting
        $data = getRequestBody();
        
        if (!isset($data['key'])) {
            sendResponse(['status' => 'error', 'message' => 'Setting key is required'], 400);
        }

        $key = $conn->real_escape_string($data['key']);

        // Prevent deleting essential settings
        $essential_settings = ['tax_rate', 'currency', 'business_name'];
        if (in_array($key, $essential_settings)) {
            sendResponse(['status' => 'error', 'message' => 'Cannot delete essential settings'], 400);
        }

        $sql = "DELETE FROM settings WHERE setting_key = '$key'";
        
        if ($conn->query($sql)) {
            sendResponse(['status' => 'success', 'message' => 'Setting deleted successfully']);
        } else {
            sendResponse(['status' => 'error', 'message' => 'Failed to delete setting'], 500);
        }
        break;

    default:
        sendResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
        break;
}

$conn->close();
?> 