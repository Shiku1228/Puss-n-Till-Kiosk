<?php
include '../../Log in Staff/connect.php';

$sql = "SELECT staffID, firstName, lastName, status FROM staff";
$result = $conn->query($sql);

$staff = [];
while ($row = $result->fetch_assoc()) {
    $staff[] = $row;
}
echo json_encode($staff);
?> 