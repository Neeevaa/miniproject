<?php
session_start();
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "admin";
$password = "1234";
$database = "aromiq";

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . mysqli_connect_error()
    ]);
    exit;
}

// Get order data from POST request
$customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
$table_number = mysqli_real_escape_string($conn, $_POST['table_number']);
$items = json_decode($_POST['items'], true);
$total_amount = floatval($_POST['total_amount']);

// Start transaction
mysqli_begin_transaction($conn);

try {
    // Insert order
    $order_query = "INSERT INTO tbl_orders (customer_name, table_number, total_amount, order_status) 
                    VALUES (?, ?, ?, 'Pending')";
    
    $stmt = $conn->prepare($order_query);
    $stmt->bind_param("ssd", $customer_name, $table_number, $total_amount);
    
    if (!$stmt->execute()) {
        throw new Exception("Error creating order: " . $stmt->error);
    }
    
    $order_id = $stmt->insert_id;
    
    // Insert order items
    $items_query = "INSERT INTO tbl_order_items (order_id, food_name, quantity, price, item_total) 
                    VALUES (?, ?, ?, ?, ?)";
    
    $items_stmt = $conn->prepare($items_query);
    
    foreach ($items as $item) {
        $item_total = $item['quantity'] * $item['price'];
        $items_stmt->bind_param("isids", 
            $order_id,
            $item['food_name'],
            $item['quantity'],
            $item['price'],
            $item_total
        );
        
        if (!$items_stmt->execute()) {
            throw new Exception("Error adding order items: " . $items_stmt->error);
        }
    }
    
    // Get the generated order number
    $order_number_query = "SELECT order_number FROM tbl_orders WHERE order_id = ?";
    $order_number_stmt = $conn->prepare($order_number_query);
    $order_number_stmt->bind_param("i", $order_id);
    $order_number_stmt->execute();
    $order_number_result = $order_number_stmt->get_result();
    $order_number = $order_number_result->fetch_assoc()['order_number'];
    
    // Commit transaction
    mysqli_commit($conn);
    
    echo json_encode([
        'success' => true,
        'message' => 'Order placed successfully',
        'order_id' => $order_id,
        'order_number' => $order_number
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// Close connections
$stmt->close();
$items_stmt->close();
$order_number_stmt->close();
$conn->close();
?> 