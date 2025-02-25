<?php
session_start();
include 'connect.php';

// First, let's create or modify the table structure if it doesn't exist
$createTable = "CREATE TABLE IF NOT EXISTS tbl_shoppingcart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usersessionid VARCHAR(255) NOT NULL,
    food_name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    image_path VARCHAR(255) NOT NULL
)";
mysqli_query($conn, $createTable);

// Now update the query to use the correct column name (usersessionid instead of user_session_id)
$session_id = session_id();
$sql = "SELECT * FROM tbl_shoppingcart WHERE usersessionid = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $session_id);
$stmt->execute();
$result = $stmt->get_result();
$cartItems = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Calculate total
$total = 0;
foreach($cartItems as $item) {
    $total += $item['price'] * $item['quantity'];
}

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
    <link rel="stylesheet" href="css/style.css">
    <style>
        .checkout-container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
        }

        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        @media (max-width: 768px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }
        }

        .empty-cart-message {
            text-align: center;
            padding: 40px;
            color: #666;
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

        .order-preview {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
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

        .quantity {
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
                <h2>Order Preview</h2>
                <div class="order-items">
                    <?php foreach($cartItems as $item): ?>
                        <div class="preview-item">
                            <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['food_name']); ?>">
                            <div class="preview-details">
                                <h3><?php echo htmlspecialchars($item['food_name']); ?></h3>
                                <p class="price">₹<?php echo number_format($item['price'], 2); ?></p>
                                <div class="quantity">
                                    <span>Quantity: <?php echo $item['quantity']; ?></span>
                                </div>
                            </div>
                            <div class="item-total">
                                ₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="order-total">
                    <span>Total Amount:</span>
                    <span class="total-price">₹<?php echo number_format($total, 2); ?></span>
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
                        <label for="mobile">Mobile Number</label>
                        <input type="tel" id="mobile" name="mobile" pattern="[0-9]{10}" required>
                    </div>

                    <div class="form-group">
                        <label for="table">Table Number</label>
                        <input type="number" id="table" name="table" min="1" max="30" required>
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
    document.querySelector('form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('cartItems', JSON.stringify(<?php echo json_encode($cartItems); ?>));
        formData.append('totalAmount', '<?php echo $total; ?>');
        
        fetch('process-order.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                clearCart();
                window.location.href = 'order-confirmation.php';
            } else {
                alert('There was an error processing your order. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('There was an error processing your order. Please try again.');
        });
    });

    function clearCart() {
        fetch('clear-cart.php')
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    console.error('Error clearing cart');
                }
            });
    }
    </script>
</body>
</html>
