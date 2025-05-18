<?php
include(__DIR__ . '/../../Log in Staff/connect.php');

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check database connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$sql = "SELECT ItemID AS ItemID, Category AS Category, Name AS Name, Price AS Price, ImagePath AS ImagePath, Quantity AS Quantity FROM menuitem ORDER BY Category"; 
$result = $conn->query($sql);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Query failed: ' . $conn->error]);
    exit();
}

$categories = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $category = $row['Category'];
        if (!isset($categories[$category])) {
            $categories[$category] = array();
        }
        
        // Clean up path
        $originalPath = $row['ImagePath'];
        $filename = basename($originalPath); // just the file name
        $finalPath = "/puss'n_till_kiosk/Image_source/" . $filename;
        $row['ImagePath'] = $finalPath;
        
        // Add the row to the appropriate category
        $categories[$category][] = $row;
    }
}

// Debug information
error_log("Categories data: " . print_r($categories, true));

// Return categories with names in the response
$response = [
    'success' => true,
    'categories' => $categories,
    'category_names' => array_keys($categories)
];

// Set proper content type
header('Content-Type: application/json');
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>