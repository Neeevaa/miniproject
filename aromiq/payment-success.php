<?php
session_start();
include 'connect.php';

if (!isset($_GET['order']) || empty($_GET['order'])) {
    header('Location: menu.php');
    exit();
}

$order_id = $_GET['order'];

// Fetch order details
$sql = "SELECT * FROM tbl_orders WHERE order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: menu.php');
    exit();
}

$order = $result->fetch_assoc();

// Store order details in session for bill.php
$_SESSION['final_order'] = [
    'order_id' => $order['order_id'],
    'customer_name' => $order['customer_name'],
    'customer_email' => $order['customer_email'],
    'customer_phone' => $order['customer_phone'],
    'table_number' => $order['table_number'],
    'subtotal' => $order['subtotal'],
    'tax' => $order['tax'],
    'total' => $order['total'],
    'payment_method' => $order['payment_method'],
    'timestamp' => $order['timestamp']
];

// Also fetch order items for the bill
$items_sql = "SELECT * FROM tbl_order_items WHERE order_id = ?";
$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("s", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
$order_items = $items_result->fetch_all(MYSQLI_ASSOC);

// Store items in session
$_SESSION['final_order']['items'] = $order_items;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success - Aromiq</title>
    <!-- Include your CSS files here -->
    <link rel="stylesheet" href="css/style.css">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Nunito', sans-serif;
        }
        
        .success-container {
            max-width: 650px;
            margin: 80px auto;
            padding: 0;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            overflow: hidden;
            animation: fadeIn 0.8s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .success-header {
            background: linear-gradient(135deg, #fea116, #ff8a00);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }
        
        .success-icon {
            font-size: 60px;
            background: white;
            color: #28a745;
            width: 100px;
            height: 100px;
            line-height: 100px;
            border-radius: 50%;
            margin: 0 auto 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            animation: bounceIn 1s;
        }
        
        @keyframes bounceIn {
            0% { transform: scale(0); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .success-title {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 10px;
        }
        
        .success-body {
            padding: 30px;
            text-align: center;
        }
        
        .order-info {
            margin: 25px 0;
            padding: 25px;
            background: #f8f9fa;
            border-radius: 12px;
            text-align: left;
            border-left: 4px solid #fea116;
        }
        
        .order-info p {
            margin-bottom: 15px;
            font-size: 16px;
            display: flex;
            align-items: center;
        }
        
        .order-info p:last-child {
            margin-bottom: 0;
        }
        
        .order-info i {
            margin-right: 10px;
            color: #fea116;
            width: 20px;
            text-align: center;
        }
        
        .btn-home {
            background: linear-gradient(135deg, #fea116, #ff8a00);
            color: white;
            padding: 12px 35px;
            border-radius: 30px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
            transition: all 0.3s;
            font-weight: 700;
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(254, 161, 22, 0.3);
        }
        
        .btn-home:hover {
            background: linear-gradient(135deg, #ff8a00, #fea116);
            transform: translateY(-3px);
            box-shadow: 0 7px 20px rgba(254, 161, 22, 0.4);
            color: white;
            text-decoration: none;
        }
        
        .btn-home i {
            margin-right: 8px;
        }
        
        .thank-you-message {
            font-size: 17px;
            color: #555;
            margin: 25px 0;
            line-height: 1.6;
        }
        
        .confetti {
            position: absolute;
            top: -20px;
            left: 0;
            width: 100%;
            height: 100px;
            display: flex;
            justify-content: space-around;
        }
        
        .confetti-piece {
            background-color: #fea116;
            width: 10px;
            height: 30px;
            opacity: 0.7;
            animation: fall 3s linear infinite;
        }
        
        .confetti-piece:nth-child(2n) {
            background-color: #0f172b;
            width: 12px;
            height: 18px;
            animation-delay: 0.5s;
        }
        
        .confetti-piece:nth-child(3n) {
            background-color: white;
            width: 8px;
            height: 10px;
            animation-delay: 1s;
        }
        
        @keyframes fall {
            0% {
                transform: translateY(-100px) rotate(0deg);
                opacity: 0.7;
            }
            100% {
                transform: translateY(200px) rotate(360deg);
                opacity: 0;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-header">
            <div class="confetti">
                <div class="confetti-piece"></div>
                <div class="confetti-piece"></div>
                <div class="confetti-piece"></div>
                <div class="confetti-piece"></div>
                <div class="confetti-piece"></div>
                <div class="confetti-piece"></div>
                <div class="confetti-piece"></div>
                <div class="confetti-piece"></div>
            </div>
            <div class="success-icon">
                <i class="bi bi-check-lg"></i>
            </div>
            <h1 class="success-title">Payment Successful!</h1>
            <p>Your transaction has been completed</p>
        </div>
        
        <div class="success-body">
            <div class="order-info">
                <p><i class="fas fa-receipt"></i> <strong>Order ID:</strong> <?php echo htmlspecialchars($order['order_id']); ?></p>
                <p><i class="fas fa-rupee-sign"></i> <strong>Amount Paid:</strong> ₹<?php echo number_format($order['total'], 2); ?></p>
                <p><i class="fas fa-utensils"></i> <strong>Table Number:</strong> <?php echo htmlspecialchars($order['table_number']); ?></p>
                <p><i class="far fa-clock"></i> <strong>Date & Time:</strong> <?php echo date('d M Y, h:i A', strtotime($order['timestamp'])); ?></p>
            </div>
            
            <p class="thank-you-message">Your order has been placed and is being prepared. Thank you for choosing Aromiq - we're delighted to serve you!</p>
            
            <a href="bill.php?order_number=<?php echo htmlspecialchars($order['order_id']); ?>" class="btn-home">
                <i class="fas fa-file-invoice"></i> Generate Bill
            </a>
            
            <p style="margin-top: 20px; font-size: 14px; color: #999;">
                A copy of this receipt has been sent to your email.
            </p>
        </div>
    </div>
    
    <script>
        // Optional: Add a small animation when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const confettiPieces = document.querySelectorAll('.confetti-piece');
                confettiPieces.forEach(piece => {
                    piece.style.animationPlayState = 'running';
                });
            }, 500);
        });
    </script>
</body>
</html> 