<?php
session_start();
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "admin";
$password = "1234";
$database = "aromiq";

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . mysqli_connect_error()
    ]);
    exit;
}

// Validate input
if (!isset($_GET['order_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing order ID'
    ]);
    exit;
}

$order_id = mysqli_real_escape_string($conn, $_GET['order_id']);

// Get order status using prepared statement
$stmt = $conn->prepare("SELECT order_status FROM tbl_orders WHERE order_id = ?");
$stmt->bind_param("s", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode([
        'success' => true,
        'status' => $row['order_status']
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Order not found'
    ]);
}

$stmt->close();
$conn->close();
?> 