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

    // Use connection from connect.php if available, otherwise create a new connection
    if (!isset($conn)) {
        $servername = "localhost";
        $username = "chef";
        $password = "1234";
        $database = "aromiq";

        // Connect to MySQL
        $conn = mysqli_connect($servername, $username, $password, $database);

        if (!$conn) {
            throw new Exception('Database connection failed: ' . mysqli_connect_error());
        }
    }

    // Query to find orders with feedback submitted
    $sql = "SELECT order_id, table_number, status, feedback_submitted 
            FROM tbl_orders 
            WHERE feedback_submitted = 'Yes' 
            AND status = 'Served'";
    
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        throw new Exception('Query failed: ' . mysqli_error($conn));
    }

    $feedback_orders = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $feedback_orders[] = [
            'order_id' => $row['order_id'],
            'table_number' => $row['table_number'],
            'status' => $row['status'],
            'feedback_submitted' => $row['feedback_submitted']
        ];
    }

    echo json_encode([
        'success' => true,
        'feedback_orders' => $feedback_orders
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    // Close connection if we created it in this file
    if (isset($conn) && $conn && !isset($GLOBALS['conn'])) {
        mysqli_close($conn);
    }
}
?> 