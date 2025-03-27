<?php
session_start();
if(!isset($_SESSION['uname'])) {
    header("Location: login.php");
    exit;
}

$servername = "localhost";
$username = "admin";
$password = "1234";
$database = "aromiq";

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$item = null;
$message = '';

if (isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    // First get the basic food item info
    $stmt = $conn->prepare("SELECT * FROM tbl_fooditem WHERE itemid = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();

    // Then get the detailed description if it exists
    if ($item) {
        $detailed_stmt = $conn->prepare("SELECT itemdetailed FROM tbl_fooditemdetailed WHERE itemid = ?");
        $detailed_stmt->bind_param("i", $id);
        $detailed_stmt->execute();
        $detailed_result = $detailed_stmt->get_result();
        if ($detailed_row = $detailed_result->fetch_assoc()) {
            $item['itemdetailed'] = $detailed_row['itemdetailed'];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = filter_var($_POST['itemid'], FILTER_VALIDATE_INT);
    $name = mysqli_real_escape_string($conn, trim($_POST['itemname']));
    $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
    $description = mysqli_real_escape_string($conn, trim($_POST['itemdescription']));
    $detailed_description = mysqli_real_escape_string($conn, trim($_POST['itemdetailed']));
    $category = mysqli_real_escape_string($conn, trim($_POST['category']));
    
    // Image upload handling
    $image_update = false;
    $image_path = $item['itemimage']; // Keep existing image path by default

    if(isset($_FILES['itemimage']) && $_FILES['itemimage']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($_FILES['itemimage']['type'], $allowed_types)) {
            $message = "Only JPG, JPEG & PNG files are allowed";
        } elseif ($_FILES['itemimage']['size'] > $max_size) {
            $message = "File size must be less than 5MB";
        } else {
            $file_extension = pathinfo($_FILES['itemimage']['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid() . '.' . $file_extension;
            $upload_path = 'images/' . $new_filename;
            
            if (move_uploaded_file($_FILES['itemimage']['tmp_name'], $upload_path)) {
                $image_path = $upload_path;
                $image_update = true;
                
                // Delete old image if it exists
                if ($item['itemimage'] && file_exists($item['itemimage'])) {
                    unlink($item['itemimage']);
                }
            } else {
                $message = "Error uploading file";
            }
        }
    }

    // Validate price
    if ($price === false || $price < 0.01 || $price > 1000.00) {
        $message = "Invalid price. Please enter a price between ₹0.01 and ₹1000.00";
    } else {
        try {
            $conn->begin_transaction();
            
            // Update query with optional image update
            if ($image_update) {
                $sql = "UPDATE tbl_fooditem SET itemname=?, price=?, itemdescription=?, category=?, itemimage=? WHERE itemid=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sdsssi", $name, $price, $description, $category, $image_path, $id);
            } else {
                $sql = "UPDATE tbl_fooditem SET itemname=?, price=?, itemdescription=?, category=? WHERE itemid=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sdssi", $name, $price, $description, $category, $id);
            }
            
            if ($stmt->execute()) {
                // Update or insert detailed description
                $detailed_sql = "INSERT INTO tbl_fooditemdetailed (itemid, itemdetailed) 
                               VALUES (?, ?) 
                               ON DUPLICATE KEY UPDATE itemdetailed = ?";
                $detailed_stmt = $conn->prepare($detailed_sql);
                $detailed_stmt->bind_param("iss", $id, $detailed_description, $detailed_description);
                
                if ($detailed_stmt->execute()) {
                    $conn->commit();
                    $_SESSION['message'] = "Food item updated successfully!";
                    header("Location: admin.php#menuManagement");
                    exit;
                } else {
                    throw new Exception("Error updating detailed description");
                }
            } else {
                throw new Exception("Error updating food item");
            }
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Error updating food item: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Food Item</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            object-fit: cover;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>Edit Food Item</h2>
        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($item): ?>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="itemid" value="<?php echo htmlspecialchars($item['itemid']); ?>">
                
                <div class="mb-3">
                    <label class="form-label">Food Name</label>
                    <input type="text" class="form-control" name="itemname" 
                           value="<?php echo htmlspecialchars($item['itemname']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select class="form-control" name="category" required>
                        <option value="">Select Category</option>
                        <?php
                        $categories = ['Starters', 'Main Course', 'Desserts', 'Beverages'];
                        foreach ($categories as $cat) {
                            $selected = ($item['category'] == $cat) ? 'selected' : '';
                            echo "<option value=\"$cat\" $selected>$cat</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Price</label>
                    <input type="number" class="form-control" name="price" 
                           value="<?php echo htmlspecialchars($item['price']); ?>" 
                           step="0.01" min="25" max="1000.00" required>
                    <small class="text-muted">Enter price between ₹0.01 and ₹1000.00</small>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Basic Description</label>
                    <textarea class="form-control" name="itemdescription" required><?php 
                        echo htmlspecialchars($item['itemdescription']); 
                    ?></textarea>
                    <small class="text-muted">Brief description that appears in the menu</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Detailed Description</label>
                    <textarea class="form-control" name="itemdetailed" required rows="4"><?php 
                        echo htmlspecialchars($item['itemdetailed'] ?? ''); 
                    ?></textarea>
                    <small class="text-muted">Detailed description that appears in the popup</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Food Image</label>
                    <?php if ($item['itemimage']): ?>
                        <div>
                            <img src="<?php echo htmlspecialchars($item['itemimage']); ?>" 
                                 alt="Current food image" class="preview-image mb-2">
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control" name="itemimage" accept="images/*" 
                           onchange="previewImage(this)">
                    <small class="text-muted">Leave empty to keep current image. Max size: 5MB</small>
                    <div id="imagePreview"></div>
                </div>
                
                <button type="submit" class="btn btn-primary">Update Item</button>
                <a href="admin.php#menuManagement" class="btn btn-secondary">Cancel</a>
            </form>
        <?php else: ?>
            <div class="alert alert-danger">Food item not found.</div>
            <a href="admin.php#menuManagement" class="btn btn-primary">Back to Menu List</a>
        <?php endif; ?>
    </div>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            preview.innerHTML = '';
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.classList.add('preview-image');
                    preview.appendChild(img);
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>