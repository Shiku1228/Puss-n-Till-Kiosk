<?php
include(__DIR__ . '/../../Log in Staff/connect.php');

$sql = "SELECT staffID, firstName, lastName, role, imagePath FROM staff ORDER BY lastName";
$result = $conn->query($sql);

$staff_list = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Clean up image path
        $originalPath = $row['imagePath'];
        $cleanedPath = str_replace(["../Image_source/", "Image_source"], "", $originalPath);
        $finalPath = "../Image_source/" . ltrim($cleanedPath, "/");

        // Verify file exists
        $absolutePath = __DIR__ . '/../../' . $finalPath;
        if (!file_exists($absolutePath)) {
            error_log("Staff image not found: $absolutePath");
        }

        $row['img'] = $finalPath;
        $staff_list[] = $row;
    }
}

echo json_encode(['staff' => $staff_list]);
?>
