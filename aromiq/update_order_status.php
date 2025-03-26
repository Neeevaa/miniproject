<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Always set content type to JSON
header('Content-Type: application/json');

try {
    session_start();
    if(!isset($_SESSION['uname'])){
        throw new Exception('Not authenticated');
    }

    // Log all request data for debugging
    error_log('POST data in update_order_status.php: ' . print_r($_POST, true));

    include 'connect.php';

    // Get order_id and status with fallbacks
    $order_id = '';
    $status = '';

    if (isset($_POST['order_id']) && !empty($_POST['order_id'])) {
        $order_id = trim($_POST['order_id']);
    } 
    
    if (isset($_POST['status']) && !empty($_POST['status'])) {
        $status = trim($_POST['status']);
    }

    // If still empty, try GET params (for direct links)
    if (empty($order_id) && isset($_GET['order_id']) && !empty($_GET['order_id'])) {
        $order_id = trim($_GET['order_id']);
    }
    
    if (empty($status) && isset($_GET['status']) && !empty($_GET['status'])) {
        $status = trim($_GET['status']);
    }

    // Final validation
    if (empty($order_id) || empty($status)) {
        throw new Exception('Order ID and status are required. Received: order_id=' . $order_id . ', status=' . $status);
    }

    // Validate status
    $allowed_statuses = ['Pending', 'Preparing', 'Ready', 'Plating', 'Served', 'Cancelled'];
    if (!in_array($status, $allowed_statuses)) {
        throw new Exception('Invalid status value: ' . $status);
    }

    // Log the update for debugging
    error_log("Updating order $order_id to status $status");

    // Verify the status column exists (just to double check)
    $describe_result = mysqli_query($conn, "DESCRIBE tbl_orders");
    $columns = [];
    while ($col = mysqli_fetch_assoc($describe_result)) {
        $columns[] = $col['Field'];
    }
    
    if (!in_array('status', $columns)) {
        throw new Exception("The 'status' column does not exist in tbl_orders table. Available columns: " . implode(", ", $columns));
    }

    // Only update tbl_orders, not tbl_order_items
    $sql = "UPDATE tbl_orders SET status = ? WHERE order_id = ?";
    error_log("Update SQL: $sql with status=$status, order_id=$order_id");
    
    $stmt = mysqli_prepare($conn, $sql);
    
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, "ss", $status, $order_id);
    $result = mysqli_stmt_execute($stmt);

    if (!$result) {
        throw new Exception('Failed to update order status: ' . mysqli_error($conn));
    }

    $affected_rows = mysqli_affected_rows($conn);
    error_log("Updated order status. Affected rows: $affected_rows");
    
    echo json_encode([
        'success' => true, 
        'message' => 'Order status updated to ' . $status,
        'details' => [
            'order_id' => $order_id,
            'status' => $status,
            'affected_rows' => $affected_rows
        ]
    ]);
    
} catch (Exception $e) {
    error_log('Error in update_order_status.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage(),
        'details' => [
            'post_data' => $_POST,
            'get_data' => $_GET
        ]
    ]);
} finally {
    if (isset($conn) && $conn) {
        mysqli_close($conn);
    }
}
?>