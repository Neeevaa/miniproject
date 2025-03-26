<?php
session_start();
if(!isset($_SESSION['uname'])){
    header("Location:login.php");
    exit;
}

// Check if order_id parameter is present
if (!isset($_GET['order_id'])) {
    echo '<div class="alert alert-danger">Missing order ID parameter</div>';
    exit;
}

// Get order ID
$order_id = intval($_GET['order_id']);

// Database connection
$servername = "localhost";
$username = "admin";
$password = "1234";
$database = "aromiq";

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    echo '<div class="alert alert-danger">Database connection failed</div>';
    exit;
}

// Fetch order details
$order_query = "SELECT * FROM tbl_orders WHERE order_id = ?";
$stmt = $conn->prepare($order_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_result = $stmt->get_result();

if ($order_result->num_rows === 0) {
    echo '<div class="alert alert-warning">Order not found</div>';
    exit;
}

$order = $order_result->fetch_assoc();

// Fetch order items
$items_query = "SELECT * FROM tbl_order_items WHERE order_id = ?";
$stmt = $conn->prepare($items_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items_result = $stmt->get_result();

// Close statement
$stmt->close();
?>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Order Information</h5>
            </div>
            <div class="card-body">
                <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order['order_id']); ?></p>
                <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['customer_phone'] ?? 'N/A'); ?></p>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($order['delivery_address'] ?? 'N/A'); ?></p>
                <p><strong>Order Date:</strong> <?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></p>
                <p><strong>Status:</strong> <span class="badge bg-primary"><?php echo htmlspecialchars($order['order_status']); ?></span></p>
                <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method'] ?? 'N/A'); ?></p>
                <p><strong>Payment Status:</strong> <?php echo htmlspecialchars($order['payment_status'] ?? 'N/A'); ?></p>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Order Summary</h5>
            </div>
            <div class="card-body">
                <p><strong>Subtotal:</strong> ₹<?php echo number_format((float)($order['total_amount'] - ($order['delivery_fee'] ?? 0)), 2); ?></p>
                <p><strong>Delivery Fee:</strong> ₹<?php echo number_format((float)($order['delivery_fee'] ?? 0), 2); ?></p>
                <p><strong>Total Amount:</strong> <span class="text-primary fw-bold">₹<?php echo number_format((float)$order['total_amount'], 2); ?></span></p>
                <p><strong>Special Instructions:</strong> <?php echo htmlspecialchars($order['special_instructions'] ?? 'None'); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0">Order Items</h5>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total = 0;
                if ($items_result->num_rows > 0) {
                    while ($item = $items_result->fetch_assoc()) {
                        $subtotal = $item['price'] * $item['quantity'];
                        $total += $subtotal;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['food_name']); ?></td>
                            <td>₹<?php echo number_format((float)$item['price'], 2); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>₹<?php echo number_format((float)$subtotal, 2); ?></td>
                        </tr>
                        <?php
                    }
                } else {
                    echo '<tr><td colspan="4" class="text-center">No items found for this order</td></tr>';
                }
                ?>
                <tr class="table-dark">
                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                    <td><strong>₹<?php echo number_format((float)$total, 2); ?></strong></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    <button class="btn btn-success me-2" onclick="updateOrderStatus(<?php echo $order_id; ?>, 'Completed')">
        Mark as Completed
    </button>
    <button class="btn btn-danger me-2" onclick="updateOrderStatus(<?php echo $order_id; ?>, 'Cancelled')">
        Cancel Order
    </button>
    <button class="btn btn-primary" onclick="printOrderDetails()">
        Print Order
    </button>
</div>

<script>
function updateOrderStatus(orderId, status) {
    if (confirm(`Are you sure you want to mark this order as ${status}?`)) {
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
}

function printOrderDetails() {
    window.print();
}
</script>
