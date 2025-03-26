<?php
header('Content-Type: application/json');

// Connect to database
$servername = "localhost";
$username = "kitchenadmin";
$password = "1234";
$database = "aromiq";

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die(json_encode(['error' => 'Connection failed: ' . mysqli_connect_error()]));
}

if (isset($_GET['order_id'])) {
    $order_id = mysqli_real_escape_string($conn, $_GET['order_id']);
    
    $query = "SELECT order_status, order_date FROM tbl_orders WHERE order_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode([
            'status' => 'success',
            'order_status' => $row['order_status'],
            'order_date' => $row['order_date']
        ]);
    } else {
        echo json_encode(['error' => 'Order not found']);
    }
    
    $stmt->close();
} else {
    echo json_encode(['error' => 'No order ID provided']);
}

$conn->close();
?>