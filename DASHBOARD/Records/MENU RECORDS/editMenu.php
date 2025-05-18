<?php
session_start();
include '../../../Log in Staff/connect.php';

// Check if staff is logged in
if (!isset($_SESSION['StaffID'])) {
    header("Location: ../../../Log in Staff/login_index.php");
    exit();
}

$successMessage = '';
$errorMessage = '';

// Get item details
if (isset($_GET['id'])) {
    $itemID = $_GET['id'];
    $sql = "SELECT * FROM menuitem WHERE ItemID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $itemID);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $itemID = $_POST['itemID'];
    $category = $_POST['category'];
    $name = $_POST['name'];
    $price = $_POST['price'];

    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = '/puss_n_till_kiosk/Image_source/';
        // Sanitize filename: remove spaces and special characters
        $image = preg_replace('/[^A-Za-z0-9_.-]/', '_', basename($_FILES['image']['name']));
        $uploadPath = $_SERVER['DOCUMENT_ROOT'] . $uploadDir . $image;

        // Ensure upload directory exists
        if (!is_dir($_SERVER['DOCUMENT_ROOT'] . $uploadDir)) {
            mkdir($_SERVER['DOCUMENT_ROOT'] . $uploadDir, 0777, true);
        }

        // Check if directory is writable
        if (!is_writable($_SERVER['DOCUMENT_ROOT'] . $uploadDir)) {
            $errorMessage = "Upload directory is not writable.";
            $imagePath = $_POST['currentImage'];
        } else if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
            $imagePath = $uploadDir . $image;
        } else {
            $errorMessage = "Image upload failed. Please check folder permissions and file size.";
            $imagePath = $_POST['currentImage'];
        }
    } else {
        $imagePath = $_POST['currentImage'];
    }

    $sql = "UPDATE menuitem SET Category=?, Name=?, Price=?, ImagePath=? WHERE ItemID=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdsi", $category, $name, $price, $imagePath, $itemID);

    if ($stmt->execute()) {
        $successMessage = "Menu item updated successfully!";
        $item['Category'] = $category;
        $item['Name'] = $name;
        $item['Price'] = $price;
        $item['ImagePath'] = $imagePath;
    } else {
        $errorMessage = "Error updating item: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Menu Item</title>
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
        <h1>Edit Menu Item</h1>

        <?php if ($successMessage): ?>
            <div class="notification" id="success-notification"><?php echo $successMessage; ?></div>
        <?php elseif ($errorMessage): ?>
            <div class="notification error" id="error-notification"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $item['ItemID']); ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="itemID" value="<?php echo $item['ItemID']; ?>">
            <input type="hidden" name="currentImage" value="<?php echo isset($item['ImagePath']) ? htmlspecialchars($item['ImagePath']) : ''; ?>">

            <label for="category">Category:</label>
            <input type="text" id="category" name="category" value="<?php echo $item['Category']; ?>" required><br><br>

            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo $item['Name']; ?>" required><br><br>

            <label for="price">Price:</label>
            <input type="number" id="price" name="price" step="0.01" value="<?php echo $item['Price']; ?>" required><br>

            <label for="image">Current Image:</label><br>
            <img src="<?php echo isset($item['ImagePath']) ? htmlspecialchars($item['ImagePath']) : ''; ?>" alt="Current Image" style="max-width:50px; max-height:90px; display:block;">
            <label for="image">Change Image:</label>
            <input type="file" id="image" name="image" accept="image/*"><br><br>

            <input type="submit" value="Update Item" />
            <a href="../records.php" class="return-btn">Return</a>
        </form>
    </div>
</body>
</html>
