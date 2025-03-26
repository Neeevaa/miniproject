<?php
session_start();
header('Content-Type: application/json');

try {
    $conn = mysqli_connect("localhost", "kitchenadmin", "1234", "aromiq");
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    $query = "SELECT o.order_id, o.table_number, o.order_status, o.order_date, 
                     GROUP_CONCAT(i.food_name SEPARATOR ', ') AS items
              FROM tbl_orders o
              JOIN tbl_orderitems i ON o.order_id = i.order_id
              WHERE o.order_status != 'Completed'
              GROUP BY o.order_id";
    
    $result = mysqli_query($conn, $query);
    
    $orders = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $orders[] = $row;
    }
    
    echo json_encode($orders);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>