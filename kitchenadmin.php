<?php
session_start();
if(!isset($_SESSION['uname'])){
    header("Location:'login.php'");
    exit;
}
$servername = "localhost";
$username = "kitchenadmin";
$password = "1234";
$database = "aromiq"; // Ensure this matches the database name you created

// Connect to MySQL server and select the database
$conn = mysqli_connect($servername, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
} 

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <script defer src="script.js"></script>
</head>
<body>
    <div class="bg-dark py-2 px-4 w-100 d-flex justify-content-between align-items-center">
        <h4 class="text-white mb-0">Kitchen Dashboard 🍳</h4>
        <a href="logout.php" class="btn btn-primary py-2 px-4" style="background-color: #fea116; border-color: #fea116;">Logout</a>
    </div>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 bg-light vh-100 d-flex flex-column align-items-center py-4">
                <div class="nav flex-column w-100">
                    <a href="#orders" class="nav-link text-dark">📦 View Orders</a>
                    <!--<a href="#todaysmenu" class="nav-link text-dark">📜 Today's Menu</a>-->
                    <a href="#transactions" class="nav-link text-dark">💳 View Transactions</a>
                    <a href="#users" class="nav-link text-dark">👤 View Users</a>
                    <a href="#editStatus" class="nav-link text-dark">✅ Edit Order's Status</a>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 bg-light py-4 " >
                <!-- Add this container for the header area -->
                <?php
// Fetch live orders
$orders_query = "
    SELECT 
        o.order_id,
        o.customer_name,
        o.table_number,
        o.order_status,
        o.order_date,
        sc.food_name,
        sc.quantity,
        sc.price
    FROM tbl_orders o
    JOIN tbl_shoppingcart sc ON o.order_id = sc.id
    ORDER BY o.order_date DESC";

$orders_result = mysqli_query($conn, $orders_query);

if ($orders_result) {
    echo '<section id="liveOrders" class="mb-5">
            <h5>Live Orders</h5>
            <table class="table table-bordered table-hover">
                <thead class="bg-dark text-light">
                    <tr>
                        <th>Order ID</th>
                        <th>Customer Name</th>
                        <th>Table</th>
                        <th>Items</th>
                        <th>Status</th>
                        <th>Order Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>';

    $current_order = null;
    $order_items = [];

    while ($row = mysqli_fetch_assoc($orders_result)) {
        if ($current_order !== $row['order_id']) {
            if ($current_order !== null) {
                // Print previous order
                outputOrderRow($current_order_data, $order_items);
            }
            $current_order = $row['order_id'];
            $current_order_data = [
                'order_id' => $row['order_id'],
                'customer_name' => $row['customer_name'],
                'table_number' => $row['table_number'],
                'status' => $row['order_status'],
                'order_date' => $row['order_date']
            ];
            $order_items = [];
        }
        $order_items[] = $row['quantity'] . 'x ' . $row['food_name'];
    }
    
    // Print last order
    if ($current_order !== null) {
        outputOrderRow($current_order_data, $order_items);
    }

    echo '</tbody></table></section>';
}

function outputOrderRow($order, $items) {
    echo '<tr>
            <td>' . htmlspecialchars($order['order_id']) . '</td>
            <td>' . htmlspecialchars($order['customer_name']) . '</td>
            <td>' . htmlspecialchars($order['table_number']) . '</td>
            <td>' . htmlspecialchars(implode(', ', $items)) . '</td>
            <td>
                <select class="form-control order-status" data-order-id="' . $order['order_id'] . '">
                    <option value="Pending" ' . ($order['status'] == 'Pending' ? 'selected' : '') . '>Pending</option>
                    <option value="In Progress" ' . ($order['status'] == 'In Progress' ? 'selected' : '') . '>In Progress</option>
                    <option value="Completed" ' . ($order['status'] == 'Completed' ? 'selected' : '') . '>Completed</option>
                    <option value="Cancelled" ' . ($order['status'] == 'Cancelled' ? 'selected' : '') . '>Cancelled</option>
                </select>
            </td>
            <td>' . date('H:i', strtotime($order['order_date'])) . '</td>
            <td>
                <button class="btn btn-sm btn-primary view-order" data-order-id="' . $order['order_id'] . '">View</button>
                <button class="btn btn-sm btn-success complete-order" data-order-id="' . $order['order_id'] . '">Complete</button>
            </td>
          </tr>';
}
?>
                <!-- Edit Menu List -->
                <section id="menuManagement" class="mb-5">
                    <h5>Edit Menu List</h5>
                    <?php
                    if (isset($_SESSION['message'])) {
                        echo '<div class="alert alert-success">' . $_SESSION['message'] . '</div>';
                        unset($_SESSION['message']); // Clear the message after displaying
                    }
                    ?>
                    <table class="table table-bordered table-hover">
                        <thead class="bg-dark text-light">
                            <tr>
                                <th>Item ID</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Fetch food items from database with category and image
                            $sql = "SELECT itemid, itemname, category, price, itemimage FROM tbl_fooditem ORDER BY itemid DESC";
                            $result = mysqli_query($conn, $sql);

                            if (mysqli_num_rows($result) > 0) {
                                while($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['itemid']) . "</td>";
                                    echo "<td><img src='images/" . htmlspecialchars($row['itemimage']) . "' alt='" . htmlspecialchars($row['itemname']) . "' style='width: 50px; height: 50px; object-fit: cover;'></td>";
                                    echo "<td>" . htmlspecialchars($row['itemname']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                                    echo "<td>₹" . number_format((float)$row['price'], 2) . "</td>";
                                    echo "<td>
                                            <a href='update.php?id=" . $row['itemid'] . "' class='btn btn-sm btn-primary'>Edit</a>
                                            <button onclick='deleteFood(" . $row['itemid'] . ")' class='btn btn-sm btn-danger'>Delete</button>
                                          </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center'>No food items found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </section>

<?php 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate inputs
    $name = mysqli_real_escape_string($conn, trim($_POST['itemname']));
    $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
    $description = mysqli_real_escape_string($conn, trim($_POST['itemdescription']));
    $category = mysqli_real_escape_string($conn, trim($_POST['category']));
    
    // Validate inputs
    $errors = [];
    if (empty($name)) $errors[] = "Food name is required";
    if ($price === false || $price <= 0) $errors[] = "Please enter a valid price";
    if (empty($description)) $errors[] = "Description is required";
    if (empty($category)) $errors[] = "Category is required";

    // Check if food item name already exists
    $check_stmt = $conn->prepare("SELECT itemid FROM tbl_fooditem WHERE itemname = ?");
    $check_stmt->bind_param("s", $name);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $errors[] = "A food item with this name already exists. Please use a different name.";
    }
    $check_stmt->close();

    // File upload handling
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!isset($_FILES['itemimage']) || $_FILES['itemimage']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Please select an image file";
    } else {
        $file_info = $_FILES['itemimage'];
        
        // Validate file type
        if (!in_array($file_info['type'], $allowed_types)) {
            $errors[] = "Only JPG, JPEG & PNG files are allowed";
        }
        
        // Validate file size
        if ($file_info['size'] > $max_size) {
            $errors[] = "File size must be less than 5MB";
        }
        
        // Create uploads/images directory if it doesn't exist
        $upload_dir = 'images/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Generate unique filename
        $file_extension = pathinfo($file_info['name'], PATHINFO_EXTENSION);
        $new_filename = uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;
    }

    $detailed_description = mysqli_real_escape_string($conn, trim($_POST['itemdetailed']));
    
    if (empty($detailed_description)) $errors[] = "Detailed description is required";

    // If no errors, proceed with insert
    if (empty($errors)) {
        try {
            // Start transaction
            $conn->begin_transaction();
            
            // Move uploaded file and insert into tbl_fooditem
            if (move_uploaded_file($file_info['tmp_name'], $upload_path)) {
                // Insert into tbl_fooditem
                $stmt = $conn->prepare("INSERT INTO tbl_fooditem (itemname, price, itemdescription, itemdetailed, category, itemimage) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sdsss", $name, $price, $description, $category, $new_filename);
                
                //if ($stmt->execute()) {
                   // $itemid = $stmt->insert_id;
                    
                    // Insert into tbl_fooditemdetailed
                    //$detailed_stmt = $conn->prepare("INSERT INTO tbl_fooditemdetailed (itemid, ) VALUES (?, ?)");
                    //$detailed_stmt->bind_param("is", $itemid, $detailed_description);
                    
                    //if ($detailed_stmt->execute()) {
                      //  $conn->commit();
                        //echo '<div class="alert alert-success">Food item added successfully!</div>';
                    //} else {
                      //  throw new Exception("Error inserting detailed description");
                    //}
                   // $detailed_stmt->close();
                //} else {
                  //  throw new Exception("Error inserting food item");
                //}
                $stmt->close();
            } else {
                throw new Exception("Error uploading file");
            }
        } catch (Exception $e) {
            $conn->rollback();
            echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
        }
    } else {
        // Display errors
        echo '<div class="alert alert-danger">';
        foreach ($errors as $error) {
            echo $error . '<br>';
        }
        echo '</div>';
    }
}?>
                 <!-- Add Food Items -->
                 <section id="addFood" >
                    <h5>Add Food Items</h5>
                    <form method="POST" enctype="multipart/form-data" class="bg-white p-4 rounded shadow-sm">
                        <div class="mb-2">
                            <input type="text" class="form-control" placeholder="Food Name" name="itemname" required 
                                   pattern="[A-Za-z0-9\s]+" title="Only letters, numbers and spaces allowed">
                        </div>
                        <div class="mb-2">
                            <select class="form-control" name="category" required>
                                <option value="">Select Category</option>
                                <option value="Starters">Starters</option>
                                <option value="Main Course">Main Course</option>
                                <option value="Desserts">Desserts</option>
                                <option value="Beverages">Beverages</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <input type="number" class="form-control" placeholder="Price" name="price" 
                                   step="0.01" min="25" max="1000.00" required>
                        </div>
                        <div class="mb-2">
                            <textarea class="form-control" placeholder="Basic Description" name="itemdescription" 
                                      required maxlength="500"></textarea>
                            <small class="text-muted">Brief description that appears in the menu</small>
                        </div>
                        <div class="mb-2">
                            <textarea class="form-control" placeholder="Detailed Description" name="itemdetailed" 
                                      required maxlength="1000" rows="4"></textarea>
                            <small class="text-muted">Detailed description that appears in the popup</small>
                        </div>
                        <div class="mb-2">
                            <input type="file" class="form-control" name="itemimage" required 
                                   accept=".jpg,.jpeg,.png">
                            <small class="text-muted">Max file size: 5MB. Allowed types: JPG, JPEG, PNG</small>
                        </div>
                        <button type="submit" class="btn btn-primary" style="background-color: #fea116; border-color: #fea116;">Add Item</button>
                    </form>
                </section>
            </div>
            <!-- Kitchen Controls -->
            
            <script>
    function deleteFood(itemId) {
        if (confirm('Are you sure you want to delete this item?')) {
            fetch('delete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'itemid=' + itemId
            })
            .then(response => response.text())
            .then(data => {
                alert(data);
                location.reload(); // Reload the page to update the table
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error deleting item');
            });
        }
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
