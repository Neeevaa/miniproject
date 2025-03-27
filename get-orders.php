<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection parameters
$servername = "localhost";
$username = "chef";
$password = "1234";
$database = "aromiq";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $database);

// Check connection
if (!$conn) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . mysqli_connect_error()]));
}

// Set header to return JSON
header('Content-Type: application/json');

try {
    // Get all orders sorted by date (newest first)
    // Using tbl_orders instead of orders
    $query = "SELECT o.id, o.order_id, o.customer_name, o.table_number, o.timestamp as order_date, 
                     o.status as order_status, o.payment_status 
              FROM tbl_orders o 
              ORDER BY o.timestamp DESC";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        throw new Exception(mysqli_error($conn));
    }
    
    $orders = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $orders[] = $row;
    }
    
    echo json_encode($orders);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

// Close connection
mysqli_close($conn);
?>
