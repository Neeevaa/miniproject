<?php
session_start();
include 'connect.php';

// Check if order details exist in session
if (!isset($_SESSION['order_details']) || !isset($_SESSION['cart'])) {
    header('Location: bill.php');
    exit();
}

// Set the timezone to match your system (adjust to your timezone)
date_default_timezone_set('Asia/Kolkata'); // Indian Standard Time

// Get current server date and time for timestamp with the correct timezone
$server_timestamp = date('Y-m-d H:i:s');

$_SESSION['final_order'] = [
    'details' => [
        'name' => $_SESSION['order_details']['name'],
        'email' => $_SESSION['order_details']['email'],
        'mobile' => $_SESSION['order_details']['mobile'],
        'table' => $_SESSION['order_details']['table'],
        'payment_mode' => $_SESSION['order_details']['payment']
    ],
    'items' => $_SESSION['cart'],
    'total' => $_SESSION['cart_total'],
    'timestamp' => $server_timestamp
];

// Store timestamp in a separate variable for future reference
$_SESSION['order_timestamp'] = $server_timestamp;

// Regenerate session ID on important transitions
session_regenerate_id(true);

// Add CSRF protection
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$order_details = $_SESSION['order_details'];
$cart_items = $_SESSION['cart'];
$total_amount = $_SESSION['cart_total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Aromiq</title>
    <!-- Template Stylesheet -->
    <link rel="stylesheet" href="css/style.css">
    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Nunito:wght@600;700;800&family=Pacifico&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <style>
        .confirmation-container {
            max-width: 1000px;
            margin: 50px auto;
            padding: 20px;
        }

        .order-details, .payment-options {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .section-title {
            color:rgb(7, 2, 27);
            margin-bottom: 20px;
            padding-bottom: 5px;
            border-bottom: 5px solid #fea116;
        }

        .customer-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-item {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .info-label {
            font-weight: bold;
            color: #0f172b;
        }

        .order-items {
            margin-bottom: 30px;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .payment-method {
            text-align: center;
            padding: 15px;
            border: 2px solid #eee;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .payment-method:hover {
            border-color: #fea116;
            transform: translateY(-2px);
        }

        .payment-method img {
            width: 80px;
            height: 60px;
            margin-bottom: 10px;
        }

        .payment-method.selected {
            border-color: #fea116;
            background: #fff8e7;
        }

        .upi-details {
            display: none;
            margin-top: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .proceed-btn {
            background: #fea116;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-size: 1.1em;
            margin-top: 20px;
            transition: background 0.3s;
        }

        .proceed-btn:hover {
            background: #e89114;
        }

        .card-details {
    margin-top: 20px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.payment-form {
    max-width: 500px;
    margin: 0 auto;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    color: #0f172b;
    font-weight: 500;
}

.form-group input {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 16px;
}

.form-group input:focus {
    border-color: #fea116;
    outline: none;
}

.card-info-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

/* Added validation styles */
.form-group input.error {
    border-color: #dc3545;
}

.error-message {
    color: #dc3545;
    font-size: 12px;
    margin-top: 5px;
}

/* Style for accepted cards display */
.accepted-cards {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
}

.accepted-cards img {
    height: 30px;
    opacity: 0.7;
}

        @media (max-width: 768px) {
            .customer-info {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <div class="order-details">
            <h2 class="section-title">Order Details</h2>
            
            <div class="customer-info">
                <div class="info-item">
                    <span class="info-label">Customer Name:</span>
                    <span><?php echo htmlspecialchars($_SESSION['order_details']['name']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email:</span>
                    <span><?php echo htmlspecialchars($_SESSION['order_details']['email']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Mobile Number:</span>
                    <span><?php echo htmlspecialchars($_SESSION['order_details']['mobile']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Table Number:</span>
                    <span><?php echo htmlspecialchars($_SESSION['order_details']['table']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Payment Mode:</span>
                    <span><?php echo htmlspecialchars($_SESSION['order_details']['payment']); ?></span>
                </div>
            </div>

            <div class="order-items">
                <h3>Ordered Items</h3>
                <?php foreach ($_SESSION['cart'] as $item): ?>
                    <div class="item-row">
                        <span><?php echo htmlspecialchars($item['food_name']); ?> × <?php echo $item['quantity']; ?></span>
                        <span>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                    </div>
                <?php endforeach; ?>
                <div class="item-row" style="font-weight: bold; margin-top: 10px;">
                    <span>Total Amount</span>
                    <span>₹<?php echo number_format($_SESSION['cart_total'], 2); ?></span>
                </div>
            </div>
        </div>
        <?php if ($_SESSION['order_details']['payment'] === 'upi'): ?>
        <div class="payment-options">
            <h2 class="section-title">UPI Payment Options</h2>
            <div class="payment-methods">
                <div class="payment-method" onclick="selectPaymentMethod('gpay')">
                    <img src="img/gpay-icon.png" alt="Google Pay">
                    <div>Google Pay</div>
                </div>
                <div class="payment-method" onclick="selectPaymentMethod('paytm')">
                    <img src="img/paytm-icon.png" alt="Paytm">
                    <div>Paytm</div>
                </div>
                <div class="payment-method" onclick="selectPaymentMethod('phonepe')">
                    <img src="img/phonepe-icon.png" alt="PhonePe">
                    <div>PhonePe</div>
                </div>
                <div class="payment-method" onclick="selectPaymentMethod('upi')">
                    <img src="img/upi-icon.png" alt="Other UPI">
                    <div>Other UPI</div>
                </div>
            </div>

            <div id="upi-details" class="upi-details">
                <p>UPI ID: nevonj2am-2@okicici</p>
                <p>Scan QR code or enter UPI ID to pay</p>
                <img src="img/qr-code.jpg" alt="QR Code" style="width: 200px; margin: 20px auto; display: block;">
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($_SESSION['order_details']['payment'] === 'card'): ?>
        <div class="payment-options">
            <h2 class="section-title">Card Payment Options</h2>
            <div class="payment-methods">
                <div class="payment-method" onclick="selectCardType('credit')">
                    <img src="img/credit-card-icon.png" alt="Credit Card">
                    <div>Credit Card</div>
                </div>
                <div class="payment-method" onclick="selectCardType('debit')">
                    <img src="img/debit-card-icon.png" alt="Debit Card">
                    <div>Debit Card</div>
                </div>
            </div>

            <div id="card-details" class="card-details" style="display: none;">
                <form id="card-payment-form" class="payment-form">
                    <div class="form-group">
                        <label for="card-number">Card Number</label>
                        <input type="text" id="card-number" maxlength="16" placeholder="XXXX XXXX XXXX XXXX" required>
                    </div>
                    
                    <div class="card-info-row">
                        <div class="form-group">
                            <label for="expiry-date">Expiry Date</label>
                            <input type="text" id="expiry-date" placeholder="MM/YY" maxlength="5" required>
                        </div>
                        <div class="form-group">
                            <label for="cvv">CVV</label>
                            <input type="password" id="cvv" maxlength="3" placeholder="XXX" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="card-name">Name on Card</label>
                        <input type="text" id="card-name" placeholder="Enter name as on card" required>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <button onclick="proceedToBill()" class="proceed-btn">Proceed to Bill</button>
    </div>

    <script>
        function selectPaymentMethod(method) {
            // Remove selected class from all payment methods
            document.querySelectorAll('.payment-method').forEach(el => {
                el.classList.remove('selected');
            });
            
            // Add selected class to clicked method
            event.currentTarget.classList.add('selected');
            
            // Show UPI details
            document.getElementById('upi-details').style.display = 'block';
        }

        function proceedToBill() {
            // Clear cart and redirect to bill page
            fetch('clear-cart.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = 'bill.php';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        // Add these functions to your existing script
function selectCardType(type) {
    // Remove selected class from all payment methods
    document.querySelectorAll('.payment-method').forEach(el => {
        el.classList.remove('selected');
    });
    
    // Add selected class to clicked method
    event.currentTarget.classList.add('selected');
    
    // Show card details form
    document.getElementById('card-details').style.display = 'block';
}

// Card input validation
document.addEventListener('DOMContentLoaded', function() {
    const cardNumber = document.getElementById('card-number');
    const expiryDate = document.getElementById('expiry-date');
    const cvv = document.getElementById('cvv');

    if(cardNumber) {
        cardNumber.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '').replace(/[^\d]/g, '');
            let formatted = '';
            for(let i = 0; i < value.length; i++) {
                if(i > 0 && i % 4 === 0) {
                    formatted += ' ';
                }
                formatted += value[i];
            }
            e.target.value = formatted.substring(0, 19);
        });
    }

    if(expiryDate) {
        expiryDate.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '').replace(/[^\d]/g, '');
            if(value.length > 2) {
                value = value.substring(0, 2) + '/' + value.substring(2);
            }
            e.target.value = value.substring(0, 5);
        });
    }

    if(cvv) {
        cvv.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/[^\d]/g, '').substring(0, 3);
        });
    }
});
    </script>
</body>
</html>