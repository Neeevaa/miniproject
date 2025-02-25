<?php
session_start();
if(!isset($_SESSION['uname'])){
    header("Location:'login.php'");
    exit;
}
$servername = "localhost";
$username = "kitchenadmin";
$password = "1234";
$database = "aromiq"; // Ensure this matches the database name you created

// Connect to MySQL server and select the database
$conn = mysqli_connect($servername, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
} 

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chef's Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <script defer src="script.js"></script>
</head>
<body >
    <div class="bg-dark py-2 px-4 w-100 d-flex justify-content-between align-items-center">
        <h4 class="text-white mb-0">Chef's Dashboard 👨‍🍳</h4>
        <a href="logout.php" class="btn btn-primary py-2 px-4" style="background-color: #fea116; border-color: #fea116;">Logout</a>
    </div>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 bg-light vh-100 d-flex flex-column align-items-center py-4">
                <div class="nav flex-column w-100">
                    <a href="#orders" class="nav-link text-dark active">📦 View Orders</a>
                    <a href="#editStatus" class="nav-link text-dark">✅ Update Order Status</a>
                </div>
            </div>
            
            <div class="col-md-10 bg-light py-4 " >
           <?php $kitchen_orders_query = "
    SELECT 
        o.order_id,
        o.customer_name,
        o.table_number,
        o.order_status,
        o.order_date,
        sc.food_name,
        sc.quantity,
        sc.price
    FROM tbl_orders o
    JOIN tbl_shoppingcart sc ON o.order_id = sc.id
    WHERE o.order_status IN ('Pending', 'In Progress')
    ORDER BY o.order_date ASC";

$kitchen_result = mysqli_query($conn, $kitchen_orders_query);

if ($kitchen_result) {
    echo '<section id="orders" class="mb-5">
            <h5>Kitchen Orders</h5>
            <table class="table table-bordered table-hover bg-white">
                <thead class="bg-dark text-light">
                    <tr>
                        <th>Order ID</th>
                        <th>Table</th>
                        <th>Items</th>
                        <th>Status</th>
                        <th>Waiting Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>';

    $current_order = null;
    $order_items = [];

    while ($row = mysqli_fetch_assoc($kitchen_result)) {
        if ($current_order !== $row['order_id']) {
            if ($current_order !== null) {
                outputKitchenOrderRow($current_order_data, $order_items);
            }
            $current_order = $row['order_id'];
            $current_order_data = [
                'order_id' => $row['order_id'],
                'table_number' => $row['table_number'],
                'status' => $row['order_status'],
                'order_date' => $row['order_date']
            ];
            $order_items = [];
        }
        $order_items[] = $row['quantity'] . 'x ' . $row['food_name'];
    }
    
    if ($current_order !== null) {
        outputKitchenOrderRow($current_order_data, $order_items);
    }

    echo '</tbody></table></section>';
}

function outputKitchenOrderRow($order, $items) {
    $waiting_time = round((time() - strtotime($order['order_date'])) / 60);
    $time_class = $waiting_time > 30 ? 'text-danger' : ($waiting_time > 15 ? 'text-warning' : 'text-success');
    
    echo '<tr>
            <td>' . htmlspecialchars($order['order_id']) . '</td>
            <td>' . htmlspecialchars($order['table_number']) . '</td>
            <td>' . htmlspecialchars(implode(', ', $items)) . '</td>
            <td>
                <select class="form-control kitchen-status" data-order-id="' . $order['order_id'] . '">
                    <option value="Pending" ' . ($order['status'] == 'Pending' ? 'selected' : '') . '>Pending</option>
                    <option value="In Progress" ' . ($order['status'] == 'In Progress' ? 'selected' : '') . '>In Progress</option>
                    <option value="Completed" ' . ($order['status'] == 'Completed' ? 'selected' : '') . '>Completed</option>
                </select>
            </td>
            <td class="' . $time_class . '">' . $waiting_time . ' mins</td>
            <td>
                <button class="btn btn-sm btn-success complete-kitchen-order" data-order-id="' . $order['order_id'] . '">Complete</button>
            </td>
          </tr>';
}
?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
