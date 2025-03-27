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

    include 'connect.php';

    // Query to get the status of all tables
    $query = "SELECT table_number, status FROM (
                SELECT table_number, status, MAX(order_time) as latest_time
                FROM tbl_orders
                GROUP BY table_number
              ) as latest_orders";
    
    $result = mysqli_query($conn, $query);

    if (!$result) {
        throw new Exception('Failed to query table statuses: ' . mysqli_error($conn));
    }

    $tables = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $tables[] = [
            'number' => $row['table_number'],
            'status' => $row['status']
        ];
    }

    echo json_encode([
        'success' => true,
        'tables' => $tables
    ]);
    
} catch (Exception $e) {
    error_log('Error in get_table_statuses.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn) && $conn) {
        mysqli_close($conn);
    }
}
?> 