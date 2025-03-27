<?php
session_start();
// Redirect if no final order details
if (!isset($_SESSION['final_order'])) {
      header('Location: customer-order-status.php');
    exit();
}
include 'connect.php';
include 'generate-bill-number.php';
include 'session-manager.php';
// Load order details from session
$order_number = '';
$bill_number = '';
$query = '';
$items_query = '';
$stmt = null;
$items_stmt = null;
$result = null;
$items_result = null;
$order = array(
    'order_id' => '',
    'customer_name' => '',
    'customer_email' => '',
    'customer_phone' => '',
    'table_number' => '',
    'subtotal' => 0,
    'tax' => 0,
    'total' => 0,
    'status' => 'pending',
    'payment_status' => 'pending',
    'payment_method' => '',
    'timestamp' => date('Y-m-d H:i:s'), // Set current timestamp by default
    'items' => []
);
$is_debug = false; // Set to true to see debugging information

// Get order number from URL or session
$order_number = isset($_GET['order_number']) ? trim($_GET['order_number']) : '';

// If no order number in URL, try to get from session
if (empty($order_number) && isset($_SESSION['final_order']) && isset($_SESSION['final_order']['order_id'])) {
    $order_number = $_SESSION['final_order']['order_id'];
    error_log("Retrieved order number from session: " . $order_number);
}

// If still no order number, generate one
if (empty($order_number)) {
    $order_number = 'ORD-' . date('Ymd') . '-' . mt_rand(1000, 9999);
    error_log("Generated new order number: " . $order_number);
    // Store in session
    if (isset($_SESSION['final_order'])) {
        $_SESSION['final_order']['order_id'] = $order_number;
    }
}

// Debug log
error_log("Accessing bill.php with order_number: " . $order_number);

// First verify database connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Check if database and tables exist
$check_table_query = "SHOW TABLES LIKE 'tbl_orders'";
$table_result = $conn->query($check_table_query);

if ($table_result->num_rows === 0) {
    die("Error: Required database table 'tbl_orders' does not exist.");
}

// Try to load order from database first
$order_loaded = false;
if (!empty($order_number)) {
    $order_loaded = loadOrderFromDatabase($order_number);
    if ($order_loaded) {
        error_log("Successfully loaded order from database: " . $order_number);
    } else {
        error_log("Failed to load order from database: " . $order_number);
    }
}

// If order not loaded from database, use session data
if (!$order_loaded && isset($_SESSION['final_order']) && !empty($_SESSION['final_order'])) {
    error_log("Using order details from session");
    
    // Save order to database if it has an ID but couldn't be loaded (means it's not in DB yet)
    if (!empty($_SESSION['final_order']['order_id'])) {
        $saved = saveOrderToDatabase(true); // Force save/update
        if ($saved) {
            error_log("Successfully saved order to database from session data");
        } else {
            error_log("Failed to save order to database from session data");
        }
    }
}

// Populate order array from session data with careful fallbacks
if (isset($_SESSION['final_order'])) {
    // Order ID - ensure we always have a valid order ID
    if (!empty($_SESSION['final_order']['order_id'])) {
        $order['order_id'] = $_SESSION['final_order']['order_id'];
    } else if (!empty($order_number)) {
        $order['order_id'] = $order_number;
    }
    
    // If still no order ID, set it to the previously generated order number
    if (empty($order['order_id'])) {
        $order['order_id'] = $order_number;
    }
    
    // Customer name
    if (!empty($_SESSION['final_order']['details']['name'])) {
        $order['customer_name'] = $_SESSION['final_order']['details']['name'];
    } else if (!empty($_SESSION['final_order']['customer_name'])) {
        $order['customer_name'] = $_SESSION['final_order']['customer_name'];
    }
    
    // Customer email
    if (!empty($_SESSION['final_order']['details']['email'])) {
        $order['customer_email'] = $_SESSION['final_order']['details']['email'];
    } else if (!empty($_SESSION['final_order']['email'])) {
        $order['customer_email'] = $_SESSION['final_order']['email'];
    }
    
    // Customer phone
    if (!empty($_SESSION['final_order']['details']['mobile'])) {
        $order['customer_phone'] = $_SESSION['final_order']['details']['mobile'];
    } else if (!empty($_SESSION['final_order']['mobile_number'])) {
        $order['customer_phone'] = $_SESSION['final_order']['mobile_number'];
    }
    
    // Table number
    if (!empty($_SESSION['final_order']['details']['table'])) {
        $order['table_number'] = $_SESSION['final_order']['details']['table'];
    } else if (!empty($_SESSION['final_order']['table_number'])) {
        $order['table_number'] = $_SESSION['final_order']['table_number'];
    }
    
    // Order amounts
    if (isset($_SESSION['final_order']['subtotal'])) {
        $order['subtotal'] = $_SESSION['final_order']['subtotal'];
    }
    
    if (isset($_SESSION['final_order']['tax'])) {
        $order['tax'] = $_SESSION['final_order']['tax'];
    }
    
    if (isset($_SESSION['final_order']['total'])) {
        $order['total'] = $_SESSION['final_order']['total'];
    }
    
    // Order status
    if (!empty($_SESSION['final_order']['order_status'])) {
        $order['status'] = $_SESSION['final_order']['order_status'];
    }
    
    if (!empty($_SESSION['final_order']['payment_status'])) {
        $order['payment_status'] = $_SESSION['final_order']['payment_status'];
    }
    
    if (!empty($_SESSION['final_order']['payment_mode'])) {
        $order['payment_method'] = $_SESSION['final_order']['payment_mode'];
    }
    
    // Timestamp - ensure we always have a timestamp
    if (!empty($_SESSION['final_order']['timestamp'])) {
        $order['timestamp'] = $_SESSION['final_order']['timestamp'];
    } else {
        $order['timestamp'] = date('Y-m-d H:i:s'); // Current timestamp as fallback
    }
    
    // Order items
    if (!empty($_SESSION['final_order']['items'])) {
        $order['items'] = $_SESSION['final_order']['items'];
    } else if (!empty($_SESSION['cart'])) {
        $order['items'] = $_SESSION['cart'];
    }
}

// If order is still empty, show error
if (empty($order['order_id']) || empty($order['customer_name'])) {
    echo "<div style='text-align:center; margin-top:50px;'>";
    echo "<h2>Order Not Found</h2>";
    echo "<p>We couldn't find the order information. Please try again or contact support.</p>";
    echo "<p><a href='customer-order-status.php' class='btn btn-primary'>Return to Order Status</a></p>";
    exit;
}

// Make sure order_number is set to the final order ID
$order_number = $order['order_id'];

// Generate bill number
$bill_number = "BILL-" . $order['order_id'];

// Store in session for reference
$_SESSION['bill_number'] = $bill_number;
$_SESSION['order_number'] = $order['order_id'];

// Also update the final_order session with the most complete data
$_SESSION['final_order']['timestamp'] = $order['timestamp']; // Ensure timestamp is set
$_SESSION['final_order']['order_id'] = $order['order_id']; // Ensure order_id is set

// Get order items
$items_query = "SELECT * FROM tbl_order_items WHERE order_id = ?";
$items_stmt = $conn->prepare($items_query);
$items_stmt->bind_param("s", $order_number);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

// If we have items in the database, use those
if ($items_result->num_rows > 0) {
    $order['items'] = [];
    while ($item = $items_result->fetch_assoc()) {
        $order['items'][] = $item;
    }
    error_log("Retrieved " . count($order['items']) . " items from database");
} else {
    error_log("No items found in database, using session items");
}

// Debug log successful retrieval
error_log("Successfully processed order: " . $order_number . " with " . count($order['items']) . " items");

// If there was an error generating the bill number
if (!$bill_number) {
    $bill_number = "BILL-" . $order_number; // Fallback
}

// For debugging - output all order information to error log
if ($is_debug) {
    error_log("Final order information: " . print_r($order, true));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill - Aromiq</title>
    <!-- Template Stylesheet -->
    <link rel="stylesheet" href="css/style.css">
    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Nunito:wght@600;700;800&family=Pacifico&display=swap" rel="stylesheet">

    <!-- Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <style>
       :root {
            --primary-color: #fea116;
            --secondary-color: #0f172b;
            --light-color: #ffffff;
            --accent-color: #f8f9fa;
            --border-color: #e0e0e0;
            --success-color: #28a745;
        }
        
        body {
            background-color: #f5f5f5;
            font-family: 'Nunito', sans-serif;
        }
        
        .bill-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 0;
            background: var(--light-color);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
            overflow: hidden;
        }

        .bill-header {
            text-align: center;
            padding: 30px 20px;
            background: var(--secondary-color);
            color: var(--light-color);
            position: relative;
        }

        .bill-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: var(--primary-color);
        }

        .restaurant-name {
            color: var(--primary-color);
            font-size: 2.5em;
            font-family: 'Pacifico', cursive;
            margin-bottom: 5px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
        }
        
        .bill-subtitle {
            font-size: 1.2em;
            color: #ccc;
            margin-bottom: 10px;
        }

        .bill-content {
            padding: 30px;
        }

        .bill-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-group {
            padding: 15px;
            background: var(--accent-color);
            border-radius: 10px;
            border-left: 4px solid var(--primary-color);
        }

        .info-title {
            font-size: 1.1em;
            font-weight: 700;
            color: var(--secondary-color);
            border-bottom: 2px dashed var(--border-color);
            padding-bottom: 8px;
            margin-bottom: 10px;
        }

        .info-item {
            margin-bottom: 5px;
            display: flex;
            justify-content: space-between;
        }

        .info-label {
            font-weight: 600;
            color: var(--secondary-color);
        }

        .bill-items {
            margin: 30px 0;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .bill-table {
            width: 100%;
            border-collapse: collapse;
        }

        .bill-table th {
            background-color: var(--secondary-color);
            color: var(--light-color);
            text-align: left;
            padding: 12px 15px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85em;
            letter-spacing: 0.5px;
        }

        .bill-table th:last-child {
            text-align: right;
        }

        .bill-table td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--border-color);
        }

        .bill-table tr:last-child td {
            border-bottom: none;
        }

        .bill-table tr:nth-child(even) {
            background-color: var(--accent-color);
        }

        .bill-table .item-name {
            font-weight: 600;
            color: var(--secondary-color);
        }

        .bill-table .amount-cell {
            text-align: right;
            font-weight: 600;
        }

        .bill-table .quantity-cell {
            text-align: center;
        }

        .bill-summary {
            margin: 30px 0;
            background: var(--accent-color);
            padding: 20px;
            border-radius: 10px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dashed var(--border-color);
        }

        .summary-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .summary-label {
            font-weight: 600;
            color: var(--secondary-color);
        }

        .grand-total {
            font-size: 1.5em;
            font-weight: 800;
            color: var(--primary-color);
            padding-top: 15px;
            margin-top: 15px;
            border-top: 2px solid var(--primary-color);
            text-align: right;
        }

        .payment-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 600;
            background-color: #ffc107;
            color: #212529;
        }

        .payment-status.paid {
            background-color: var(--success-color);
            color: white;
        }

        .payment-status.unpaid {
            background-color: #dc3545;
            color: white;
        }

        .payment-status.pending {
            background-color: #ffc107;
            color: #212529;
        }

        .bill-footer {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            border-top: 1px solid var(--border-color);
            color: #666;
            font-size: 0.9em;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin: 30px 0;
        }

        .action-buttons .btn {
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }

        .action-buttons .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .action-buttons .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .action-buttons .btn-primary:hover {
            background-color: #e59000;
            border-color: #e59000;
        }

        .action-buttons .btn-secondary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .action-buttons .btn-secondary:hover {
            background-color: #1a2234;
            border-color: #1a2234;
        }

        .action-buttons .btn-info {
            background-color: #17a2b8;
            border-color: #17a2b8;
            color: white;
        }

        .action-buttons .btn-info:hover {
            background-color: #138496;
            border-color: #138496;
        }

        .qr-code {
            text-align: center;
            margin: 20px 0;
        }

        .qr-code img {
            width: 120px;
            height: 120px;
            padding: 5px;
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 5px;
        }

        .bill-thanks {
            text-align: center;
            margin: 20px 0;
            font-size: 1.2em;
            color: var(--primary-color);
            font-weight: 700;
        }

        .bill-disclaimer {
            text-align: center;
            font-size: 0.8em;
            color: #999;
            margin-top: 10px;
        }
        
        @media print {
            body {
                background: white;
                font-size: 12pt;
            }
            
            .bill-container {
                box-shadow: none;
                max-width: 100%;
                margin: 0;
            }
            
            .action-buttons,
            .modal {
                display: none !important;
            }
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
        } 
    </style>
</head>
<body>
    <div class="bill-container">
        <div class="bill-header">
            <div class="restaurant-name">Aromiq</div>
            <p>123 Restaurant Street, Foodie City</p>
            <p>Tel: +91 9895316120</p>
            <p>GSTIN: 22AAAAA0000A1Z5</p>
        </div>

        <div class="bill-info">
            <div class="info-group">
                <div><span class="info-label">Bill No:</span> <?php echo htmlspecialchars($bill_number); ?></div>
                <div><span class="info-label">Date:</span> <?php echo date('d-m-Y', strtotime($order['timestamp'])); ?></div>
                <div><span class="info-label">Time:</span> <?php echo date('H:i', strtotime($order['timestamp'])); ?></div>
            </div>
            <div class="info-group">
                <div><span class="info-label">Customer:</span> <?php echo htmlspecialchars($order['customer_name']); ?></div>
                <div><span class="info-label">Email:</span> <?php echo htmlspecialchars($order['customer_email']); ?></div>
                <div><span class="info-label">Table No:</span> <?php echo htmlspecialchars($order['table_number']); ?></div>
            </div>
        </div>

        <!--<<div class="bill-items">
            <div class="item-row item-header">
                <div>Item</div>
                <div>Price</div>
                <div>Qty</div>
                <div>Amount</div>
            </div>
            <?php foreach ($order['items'] as $item): ?>
            <div class="item-row">
                <div><?php echo htmlspecialchars($item['food_name']); ?></div>
                <div>₹<?php echo number_format($item['price'], 2); ?></div>
                <div><?php echo $item['quantity']; ?></div>
                <div>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
            </div>
            <?php endforeach; ?>
        </div>-->

        <div class="bill-items">
                <table class="bill-table">
                    <thead>
                        <tr>
                            <th width="45%">Item</th>
                            <th width="15%">Price</th>
                            <th width="15%">Quantity</th>
                            <th width="25%">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($order['items'])): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 20px;">No items found in this order.</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($order['items'] as $item): ?>
                            <tr>
                                <td class="item-name">
                                    <?php 
                                    // Try to get the item name from different possible field names
                                    $item_name = '';
                                    if (isset($item['food_name'])) {
                                        $item_name = $item['food_name'];
                                    } elseif (isset($item['name'])) {
                                        $item_name = $item['name'];
                                    }
                                    echo htmlspecialchars($item_name);
                                    ?>
                                </td>
                                <td>
                                    ₹<?php echo number_format(isset($item['price']) ? (float)$item['price'] : 0, 2); ?>
                                </td>
                                <td class="quantity-cell">
                                    <?php echo isset($item['quantity']) ? (int)$item['quantity'] : 1; ?>
                                </td>
                                <td class="amount-cell">
                                    ₹<?php 
                                        $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 1;
                                        $price = isset($item['price']) ? (float)$item['price'] : 0;
                                        $item_total = $quantity * $price;
                                        echo number_format($item_total, 2);
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        <div class="bill-total">
            <?php
            $subtotal = $order['total'];
            $cgst = $subtotal * 0.025; // 2.5% CGST
            $sgst = $subtotal * 0.025; // 2.5% SGST
            $grand_total = $subtotal + $cgst + $sgst;
            ?>
            <div class="total-row">
                <span class="info-label">Subtotal:</span>
                <span>₹<?php echo number_format($subtotal, 2); ?></span>
            </div>
            <div class="total-row">
                <span class="info-label">CGST (2.5%):</span>
                <span>₹<?php echo number_format($cgst, 2); ?></span>
            </div>
            <div class="total-row">
                <span class="info-label">SGST (2.5%):</span>
                <span>₹<?php echo number_format($sgst, 2); ?></span>
            </div>
            <div class="total-row grand-total">
                <span class="info-label">Grand Total:</span>
                <span>₹<?php echo number_format($grand_total, 2); ?></span>
            </div>
        </div>

        <div class="gst-details">
            <p>CGST: 2.5% | SGST: 2.5%</p>
            <p>This is a computer-generated bill and does not require signature.</p>
        </div>

        <div class="bill-footer">
            <p>Thank you for dining with us!</p>
            <p>Please visit again</p>
        </div>

        <div class="action-buttons">
            <button onclick="window.print()" class="btn btn-primary">Print Bill</button>
            <button onclick="showEmailForm()" class="btn btn-primary">Email Bill</button>
            <button onclick="orderStatus()" class="btn btn-primary">View Order Status</button>
        </div>
    </div>

    <!-- Email Modal -->
    <div id="emailModal" class="modal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgba(0,0,0,0.4);">
        <div class="modal-content" style="background-color:#fefefe; margin:15% auto; padding:20px; border:1px solid #888; width:80%; max-width:500px; border-radius:5px; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
            <span class="close" onclick="hideEmailModal()" style="color:#aaa; float:right; font-size:28px; font-weight:bold; cursor:pointer;">&times;</span>
            <h3 style="color:#fea116; margin-bottom:20px;">Email Your Bill</h3>
            <div id="emailFormContainer">
                <form id="emailForm" onsubmit="sendEmail(event)">
                    <div style="margin-bottom:15px;">
                        <label for="recipient" style="display:block; margin-bottom:5px; font-weight:bold;">Email Address:</label>
                        <input type="email" id="recipient" name="recipient" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;" 
                               value="<?php echo htmlspecialchars($order['customer_email']); ?>" required>
                    </div>
                    <div style="margin-bottom:15px;">
                        <label for="subject" style="display:block; margin-bottom:5px; font-weight:bold;">Subject:</label>
                        <input type="text" id="subject" name="subject" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;" 
                               value="Your Aromiq Bill Receipt #<?php echo htmlspecialchars($bill_number); ?>" required>
                    </div>
                    <div style="margin-bottom:15px;">
                        <label for="message" style="display:block; margin-bottom:5px; font-weight:bold;">Additional Message (Optional):</label>
                        <textarea id="message" name="message" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px; height:100px;"
                                  placeholder="Thank you for dining with us!"></textarea>
                    </div>
                    <input type="hidden" name="order_number" value="<?php echo htmlspecialchars($order_number); ?>">
                    <?php if(isset($_SESSION['csrf_token'])): ?>
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <?php else: ?>
                    <?php $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); ?>
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <?php endif; ?>
                    <button type="submit" style="background:#fea116; color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer; font-weight:bold;">Send Email</button>
                </form>
            </div>
            <div id="emailStatus" style="margin-top:15px; padding:10px; border-radius:4px; display:none;"></div>
        </div>
    </div>

    <script>
        // Function to show email form modal
        function showEmailForm() {
            document.getElementById('emailModal').style.display = 'block';
            document.getElementById('emailStatus').style.display = 'none';
            document.getElementById('emailFormContainer').style.display = 'block';
        }
        
        // Function to hide email modal
        function hideEmailModal() {
            document.getElementById('emailModal').style.display = 'none';
        }
        
        // Function to send email via AJAX
        function sendEmail(event) {
            event.preventDefault();
            
            // Get form and status elements
            const form = document.getElementById('emailForm');
            const status = document.getElementById('emailStatus');
            const formData = new FormData(form);
            
            // Show loading message
            status.innerHTML = '<div style="text-align:center;"><p style="color:#666;">Sending email, please wait...</p><div class="spinner" style="margin:10px auto; width:40px; height:40px; border:4px solid #f3f3f3; border-top:4px solid #fea116; border-radius:50%; animation:spin 2s linear infinite;"></div></div>';
            status.style.display = 'block';
            
            // Add keyframe animation for spinner
            if (!document.getElementById('spinnerStyle')) {
                const style = document.createElement('style');
                style.id = 'spinnerStyle';
                style.innerHTML = '@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }';
                document.head.appendChild(style);
            }
            
            console.log('Sending email to:', formData.get('recipient'));
            
            // Send AJAX request
            fetch('send-bill-email.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                // Check if response is ok
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                // Try to parse as JSON, but handle text response as fallback
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return response.json();
                } else {
                    return response.text().then(text => {
                        try {
                            // Try to parse it as JSON anyway
                            return JSON.parse(text);
                        } catch (e) {
                            // If it's not valid JSON, wrap it in an error object
                            console.error('Response is not valid JSON:', text);
                            throw new Error('Received invalid response from server');
                        }
                    });
                }
            })
            .then(data => {
                console.log('Email response:', data);
                
                if (data.success) {
                    // Success message
                    status.innerHTML = '<div style="background-color:#d4edda; color:#155724; padding:15px; border-radius:4px;"><p><strong>Success!</strong> ' + data.message + '</p></div>';
                    // Hide form
                    document.getElementById('emailFormContainer').style.display = 'none';
                    // Automatically close modal after 3 seconds
                    setTimeout(() => {
                        hideEmailModal();
                    }, 3000);
                } else {
                    // Error message
                    status.innerHTML = '<div style="background-color:#f8d7da; color:#721c24; padding:15px; border-radius:4px;"><p><strong>Error:</strong> ' + data.message + '</p></div>';
                }
            })
            .catch(error => {
                // Network or other error
                console.error('Email sending error:', error);
                status.innerHTML = '<div style="background-color:#f8d7da; color:#721c24; padding:15px; border-radius:4px;"><p><strong>Error:</strong> ' + error.message + '</p><p>Please try again later.</p></div>';
            });
        }
        
        // Function for order status button click
        function orderStatus() {
            window.location.href = "customer-order-status.php?order_number=<?php echo urlencode($order_number); ?>";
        }
        
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('emailModal');
            if (event.target == modal) {
                hideEmailModal();
            }
        }

        // Display error messages if any are passed from PHP
        <?php if(isset($email_error)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            showEmailForm();
            const status = document.getElementById('emailStatus');
            status.innerHTML = '<div style="background-color:#f8d7da; color:#721c24; padding:15px; border-radius:4px;"><p><strong>Error:</strong> <?php echo htmlspecialchars($email_error); ?></p></div>';
            status.style.display = 'block';
        });
        <?php endif; ?>
    </script>
</body>
</html>