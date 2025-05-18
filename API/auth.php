<?php
require_once 'config.php';

// Get database connection
$conn = getDBConnection();

// Handle different HTTP methods
switch (getRequestMethod()) {
    case 'POST':
        $data = getRequestBody();
        
        if (!isset($data['action'])) {
            sendResponse(['status' => 'error', 'message' => 'Action is required'], 400);
        }

        switch ($data['action']) {
            case 'login':
                if (!isset($data['username']) || !isset($data['password'])) {
                    sendResponse(['status' => 'error', 'message' => 'Username and password are required'], 400);
                }

                $username = $conn->real_escape_string($data['username']);
                $password = $data['password'];

                $sql = "SELECT id, username, role, password FROM users WHERE username = '$username'";
                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    $user = $result->fetch_assoc();
                    if (password_verify($password, $user['password'])) {
                        // Remove password from response
                        unset($user['password']);
                        sendResponse([
                            'status' => 'success',
                            'message' => 'Login successful',
                            'user' => $user
                        ]);
                    }
                }
                sendResponse(['status' => 'error', 'message' => 'Invalid username or password'], 401);
                break;

            case 'register':
                if (!isset($data['username']) || !isset($data['password']) || !isset($data['role'])) {
                    sendResponse(['status' => 'error', 'message' => 'Username, password, and role are required'], 400);
                }

                $username = $conn->real_escape_string($data['username']);
                $password = password_hash($data['password'], PASSWORD_DEFAULT);
                $role = $conn->real_escape_string($data['role']);

                // Check if username already exists
                $check_sql = "SELECT id FROM users WHERE username = '$username'";
                $check_result = $conn->query($check_sql);
                
                if ($check_result && $check_result->num_rows > 0) {
                    sendResponse(['status' => 'error', 'message' => 'Username already exists'], 400);
                }

                $sql = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')";
                
                if ($conn->query($sql)) {
                    sendResponse([
                        'status' => 'success',
                        'message' => 'User registered successfully',
                        'user_id' => $conn->insert_id
                    ], 201);
                } else {
                    sendResponse(['status' => 'error', 'message' => 'Failed to register user'], 500);
                }
                break;

            default:
                sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                break;
        }
        break;

    default:
        sendResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
        break;
}

$conn->close();
?> 