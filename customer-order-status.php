<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'session-manager.php';
include 'connect.php';

// Add debugging to see what's happening with GET and SESSION
error_log("Accessed with GET: " . json_encode($_GET));
error_log("Session contains: " . json_encode($_SESSION));

// Database connection
$servername = "localhost";
$username = "kitchenadmin";
$password = "1234";
$database = "aromiq";

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get order number from URL or session


// Check for order number in GET parameters
if (isset($_GET['order_number']) && !empty($_GET['order_number'])) {
    $order_number = mysqli_real_escape_string($conn, $_GET['order_number']);
    // Store in session for persistence
    $_SESSION['order_number'] = $order_number;
} 
// Check for order number in SESSION if not in GET
elseif (isset($_SESSION['order_number']) && !empty($_SESSION['order_number'])) {
    $order_number = mysqli_real_escape_string($conn, $_SESSION['order_number']);
} else {
    // Log the issue for debugging
    error_log("No order number found in GET or SESSION");
    error_log("GET: " . json_encode($_GET));
    error_log("SESSION: " . json_encode($_SESSION));
    
    // Redirect with an error message
    header('Location: menu.php?error=no_order_number');
    exit();
}

// Log for debugging
error_log("Looking up order number: " . $order_number);

// Get order details
$query = "SELECT o.order_id, o.customer_name, o.table_number, o.status AS order_status, o.timestamp AS order_date 
          FROM tbl_orders o 
          WHERE o.order_id = ?";

$stmt = $conn->prepare($query);
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    die("Database query preparation failed");
}

$stmt->bind_param("s", $order_number);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Log the issue for debugging
    error_log("Order not found: " . $order_number);
    
    // Try a direct query to see what's in the database
    $debug_query = "SELECT order_id FROM tbl_orders";
    $debug_result = $conn->query($debug_query);
    $orders = [];
    while ($debug_row = $debug_result->fetch_assoc()) {
        $orders[] = $debug_row['order_id'];
    }
    error_log("Orders in database: " . json_encode($orders));
    
    // Redirect with an error message
    header('Location: menu.php?error=order_not_found');
    exit();
}

$row = $result->fetch_assoc();
$order_status = $row['order_status'];
$customer_name = $row['customer_name'];
$table_number = $row['table_number'];
$order_date = $row['order_date'];

// Get order items with complete information
$items_query = "SELECT item.id, item.food_name, item.quantity, item.price, 
                (item.quantity * item.price) as item_total 
                FROM tbl_order_items item 
                WHERE item.order_id = ?";
                
$items_stmt = $conn->prepare($items_query);
if (!$items_stmt) {
    error_log("Failed to prepare items query: " . $conn->error);
    die("Database error occurred");
}

$items_stmt->bind_param("s", $order_number);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

if ($items_result) {
    $order_items = [];
    while ($item = $items_result->fetch_assoc()) {
        $order_items[] = $item;
    }
    
    // Log the number of items found
    error_log("Found " . count($order_items) . " items for order #" . $order_number);
} else {
    error_log("Failed to fetch order items: " . $items_stmt->error);
    $order_items = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Status - Aromiq</title>
    
    <!-- Add Font Awesome -->
    <script src="https://kit.fontawesome.com/07264d6aa5.js" crossorigin="anonymous"></script>
    
    <!-- Your existing CSS links -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <style>
        .order-tracking-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .order-header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .order-header h3 {
            color: #fea116;
            margin-bottom: 15px;
            font-size: 24px;
            font-weight: bold;
        }

        .order-details {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
            margin-top: 15px;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 15px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .detail-item i {
            color: #fea116;
            font-size: 16px;
        }

        .detail-item span {
            color: #0f172b;
            font-weight: 500;
        }

        .progress-tracker {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px 0;
            position: relative;
        }

        .progress-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .step-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #f1f1f1;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }

        .step-icon i {
            font-size: 20px;
            color: #666;
            transition: all 0.3s ease;
        }

        .step-label {
            font-size: 14px;
            color: #666;
            transition: all 0.3s ease;
        }

        .progress-line {
            flex: 1;
            height: 3px;
            background: #f1f1f1;
            margin: 0 15px;
            position: relative;
            top: -35px;
            z-index: 0;
            transition: all 0.3s ease;
        }

        /* Active and completed states */
        .progress-step.active .step-icon {
            background: #fea116;
        }

        .progress-step.active .step-icon i {
            color: white;
        }

        .progress-step.active .step-label {
            color: #fea116;
            font-weight: bold;
        }

        .progress-line.completed {
            background: #fea116;
        }

        /* Animation */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .progress-step.active .step-icon {
            animation: pulse 2s infinite;
        }

        /* Order items styling */
        .order-items {
            margin: 20px 0;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

        .order-items h4 {
            color: #0f172b;
            margin-bottom: 15px;
        }

        .item-table {
            width: 100%;
            border-collapse: collapse;
        }

        .item-table th {
            background-color: #f8f9fa;
            color: #0f172b;
            padding: 10px;
            text-align: left;
        }

        .item-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .menu-btn {
            background: #fea116;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            margin-top: 20px;
            text-decoration: none;
            display: inline-block;
        }

        .menu-btn:hover {
            background: #e08e0b;
            color: white;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .progress-tracker {
                flex-direction: column;
                gap: 20px;
            }

            .progress-line {
                width: 3px;
                height: 30px;
                margin: 10px 0;
                top: 0;
            }

            .step-label {
                margin-top: 5px;
            }

            .order-details {
                flex-direction: column;
                align-items: center;
                gap: 15px;
            }
            
            .detail-item {
                width: 100%;
                max-width: 250px;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="order-tracking-container">
            <div class="order-header">
                <h3>ORDER #<?php echo htmlspecialchars($order_number); ?></h3>
                <div class="order-details">
                    <div class="detail-item">
                        <i class="fas fa-user"></i>
                        <span><?php echo htmlspecialchars($customer_name); ?></span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-chair"></i>
                        <span>Table <?php echo htmlspecialchars($table_number); ?></span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-clock"></i>
                        <span>Expected: <?php echo date('H:i', strtotime($order_date . ' +20 minutes')); ?></span>
                    </div>
                </div>
                <div class="current-status text-center mb-4">
                    <span class="badge bg-primary p-2">Current Status: <span class="current-status-text"><?php echo htmlspecialchars($order_status); ?></span></span>
                </div>
            </div>

            <div class="progress-tracker">
                <?php
                $statuses = ['Pending', 'Preparing', 'Ready', 'Plating', 'Served'];
                $currentIndex = array_search($order_status, $statuses);
                
                if ($currentIndex === false) {
                    $currentIndex = 0; // Default to Pending if status not found
                    error_log("Unknown order status: $order_status");
                }
                
                foreach ($statuses as $index => $status):
                    $isActive = $index <= $currentIndex;
                    $isCompleted = $index < $currentIndex;
                ?>
                    <div class="progress-step <?php echo $isActive ? 'active' : ''; ?>">
                        <div class="step-icon">
                            <i class="fas <?php
                                switch($status) {
                                    case 'Pending':
                                        echo 'fa-clipboard-list';
                                        break;
                                    case 'Preparing':
                                        echo 'fa-utensils';
                                        break;
                                    case 'Ready':
                                        echo 'fa-check-circle';
                                        break;
                                    case 'Plating':
                                        echo 'fa-concierge-bell';
                                        break;
                                    case 'Served':
                                        echo 'fa-smile';
                                        break;
                                }
                            ?>"></i>
                        </div>
                        <div class="step-label"><?php echo $status; ?></div>
                    </div>
                    <?php if ($index < count($statuses) - 1): ?>
                        <div class="progress-line <?php echo $isCompleted ? 'completed' : ''; ?>"></div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <!-- Order Items Section -->
            <div class="order-items">
                <h4>Order Items</h4>
                <table class="item-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['food_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                            <td>₹<?php echo number_format($item['price'], 2); ?></td>
                            <td>₹<?php echo number_format($item['item_total'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="text-center">
                <a href="feedback.php?order_id=<?php echo urlencode($order_number); ?>" class="menu-btn">
                    <i class="fas fa-comment"></i> Provide Feedback
                </a>
            </div>
        </div>
    </div>

    <script>
        function updateOrderProgress() {
            const orderId = '<?php echo $order_number; ?>';
            fetch(`get_order_status_update.php?order_id=${encodeURIComponent(orderId)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        console.log('Order status update received:', data.order_status);
                        const steps = document.querySelectorAll('.progress-step');
                        const lines = document.querySelectorAll('.progress-line');
                        const statuses = ['Pending', 'Preparing', 'Ready', 'Plating', 'Served'];
                        const currentIndex = statuses.indexOf(data.order_status);

                        if (currentIndex >= 0) {
                            // Update step status (active/inactive)
                            steps.forEach((step, index) => {
                                if (index <= currentIndex) {
                                    step.classList.add('active');
                                } else {
                                    step.classList.remove('active');
                                }
                            });

                            // Update connecting lines
                            lines.forEach((line, index) => {
                                if (index < currentIndex) {
                                    line.classList.add('completed');
                                } else {
                                    line.classList.remove('completed');
                                }
                            });
                            
                            // Also update the status text if present on the page
                            const statusText = document.querySelector('.current-status-text');
                            if (statusText) {
                                statusText.textContent = data.order_status;
                            }
                        }
                    } else {
                        console.error('Error fetching order status:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Network error:', error);
                });
        }

        // Initial update and polling every 10 seconds
        document.addEventListener('DOMContentLoaded', function() {
            updateOrderProgress();
            setInterval(updateOrderProgress, 10000);
        });
    </script>
</body>
</html>