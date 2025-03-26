<?php
session_start();
$servername = "localhost";
$username = "chef";
$password = "1234";
$database = "aromiq";

$conn = mysqli_connect($servername, $username, $password, $database);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $itemname = mysqli_real_escape_string($conn, $_POST['itemname']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $itemdescription = mysqli_real_escape_string($conn, $_POST['itemdescription']);
    $itemdetailed = mysqli_real_escape_string($conn, $_POST['itemdetailed']);
    
    // Handle image upload
    $target_dir = "uploads/";
    $imageFileType = strtolower(pathinfo($_FILES["itemimage"]["name"], PATHINFO_EXTENSION));
    $target_file = $target_dir . time() . "." . $imageFileType;
    
    if (move_uploaded_file($_FILES["itemimage"]["tmp_name"], $target_file)) {
        // Insert into tbl_fooditem
        $query = "INSERT INTO tbl_fooditem (itemname, category, price, itemdescription, itemimage) 
                 VALUES ('$itemname', '$category', '$price', '$itemdescription', '$target_file')";
        
        if (mysqli_query($conn, $query)) {
            $itemid = mysqli_insert_id($conn);
            
            // Insert into tbl_fooditemdetailed
            $detailed_query = "INSERT INTO tbl_fooditemdetailed (itemid, itemdetailed, added_at) 
                             VALUES ('$itemid', '$itemdetailed', NOW())";
            
            if (mysqli_query($conn, $detailed_query)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error adding detailed info']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error adding food item']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error uploading image']);
    }
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Food Item</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Add New Food Item</h2>
        <form id="addFoodForm" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Item Name</label>
                <input type="text" class="form-control" name="itemname" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Category</label>
                <select class="form-select" name="category" required>
                    <option value="Appetizer">Appetizer</option>
                    <option value="Main Course">Main Course</option>
                    <option value="Dessert">Dessert</option>
                    <option value="Beverage">Beverage</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Price</label>
                <input type="number" class="form-control" name="price" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="itemdescription" required></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Detailed Information</label>
                <textarea class="form-control" name="itemdetailed" required></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Image</label>
                <input type="file" class="form-control" name="itemimage" accept="image/*" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Item</button>
        </form>
    </div>

    <script>
        document.getElementById('addFoodForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('add_food_item.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Food item added successfully!');
                    this.reset();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error adding food item');
            });
        });
    </script>
</body>
</html> 