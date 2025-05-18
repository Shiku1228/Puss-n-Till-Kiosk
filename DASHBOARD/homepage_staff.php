<?php
session_start();
include '../../Log in Staff/connect.php';

if (!isset($_SESSION['StaffID'])) {
    header("Location: index.php");
    exit();
}

$staffID = $_SESSION['StaffID'];
$sql = "SELECT firstName, lastName FROM staff WHERE staffID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $staffID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $staff = $result->fetch_assoc();
    $fullName = htmlspecialchars($staff['firstName']) . " " . htmlspecialchars($staff['lastName']);
} else {
    $fullName = "Unknown Staff";
}


$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Staff Dashboard | Home</title>
  <link rel="stylesheet" href="homepage.css">
</head>
<body>

  <ul class="navbar">
    <li id="home"><a href="../Home/homepage_staff.php">Home</a></li>
    <li><a href="../Receive Order/receiveOrder.php">Receive Order</a></li>
    <li><a href="../Order History/orderHistory.php">Order History</a></li>
    <li><a href="../Records/records.php">Records</a></li>
    <li><a href="../logout.php" class="btn">Logout</a></li>
  </ul>   

  <h1>Welcome, <?php echo htmlspecialchars($fullName); ?>!</h1>
  <p>You are now logged in as a staff member.</p>

  <div class="toggle-images">
    <!-- Image 1 -->
    <div class="image-container">
      <img src="../../Image_source/receive_order.png" alt="Receive Order" id="receiveOrder" class="toggle-img" />
      <div class="description"><b>RECEIVE ORDER</b><br><br>This section allows you to receive new orders from customers, manage order details, and process transactions efficiently to ensure smooth service.<br><br>
        <a href="../Receive Order/receiveOrder.php" class="desc-button">GO TO PAGE</a>
      </div>
    </div>

    <!-- Image 2 -->
    <div class="image-container">
      <img src="../../Image_source/order_history.png" alt="Order History" id="orderHistory" class="toggle-img" />
      <div class="description"><b>ORDER HISTORY</b><br><br>Here you can view the complete history of all past orders, including order dates, items purchased, and payment status for accurate record keeping.<br><br>
        <a href="../Order History/orderHistory.php" class="desc-button">GO TO PAGE</a>
      </div>
    </div>

    <!-- Image 3 -->
    <div class="image-container">
      <img src="../../Image_source/records.png" alt="Staff Records" id="records" class="toggle-img" />
      <div class="description"><b>STAFF RECORDS</b><br><br>Access detailed records related to staff members and menu items, including infos, and other relevant information to manage your team, and item records effectively. <br><br>
        <a href="../Records/records.php" class="desc-button">GO TO PAGE</a>
      </div>
    </div>
  </div>

  <script src="./home.js"></script>
</body>
</html>
