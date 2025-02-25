<?php
session_start();
if(!isset($_SESSION['uname'])){
    header("Location:'login.php'");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <script defer src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="script.js"></script>
</head>
<body>
    <div class="bg-dark py-2 px-4 w-100 d-flex justify-content-between align-items-center">
        <h4 class="text-white mb-0">Admin Dashboard 🛠️</h4>
        <a href="logout.php" class="btn btn-primary py-2 px-4" style="background-color: #fea116; border-color: #fea116;">Logout</a>
    </div>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 bg-light vh-100 d-flex flex-column align-items-center py-4">
                <div class="nav flex-column w-100">
                    <a href="#viewOrders" class="nav-link text-dark">📦 View Orders</a>
                    <a href="#todaysmenu" class="nav-link text-dark">📜 Today's Menu</a>
                  <!--  <a href="#viewUsers" class="nav-link">👥 View Users</a>-->
                    <a href="#topFoods" class="nav-link text-dark">📊 Top Ordered Foods</a>
                </div>
            </div>

            
            <!-- Main Content -->
            <div class="col-md-10 bg-light py-4">
                <!-- Add this container for the header area -->
                
                    <h2>Welcome, Admin!</h2>
                
                <!-- Remove the old logout button from here -->
                <?php
$servername = "localhost";
$username = "admin";
$password = "1234";
$database = "aromiq"; // Ensure this matches the database name you created

// Connect to MySQL server and select the database
$conn = mysqli_connect($servername, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
} else {

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
        
        // Create images directory if it doesn't exist
        $upload_dir = 'images/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Generate unique filename
        $file_extension = pathinfo($file_info['name'], PATHINFO_EXTENSION);
        $new_filename = uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;
    }

    // If no errors, proceed with insert
    if (empty($errors)) {
        try {
            // Move uploaded file
            if (move_uploaded_file($file_info['tmp_name'], $upload_path)) {
                // Prepare SQL statement
                $stmt = $conn->prepare("INSERT INTO tbl_fooditem (itemname, price, itemdescription, category, itemimage) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sdsss", $name, $price, $description, $category, $new_filename);
                
                if ($stmt->execute()) {
                    echo '<div class="alert alert-success">Food item added successfully!</div>';
                } else {
                    echo '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
                }
                $stmt->close();
            } else {
                echo '<div class="alert alert-danger">Error uploading file</div>';
            }
        } catch (Exception $e) {
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
}
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

                 <!-- Add Food Items -->
                 <section id="addFood" class="mb-5">
                    <h5>Add Food Items</h5>
                    <form method="POST" enctype="multipart/form-data">
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
                            <small class="text-muted">Enter price between ₹0.01 and ₹1000.00</small>
                        </div>
                        <div class="mb-2">
                            <textarea class="form-control" placeholder="Description" name="itemdescription" 
                                      required maxlength="500"></textarea>
                        </div>
                        <div class="mb-2">
                            <input type="file" class="form-control" name="itemimage" required 
                                   accept=".jpg,.jpeg,.png">
                            <small class="text-muted">Max file size: 5MB. Allowed types: JPG, JPEG, PNG</small>
                        </div>
                        <button type="submit" class="btn btn-primary" style="background-color: #fea116; border-color: #fea116;">Add Item</button>
                    </form>
                </section>

                <!-- Manage Items 
                <section id="manageItems" class="mb-5">
                    <h5>Manage Items</h5>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Item ID</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>Pasta</td>
                                <td>Main Course</td>
                                <td>$8</td>
                                <td>Enabled</td>
                                <td>
                                    <button class="btn btn-sm btn-primary">Edit</button>
                                    <button class="btn btn-sm btn-secondary">Disable</button>
                                    <button class="btn btn-sm btn-secondary">Enable</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </section>-->

                <!-- View Orders -->
                <section id="viewOrders" class="mb-5">
                    <h5>View Orders</h5>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>101</td>
                                <td>John Doe</td>
                                <td>Pizza, Coke</td>
                                <td>In Progress</td>
                                <td>
                                    <button class="btn btn-sm btn-info">Track</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </section>

                <!-- View Previous Orders -->
                <section id="previousOrders" class="mb-5">
                    <h5>View Previous Orders</h5>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Date</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>100</td>
                                <td>Jane Smith</td>
                                <td>Burger, Fries</td>
                                <td>2025-01-10</td>
                                <td>$12</td>
                            </tr>
                        </tbody>
                    </table>
                </section>

                <!-- View Users 
                <section id="viewUsers" class="mb-5">
                    <h5>View Users</h5>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>John Doe</td>
                                <td>john@example.com</td>
                                <td>+123456789</td>
                            </tr>
                        </tbody>
                    </table>
                </section>-->

                <!-- Top Ordered Foods -->
                <section id="topFoods">
                    <h5>Top 3 Most Ordered Foods</h5>
                    <canvas id="topFoodsChart" width="400" height="200"></canvas>
                </section>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Data for the chart
        const foodData = {
            labels: ['Margherita Pizza', 'Pasta', 'Burger'],
            datasets: [{
                label: 'Orders',
                data: [120, 95, 80],
                fill: false,
                borderColor: '#fea116',
                tension: 0.4,
                pointBackgroundColor: '#0F172B',
                pointBorderColor: '#fea116',
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        };

        // Config for the chart
        const config = {
            type: 'line', // Changed from 'bar' to 'line'
            data: foodData,
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                }
            }
        };

        // Render the chart
        const ctx = document.getElementById('topFoodsChart').getContext('2d');
        new Chart(ctx, config);
    });
</script>
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
