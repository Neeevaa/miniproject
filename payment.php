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

// Razorpay API credentials
// Replace with your actual API keys from Razorpay Dashboard
$razorpay_key_id = 'rzp_test_MMwxZlNfUJbPcP';  // Test Key
$razorpay_key_secret = 'NA4CDgKCkwHKgdAUhef3w0mY';  // Don't expose this on the client side

// Convert amount to paise (Razorpay expects amount in smallest currency unit)
$amount_in_paise = $order['total'] * 100;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Aromiq</title>
    <!-- Stylesheet and other head elements -->
    <link rel="stylesheet" href="css/style.css">
    <link href="img/favicon.ico" rel="icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Nunito:wght@600;700;800&family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Razorpay JavaScript SDK -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Nunito', sans-serif;
            color: #555;
        }
        
        .payment-container {
            max-width: 1100px;
            margin: 60px auto;
            padding: 0;
        }
        
        .order-summary {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            animation: fadeIn 0.6s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .section-title {
            color: #0f172b;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #fea116;
            font-weight: 800;
            display: flex;
            align-items: center;
        }
        
        .section-title i {
            margin-right: 12px;
            font-size: 24px;
            color: #fea116;
        }
        
        .order-info {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .info-item {
            padding: 18px;
            background: #f8f9fa;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.03);
            transition: all 0.3s;
        }
        
        .info-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.08);
        }
        
        .info-label {
            font-weight: bold;
            color: #0f172b;
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-value {
            font-size: 16px;
            color: #555;
        }
        
        .order-items {
            margin-bottom: 30px;
        }
        
        .items-header {
            background: #0f172b;
            color: white;
            padding: 15px 20px;
            border-radius: 10px 10px 0 0;
            display: grid;
            grid-template-columns: 3fr 1fr 1fr 1fr;
            font-weight: 700;
        }
        
        .item-row {
            display: grid;
            grid-template-columns: 3fr 1fr 1fr 1fr;
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            align-items: center;
        }
        
        .item-row:nth-child(odd) {
            background-color: rgba(0,0,0,0.02);
        }
        
        .item-row:last-child {
            border-bottom: none;
            border-radius: 0 0 10px 10px;
        }
        
        .item-name {
            font-weight: 600;
            color: #333;
        }
        
        .item-total {
            padding: 15px 20px;
            background: #f8f9fa;
            border-radius: 0 0 10px 10px;
            font-weight: bold;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
        }
        
        .grand-total {
            margin-top: 10px;
            padding-top: 15px;
            border-top: 2px dashed #eee;
            font-size: 18px;
            color: #0f172b;
        }
        
        .payment-options {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            animation: fadeIn 0.8s ease-out;
        }
        
        .payment-provider {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 10px;
            display: flex;
            align-items: center;
            background: #fcfcfc;
        }
        
        .provider-logo {
            height: 40px;
            margin-right: 15px;
        }
        
        .provider-text {
            flex-grow: 1;
        }
        
        .provider-name {
            font-weight: 700;
            color: #333;
            font-size: 16px;
        }
        
        .provider-description {
            font-size: 14px;
            color: #777;
        }
        
        .pay-btn {
            background: linear-gradient(135deg, #fea116, #ff8a00);
            color: white;
            border: none;
            padding: 16px 30px;
            border-radius: 50px;
            cursor: pointer;
            width: 100%;
            font-size: 18px;
            font-weight: 700;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(254, 161, 22, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 30px;
        }
        
        .pay-btn:hover {
            background: linear-gradient(135deg, #ff8a00, #fea116);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(254, 161, 22, 0.4);
        }
        
        .pay-btn i {
            margin-right: 10px;
            font-size: 20px;
        }
        
        /* Secure payment badge */
        .secure-payment {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 20px;
            color: #777;
            font-size: 14px;
        }
        
        .secure-payment i {
            margin-right: 8px;
            color: #28a745;
        }
        
        @media (max-width: 992px) {
            .order-info {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .item-row, .items-header {
                grid-template-columns: 2fr 1fr 1fr;
            }
            
            .item-quantity {
                display: none;
            }
        }
        
        @media (max-width: 576px) {
            .order-info {
                grid-template-columns: 1fr;
            }
            
            .item-row, .items-header {
                grid-template-columns: 2fr 1fr;
            }
            
            .item-price {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="order-summary">
            <h2 class="section-title"><i class="fas fa-clipboard-check"></i> Order Summary</h2>
            
            <div class="order-info">
                <div class="info-item">
                    <span class="info-label">Order ID</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['order_id']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Customer Name</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['customer_name']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['customer_email']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Phone</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['customer_phone']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Table Number</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['table_number']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Payment Method</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['payment_method']); ?></span>
                </div>
            </div>
            
            <div class="order-items">
                <h3 class="items-header">
                    <div>Item</div>
                    <div>Quantity</div>
                    <div>Price</div>
                    <div>Total</div>
                </h3>
                <?php foreach ($order_items as $item): ?>
                    <div class="item-row">
                        <div class="item-name"><?php echo htmlspecialchars($item['food_name']); ?></div>
                        <div class="item-quantity"><?php echo $item['quantity']; ?></div>
                        <div class="item-price">₹<?php echo number_format($item['price'], 2); ?></div>
                        <div class="item-subtotal">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                    </div>
                <?php endforeach; ?>
                
                <div class="item-total">
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>₹<?php echo number_format($order['subtotal'], 2); ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Tax (GST)</span>
                        <span>₹<?php echo number_format($order['tax'], 2); ?></span>
                    </div>
                    
                    <div class="summary-row grand-total">
                        <span>Total Amount</span>
                        <span>₹<?php echo number_format($order['total'], 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="payment-options">
            <h2 class="section-title"><i class="fas fa-credit-card"></i> Payment Options</h2>
            
            <div class="payment-provider">
                <img src="https://razorpay.com/assets/razorpay-glyph.svg" alt="Razorpay" class="provider-logo">
                <div class="provider-text">
                    <div class="provider-name">Razorpay</div>
                    <div class="provider-description">Secure payments powered by Razorpay. Pay using Credit/Debit Card, UPI, Netbanking and more.</div>
                </div>
            </div>
            
            <button id="pay-button" class="pay-btn">
                <i class="fas fa-lock"></i> Pay Now ₹<?php echo number_format($order['total'], 2); ?>
            </button>
            
            <div class="secure-payment">
                <i class="fas fa-shield-alt"></i> All payments are secure and encrypted
            </div>
        </div>
    </div>
    
    <script>
        // Razorpay payment implementation
        document.getElementById('pay-button').onclick = function(e) {
            var options = {
                "key": "<?php echo $razorpay_key_id; ?>",
                "amount": "<?php echo $amount_in_paise; ?>", // Amount in smallest currency unit
                "currency": "INR",
                "name": "Aromiq Restaurant",
                "description": "Order #<?php echo $order['order_id']; ?>",
                "image": "img/logo.png", // Your logo URL
                "order_id": "", // Leave blank for now; in production, you'd create a Razorpay order first
                "handler": function (response) {
                    // Handle successful payment
                    console.log(response);
                    // Send payment ID to server to verify payment
                    verifyPayment(response.razorpay_payment_id);
                },
                "prefill": {
                    "name": "<?php echo htmlspecialchars($order['customer_name']); ?>",
                    "email": "<?php echo htmlspecialchars($order['customer_email']); ?>",
                    "contact": "<?php echo htmlspecialchars($order['customer_phone']); ?>"
                },
                "notes": {
                    "order_id": "<?php echo $order['order_id']; ?>",
                    "table_number": "<?php echo $order['table_number']; ?>"
                },
                "theme": {
                    "color": "#fea116"
                }
            };
            
            var rzp = new Razorpay(options);
            rzp.open();
            e.preventDefault();
        }
        
        // Function to verify payment on server side
        function verifyPayment(paymentId) {
            // Show loading indicator
            document.getElementById('pay-button').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying Payment...';
            document.getElementById('pay-button').disabled = true;
            
            fetch('verify-payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    payment_id: paymentId,
                    order_id: '<?php echo $order_id; ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Display success message briefly
                    document.getElementById('pay-button').innerHTML = '<i class="fas fa-check-circle"></i> Payment Successful!';
                    document.getElementById('pay-button').style.background = 'linear-gradient(135deg, #28a745, #218838)';
                    
                    // Redirect to success page after brief delay
                    setTimeout(function() {
                        window.location.href = 'payment-success.php?order=<?php echo $order_id; ?>';
                    }, 1500);
                } else {
                    // Reset button on failure
                    document.getElementById('pay-button').innerHTML = '<i class="fas fa-exclamation-circle"></i> Try Again';
                    document.getElementById('pay-button').disabled = false;
                    alert('Payment verification failed: ' + data.message);
                }
            })
            .catch(error => {
                document.getElementById('pay-button').innerHTML = '<i class="fas fa-exclamation-circle"></i> Try Again';
                document.getElementById('pay-button').disabled = false;
                console.error('Error:', error);
                alert('Error verifying payment. Please contact support.');
            });
        }
    </script>
</body>
</html> 