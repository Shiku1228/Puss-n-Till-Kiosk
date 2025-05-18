<?php
require_once 'config.php';

// Get database connection
$conn = getDBConnection();

// Handle different HTTP methods
switch (getRequestMethod()) {
    case 'GET':
        // Get all users or specific user
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $sql = "SELECT id, username, role, created_at FROM users WHERE id = $id";
        } else {
            $sql = "SELECT id, username, role, created_at FROM users ORDER BY username ASC";
        }
        
        $result = $conn->query($sql);
        
        if ($result) {
            $users = [];
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
            sendResponse(['status' => 'success', 'data' => $users]);
        } else {
            sendResponse(['status' => 'error', 'message' => 'Failed to fetch users'], 500);
        }
        break;

    case 'PUT':
        // Update user details
        $data = getRequestBody();
        
        if (!isset($data['id'])) {
            sendResponse(['status' => 'error', 'message' => 'User ID is required'], 400);
        }

        $id = intval($data['id']);
        $updates = [];
        
        if (isset($data['username'])) {
            $updates[] = "username = '" . $conn->real_escape_string($data['username']) . "'";
        }
        if (isset($data['role'])) {
            $updates[] = "role = '" . $conn->real_escape_string($data['role']) . "'";
        }
        if (isset($data['password'])) {
            $updates[] = "password = '" . password_hash($data['password'], PASSWORD_DEFAULT) . "'";
        }

        if (empty($updates)) {
            sendResponse(['status' => 'error', 'message' => 'No fields to update'], 400);
        }

        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = $id";
        
        if ($conn->query($sql)) {
            sendResponse(['status' => 'success', 'message' => 'User updated successfully']);
        } else {
            sendResponse(['status' => 'error', 'message' => 'Failed to update user'], 500);
        }
        break;

    case 'DELETE':
        // Delete user
        $data = getRequestBody();
        
        if (!isset($data['id'])) {
            sendResponse(['status' => 'error', 'message' => 'User ID is required'], 400);
        }

        $id = intval($data['id']);

        // Prevent deleting the last admin
        $check_sql = "SELECT COUNT(*) as admin_count FROM users WHERE role = 'admin'";
        $check_result = $conn->query($check_sql);
        $admin_count = $check_result->fetch_assoc()['admin_count'];

        $user_sql = "SELECT role FROM users WHERE id = $id";
        $user_result = $conn->query($user_sql);
        $user_role = $user_result->fetch_assoc()['role'];

        if ($user_role === 'admin' && $admin_count <= 1) {
            sendResponse(['status' => 'error', 'message' => 'Cannot delete the last admin user'], 400);
        }

        $sql = "DELETE FROM users WHERE id = $id";
        
        if ($conn->query($sql)) {
            sendResponse(['status' => 'success', 'message' => 'User deleted successfully']);
        } else {
            sendResponse(['status' => 'error', 'message' => 'Failed to delete user'], 500);
        }
        break;

    default:
        sendResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
        break;
}

$conn->close();
?> 