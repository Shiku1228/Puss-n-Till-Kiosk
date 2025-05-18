<?php
session_start();
include '../Log in Staff/connect.php';

if (isset($_SESSION['StaffID'])) {
    // Update staff status to offline
    $updateSql = "UPDATE staff SET status = 'offline' WHERE StaffID = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("i", $_SESSION['StaffID']);
    $updateStmt->execute();
    $updateStmt->close();
}

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: ../Log in Staff/login_index.php");
exit();
?>
