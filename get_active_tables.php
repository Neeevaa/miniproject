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

    // Modified query to exclude tables with served orders
    $query = "SELECT table_number, feedback_submitted, status 
              FROM tbl_orders 
              WHERE status NOT IN ('Cancelled', 'Completed', 'Served') 
              AND table_number IN (
                  SELECT table_number 
                  FROM tbl_orders 
                  GROUP BY table_number 
                  HAVING MAX(status) NOT IN ('Cancelled', 'Completed', 'Served')
              )";
    
    $result = mysqli_query($conn, $query);

    if (!$result) {
        throw new Exception('Failed to query active tables: ' . mysqli_error($conn));
    }

    $activeTables = array();
    while ($row = mysqli_fetch_assoc($result)) {
        // Only include tables that are not served
        if ($row['status'] !== 'Served') {
            $activeTables[] = [
                'table_number' => $row['table_number'],
                'feedback_submitted' => $row['feedback_submitted'] ?? 'No',
                'status' => $row['status']
            ];
        }
    }

    echo json_encode([
        'success' => true,
        'tables' => $activeTables
    ]);
    
} catch (Exception $e) {
    error_log('Error in get_active_tables.php: ' . $e->getMessage());
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