<?php
session_start();
include 'connect.php';

// Check if order_id is set in URL parameter
if (!isset($_GET['order']) || empty($_GET['order'])) {
    header('Location: checkout.php');
    exit();
}

$order_id = $_GET['order'];

// Fetch order details from tbl_orders
$sql = "SELECT * FROM tbl_orders WHERE order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Order not found, redirect to menu
    header('Location: menu.php');
    exit();
}

$order = $result->fetch_assoc();

// Fetch order items
$sql = "SELECT * FROM tbl_order_items WHERE order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $order_id);
$stmt->execute();
$items_result = $stmt->get_result();
$order_items = $items_result->fetch_all(MYSQLI_ASSOC);

// Process payment completion if submitted
$payment_message = "";
if (isset($_POST['payment_complete'])) {
    // Update order payment status to "completed" in database
    $update_sql = "UPDATE tbl_orders SET payment_status = 'Completed' WHERE order_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("s", $order_id);
    
    if ($update_stmt->execute()) {
        $payment_message = '<div class="alert alert-success">Payment marked as complete! Your order is being processed.</div>';
        
        // Clear cart after successful payment
        $session_id = session_id();
        $clear_sql = "DELETE FROM tbl_shoppingcart WHERE usersessionid = ?";
        $clear_stmt = $conn->prepare($clear_sql);
        $clear_stmt->bind_param("s", $session_id);
        $clear_stmt->execute();
        
        // Redirect to success page after 2 seconds
        header("refresh:2;url=payment-success.php?order=$order_id");
    } else {
        $payment_message = '<div class="alert alert-danger">Error updating payment status. Please try again.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cash Payment - Aromiq</title>
    <!-- Stylesheet and other head elements -->
    <link rel="stylesheet" href="css/style.css">
    <link href="img/favicon.ico" rel="icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Nunito:wght@600;700;800&family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Nunito', sans-serif;
            color: #555;
        }
        
        .payment-container {
            max-width: 800px;
            margin: 60px auto;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .icon-container {
            margin: 30px 0;
            font-size: 72px;
            color: #fea116;
        }
        
        .payment-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 30px 0;
            text-align: left;
        }
        
        .payment-details h3 {
            color: #0f172b;
            font-weight: 700;
            margin-bottom: 20px;
            font-size: 20px;
        }
        
        .item-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            font-weight: bold;
            font-size: 18px;
            border-top: 2px solid #dee2e6;
            margin-top: 10px;
        }
        
        .btn-complete {
            background: #fea116;
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 18px;
            border-radius: 50px;
            margin-top: 20px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-complete:hover {
            background: #e79215;
            transform: translateY(-2px);
        }
        
        .instructions {
            margin: 30px 0;
            padding: 20px;
            background: #fff8e1;
            border-left: 4px solid #fea116;
            text-align: left;
            border-radius: 8px;
        }
        
        .instructions h4 {
            color: #0f172b;
            margin-bottom: 10px;
        }
        
        .instructions ol {
            padding-left: 20px;
        }
        
        .instructions li {
            margin-bottom: 10px;
        }
        
        /* Basic navbar and footer styles */
        .navbar {
            background-color: #0f172b;
            padding: 20px 0;
            color: white;
        }
        
        .navbar-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .navbar-brand {
            color: white;
            font-family: 'Pacifico', cursive;
            font-size: 30px;
            text-decoration: none;
        }
        
        .navbar-brand:hover {
            color: #fea116;
        }
        
        .navbar-nav {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        .nav-item {
            margin-left: 20px;
        }
        
        .nav-link {
            color: white;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .nav-link:hover {
            color: #fea116;
        }
        
        .footer {
            background-color: #0f172b;
            color: white;
            padding: 30px 0;
            margin-top: 60px;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Custom navbar instead of include -->
    <div class="navbar">
        <div class="navbar-container">
            <h2 class="navbar-brand">Aromiq</h2>
        </div>
    </div>

    <div class="payment-container">
        <h2>Cash Payment</h2>
        
        <?php echo $payment_message; ?>
        
        <div class="icon-container">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        
        <div class="instructions">
            <h4>How to proceed:</h4>
            <ol>
                <li>Please proceed to the counter with your order number.</li>
                <li>Pay the amount of ₹<?php echo number_format($order['total'], 2); ?> in cash.</li>
                <li>After payment, click the "Payment Complete" button below.</li>
                <li>Your order will start processing immediately after payment confirmation.</li>
            </ol>
        </div>
        
        <div class="payment-details">
            <h3>Order Details</h3>
            <?php foreach ($order_items as $item): ?>
            <div class="item-row">
                <div><?php echo htmlspecialchars($item['food_name']); ?> x <?php echo $item['quantity']; ?></div>
                <div>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
            </div>
            <?php endforeach; ?>
            
            <div class="total-row">
                <div>Total Amount</div>
                <div>₹<?php echo number_format($order['total'], 2); ?></div>
            </div>
        </div>
        
        <div>
            <p>Order #<?php echo $order['order_id']; ?></p>
            <p>Table #<?php echo $order['table_number']; ?></p>
        </div>
        
        <form method="post" action="">
            <button type="submit" name="payment_complete" class="btn-complete">
                <i class="fas fa-check-circle"></i> Mark Payment as Complete
            </button>
        </form>
    </div>

    <!-- Custom footer instead of include -->
    <div class="footer">
        <div class="container">
            <p>© <?php echo date('Y'); ?> Aromiq Restaurant. All Rights Reserved.</p>
        </div>
    </div>
    
    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 