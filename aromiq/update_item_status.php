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
    error_log('POST data in update_item_status.php: ' . print_r($_POST, true));

    include 'connect.php';

    // Get item_id and status
    $item_id = isset($_POST['item_id']) ? trim($_POST['item_id']) : '';
    $status = isset($_POST['status']) ? trim($_POST['status']) : '';

    // Final validation
    if (empty($item_id) || empty($status)) {
        throw new Exception('Item ID and status are required. Received: item_id=' . $item_id . ', status=' . $status);
    }
    
    // Specific check for the string "undefined"
    if ($item_id === "undefined") {
        throw new Exception('Invalid item ID: "undefined" was passed instead of a valid ID');
    }

    // Validate status
    $allowed_statuses = ['Pending', 'Preparing', 'Ready', 'Plating', 'Served', 'Cancelled'];
    if (!in_array($status, $allowed_statuses)) {
        throw new Exception('Invalid status value: "' . $status . '"');
    }

    // Convert numeric string to int if needed
    if (is_numeric($item_id)) {
        $item_id = intval($item_id);
    }

    // Log current table structure
    $describe_query = mysqli_query($conn, "DESCRIBE tbl_order_items");
    $all_columns = [];
    while ($col = mysqli_fetch_assoc($describe_query)) {
        $all_columns[] = $col['Field'];
    }
    error_log("tbl_order_items columns: " . implode(", ", $all_columns));

    // First, try to get the order_id for this item
    $sql = "SELECT order_id FROM tbl_order_items WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if (!$stmt) {
        throw new Exception('Failed to prepare SELECT statement: ' . mysqli_error($conn));
    }
    
    if (is_int($item_id)) {
        mysqli_stmt_bind_param($stmt, "i", $item_id);
    } else {
        mysqli_stmt_bind_param($stmt, "s", $item_id);
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $order_id = $row['order_id'];
        error_log("Found order_id: $order_id for item_id: $item_id");
    } else {
        throw new Exception('Item not found with ID: ' . $item_id);
    }

    // Check if tbl_order_items has a status column
    if (in_array('status', $all_columns)) {
        // Update the item status if the column exists
        $sql = "UPDATE tbl_order_items SET status = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        
        if (!$stmt) {
            throw new Exception('Failed to prepare UPDATE statement: ' . mysqli_error($conn));
        }
        
        if (is_int($item_id)) {
            mysqli_stmt_bind_param($stmt, "si", $status, $item_id);
        } else {
            mysqli_stmt_bind_param($stmt, "ss", $status, $item_id);
        }
        
        $result = mysqli_stmt_execute($stmt);
        
        if (!$result) {
            throw new Exception('Failed to update item status: ' . mysqli_error($conn));
        }
        
        $affected_rows = mysqli_affected_rows($conn);
        error_log("Updated item status. Affected rows: $affected_rows");
    } else {
        // If there's no status column in tbl_order_items, just update the order
        error_log("No status column in tbl_order_items. Updating only the main order.");
    }
    
    // Always update the main order status
    $sql = "UPDATE tbl_orders SET status = ? WHERE order_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $status, $order_id);
    $result = mysqli_stmt_execute($stmt);
    
    if (!$result) {
        throw new Exception('Failed to update order status: ' . mysqli_error($conn));
    }
    
    $affected_order_rows = mysqli_affected_rows($conn);
    error_log("Updated order status. Affected rows: $affected_order_rows");
    
    echo json_encode([
        'success' => true, 
        'message' => 'Item status updated successfully',
        'details' => [
            'item_id' => $item_id,
            'status' => $status,
            'order_id' => $order_id,
            'affected_order_rows' => $affected_order_rows
        ]
    ]);
    
} catch (Exception $e) {
    error_log('Error in update_item_status.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage(),
        'details' => [
            'post_data' => $_POST
        ]
    ]);
} finally {
    if (isset($conn) && $conn) {
        mysqli_close($conn);
    }
}
?>