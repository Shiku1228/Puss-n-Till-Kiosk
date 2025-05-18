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

$sql = "SELECT firstName, lastName FROM staff WHERE staffID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $staffID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $stmt->close();
    $conn->close();
    header("Location: ../records.php");
    exit();
}

$staff = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];

    $updateSql = "UPDATE staff SET firstName = ?, lastName = ? WHERE staffID = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("ssi", $firstName, $lastName, $staffID);

    if ($updateStmt->execute()) {
        echo '<div class="notification" id="success-notification">Staff updated successfully!</div>';
        // Refresh staff data
        $staff['firstName'] = $firstName;
        $staff['lastName'] = $lastName;
    } else {
        echo '<div class="notification error" id="error-notification">Error updating staff: ' . $updateStmt->error . '</div>';
    }

    $updateStmt->close();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Edit Staff</title>
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
        <h1>Edit Staff</h1>
        <form action="./editStaff.php?id=<?php echo $staffID; ?>" method="POST">
            <label for="firstName">First Name:</label>
            <input type="text" id="firstName" name="firstName" value="<?php echo htmlspecialchars($staff['firstName']); ?>" required /><br /><br />

            <label for="lastName">Last Name:</label>
            <input type="text" id="lastName" name="lastName" value="<?php echo htmlspecialchars($staff['lastName']); ?>" required /><br /><br />

            <input type="submit" value="Update Staff" />
        </form>
        <a href="../records.php" class="return-btn">Return</a>
    </div>
</body>
</html>
