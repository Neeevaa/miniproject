<?php
session_start();
if(!isset($_SESSION['uname'])){
    header("Location:login.php");
    exit;
}

// Helper function to get Bootstrap badge class for order status
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'Pending':
            return 'bg-warning text-dark';
        case 'Preparing':
            return 'bg-info text-dark';
        case 'Ready':
            return 'bg-primary';
        case 'Plating':
            return 'bg-success';
        case 'Served':
            return 'bg-success text-white';
        case 'Cancelled':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="script.js"></script>
    <script src="https://kit.fontawesome.com/07264d6aa5.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="bg-dark py-2 px-4 w-100 d-flex justify-content-between align-items-center">
        <h4 class="text-white mb-0">Admin Dashboard 🛠️</h4>
        <a href="logout.php" class="btn btn-primary py-2 px-4" style="background-color: #fea116; border-color: #fea116;">Logout</a>
    </div>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 bg-dark vh-100 d-flex flex-column align-items-center py-4">
            <div class="nav flex-column w-100">
                    <a href="#viewOrders" class="nav-link text-light">📦 View Orders</a>
                    <a href="#menuManagement" class="nav-link text-light">📜 Today's Menu</a>
                    <a href="#topFoods" class="nav-link text-light">📊 Top Ordered Foods</a>
                    <a href="#payments" class="nav-link text-light">💰 Payments</a>
                    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Add the bookings link to your sidebar
    const sidebar = document.querySelector('.nav.flex-column.w-100');
    const bookingsLink = document.createElement('a');
    bookingsLink.href = '#bookings';
    bookingsLink.className = 'nav-link text-light';
    bookingsLink.innerHTML = '📅 Table Reservations';
    sidebar.appendChild(bookingsLink);
    
    // Handle section visibility
    const sections = document.querySelectorAll('section');
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = this.getAttribute('href').substring(1);
            
            sections.forEach(section => {
                if (section.id === target) {
                    section.style.display = 'block';
                } else {
                    section.style.display = 'none';
                }
            });
        });
    });
    
    // Handle booking status changes
    document.querySelectorAll('.booking-status').forEach(select => {
        select.addEventListener('change', function() {
            const bookingId = this.getAttribute('data-booking-id');
            const status = this.value;
            
            updateBookingStatus(bookingId, status);
        });
    });
    
    // Handle confirm booking button
    document.querySelectorAll('.confirm-booking').forEach(button => {
        button.addEventListener('click', function() {
            const bookingId = this.getAttribute('data-booking-id');
            updateBookingStatus(bookingId, 'Confirmed');
        });
    });
    
    // Handle cancel booking button
    document.querySelectorAll('.cancel-booking').forEach(button => {
        button.addEventListener('click', function() {
            const bookingId = this.getAttribute('data-booking-id');
            if (confirm('Are you sure you want to cancel this booking?')) {
                updateBookingStatus(bookingId, 'Cancelled');
            }
        });
    });
    
    function updateBookingStatus(bookingId, status) {
        fetch('update_booking_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `booking_id=${bookingId}&status=${status}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message || 'An error occurred. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
});
</script><body>
    </div>        </div>

            
            <!-- Main Content -->
            <div class="col-md-10 bg-light py-4">
                <!-- Add this container for the header area -->
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
                                <th>Detailed</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Fetch food items from database with category and image
                            $sql = "SELECT f.itemid, f.itemname, f.category, f.price, f.itemimage, CASE WHEN d.ditemid IS NOT NULL THEN 'Yes' ELSE 'No' END as has_detailed 
                                    FROM tbl_fooditem f 
                                    LEFT JOIN tbl_fooditemdetailed d ON f.itemid = d.itemid 
                                    ORDER BY f.itemid DESC";
                            $result = mysqli_query($conn, $sql);

                            if (mysqli_num_rows($result) > 0) {
                                while($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['itemid']) . "</td>";
                                    echo "<td><img src='images/" . htmlspecialchars( $row['itemimage']) . "' alt='" . htmlspecialchars($row['itemname']) . "' style='width: 50px; height: 50px; object-fit: cover;'></td>";
                                    echo "<td>" . htmlspecialchars($row['itemname']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                                    echo "<td>₹" . number_format((float)$row['price'], 2) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['has_detailed']) . "</td>";
                                    echo "<td>
                                            <a href='update.php?id=" . $row['itemid'] . "' class='btn btn-sm btn-primary'>Edit</a>
                                            <button onclick='deleteFood(" . $row['itemid'] . ")' class='btn btn-sm btn-danger'>Delete</button>
                                          </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' class='text-center'>No food items found</td></tr>";
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
                            <textarea class="form-control" placeholder="Short Description" name="itemdescription" 
                                      required maxlength="500"></textarea>
                            <small class="text-muted">Brief description for menu display (max 500 chars)</small>
                        </div>
                        <div class="mb-2">
                            <textarea class="form-control" placeholder="Detailed Description" name="itemdetailed" 
                                      rows="4" maxlength="2000"></textarea>
                            <small class="text-muted">Detailed description including ingredients, preparation methods, etc. (max 2000 chars)</small>
                        </div>
                        <div class="mb-2">
                            <input type="file" class="form-control" name="itemimage" required 
                                   accept=".jpg,.jpeg,.png">
                            <small class="text-muted">Max file size: 5MB. Allowed types: JPG, JPEG, PNG</small>
                        </div>
                        <button type="submit" class="btn btn-primary" style="background-color: #fea116; border-color: #fea116;">Add Item</button>
                    </form>
                </section>

                <section id="bookings" class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5>Table Reservations</h5>
        <div>
            <button class="btn btn-sm btn-outline-primary export-bookings">Export to CSV</button>
            <select class="form-select form-select-sm d-inline-block ms-2" style="width: auto;" id="bookingStatusFilter">
                <option value="all">All Bookings</option>
                <option value="Pending">Pending</option>
                <option value="Confirmed">Confirmed</option>
                <option value="Cancelled">Cancelled</option>
            </select>
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="table table-bordered table-hover" id="bookingsTable">
            <thead class="bg-dark text-light">
                <tr>
                    <th>Booking ID</th>
                    <th>Name</th>
                    <th>Table</th>
                    <th>Date & Time</th>
                    <th>People</th>
                    <th>Special Request</th>
                    <th>Special Option</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch bookings from database
                $bookings_query = "SELECT * FROM tbl_bookings ORDER BY datetime ASC";
                $bookings_result = mysqli_query($conn, $bookings_query);

                if ($bookings_result && mysqli_num_rows($bookings_result) > 0) {
                    while($booking = mysqli_fetch_assoc($bookings_result)) {
                        echo "<tr data-status='" . $booking['status'] . "'>";
                        echo "<td>" . htmlspecialchars($booking['booking_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($booking['name']) . "</td>";
                        echo "<td>" . htmlspecialchars($booking['table_number']) . "</td>";
                        echo "<td>" . date('Y-m-d H:i', strtotime($booking['datetime'])) . "</td>";
                        echo "<td>" . htmlspecialchars($booking['people_count']) . "</td>";
                        echo "<td>" . htmlspecialchars($booking['special_request']) . "</td>";
                        echo "<td>" . htmlspecialchars($booking['special_option']) . "</td>";
                        echo "<td>
                                <select class='form-control booking-status' data-booking-id='" . $booking['booking_id'] . "'>
                                    <option value='Pending' " . ($booking['status'] == 'Pending' ? 'selected' : '') . ">Pending</option>
                                    <option value='Confirmed' " . ($booking['status'] == 'Confirmed' ? 'selected' : '') . ">Confirmed</option>
                                    <option value='Cancelled' " . ($booking['status'] == 'Cancelled' ? 'selected' : '') . ">Cancelled</option>
                                </select>
                              </td>";
                        echo "<td>" . date('Y-m-d H:i', strtotime($booking['created_at'])) . "</td>";
                        echo "<td>
                                <button class='btn btn-sm btn-success confirm-booking' data-booking-id='" . $booking['booking_id'] . "'>Confirm</button>
                                <button class='btn btn-sm btn-danger cancel-booking' data-booking-id='" . $booking['booking_id'] . "'>Cancel</button>
                                <button class='btn btn-sm btn-primary send-email' data-booking-id='" . $booking['booking_id'] . "'>Notify</button>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='10' class='text-center'>No bookings found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle booking status filter
    document.getElementById('bookingStatusFilter').addEventListener('change', function() {
        const status = this.value;
        const rows = document.querySelectorAll('#bookingsTable tbody tr');
        
        rows.forEach(row => {
            if (status === 'all' || row.getAttribute('data-status') === status) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
    
    // Handle booking status changes
    document.querySelectorAll('.booking-status').forEach(select => {
        select.addEventListener('change', function() {
            const bookingId = this.getAttribute('data-booking-id');
            const status = this.value;
            
            updateBookingStatus(bookingId, status);
        });
    });
    
    // Handle confirm booking button
    document.querySelectorAll('.confirm-booking').forEach(button => {
        button.addEventListener('click', function() {
            const bookingId = this.getAttribute('data-booking-id');
            updateBookingStatus(bookingId, 'Confirmed');
        });
    });
    
    // Handle cancel booking button
    document.querySelectorAll('.cancel-booking').forEach(button => {
        button.addEventListener('click', function() {
            const bookingId = this.getAttribute('data-booking-id');
            if (confirm('Are you sure you want to cancel this booking?')) {
                updateBookingStatus(bookingId, 'Cancelled');
            }
        });
    });
    
    // Handle export to CSV
    document.querySelector('.export-bookings').addEventListener('click', function() {
        window.location.href = 'export_bookings.php';
    });
    
    // Handle send email notification
    document.querySelectorAll('.send-email').forEach(button => {
        button.addEventListener('click', function() {
            const bookingId = this.getAttribute('data-booking-id');
            notifyCustomer(bookingId);
        });
    });
    
    function updateBookingStatus(bookingId, status) {
        fetch('update_booking_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `booking_id=${bookingId}&status=${status}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                // Update the row's data-status attribute
                const row = document.querySelector(`tr [data-booking-id="${bookingId}"]`).closest('tr');
                row.setAttribute('data-status', status);
                
                // Reapply filter if active
                const filter = document.getElementById('bookingStatusFilter').value;
                if (filter !== 'all' && filter !== status) {
                    row.style.display = 'none';
                }
            } else {
                alert(data.message || 'An error occurred. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }

    // Add this function to send email notification
    function notifyCustomer(bookingId) {
        const button = event.target;
        const originalText = button.textContent;

        // Show loading state
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...';

        fetch('notify_booking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `booking_id=${bookingId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success state
                button.classList.remove('btn-info');
                button.classList.add('btn-success');
                button.innerHTML = '<i class="fas fa-check"></i> Sent';

                // Show toast notification
                showToast('Success', 'Booking confirmation email sent successfully', 'success');

                // Reset button after 3 seconds
                setTimeout(() => {
                    button.disabled = false;
                    button.classList.remove('btn-success');
                    button.classList.add('btn-info');
                    button.textContent = originalText;
                }, 3000);
            } else {
                throw new Error(data.message || 'Failed to send notification');
            }
        })
        .catch(error => {
            console.error('Error:', error);

            // Reset button to error state
            button.disabled = false;
            button.classList.remove('btn-info');
            button.classList.add('btn-danger');
            button.textContent = 'Failed';

            // Show error toast
            showToast('Error', 'Failed to send notification. Please try again.', 'danger');

            // Reset button after 3 seconds
            setTimeout(() => {
                button.classList.remove('btn-danger');
                button.classList.add('btn-info');
                button.textContent = originalText;
            }, 3000);
        });
    }
});
</script>
                <!-- View Orders -->
<section id="viewOrders" class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5>Current Orders</h5>
        <div>
            <select class="form-select form-select-sm d-inline-block" style="width: auto;" id="orderStatusFilter">
                <option value="all">All Orders</option>
                <option value="Pending">Pending</option>
                <option value="Preparing">Preparing</option>
                <option value="Ready">Ready</option>
                <option value="Plating">Plating</option>
                <option value="Served">Served</option>
                <option value="Cancelled">Cancelled</option>
            </select>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="bg-dark text-light">
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Table No.</th>
                    <th>Order Time</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch orders from database
                $orders_query = "SELECT o.order_id, o.customer_name, o.table_number, o.status, 
                                o.timestamp as order_date, o.total as total_amount
                                FROM tbl_orders o 
                                ORDER BY o.timestamp DESC";

                $orders_result = mysqli_query($conn, $orders_query);

                if ($orders_result && mysqli_num_rows($orders_result) > 0) {
                    while($order = mysqli_fetch_assoc($orders_result)) {
                        echo "<tr data-status='" . htmlspecialchars($order['status']) . "'>";
                        echo "<td>" . htmlspecialchars($order['order_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($order['customer_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($order['table_number']) . "</td>";
                        echo "<td>" . date('H:i', strtotime($order['order_date'])) . "</td>";
                        echo "<td>
                                <span class='order-status-badge badge " . getStatusBadgeClass($order['status']) . "' 
                                      data-order-id='" . $order['order_id'] . "'>
                                    " . htmlspecialchars($order['status']) . "
                                </span>
                              </td>";
                        echo "<td>
                                <button class='btn btn-sm btn-primary view-order' data-order-id='" . $order['order_id'] . "'>View</button>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center'>No orders found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle order status filter
    document.getElementById('orderStatusFilter').addEventListener('change', function() {
        const status = this.value;
        const rows = document.querySelectorAll('#viewOrders tbody tr');
        
        rows.forEach(row => {
            if (status === 'all' || row.getAttribute('data-status') === status) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
    
    // Handle order status changes
    document.querySelectorAll('.order-status').forEach(select => {
        select.addEventListener('change', function() {
            const orderId = this.getAttribute('data-order-id');
            const status = this.value;
            
            updateOrderStatus(orderId, status);
        });
    });
    
    function updateOrderStatus(orderId, status) {
        fetch('update_order_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `order_id=${orderId}&status=${status}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the row's data-status attribute in the orders table
                const row = document.querySelector(`span[data-order-id="${orderId}"]`).closest('tr');
                row.setAttribute('data-status', status);
                
                // Update the status text in the table
                const statusBadge = document.querySelector(`span[data-order-id="${orderId}"]`);
                if (statusBadge) {
                    statusBadge.textContent = status;
                    
                    // Update badge color
                    statusBadge.className = 'order-status-badge badge';
                    switch (status) {
                        case 'Pending':
                            statusBadge.classList.add('bg-warning', 'text-dark');
                            break;
                        case 'Preparing':
                            statusBadge.classList.add('bg-info', 'text-dark');
                            break;
                        case 'Ready':
                            statusBadge.classList.add('bg-primary');
                            break;
                        case 'Plating':
                            statusBadge.classList.add('bg-success');
                            break;
                        case 'Served':
                            statusBadge.classList.add('bg-success', 'text-white');
                            break;
                        case 'Cancelled':
                            statusBadge.classList.add('bg-danger');
                            break;
                        default:
                            statusBadge.classList.add('bg-secondary');
                    }
                }
            } else {
                alert(data.message || 'An error occurred. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
});
</script>
                <!-- ... existing sections ... -->
                
                <!-- Payments Section -->
                <section id="payments" class="mb-5" style="display: none;">
                    <h5>Payment Transactions</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="bg-dark text-light">
                                <tr>
                                    <th>Bill No</th>
                                    <th>Customer Name</th>
                                    <th>Payment Mode</th>
                                    <th>Total Amount</th>
                                    <th>Payment Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Fetch payment transactions from database
                                $sql = "SELECT order_id, customer_name, payment_method, total, payment_status 
                                        FROM tbl_orders 
                                        ORDER BY order_id DESC";
                                $result = mysqli_query($conn, $sql);

                                if (mysqli_num_rows($result) > 0) {
                                    while($row = mysqli_fetch_assoc($result)) {
                                        // Determine badge class for payment status
                                        $statusClass = '';
                                        switch($row['payment_status']) {
                                            case 'Paid':
                                                $statusClass = 'bg-success';
                                                break;
                                            case 'Pending':
                                                $statusClass = 'bg-warning text-dark';
                                                break;
                                            case 'Failed':
                                                $statusClass = 'bg-danger';
                                                break;
                                            default:
                                                $statusClass = 'bg-secondary';
                                        }
                                        
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['order_id']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['customer_name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['payment_method']) . "</td>";
                                        echo "<td>₹" . number_format($row['total'], 2) . "</td>";
                                        echo "<td><span class='badge {$statusClass}'>" . htmlspecialchars($row['payment_status']) . "</span></td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5' class='text-center'>No payment transactions found</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
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
                <section id="topFoods" class="mb-5">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5>Top 3 Most Ordered Foods</h5>
                        <div>
                            <select class="form-select form-select-sm d-inline-block" style="width: auto;" id="topFoodsPeriod">
                                <option value="all">All Time</option>
                                <option value="month">This Month</option>
                                <option value="week">This Week</option>
                                <option value="day">Today</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <canvas id="topFoodsChart" width="400" height="200"></canvas>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header bg-dark text-white">
                                    Top 5 Items
                                </div>
                                <ul class="list-group list-group-flush" id="topFoodsList">
                                    <?php
                                    // Fetch top ordered items
                                    $top_items_query = "SELECT food_name, SUM(quantity) as total_quantity 
                                                       FROM tbl_order_items 
                                                       GROUP BY food_name 
                                                       ORDER BY total_quantity DESC 
                                                       LIMIT 5";
                                    $top_items_result = mysqli_query($conn, $top_items_query);

                                    if ($top_items_result && mysqli_num_rows($top_items_result) > 0) {
                                        while($item = mysqli_fetch_assoc($top_items_result)) {
                                            echo "<li class='list-group-item d-flex justify-content-between align-items-center'>";
                                            echo htmlspecialchars($item['food_name']);
                                            echo "<span class='badge bg-primary rounded-pill'>" . $item['total_quantity'] . "</span>";
                                            echo "</li>";
                                        }
                                    } else {
                                        echo "<li class='list-group-item'>No data available</li>";
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>

                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Top foods chart
                    <?php
                    // Get data for chart
                    $chart_data_query = "SELECT food_name, SUM(quantity) as total_quantity 
                                         FROM tbl_order_items 
                                         GROUP BY food_name 
                                         ORDER BY total_quantity DESC 
                                         LIMIT 10";
                    $chart_data_result = mysqli_query($conn, $chart_data_query);
                    
                    $labels = [];
                    $data = [];
                    
                    if ($chart_data_result && mysqli_num_rows($chart_data_result) > 0) {
                        while($item = mysqli_fetch_assoc($chart_data_result)) {
                            $labels[] = $item['food_name'];
                            $data[] = $item['total_quantity'];
                        }
                    }
                    ?>
                    
                    // Chart data
                    const foodLabels = <?php echo json_encode($labels); ?>;
                    const foodData = <?php echo json_encode($data); ?>;
                    
                    // Create chart
                    const ctx = document.getElementById('topFoodsChart').getContext('2d');
                    const topFoodsChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: foodLabels,
                            datasets: [{
                                label: 'Orders',
                                data: foodData,
                                backgroundColor: 'rgba(254, 161, 22, 0.7)',
                                borderColor: 'rgba(254, 161, 22, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Quantity Ordered'
                                    }
                                },
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Food Items'
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                title: {
                                    display: true,
                                    text: 'Top Ordered Food Items'
                                }
                            }
                        }
                    });
                    
                    // Period filter for top foods
                    document.getElementById('topFoodsPeriod').addEventListener('change', function() {
                        const period = this.value;
                        
                        // In a real implementation, you would fetch new data with AJAX
                        // For now, let's simulate with random data
                        fetch('get_top_foods.php?period=' + period)
                            .then(response => response.json())
                            .then(data => {
                                // Update chart
                                topFoodsChart.data.labels = data.labels;
                                topFoodsChart.data.datasets[0].data = data.data;
                                topFoodsChart.update();
                                
                                // Update list
                                const list = document.getElementById('topFoodsList');
                                list.innerHTML = '';
                                
                                data.labels.forEach((label, index) => {
                                    if (index < 5) {  // Only top 5 for the list
                                        const li = document.createElement('li');
                                        li.className = 'list-group-item d-flex justify-content-between align-items-center';
                                        li.innerHTML = `
                                            ${label}
                                            <span class="badge bg-primary rounded-pill">${data.data[index]}</span>
                                        `;
                                        list.appendChild(li);
                                    }
                                });
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('Error fetching data');
                            });
                    });
                });
                </script>
            </div>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-dark text-white">
            <h5 class="modal-title" id="orderDetailsModalLabel">Order Details</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row mb-3">
              <div class="col-md-6">
                <h6>Order Information</h6>
                <table class="table table-sm">
                  <tr>
                    <th>Order ID:</th>
                    <td id="modal-order-id"></td>
                  </tr>
                  <tr>
                    <th>Customer:</th>
                    <td id="modal-customer-name"></td>
                  </tr>
                  <tr>
                    <th>Email:</th>
                    <td id="modal-customer-email"></td>
                  </tr>
                  <tr>
                    <th>Phone:</th>
                    <td id="modal-customer-phone"></td>
                  </tr>
                </table>
              </div>
              <div class="col-md-6">
                <h6>Order Details</h6>
                <table class="table table-sm">
                  <tr>
                    <th>Table Number:</th>
                    <td id="modal-table-number"></td>
                  </tr>
                  <tr>
                    <th>Date & Time:</th>
                    <td id="modal-timestamp"></td>
                  </tr>
                  <tr>
                    <th>Payment Method:</th>
                    <td id="modal-payment-method"></td>
                  </tr>
                  <tr>
                    <th>Payment Status:</th>
                    <td id="modal-payment-status"></td>
                  </tr>
                </table>
              </div>
            </div>
            
            <h6>Order Items</h6>
            <div class="table-responsive">
              <table class="table table-bordered table-sm">
                <thead class="table-light">
                  <tr>
                    <th>Item</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                  </tr>
                </thead>
                <tbody id="modal-order-items">
                  <!-- Order items will be added here dynamically -->
                </tbody>
                <tfoot>
                  <tr>
                    <th colspan="3" class="text-end">Subtotal:</th>
                    <td id="modal-subtotal"></td>
                  </tr>
                  <tr>
                    <th colspan="3" class="text-end">Tax:</th>
                    <td id="modal-tax"></td>
                  </tr>
                  <tr class="table-active">
                    <th colspan="3" class="text-end">Total:</th>
                    <td id="modal-total" class="fw-bold"></td>
                  </tr>
                </tfoot>
              </table>
            </div>
            
            <div class="mt-3">
              <h6>Update Order Status</h6>
              <div class="d-flex gap-2">
                <select id="modal-order-status" class="form-select">
                  <option value="Pending">Pending</option>
                  <option value="Preparing">Preparing</option>
                  <option value="Ready">Ready</option>
                  <option value="Plating">Plating</option>
                  <option value="Served">Served</option>
                  <option value="Cancelled">Cancelled</option>
                </select>
                <button id="modal-update-status" class="btn btn-primary">Update</button>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add this JavaScript right before the closing </body> tag -->
    <script>
    // Add Bootstrap JS if not already included
    if (typeof bootstrap === 'undefined') {
        const bootstrapScript = document.createElement('script');
        bootstrapScript.src = 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js';
        document.body.appendChild(bootstrapScript);
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Modal instance
        let orderModal;
        
        // Initialize once DOM is fully loaded or Bootstrap is available
        function initializeModal() {
            if (typeof bootstrap !== 'undefined') {
                orderModal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
                
                // Set up event handlers for view buttons
                setupViewButtons();
            } else {
                // If Bootstrap isn't loaded yet, wait a bit and try again
                setTimeout(initializeModal, 200);
            }
        }
        
        initializeModal();
        
        // Function to set up view button handlers
        function setupViewButtons() {
            // Add click event listeners to all "View" buttons
            document.querySelectorAll('.view-order').forEach(button => {
                button.addEventListener('click', function() {
                    const orderId = this.getAttribute('data-order-id');
                    fetchOrderDetails(orderId);
                });
            });
        }
        
        // Function to fetch order details from server
        function fetchOrderDetails(orderId) {
            // Show loading state
            document.getElementById('modal-order-id').textContent = 'Loading...';
            
            // Fetch order details
            fetch('get_order_details.php?order_id=' + orderId)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        populateOrderModal(data.order, data.items);
                        orderModal.show();
                    } else {
                        alert('Error loading order details: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error fetching order details:', error);
                    alert('Error loading order details. Please try again.');
                });
        }
        
        // Function to populate modal with order details
        function populateOrderModal(order, items) {
            // Populate order information
            document.getElementById('modal-order-id').textContent = order.order_id;
            document.getElementById('modal-customer-name').textContent = order.customer_name;
            document.getElementById('modal-customer-email').textContent = order.customer_email;
            document.getElementById('modal-customer-phone').textContent = order.customer_phone;
            document.getElementById('modal-table-number').textContent = order.table_number;
            document.getElementById('modal-timestamp').textContent = new Date(order.timestamp).toLocaleString();
            document.getElementById('modal-payment-method').textContent = order.payment_method;
            document.getElementById('modal-payment-status').textContent = order.payment_status;
            
            // Set financial details
            document.getElementById('modal-subtotal').textContent = '₹' + parseFloat(order.subtotal).toFixed(2);
            document.getElementById('modal-tax').textContent = '₹' + parseFloat(order.tax).toFixed(2);
            document.getElementById('modal-total').textContent = '₹' + parseFloat(order.total).toFixed(2);
            
            // Set current status in dropdown
            const statusSelect = document.getElementById('modal-order-status');
            statusSelect.value = order.status;
            
            // Clear existing order items
            const orderItemsContainer = document.getElementById('modal-order-items');
            orderItemsContainer.innerHTML = '';
            
            // Add order items
            items.forEach(item => {
                const row = document.createElement('tr');
                const total = parseFloat(item.price) * parseInt(item.quantity);
                
                row.innerHTML = `
                    <td>${item.food_name}</td>
                    <td>₹${parseFloat(item.price).toFixed(2)}</td>
                    <td>${item.quantity}</td>
                    <td>₹${total.toFixed(2)}</td>
                `;
                
                orderItemsContainer.appendChild(row);
            });
            
            // Setup update status button
            const updateButton = document.getElementById('modal-update-status');
            updateButton.onclick = function() {
                const newStatus = statusSelect.value;
                updateOrderStatus(order.order_id, newStatus, true);
            };
        }
        
        // Function to update order status (enhanced version with modal support)
        function updateOrderStatus(orderId, status, fromModal = false) {
            fetch('update_order_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `order_id=${orderId}&status=${status}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // If update was triggered from modal, show feedback but don't close modal
                    if (fromModal) {
                        // Show success indicator
                        const updateButton = document.getElementById('modal-update-status');
                        const originalText = updateButton.textContent;
                        updateButton.textContent = 'Updated!';
                        updateButton.classList.add('btn-success');
                        
                        // Reset button after a brief delay
                        setTimeout(() => {
                            updateButton.textContent = originalText;
                            updateButton.classList.remove('btn-success');
                        }, 2000);
                    }
                    
                    // Update the row's data-status attribute in the orders table
                    const row = document.querySelector(`span[data-order-id="${orderId}"]`).closest('tr');
                    row.setAttribute('data-status', status);
                    
                    // Update the status text in the table
                    const statusBadge = document.querySelector(`span[data-order-id="${orderId}"]`);
                    if (statusBadge) {
                        statusBadge.textContent = status;
                        
                        // Update badge color
                        statusBadge.className = 'order-status-badge badge';
                        switch (status) {
                            case 'Pending':
                                statusBadge.classList.add('bg-warning', 'text-dark');
                                break;
                            case 'Preparing':
                                statusBadge.classList.add('bg-info', 'text-dark');
                                break;
                            case 'Ready':
                                statusBadge.classList.add('bg-primary');
                                break;
                            case 'Plating':
                                statusBadge.classList.add('bg-success');
                                break;
                            case 'Served':
                                statusBadge.classList.add('bg-success', 'text-white');
                                break;
                            case 'Cancelled':
                                statusBadge.classList.add('bg-danger');
                                break;
                            default:
                                statusBadge.classList.add('bg-secondary');
                        }
                    }
                    
                    // Reapply filter if active
                    const filter = document.getElementById('orderStatusFilter').value;
                    if (filter !== 'all' && filter !== status) {
                        row.style.display = 'none';
                    }
                } else {
                    alert(data.message || 'An error occurred. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        }
    });
    </script>

    <!-- Add this JavaScript function where you add other JavaScript in admin.php -->
    <!-- Add it near the end of the file, before </body> -->
    <script>
    // Booking management functions
    function loadBookings() {
        fetch('get_booking_details.php')
            .then(response => response.json())
            .then(data => {
                const bookingsTable = document.getElementById('bookingsTable');
                if (!bookingsTable) return;
                
                const tbody = bookingsTable.querySelector('tbody');
                tbody.innerHTML = '';
                
                if (data.success && data.bookings && data.bookings.length > 0) {
                    data.bookings.forEach(booking => {
                        const row = document.createElement('tr');
                        
                        // Format date and time
                        const bookingDate = new Date(booking.datetime);
                        const formattedDate = bookingDate.toLocaleDateString();
                        const formattedTime = bookingDate.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                        
                        // Create status badge
                        const statusClass = booking.status === 'Confirmed' ? 'bg-success' : 
                                            booking.status === 'Cancelled' ? 'bg-danger' : 'bg-warning';
                        
                        row.innerHTML = `
                            <td>${booking.booking_id}</td>
                            <td>${booking.customer_name}</td>
                            <td>${booking.table_number}</td>
                            <td>${booking.email}</td>
                            <td>${formattedDate} ${formattedTime}</td>
                            <td>${booking.guest_count}</td>
                            <td>${booking.special_requests || '-'}</td>
                            <td>${booking.special_option || '-'}</td>
                            <td><span class="badge ${statusClass}">${booking.status}</span></td>
                            <td>
                                <button class="btn btn-sm btn-success" onclick="updateBookingStatus(${booking.booking_id}, 'Confirmed')">Confirm</button>
                                <button class="btn btn-sm btn-danger" onclick="updateBookingStatus(${booking.booking_id}, 'Cancelled')">Cancel</button>
                                <button class="btn btn-sm btn-info" onclick="notifyCustomer(${booking.booking_id})">Notify</button>
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                    
                    // Show the bookings section
                    document.getElementById('bookingsSection').style.display = 'block';
                } else {
                    // No bookings or error
                    tbody.innerHTML = `<tr><td colspan="10" class="text-center">No bookings found</td></tr>`;
                }
            })
            .catch(error => {
                console.error('Error loading bookings:', error);
                const bookingsTable = document.getElementById('bookingsTable');
                if (bookingsTable) {
                    const tbody = bookingsTable.querySelector('tbody');
                    tbody.innerHTML = `<tr><td colspan="10" class="text-center text-danger">Failed to load bookings</td></tr>`;
                }
            });
    }

    // Update booking status function
    function updateBookingStatus(bookingId, status) {
        // Get button that was clicked
        const button = event.target;
        const originalText = button.textContent;
        
        // Show loading state
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
        
        fetch('update_booking_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `booking_id=${bookingId}&status=${status}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload bookings to show updated status
                loadBookings();
                
                // Show success message
                showToast('Success', `Booking status updated to ${status}`, 'success');
            } else {
                throw new Error(data.message || 'Failed to update status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            button.disabled = false;
            button.textContent = originalText;
            
            // Show error message
            showToast('Error', 'Failed to update booking status', 'danger');
        });
    }

    // Customer notification function
    function notifyCustomer(bookingId) {
        // Get button that was clicked
        const button = event.target;
        const originalText = button.textContent;
        
        // Show loading state
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...';
        
        fetch('notify_booking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `booking_id=${bookingId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success state
                button.classList.remove('btn-info');
                button.classList.add('btn-success');
                button.innerHTML = '<i class="fas fa-check"></i> Sent';
                
                // Show toast notification
                showToast('Success', 'Booking confirmation email sent successfully', 'success');
                
                // Reset button after 3 seconds
                setTimeout(() => {
                    button.disabled = false;
                    button.classList.remove('btn-success');
                    button.classList.add('btn-info');
                    button.textContent = originalText;
                }, 3000);
            } else {
                throw new Error(data.message || 'Failed to send notification');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            // Reset button to error state
            button.disabled = false;
            button.classList.remove('btn-info');
            button.classList.add('btn-danger');
            button.textContent = 'Failed';
            
            // Show error toast
            showToast('Error', 'Failed to send notification. Please try again.', 'danger');
            
            // Reset button after 3 seconds
            setTimeout(() => {
                button.classList.remove('btn-danger');
                button.classList.add('btn-info');
                button.textContent = originalText;
            }, 3000);
        });
    }

    // Helper function for toast notifications
    function showToast(title, message, type = 'info') {
        const toastContainer = document.getElementById('toastContainer') || createToastContainer();
        
        const toast = document.createElement('div');
        toast.className = `toast show bg-${type === 'success' ? 'success' : type === 'danger' ? 'danger' : 'info'} text-white`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        toast.innerHTML = `
            <div class="toast-header bg-${type === 'success' ? 'success' : type === 'danger' ? 'danger' : 'info'} text-white">
                <strong class="me-auto">${title}</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        `;
        
        toastContainer.appendChild(toast);
        
        // Remove toast after 5 seconds
        setTimeout(() => {
            toast.remove();
        }, 5000);
    }

    function createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        container.style.zIndex = '1050';
        document.body.appendChild(container);
        return container;
    }

    // Add event listeners when DOM is fully loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Add click event for the bookings tab
        const bookingsLink = document.querySelector('a[href="#bookings"]');
        if (bookingsLink) {
            bookingsLink.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Hide other sections
                document.querySelectorAll('.dashboard-section').forEach(section => {
                    section.style.display = 'none';
                });
                
                // Show bookings section
                const bookingsSection = document.getElementById('bookingsSection');
                if (bookingsSection) {
                    bookingsSection.style.display = 'block';
                    
                    // Load bookings data
                    loadBookings();
                }
            });
        }
        
        // Load bookings data if we're on the bookings section
        if (window.location.hash === '#bookings') {
            loadBookings();
        }
    });
    </script>

    <!-- Add this HTML section where you want to display the bookings table 
    <div id="bookingsSection" class="container-fluid dashboard-section" style="display: none;">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Table Reservations</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="bookingsTable" class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Booking ID</th>
                                        <th>Name</th>
                                        <th>Table</th>
                                        <th>Email</th>
                                        <th>Date & Time</th>
                                        <th>People</th>
                                        <th>Special Request</th>
                                        <th>Event</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                     Booking data will be loaded here 
                                    <tr>
                                        <td colspan="10" class="text-center">Loading bookings...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>-->

    <!-- Add this at the end of your body -->
    <div id="toastContainer" class="toast-container position-fixed bottom-0 end-0 p-3"></div>
</body>
</html>
