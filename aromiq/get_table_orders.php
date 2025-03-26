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
    error_log('GET data in get_table_orders.php: ' . print_r($_GET, true));
    error_log('POST data in get_table_orders.php: ' . print_r($_POST, true));

    include 'connect.php';

    // Get table number with multiple fallbacks
    $table_number = '';

    // Try GET first (most common for this endpoint)
    if (isset($_GET['table']) && !empty($_GET['table'])) {
        $table_number = trim($_GET['table']);
    }

    // If GET failed, try POST
    if (empty($table_number) && isset($_POST['table']) && !empty($_POST['table'])) {
        $table_number = trim($_POST['table']);
    }

    // Final validation
    if (empty($table_number)) {
        throw new Exception('Table number is required. No table number provided in request.');
    }

    // Log the request
    error_log("Getting orders for table: $table_number");

    // Verify the table exists
    $result = mysqli_query($conn, "SHOW TABLES LIKE 'tbl_orders'");
    if (mysqli_num_rows($result) == 0) {
        throw new Exception('The tbl_orders table does not exist in the database');
    }

    // Get active order for this table
    $sql = "SELECT id, order_id, customer_name, customer_email, customer_phone, table_number, 
                  subtotal, tax, total, status, payment_status, payment_method, timestamp, feedback_submitted 
           FROM tbl_orders 
           WHERE table_number = ? AND status != 'Completed' 
           ORDER BY timestamp DESC LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, "s", $table_number);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (!$result) {
        throw new Exception('Query failed: ' . mysqli_error($conn));
    }

    if ($row = mysqli_fetch_assoc($result)) {
        // Map the database fields and provide both original and mapped names for compatibility
        $order = [
            'id' => $row['id'],
            'order_id' => $row['order_id'],
            'table_number' => $row['table_number'],
            'customer_name' => $row['customer_name'],
            'customer_email' => $row['customer_email'],
            'customer_phone' => $row['customer_phone'],
            'subtotal' => $row['subtotal'],
            'tax' => $row['tax'],
            'total' => $row['total'],
            'status' => $row['status'],                    // Original name
            'order_status' => $row['status'],              // Mapped name for compatibility
            'payment_status' => $row['payment_status'],
            'payment_method' => $row['payment_method'],
            'timestamp' => $row['timestamp'],              // Original name
            'order_date' => $row['timestamp'],             // Mapped name for compatibility
            'feedback_submitted' => $row['feedback_submitted']
        ];
        
        // Check if there's a tbl_order_items table
        $check_items_table = mysqli_query($conn, "SHOW TABLES LIKE 'tbl_order_items'");
        if (mysqli_num_rows($check_items_table) > 0) {
            // Get items for this order
            $sql = "SELECT * FROM tbl_order_items WHERE order_id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $row['order_id']);
            mysqli_stmt_execute($stmt);
            $items_result = mysqli_stmt_get_result($stmt);
            
            if (!$items_result) {
                throw new Exception('Failed to get order items: ' . mysqli_error($conn));
            }
            
            $items = [];
            while ($item = mysqli_fetch_assoc($items_result)) {
                $items[] = $item;
            }
            
            $order['items'] = $items;
        } else {
            // Create dummy item based on order total
            $order['items'] = [
                [
                    'id' => 1,
                    'order_id' => $row['order_id'],
                    'food_name' => 'Order Total',
                    'quantity' => 1,
                    'price' => $row['total'],
                    'status' => $row['status']
                ]
            ];
        }
        
        echo json_encode($order);
    } else {
        throw new Exception('No active order found for table ' . $table_number);
    }
    
} catch (Exception $e) {
    error_log('Error in get_table_orders.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'details' => [
            'get_data' => $_GET,
            'post_data' => $_POST
        ]
    ]);
} finally {
    if (isset($conn) && $conn) {
        mysqli_close($conn);
    }
}
?>