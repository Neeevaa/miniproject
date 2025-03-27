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

    $servername = "localhost";
    $username = "chef";
    $password = "1234";
    $database = "aromiq";

    // Connect to MySQL
    $conn = mysqli_connect($servername, $username, $password, $database);

    if (!$conn) {
        throw new Exception('Database connection failed: ' . mysqli_connect_error());
    }

    // Verify the table exists
    $result = mysqli_query($conn, "SHOW TABLES LIKE 'tbl_orders'");
    if (mysqli_num_rows($result) == 0) {
        throw new Exception('The tbl_orders table does not exist in the database');
    }

    // Get active orders with exact column names from your DB
    $sql = "SELECT id, order_id, customer_name, table_number, subtotal, tax, total, status, payment_status, timestamp, feedback_submitted 
            FROM tbl_orders 
            WHERE status != 'Completed' 
            ORDER BY timestamp DESC";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        throw new Exception('Query failed: ' . mysqli_error($conn));
    }

    $orders = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $orders[] = [
            'id' => $row['id'],
            'order_id' => $row['order_id'],
            'table_number' => $row['table_number'],
            'customer_name' => $row['customer_name'],
            'subtotal' => $row['subtotal'],
            'tax' => $row['tax'],
            'total' => $row['total'],
            'order_status' => $row['status'], // Map for frontend compatibility
            'payment_status' => $row['payment_status'],
            'order_date' => $row['timestamp'], // Map for frontend compatibility
            'feedback_submitted' => $row['feedback_submitted']
        ];
    }

    echo json_encode($orders);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    if (isset($conn) && $conn) {
        mysqli_close($conn);
    }
}
?> 