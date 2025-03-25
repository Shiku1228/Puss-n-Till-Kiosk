<?php
session_start();
include 'connect.php'; // Ensure database connection is included

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['StaffID'];
    $password = $_POST['Password'];

    if (empty($id) || empty($password)) {
        echo "❌ Error: Staff ID and Password are required!";
        exit();
    }

    // Prepare and execute the query
    $sql = "SELECT * FROM staff WHERE StaffID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id); // Ensure StaffID is an integer
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        if (password_verify($password, $row['Password'])) { // For hashed passwords
            $_SESSION['StaffID'] = $row['StaffID'];
            echo "✅ Login successful! Redirecting...";
            header("Location: ../DASHBOARD/homepage_staff.php");
            exit();
        } elseif ($password == $row['Password']) { // For plain text passwords (temporary fix)
            $_SESSION['StaffID'] = $row['StaffID'];
            echo "✅ Login successful! Redirecting...";
            header("Location: ../DASHBOARD/homepage_staff.php");
            exit();
        } else {
            echo "<script>alert('❌ Incorrect Password!');</script>";
        }
    } else {
        echo "<script>alert('❌ Staff ID not found!');</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
