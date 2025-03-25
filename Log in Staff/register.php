<?php
session_start();
include("connect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signUp'])) {
    $fName = $_POST['fName'] ?? '';
    $lName = $_POST['lName'] ?? '';
    $staffID = $_POST['StaffID'] ?? '';
    $password = $_POST['Password'] ?? '';

    // fields checking
    if (empty($fName) || empty($lName) || empty($staffID) || empty($password)) {
        die("❌ Error: All fields are required!");
    }

    // hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // insert into database 
    $sql = "INSERT INTO staff (StaffID, Password, firstName, lastName) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $staffID, $hashedPassword, $fName, $lName);

    if ($stmt->execute()) {
        echo "<script>alert('✅ Registration successful! You can now log in.'); window.location='login_index.php';</script>";
    } else {
        echo "<script>alert('❌ Registration failed! Staff ID might already exist.');</script>";
    }

    $stmt->close();
}

$conn->close();
?>
