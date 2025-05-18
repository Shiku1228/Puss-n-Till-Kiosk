<?php
session_start();
include '../../Log in Staff/connect.php';

// Check if the staff is logged in
if (!isset($_SESSION['StaffID']) && !isset($_SESSION['staffID'])) {
    header("Location: index.php");
    exit();
}

// Get staff details
$staffID = isset($_SESSION['StaffID']) ? $_SESSION['StaffID'] : $_SESSION['staffID'];
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

// --- SALES SUMMARY LOGIC ---
$conn = new mysqli('localhost', 'root', '', 'kiosk_system');
// Get all menu items for category mapping
$menuItems = [];
$menuResult = $conn->query("SELECT Name, Category FROM menuitem");
if ($menuResult && $menuResult->num_rows > 0) {
    while ($row = $menuResult->fetch_assoc()) {
        $menuItems[$row['Name']] = $row['Category'];
    }
}
// Get all order histories with OrderDetails
$salesResult = $conn->query("SELECT OrderDetails FROM orderhistory WHERE OrderDetails IS NOT NULL AND orderStatus = 'approved'");
$salesSummary = [];
if ($salesResult && $salesResult->num_rows > 0) {
    while ($row = $salesResult->fetch_assoc()) {
        $details = json_decode($row['OrderDetails'], true);
        if (is_array($details)) {
            foreach ($details as $itemName => $itemData) {
                $cat = isset($menuItems[$itemName]) ? $menuItems[$itemName] : 'Uncategorized';
                if (!isset($salesSummary[$cat])) $salesSummary[$cat] = [];
                if (!isset($salesSummary[$cat][$itemName])) $salesSummary[$cat][$itemName] = 0;
                $salesSummary[$cat][$itemName] += intval($itemData['quantity']);
            }
        }
    }
}

$conn = new mysqli('localhost', 'root', '', 'kiosk_system');
$orderHistoryResult = $conn->query("SELECT HistoryID, OrderID, orderStatus, Timestamps, totalPrice FROM orderhistory ORDER BY Timestamps DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard | Order History</title>
    <link rel="stylesheet" href="orderHistory.css">
    <!-- Add FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <!-- Add Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <nav>
        <ul class="navbar">
            <li><a href="../Home/homepage_staff.php" id="home">Home</a></li>
            <li><a href="../Receive Order/receiveOrder.php" id="receive">Receive Order</a></li>
            <li id="orderHistory"><a href="../Order History/orderHistory.php" id="history">Order History</a></li>
            <li><a href="../Records/records.php" id="records">Records</a></li>
            <li><a href="../logout.php" class="btn">Logout</a></li>
        </ul>
    </nav>
    <div class="container">
        <h1>This is the Order History Page!</h1>
        <p>You are now logged in as a staff member.</p>

        <div class="content-wrapper">
            <!-- Order History Table -->
            <div class="section-card">
                <div class="section-header">
                    <span></span> Order History
                </div>
                <table id="order-history-table" border="1" cellpadding="8" cellspacing="0">
                    <thead>
                        <tr>
                            <th>History ID</th>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Total Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="order-history-body">
                        <!-- Will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Daily Sales Summary -->
            <div class="section-card">
                <div class="section-header">
                    <span></span> Daily Sales Summary
                </div>
                <div id="daily-summary">
                    <h3>Selected Date: <span id="selected-date"></span></h3>
                    <div class="summary-stats">
                        <div class="stat-box">
                            <h4>Total Orders</h4>
                            <p id="total-orders">0</p>
                        </div>
                        <div class="stat-box">
                            <h4>Total Sales</h4>
                            <p id="total-sales">₱0.00</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calendar Section -->
            <div class="section-card">
                <div class="section-header">
                    <span></span> Daily Sales Calendar
                </div>
                <div id="calendar"></div>
            </div>

            <!-- Analytics Section -->
            <div class="section-card">
                <div class="section-header">
                    <span></span> Sales Analytics
                </div>
                <div class="analytics-container">
                    <div class="analytics-filters">
                        <select id="time-range" class="analytics-select">
                            <option value="7">Last 7 Days</option>
                            <option value="30">Last 30 Days</option>
                            <option value="90">Last 90 Days</option>
                            <option value="all">All Time</option>
                        </select>
                        <select id="category-filter" class="analytics-select">
                            <option value="all">All Categories</option>
                            <!-- Will be populated by JavaScript -->
                        </select>
                    </div>
                    <div class="analytics-charts">
                        <div class="chart-container">
                            <h3>Top Selling Items by Category</h3>
                            <canvas id="categoryChart"></canvas>
                        </div>
                        <div class="chart-container">
                            <h3>Sales Trend</h3>
                            <canvas id="trendChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add FullCalendar JS -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script src="./history.js"></script>
</body>
</html>
