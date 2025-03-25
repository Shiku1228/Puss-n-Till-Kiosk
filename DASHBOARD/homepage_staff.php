<?php
session_start();
include '../Log in Staff/connect.php';

// Check if the staff is logged in
if (!isset($_SESSION['StaffID'])) {
    header("Location: index.php");
    exit();
}

// Get staff details
$staffID = $_SESSION['StaffID'];
$sql = "SELECT firstName, lastName FROM staff WHERE staffID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $staffID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $staff = $result->fetch_assoc();
    $fullName = $staff['firstName'] . " " . $staff['lastName'];
} else {
    $fullName = "Unknown Staff";
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard</title>
    <link rel="stylesheet" href="homepage.css">
</head>
<body>
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($fullName); ?>! 🎉</h1>
        <p>You are now logged in as a staff member.</p>
        <a href="logout.php" class="btn">Logout</a>
    </div>
</body>
</html>
