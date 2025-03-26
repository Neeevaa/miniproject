<?php
session_start();
include 'connect.php';

// Fetch cart items from database
$session_id = session_id();

// Debug output for session ID
//echo "<div class='debug-info' style='background: #f5f5f5; padding: 20px; margin: 20px;'>";
//echo "<h3>Current Session ID: " . $session_id . "</h3>";

// Fix the SQL query and parameter binding
$sql = "SELECT * FROM tbl_shoppingcart WHERE usersessionid = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param("s", $session_id);
$stmt->execute();
$result = $stmt->get_result();

// Debug database query
//if (!$result) {
  //  echo "<div class='debug-info'>";
    //echo "<h3>Debug Information:</h3>";
    //echo "Database Error: " . $conn->error;
    //echo "</div>";
//}

$cartItems = $result->fetch_all(MYSQLI_ASSOC);

// Debug output for cart items
//echo "<h3>Cart Items:</h3>";
//echo "<pre>";
//print_r($cartItems);
//echo "</pre>";

// Calculate total
$total = 0;
foreach($cartItems as $item) {
  $total += floatval($item['price']) * intval($item['quantity']);
}

//echo "<h3>Total: ₹" . number_format($total, 2) . "</h3>";
//echo "</div>";

// Store in session
$_SESSION['cart'] = $cartItems;
$_SESSION['cart_total'] = $total;

// Also verify the database table structure
//$table_check = "DESCRIBE tbl_shoppingcart";
//$table_result = $conn->query($table_check);
//if ($table_result) {
  //  echo "<div class='debug-info' style='margin-top: 20px;'>";
    //echo "<h3>Table Structure:</h3>";
   // echo "<pre>";
    //while ($row = $table_result->fetch_assoc()) {
      //  print_r($row);
    //}
    //echo "</pre>";
    //echo "</div>";
//}

// Store cart items in session if not already there
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = $cartItems;
}

// Add this after successful payment processing
function clearCart() {
    global $conn;
    $session_id = session_id();
    
    // Clear from database
    $sql = "DELETE FROM tbl_shoppingcart WHERE usersessionid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $session_id);
    $stmt->execute();
    
    // Clear session
    unset($_SESSION['cart']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Aromiq</title>
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

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    
    <style>
        .checkout-container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
            display: grid;
            grid-template-columns: 1fr 1fr; /* Creates two equal columns */
            gap: 30px;
        }

        .order-preview {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            height: fit-content; /* Adjusts height based on content */
        }

        .customer-details {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            height: fit-content;
        }

        /* Make it responsive for mobile devices */
        @media (max-width: 768px) {
            .checkout-container {
                grid-template-columns: 1fr; /* Stack vertically on mobile */
            }
        }

        /* If you have an empty cart message, make it span full width */
        .empty-cart-message {
            grid-column: 1 / -1; /* Spans across all columns */
            text-align: center;
            padding: 40px;
        }

        .return-to-menu {
            display: inline-block;
            background: #fea116;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 15px;
        }

        .return-to-menu:hover {
            background: #e89114;
        }

        .order-preview h2 {
            color: #fea116;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #fea116;
        }

        .preview-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
            gap: 15px;
        }

        .preview-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }

        .preview-details {
            flex-grow: 1;
        }

        .preview-details h3 {
            color: #0f172b;
            margin-bottom: 5px;
            font-size: 16px;
        }

        .preview-details .price {
            color: #fea116;
            font-weight: bold;
        }

        .preview-details .quantity {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }

        .item-total {
            font-weight: bold;
            color: #0f172b;
        }

        .order-total {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 18px;
            font-weight: bold;
        }

        .total-price {
            color: #fea116;
            font-size: 24px;
        }

        .order-summary, .customer-details {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .order-summary h2, .customer-details h2 {
            color: #fea116;
            margin-bottom: 20px;
            border-bottom: 2px solid #fea116;
            padding-bottom: 10px;
        }

        .cart-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .cart-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 15px;
        }

        .item-details {
            flex-grow: 1;
        }

        .item-details h3 {
            color: #0f172b;
            margin-bottom: 5px;
        }

        .item-details p {
            color: #fea116;
            font-weight: bold;
        }

        .item-quantity {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .quantity-btn {
            background: #fea116;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .quantity-btn:hover {
            background: #e89114;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #0f172b;
            font-weight: 500;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus, .form-group select:focus {
            border-color: #fea116;
            outline: none;
        }

        .total-amount {
            font-size: 1.3em;
            font-weight: bold;
            color: #fea116;
            margin-top: 20px;
            text-align: right;
            padding: 15px 0;
            border-top: 2px solid #eee;
        }

        .submit-btn {
            background: #fea116;
            color: white;
            border: none;
            padding: 15px 25px;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-size: 1.1em;
            margin-top: 20px;
            transition: background 0.3s;
        }

        .submit-btn:hover {
            background: #e89114;
        }

        .edit-order-btn {
            background: #0f172b;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
            transition: background 0.3s;
        }

        .edit-order-btn:hover {
            background: #1a2539;
        }

        .debug-info {
            background: #f5f5f5;
            padding: 20px;
            margin: 20px;
            border-radius: 8px;
            font-family: monospace;
        }
        .edit-menu{
            background: #fea116;
            color: white;
            border: none;
            padding: 15px 25px;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-size: 1.1em;
            margin-top: 20px;
            transition: background 0.3s;
        }
    </style>
</head>
<body>
    <div class="checkout-container">
       <?php if (empty($cartItems)): ?>
            <div class="empty-cart-message">
                <h2>Your platter is empty</h2>
                <p>Please add some items to your platter before checking out.</p>
                <a href="menu.php" class="return-to-menu">Return to Menu</a>
            </div>
        <?php else: ?>
            <div class="order-preview">
                <h2>Your Order</h2>
                <?php foreach ($cartItems as $item): ?>
                    <div class="preview-item">
                        <img src="images/<?php echo htmlspecialchars($item['image_path']); ?>" 
                             alt="<?php echo htmlspecialchars($item['food_name']); ?>">
                        <div class="preview-details">
                            <h3><?php echo htmlspecialchars($item['food_name']); ?></h3>
                            <p class="price">₹<?php echo htmlspecialchars($item['price']); ?></p>
                            <p class="quantity">Quantity: <?php echo htmlspecialchars($item['quantity']); ?></p>
                            <p class="item-total">
                                Total: ₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="order-total">
                    <span>Total Amount:</span>
                    <span class="total-price">₹<?php echo number_format($total, 2); ?></span><br>
                    <a href="menu.php" class="btn btn-primary py-2 px-4">Edit Platter</a>
                </div>
            </div>
            
            <div class="customer-details">
                <h2>Customer Details</h2>
                <form action="process-order.php" method="POST">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required>
                        <small class="form-text text-muted">We'll send your receipt and order updates to this email.</small>
                    </div>

                    <div class="form-group">
                        <label for="mobile">Mobile Number</label>
                        <input type="tel" id="mobile" name="mobile" pattern="[0-9]{10}" required>
                    </div>

                    <div class="form-group">
                        <label for="table">Table Number</label>
                        <select id="table" name="table" required>
                            <option value="">Select Table Number</option>
                            <?php for($i=1; $i<=12; $i++): ?>
                                <option value="<?php echo $i; ?>">Table <?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="payment">Payment Mode</label>
                        <select id="payment" name="payment" required>
                            <option value="">Select Payment Mode</option>
                            <option value="card">Card</option>
                            <option value="upi">UPI</option>
                            <option value="cash">Cash</option>
                        </select>
                    </div>

                    <button type="submit" class="submit-btn">Place Order</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <script>
// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Check if form exists before adding event listener
    const checkoutForm = document.querySelector('form');
    
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Create FormData object
            const formData = new FormData(this);
            
            // Get cart items from PHP and add to formData
            // Important: This needs to be valid JSON data
            const cartItems = <?php echo json_encode($cartItems); ?>;
            
            // Debug output
            console.log('Cart items being sent:', cartItems);
            
            // Ensure cartItems is not empty before proceeding
            if (!cartItems || cartItems.length === 0) {
                alert('Your cart appears to be empty. Please add items before proceeding.');
                return;
            }
            
            // Add cart items and total to form data
            formData.append('cartItems', JSON.stringify(cartItems));
            formData.append('totalAmount', '<?php echo $total; ?>');
            
            // Log all formData keys and values for debugging
            for (const pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }
            
            // Show loading indicator
            const submitBtn = this.querySelector('.submit-btn');
            const originalBtnText = submitBtn.textContent;
            submitBtn.textContent = 'Processing...';
            submitBtn.disabled = true;
            
            // Fetch to process order
            fetch('process-order.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                
                if (data.success) {
                    console.log('Order processed successfully.');
                    
                    // Save order details in session before redirecting
                    sessionStorage.setItem('order_id', data.order_id);
                    
                    // Check payment method to determine redirect
                    const paymentMethod = document.getElementById('payment').value;
                    
                    if (paymentMethod === 'cash') {
                        // Redirect to cash payment page
                        window.location.href = 'cashpayment.php?order=' + data.order_id;
                    } else {
                        // For other payment methods, use standard payment flow
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            window.location.href = 'payment.php?order=' + data.order_id;
                        }
                    }
                } else {
                    // Show error message
                    alert(data.message || 'Error processing order');
                    submitBtn.textContent = originalBtnText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('There was an error processing your order. Please try again.');
                submitBtn.textContent = originalBtnText;
                submitBtn.disabled = false;
            });
        });
    }
});
    // Optional: Function to clear cart
    function clearCart() {
        fetch('clear-cart.php')
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    console.error('Error clearing cart');
                }
            })
            .catch(error => {
                console.error('Error clearing cart:', error);
            });
    }

</script>
<?php
// Add this before the closing </body> tag in checkout.php

// Function to ensure cart data is available in session
function ensureCartInSession() {
    global $cartItems, $conn;
    
    // If cart items were fetched from database but not in session, store them
    if (!isset($_SESSION['cart']) && !empty($cartItems)) {
        $_SESSION['cart'] = $cartItems;
        $_SESSION['cart_last_updated'] = time();
    }
    // If cart is still empty in session, try fetching from database again
    else if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        $session_id = session_id();
        $sql = "SELECT * FROM tbl_shoppingcart WHERE usersessionid = ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("s", $session_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $cartItems = $result->fetch_all(MYSQLI_ASSOC);
            
            if (!empty($cartItems)) {
                $_SESSION['cart'] = $cartItems;
                $_SESSION['cart_last_updated'] = time();
            }
        }
    }
    
    // Check if cart is still empty and display warning if needed
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        echo "<script>console.warn('Warning: Cart appears to be empty in both session and database!');</script>";
    } else {
        echo "<script>console.log('Cart verified in session with " . count($_SESSION['cart']) . " items');</script>";
    }
}

// Execute the function
ensureCartInSession();
?>
</body>
</html>
