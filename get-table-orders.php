<?php
// Prevent any HTML error output
ini_set('display_errors', 0);
error_reporting(E_ERROR);

header('Content-Type: application/json');

session_start();
if(!isset($_SESSION['uname'])){
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$servername = "localhost";
$username = "chef";
$password = "1234";
$database = "aromiq";

// Connect to MySQL
$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed: ' . mysqli_connect_error()]);
    exit;
}

// Get table number from request
$table_number = isset($_GET['table']) ? $_GET['table'] : '';

if (empty($table_number)) {
    echo json_encode(['error' => 'Table number is required']);
    exit;
}

// Get active order for this table
$sql = "SELECT * FROM tbl_orders WHERE table_number = ? AND status != 'Completed' ORDER BY timestamp DESC LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $table_number);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    echo json_encode(['error' => 'Query failed: ' . mysqli_error($conn)]);
    exit;
}

if ($row = mysqli_fetch_assoc($result)) {
    // Check if there's a tbl_order_items table
    $has_items_table = false;
    $check_items_table = mysqli_query($conn, "SHOW TABLES LIKE 'tbl_order_items'");
    if (mysqli_num_rows($check_items_table) > 0) {
        $has_items_table = true;
    }
    
    // Map the database fields to the expected format
    $order = [
        'order_id' => $row['order_id'],
        'table_number' => $row['table_number'],
        'customer_name' => $row['customer_name'],
        'order_status' => $row['status'],
        'payment_status' => $row['payment_status'],
        'order_date' => $row['timestamp'],
        'feedback_submitted' => $row['feedback_submitted']
    ];
    
    // Get order items if the table exists
    if ($has_items_table) {
        $sql = "SELECT * FROM tbl_order_items WHERE order_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $row['order_id']);
        mysqli_stmt_execute($stmt);
        $items_result = mysqli_stmt_get_result($stmt);
        
        if (!$items_result) {
            echo json_encode(['error' => 'Failed to get order items: ' . mysqli_error($conn)]);
            exit;
        }
        
        $items = [];
        while ($item = mysqli_fetch_assoc($items_result)) {
            $items[] = $item;
        }
        
        $order['items'] = $items;
    } else {
        // Create dummy items based on the total for display purposes
        $items = [
            [
                'order_item_id' => 1,
                'food_name' => 'Order Total',
                'quantity' => 1,
                'price' => $row['total'],
                'status' => $row['status']
            ]
        ];
        $order['items'] = $items;
    }
    
    echo json_encode($order);
} else {
    echo json_encode(['error' => 'No active order found for this table']);
}

mysqli_close($conn);
?>