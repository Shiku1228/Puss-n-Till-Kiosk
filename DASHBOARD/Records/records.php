<?php
session_start();
include '../../Log in Staff/connect.php';

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

$menuSql = "SELECT ItemID, Category, Name, Price, ImagePath, Quantity FROM menuitem";
$menuResult = $conn->query($menuSql);

$staffSql = "SELECT staffID, firstName, lastName, status FROM staff";
$staffResult = $conn->query($staffSql);

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard | Records</title>
    <link rel="stylesheet" href="records.css">
</head>
<body>
    <nav>
        <ul class="navbar">
            <li><a href="../Home/homepage_staff.php" id="home">Home</a></li>
            <li><a href="../Receive Order/receiveOrder.php" id="receive">Receive Order</a></li>
            <li><a href="../Order History/orderHistory.php" id="history">Order History</a></li>
            <li id="records"><a href="../Records/records.php" id="records">Records</a></li>
            <li><a href="../logout.php" class="btn">Logout</a></li>
        </ul>   
    </nav>

    <div class="toggle-images">
        <img src="../../Image_source/menuImage.png" alt="Menu" id="menuImage" title="Show Menu Records">
        <img src="../../Image_source/staffImage.png" alt="Staff" id="staffImage" title="Show Staff Records">
    </div>

    <?php if (isset($_GET['message'])): ?>
        <div class="notification" id="success-notification"><?php echo htmlspecialchars($_GET['message']); ?></div>
    <?php elseif (isset($_GET['delete']) && $_GET['delete'] == 'error'): ?>
        <div class="notification error">Error deleting item!</div>
    <?php endif; ?>

    <!-- Menu Logs Section -->
    <div id="menuTableContainer" class="hidden">
        <h1>Menu Records</h1>
        <p>Check and manage the menu items.</p> <br>
        <div class="action-buttons">
            <a href="../Records/MENU RECORDS/addMenu.php" class="btn add-btn">Add New Item</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ItemID</th>
                    <th>Category</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($menuResult->num_rows > 0): ?>
                    <?php while($row = $menuResult->fetch_assoc()): ?>
                        <?php
                            $imgPathRaw = $row['ImagePath'];
                            $filename = basename($imgPathRaw); // get only the filename
                            $imagePath = "/puss_n_till_kiosk/Image_source/" . rawurlencode($filename);
                            $defaultImage = "/puss_n_till_kiosk/Image_source/default.jpg";
                            $quantity = isset($row['Quantity']) ? $row['Quantity'] : 50;
                        ?>
                        <tr>
                            <td><?php echo $row['ItemID']; ?></td>
                            <td><?php echo $row['Category']; ?></td>
                            <td><?php echo $row['Name']; ?></td>
                            <td><?php echo $row['Price']; ?></td>
                            <td>
                                <form action="update_quantity.php" method="POST" style="display:inline-flex;align-items:center;gap:5px;">
                                    <input type="hidden" name="itemID" value="<?php echo $row['ItemID']; ?>">
                                    <input type="number" name="quantity" value="<?php echo $quantity; ?>" min="0" style="width:60px;">
                                    <button type="submit" class="btn edit-btn" style="padding:2px 8px;">Set</button>
                                </form>
                            </td>
                            <td class="actions">
                                <div class="action-buttons">
                                    <a href="../Records/MENU RECORDS/editMenu.php?id=<?php echo $row['ItemID']; ?>" class="btn edit-btn">Edit</a>
                                    <a href="../Records/MENU RECORDS/deleteMenu.php?id=<?php echo $row['ItemID']; ?>" class="btn delete-btn" onclick="return confirm('Are you sure you want to delete this item?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8">No menu items available.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <br>
    </div>

    <!-- Staff Logs Section -->
    <div id="staffTableContainer" class="hidden">
        <h1>Staff Records</h1>
        <p>Check and manage the staff members.</p> <br>

        <div class="action-buttons" style="margin-bottom: 10px;">
            <a href="../Records/STAFF RECORDS/addStaff.php" class="btn add-btn">Add New Staff</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>StaffID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($staffResult->num_rows > 0): ?>
                    <?php while($row = $staffResult->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['staffID']; ?></td>
                            <td><?php echo htmlspecialchars($row['firstName']); ?></td>
                            <td><?php echo htmlspecialchars($row['lastName']); ?></td>
                            <td>
                                <span class="status-badge <?php echo $row['status']; ?>">
                                    <?php echo ucfirst($row['status']); ?>
                                </span>
                            </td>
                            <td class="actions">
                                <div class="action-buttons">
                                    <a href="../Records/STAFF RECORDS/editStaff.php?id=<?php echo $row['staffID']; ?>" class="btn edit-btn">Edit</a>
                                    <a href="../Records/STAFF RECORDS/deleteStaff.php?id=<?php echo $row['staffID']; ?>" class="btn delete-btn" onclick="return confirm('Are you sure you want to delete this staff member?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6">No staff members available.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
<script src="records.js"></script>
</html>
