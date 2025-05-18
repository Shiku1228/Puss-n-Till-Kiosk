<?php
session_start();
include '../../../Log in Staff/connect.php';

// Check if the staff is logged in
if (!isset($_SESSION['StaffID'])) {
    header("Location: index.php");
    exit();
}

// If the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category = $_POST['category'];
    $name = $_POST['name'];
    $price = $_POST['price'];

    // Get the original image name and sanitize it
    $image = $_FILES['image']['name'];
    $imagePath = "../../../Image_source/" . $image;

    // Check for upload errors
    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        echo '<div class="notification error" id="error-notification">Upload error: ' . $_FILES['image']['error'] . '</div>';
    } else {
        // Upload image to the correct folder
        if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            $sql = "INSERT INTO menuitem (Category, Name, Price, ImagePath) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssds", $category, $name, $price, $imagePath);

            if ($stmt->execute()) {
                echo '<div class="notification" id="success-notification">New item added successfully!</div>';
            } else {
                echo '<div class="notification error" id="error-notification">Error adding item: ' . $stmt->error . '</div>';
            }

            $stmt->close();
        } else {
            echo '<div class="notification error" id="error-notification">Error uploading image. Please check folder permissions.</div>';
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Menu Item</title>
    <link rel="stylesheet" href="addMenu.css">
    <script>
        document.addEventListener('DOMContentLoaded', function () {
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
            <!-- Navbar content -->
        </nav>
        <h1>Add Menu Item</h1>
        <form action="./addMenu.php" method="POST" enctype="multipart/form-data">
            <label for="category">Category:</label>
            <input type="text" id="category" name="category" required><br><br>

            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required><br><br>

            <label for="price">Price:</label>
            <input type="number" id="price" name="price" step="0.01" required><br><br>

            <label for="image">Image:</label>
            <input type="file" id="image" name="image" accept="image/*" required><br><br>

            <input type="submit" value="Add Item">
        </form>
        <a href="../records.php" class="return-btn">Return</a>
    </div>
</body>
</html>
