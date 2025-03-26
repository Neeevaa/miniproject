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

// Check if required parameters are provided
if (!isset($_POST['order_id']) || empty($_POST['order_id']) || !isset($_POST['status']) || empty($_POST['status'])) {
    echo json_encode(['success' => false, 'message' => 'Order ID and status are required']);
    exit;
}

$orderId = mysqli_real_escape_string($conn, $_POST['order_id']);
$status = mysqli_real_escape_string($conn, $_POST['status']);

// Validate status
$validStatuses = ['Pending', 'Preparing', 'Ready', 'Plating', 'Served'];
if (!in_array($status, $validStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

try {
    // Update order status
    // Using tbl_orders instead of orders and status instead of order_status
    $query = "UPDATE tbl_orders SET status = '$status' WHERE order_id = '$orderId'";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        throw new Exception(mysqli_error($conn));
    }
    
    // If status is Served or Ready, update all items to the same status
    // This requires your tbl_order_items table - adjust as needed
    if ($status == 'Served' || $status == 'Ready') {
        $itemStatus = $status == 'Served' ? 'Served' : 'Plating';
        // Assuming you have a status column in tbl_order_items
        $itemQuery = "UPDATE tbl_order_items SET status = '$itemStatus' WHERE order_id = '$orderId'";
        $itemResult = mysqli_query($conn, $itemQuery);
        
        if ($itemResult === false) {
            // Just log this error but continue - the item table might not exist or might have a different structure
            error_log("Could not update item status: " . mysqli_error($conn));
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'Order status updated successfully']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

// Close connection
mysqli_close($conn);
?>
