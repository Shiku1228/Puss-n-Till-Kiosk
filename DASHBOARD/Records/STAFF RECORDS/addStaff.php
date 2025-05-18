<?php
session_start();
include '../../../Log in Staff/connect.php';

// Check if the staff is logged in
if (!isset($_SESSION['StaffID'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $staffID = $_POST['staffID'];
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];

    $sql = "INSERT INTO staff (staffID, firstName, lastName) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $staffID, $firstName, $lastName);
    
    if ($stmt->execute()) {
        echo '<div class="notification" id="success-notification">New staff added successfully!</div>';
    } else {
        echo '<div class="notification error" id="error-notification">Error adding staff: ' . $stmt->error . '</div>';
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Add Staff</title>
    <link rel="stylesheet" href="../MENU RECORDS/addMenu.css" />
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const notification = document.querySelector('.notification');
            if (notification) {
                notification.classList.add('show');
                setTimeout(() => {
                    notification.classList.remove('show');
                    setTimeout(() => notification.remove(), 300);
                }, 3000);
            }
        });
    </script>
</head>
<body>
    <div class="container">
        <nav>
            <!-- Navbar -->
        </nav>
        <h1>Add Staff</h1>
        <form action="./addStaff.php" method="POST">
            <label for="staffID">Staff ID:</label>
            <input type="text" id="staffID" name="staffID" required /><br /><br />

            <label for="firstName">First Name:</label>
            <input type="text" id="firstName" name="firstName" required /><br /><br />

            <label for="lastName">Last Name:</label>
            <input type="text" id="lastName" name="lastName" required /><br /><br />

            <input type="submit" value="Add Staff" />
        </form>
        <a href="../records.php" class="return-btn">Return</a>
    </div>
</body>
</html>