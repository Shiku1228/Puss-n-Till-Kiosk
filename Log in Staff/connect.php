<?php
$servername = "localhost"; // Default sa XAMPP
$username = "root"; // Default user sa XAMPP
$password = ""; // Default walang password sa XAMPP
$dbname = "kiosk_system";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
