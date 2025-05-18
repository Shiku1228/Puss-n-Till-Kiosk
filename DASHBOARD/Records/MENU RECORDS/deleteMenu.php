<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include '../../../Log in Staff/connect.php';

// Check if staff is logged in
if (!isset($_SESSION['StaffID'])) {
    header("Location: ../../../Log in Staff/login_index.php");
    exit();
}

// Verify database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Delete item if ID is provided
if (isset($_GET['id'])) {
    $itemID = $_GET['id'];
    
    // First get image path to delete the file
    $sql = "SELECT ImagePath FROM menuitem WHERE ItemID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $itemID);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    
    // Debug logging
    error_log("Attempting to delete item ID: $itemID");
    
    // Delete the item from database
    $sql = "DELETE FROM menuitem WHERE ItemID = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        header("Location: ../records.php?delete=error");
        exit();
    }
    
    $stmt->bind_param("i", $itemID);
    
    if ($stmt->execute()) {
        error_log("Successfully deleted item ID: $itemID");
        // Delete the image file if it exists
        $imagePath = "../../" . $item['ImagePath'];
        error_log("Attempting to delete image: $imagePath");
        
        if (file_exists($imagePath) && is_writable($imagePath)) {
            if (!unlink($imagePath)) {
                error_log("Failed to delete image: $imagePath");
            }
        } else {
            error_log("Image not found or not writable: $imagePath");
        }
        header("Location: ../records.php?delete=success&message=Item deleted successfully!");
        exit();
    } else {
        header("Location: ../records.php?delete=error");
        exit();
    }
} else {
    header("Location: ../records.php");
    exit();
}
?>
