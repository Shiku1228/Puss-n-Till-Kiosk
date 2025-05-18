<?php
session_start();
include '../../Log in Staff/connect.php';

if (!isset($_SESSION['StaffID'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemID = intval($_POST['itemID'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 0);
    if ($itemID > 0 && $quantity >= 0) {
        $stmt = $conn->prepare("UPDATE menuitem SET Quantity = ? WHERE ItemID = ?");
        $stmt->bind_param("ii", $quantity, $itemID);
        if ($stmt->execute()) {
            $msg = "Quantity updated successfully!";
        } else {
            $msg = "Failed to update quantity.";
        }
        $stmt->close();
    } else {
        $msg = "Invalid input.";
    }
    $conn->close();
    header("Location: records.php?message=" . urlencode($msg));
    exit();
} else {
    header("Location: records.php");
    exit();
} 