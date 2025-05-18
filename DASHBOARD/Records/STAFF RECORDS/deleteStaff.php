<?php
session_start();
include '../../../Log in Staff/connect.php';

// Check if the staff is logged in
if (!isset($_SESSION['StaffID'])) {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: ../records.php");
    exit();
}

$staffID = $_GET['id'];

// Delete staff from database
$sql = "DELETE FROM staff WHERE staffID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $staffID);

if ($stmt->execute()) {
    header("Location: ../records.php?message=Staff+deleted+successfully");
} else {
    header("Location: ../records.php?delete=error");
}

$stmt->close();
$conn->close();
?>
