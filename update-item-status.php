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
if (!isset($_POST['item_id']) || empty($_POST['item_id']) || !isset($_POST['status']) || empty($_POST['status'])) {
    echo json_encode(['success' => false, 'message' => 'Item ID and status are required']);
    exit;
}

$itemId = mysqli_real_escape_string($conn, $_POST['item_id']);
$status = mysqli_real_escape_string($conn, $_POST['status']);

// Validate status
$validStatuses = ['Pending', 'Preparing', 'Plating', 'Served'];
if (!in_array($status, $validStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

try {
    // This function depends on your tbl_order_items structure
    // Assuming it has an id field and a status field and is linked to tbl_orders
    
    // First, get the order_id for this item
    $getOrderQuery = "SELECT order_id FROM tbl_order_items WHERE id = $itemId";
    $orderResult = mysqli_query($conn, $getOrderQuery);
    
    if (!$orderResult || mysqli_num_rows($orderResult) == 0) {
        // If the query fails or returns no results, throw an exception
        throw new Exception('Item not found or tbl_order_items table missing');
    }
    
    $orderRow = mysqli_fetch_assoc($orderResult);
    $orderId = $orderRow['order_id'];
    
    // Update item status
    $query = "UPDATE tbl_order_items SET status = '$status' WHERE id = $itemId";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        throw new Exception(mysqli_error($conn));
    }
    
    // Check if all items for this order are in the same status
    $checkQuery = "SELECT COUNT(*) as total, 
                  SUM(CASE WHEN status = '$status' THEN 1 ELSE 0 END) as matching
                  FROM tbl_order_items 
                  WHERE order_id = '$orderId'";
    
    $checkResult = mysqli_query($conn, $checkQuery);
    
    if (!$checkResult) {
        throw new Exception(mysqli_error($conn));
    }
    
    $counts = mysqli_fetch_assoc($checkResult);
    
    // If all items have the same status, update the order status accordingly
    if ($counts['total'] == $counts['matching']) {
        // Map item status to order status
        $orderStatus = $status;
        if ($status == 'Plating') {
            $orderStatus = 'Ready';
        }
        
        $updateOrderQuery = "UPDATE tbl_orders SET status = '$orderStatus' WHERE order_id = '$orderId'";
        $updateOrderResult = mysqli_query($conn, $updateOrderQuery);
        
        if (!$updateOrderResult) {
            throw new Exception(mysqli_error($conn));
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'Item status updated successfully']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

// Close connection
mysqli_close($conn);
?>
